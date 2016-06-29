<?
/**
* Implements playlist feature
*
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define('CC_EVENT_FILTER_CART_NSFW','cartnsfw');

require_once('cchost_lib/ccextras/cc-cart-table.inc');
require_once('mixter-lib/lib/playlist.php');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCEventsPlaylists', 'OnMapUrls'));
CCEvents::AddHandler(CC_EVENT_USER_DELETED,       array( 'CCEventsPlaylists', 'OnUserDelete'));
CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP,    array( 'CCEventsPlaylists', 'OnApiQuerySetup')); 
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,      array( 'CCEventsPlaylists', 'OnUploadDelete'));
CCEvents::AddHandler(CC_EVENT_SEARCH_META,        array( 'CCEventsPlaylists', 'OnSearchMeta'));
CCEvents::AddHandler(CC_EVENT_FILTER_MACROS,      array( 'CCEventsPlaylists', 'OnFilterMacros'));

class CCEventsPlaylists
{

    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','playlist','create'),
            array( 'CCAPIPlaylist', 'APICreatePlaylist'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '', _('Create a new playlist'), CC_AG_PLAYLIST );
    
        CCEvents::MapUrl( ccp('api','playlist','create','dynamic'),
            array( 'CCAPIPlaylist', 'APICreateDynamic'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__) ,
            '', _('Create a new dynamic playlist (ajax api)'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','delete'),
            array( 'CCAPIPlaylist', 'APIDelete'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Delete playlist'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','update'),
            array( 'CCAPIPlaylist', 'APIUpdate'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Update playlist field'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','update','dynamic'),
            array( 'CCAPIPlaylist', 'APIUpdateDynamic'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Update playlist dynamic query field'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','reorder'),
            array( 'CCAPIPlaylist', 'APIReorder'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Reorder playlist tracks'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','addtrack'),
            array( 'CCAPIPlaylist', 'APIAdd'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{upload_id},{playlist_id}', _('Add upload to playlist'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','removetrack'),
            array( 'CCAPIPlaylist', 'APIRemove'),  CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
             '{upload_id},{playlist_id}', _('Remove upload from playlist'), CC_AG_PLAYLIST );
    
    }

    function OnApiQuerySetup( &$args, &$queryObj, $requiresValidation )
    {
        if( !empty($args['dataview']) && ($args['dataview'] == 'passthru') )
            return;

        if( !empty($args['playlist_type']) )
        {
            if( !empty($args['user']) )
            {
                $user_id = CCUser::IDFromName($args['user']);
                if( empty($user_id) )
                    return; // er, no error?
                $lib = new CCLibPlaylists();
                $status = $lib->PlaylistForUser($user_id,$args['playlist_type'],false,null);
                if( !$status->ok() ) {
                    return; // er, what
                }
                $args['playlist'] = $status->data;
                unset($args['user']);
            }
        }

        if( $args['format'] == 'playlist' )
        { 
            if( empty($args['template']) )
                return;
             $queryObj->GetSourcesFromTemplate($args['template']);
        }

        if( empty($args['playlist']) ) 
            return;
        
        $id = sprintf('0%d',$args['playlist']);
        $ok = $id > 0;
        if( $ok )
        {
            $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_cart WHERE cart_id='.$id);
            $ok = !empty($row);
        }
        
        if( !$ok )
            CCUtil::Send404(true,'Invalid playlist id');

        if( !empty($row['cart_subtype']) && ($row['cart_subtype'] == 'default') )
        {
            $args += $_GET;
        }
        elseif( $row['cart_dynamic'] )
        {
            $arg_limit = $args['limit'];
            parse_str($row['cart_dynamic'],$cargs);
            $args = array_merge($args, $cargs);
            $args['title'] = $row['cart_name'];
            if( $arg_limit ) {
                $args['limit'] = $arg_limit;
            }
            if( !empty($args['limit']) )
            {
                if( $args['limit'] == 'default' )
                {
                    $page =& CCPage::GetPage();
                    $args['limit'] = $page->GetPageQueryLimit();
                }
            }
        }
        else
        {
            //if( empty($args['sort']) )
            $queryObj->sql_p['order'] = 'cart_item_order';
            $queryObj->sql_p['limit'] = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_cart_items WHERE cart_item_cart='.$id);
            $queryObj->where[] = 'cart_item_cart = '.$id;
            $queryObj->sql_p['joins'][] = 'cc_tbl_cart_items ON cart_item_upload=upload_id';
        }

    }

    function OnSearchMeta(&$search_meta)
    {
        $count = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_cart');
        if( $count < 2 ) // dynamic playlist
            return;
            
        $search_meta[] = 
            array(
                'template'   => 'search_playlists',
                'title'      => 'str_search_playlists',
                'datasource' => 'cart',
                'group'      => 'cart',
                'match'      => 'cart_name,cart_tags,cart_desc ',
            );
    }

    function OnFilterMacros(&$records)
    {
        if( !cc_playlist_enabled() )
            return;

        $k = array_keys($records);
        $c = count($k);

        if( $c && !isset($records[$k[0]]['upload_num_playlists']) )
        {
            // NOTE: this code should probably in cc-filter somewhere ? maybe ?

            // there's no playlist info in the record, we
            // have to dig it out

            if( !isset($records[$k[0]]['upload_id']) )
            {
                // there's nothing we can do...
                return;
            }
            $ids = array();
            for( $i = 0; $i < $c; $i++ )
                $ids[] = $records[$k[$i]]['upload_id'];
            $plcs = CCDatabase::QueryRows( 'SELECT upload_id,upload_num_playlists FROM cc_tbl_uploads WHERE upload_id IN (' .
                                              join(',',$ids) . ')' );
            $plcounts = array();
            foreach( $plcs as $plc )
                $plcounts[$plc['upload_id']] = $plc['upload_num_playlists'];
            for( $i = 0; $i < $c; $i++ )
            {
                $R =& $records[$k[$i]];
                $R['upload_num_playlists'] = $plcounts[ $R['upload_id'] ];
            }
        }

        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$k[$i]];

            if( empty($R['upload_num_playlists']) )
                continue;

            $R['file_macros'][] = 'file_macros.php/print_num_playlists';
        }
    }

    /**
    * Event hander for {@link CC_EVENT_DELETE_UPLOAD}
    * 
    * @param array $record Upload database record
    */
    function OnUploadDelete(&$record)
    {
        $lib = new CCLibPlaylists();
        $lib->RemoveUploadFromAllPlaylists($record['upload_id']);
    }

    function OnUserDelete($user_id)
    {
        $lib = new CCLibPlaylists();
        $lib->RemovePlaylistsForUser($user_id);
    }

}

class CCAPIPlaylist
{
    function APICreatePlaylist($upload_id='') {
        
        $upload_id = CCUtil::CleanNumber($upload_id);
        CCUtil::Strip($_REQUEST);

        if( !empty($_REQUEST['cart_name']) ) {
            $name = CCUtil::Strip($_REQUEST['cart_name']);
        } else if( !empty($_REQUEST['name']) ) {
            $name = CCUtil::Strip($_REQUEST['name']);
        }

        $lib = new CCLibPlaylists();

        $desc = empty($_REQUEST['cart_desc']) ? '' : CCUtil::Strip($_REQUEST['cart_desc']);

        global $CC_GLOBALS;

        $status = $lib->CreatePlaylistSimple( CCUser::CurrentUser(), 
                                              $CC_GLOBALS['user_real_name'], 
                                              $upload_id, 
                                              $name,
                                              $desc );

        CCUtil::ReturnAjaxObj($status);
    }

    function APICreateDynamic()
    {
        CCUtil::Strip($_REQUEST);
        $name   = $_REQUEST['title'];
        $tags   = empty($_REQUEST['tags']) ? '' : $_REQUEST['tags'];
        $lib    = new CCLibPlaylists();
        $status = $lib->CreatePlaylist('playlist',$name,'',CCUser::CurrentUser(),$_REQUEST,$tags);
        CCUtil::ReturnAjaxObj($status);
    }

    function APIUpdateDynamic($playlist_id) 
    {
        $playlist_id = CCUtil::CleanNumber($playlist_id);
        CCUtil::Strip($_GET);
        $lib = new CCLibPlaylists();
        $status = $lib->UpdateDynamic( CCUser::CurrentUser(), $playlist_id, $_GET );
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIUpdate($playlist_id)
    {
        $playlist_id = CCUtil::CleanNumber($playlist_id);        
        CCUtil::Strip($_REQUEST);
        
        $lib = new CCLibPlaylists();

        $props = array();
        if( !empty($_REQUEST['name']) ) {
            $props['name'] = $_REQUEST['name'];
        }
        if( array_key_exists('tags',$_REQUEST) ) {
            $props['tags'] = $_REQUEST['tags'];
        }
        if( array_key_exists('description',$_REQUEST) ) {
            $props['description'] = $_REQUEST['description'];
        }
        if( !empty($_REQUEST['isFeatured']) ) {
            $props['featured'] = $_REQUEST['isFeatured'] == 'true';
        }

        $status = $lib->UpdateProperties(CCUser::CurrentUser(),$playlist_id,$props);

        CCUtil::ReturnAjaxObj($status);        

    }

    function APIDelete($playlist_id)
    {
        $playlist_id = CCUtil::CleanNumber($playlist_id);
        $lib = new CCLibPlaylists();
        $status = $lib->DeletePlaylist( CCUser::CurrentUser(), $playlist_id );
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIReorder($playlist_id)
    {
        $playlist_id = CCUtil::CleanNumber($playlist_id);
        $lib = new CCLibPlaylists();
        $status = $lib->Reorder( CCUser::CurrentUser(), $playlist_id, $_GET['fo'] );
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIAdd($upload_id,$playlist_id)
    {
        $upload_id = CCUtil::CleanNumber($upload_id);
        $playlist_id = CCUtil::CleanNumber($playlist_id);
        $lib = new CCLibPlaylists();
        $status = $lib->AddTrackToPlaylist( CCUser::CurrentUser(), $upload_id, $playlist_id );
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIRemove($upload_id='',$playlist_id='')
    {
        $upload_id = CCUtil::CleanNumber($upload_id);
        $playlist_id = CCUtil::CleanNumber($playlist_id);
        $lib = new CCLibPlaylists();
        $status = $lib->RemoveTrackFromPlaylist( CCUser::CurrentUser(), $upload_id, $playlist_id );
        CCUtil::ReturnAjaxObj($status);        
    }

}

?>