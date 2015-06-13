<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-playlist.php 12917 2009-07-14 01:38:07Z fourstones $
*
*/

/**
* Implements playlist feature
*
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define('CC_EVENT_FILTER_CART_MENU','cartmenu');
define('CC_EVENT_FILTER_CART_NSFW','cartnsfw');
/**
*
*/
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCPlaylists',  'OnMapUrls'),          'cchost_lib/ccextras/cc-playlist.inc' );
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,      array( 'CCPlaylists',  'OnUploadDelete'),     'cchost_lib/ccextras/cc-playlist.inc' );

CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU,        array( 'CCPlaylistHV', 'OnUploadMenu'));
CCEvents::AddHandler(CC_EVENT_USER_PROFILE_TABS,  array( 'CCPlaylistHV', 'OnUserProfileTabs'));
CCEvents::AddHandler(CC_EVENT_FILTER_MACROS,      array( 'CCPlaylistHV', 'OnFilterMacros')      );
CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE,array( 'CCPlaylistHV', 'OnFilterUserProfile') );
CCEvents::AddHandler(CC_EVENT_SEARCH_META,        array( 'CCPlaylistHV',  'OnSearchMeta') );
CCEvents::AddHandler(CC_EVENT_FILTER_PLAY_URL,    array( 'CCPlaylistHV', 'OnFilterPlayURL'));
CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP,    array( 'CCPlaylistHV', 'OnApiQuerySetup')); 

CCEvents::AddHandler(CC_EVENT_FILTER_CART_MENU,   array( 'CCPlaylistBrowse', 'OnFilterCartMenu'), 'cchost_lib/ccextras/cc-playlist-browse.inc' );
CCEvents::AddHandler(CC_EVENT_FILTER_CART_NSFW,   array( 'CCPlaylistBrowse', 'OnFilterCartNSFW'), 'cchost_lib/ccextras/cc-playlist-browse.inc' );

CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,         array( 'CCPlaylistManage',  'OnAdminMenu'),     'cchost_lib/ccextras/cc-playlist-forms.inc' );
CCEvents::AddHandler(CC_EVENT_USER_DELETED,       array( 'CCPlaylistManage' , 'OnUserDelete'),     'cchost_lib/ccextras/cc-playlist-forms.inc' );


class CCPlaylistHV 
{
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
                $args['playlist'] = $this->_get_type_for_user($user_id,$args['playlist_type'],false,null);
                
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
            parse_str($row['cart_dynamic'],$cargs);
            $args = array_merge($args, $cargs);
            $args['title'] = $row['cart_name'];
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

    function OnFilterPlayURL( &$records ) 
    {
        if( !cc_is_player_embedded() )
            return;

        global $CC_GLOBALS;
        if( empty($CC_GLOBALS['embedded_player']) )
        {
            $CC_GLOBALS['embedded_player'] = 'ccskins/shared/players/player_none.php';
        }
        $is_native = $CC_GLOBALS['embedded_player'] == 'ccskins/shared/players/player_native.php';
        $c = count($records);
        $k = array_keys($records);
        for( $i = 0; $i < $c; $i++ )
        {
            $rec =& $records[$k[$i]];
            $cf = count($rec['files']);
            $ck = array_keys($rec['files']);
            for( $n = 0; $n < $cf; $n++ )
            {
                $R =& $rec['files'][$ck[$n]];

                foreach( array('file_extra','file_format_info') as $f )
                    if( is_string($R[$f]) )
                        $R[$f] = unserialize($R[$f]);
                if( $R['file_format_info']['media-type'] != 'audio' )
                    continue;
                if( !$is_native ||
                    (
                        !empty($R['file_format_info']['sr']) && 
                        ($R['file_format_info']['format-name'] == 'audio-mp3-mp3') && 
                        1 // ($R['file_format_info']['sr'] == '44k')
                    )
                  )
                {
                    $rec['fplay_url'] = $R['download_url'];
                    break;
                }
            }
        }
    }


    /**
    * Event handler for {@link CC_EVENT_FILTER_USER_PROFILE}
    *
    * Add extra data to a user row before display
    *
    * @param array &$record User record to massage
    */
    function OnFilterUserProfile(&$rows)
    {
        if( !cc_playlist_enabled() )
            return;

        $row =& $rows[0];
        $sql =<<<EOF
        SELECT sum( upload_num_playlists )
            FROM cc_tbl_uploads
            WHERE upload_user = {$row['user_id']}
EOF;
        $count = CCDatabase::QueryItem($sql);
        if( !$count )
            return;

        $url = url_args( ccl('playlist','browse'), 'u=' . $row['user_name'] );

        if( $count == 1 )
            $value = array('str_pl_user_num',$row['user_real_name'], "<a href=\"$url\">", '</a>');
        else
            $value = array('str_pl_user_nums',$row['user_real_name'], "<a href=\"$url\">",$count,'</a>');

        $row['user_fields'][] = array( 'label' => 'str_playlists', 
                                       'value' => $value
                                      );

    }

    function OnUserProfileTabs( &$tabs, &$record )
    {
        if( !cc_playlist_enabled() )
            return;

        if( empty($record['user_id']) )
        {
            $tabs['playlists'] = 'Playlists';
            return;
        }

        require_once('cchost_lib/ccextras/cc-cart-table.inc');
        $carts = new CCPlaylist(false);
        $w['cart_user'] = $record['user_id'];
        $num = $carts->CountRows($w);
        if( empty($num) )
            return;

        $tabs['playlists'] = array(
                    'text' => 'str_playlists',
                    'help' => 'str_playlists',
                    'tags' => 'playlists',
                    'access' => CC_DONT_CARE_LOGGED_IN,
                    'function' => 'url',
                    'user_cb' => array( 'CCPlaylistBrowse', 'User' ),
                    'user_cb_mod' => 'cchost_lib/ccextras/cc-playlist-browse.inc',
            );
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_MENU}
    * 
    * The handler is called when a menu is being displayed with
    * a specific record. All dynamic changes are made here
    * 
    * @param array $menu The menu being displayed
    * @param array $record The database record the menu is for
    */
    function OnUploadMenu(&$menu,&$record)
    {
        if( !cc_playlist_enabled() || !CCUser::IsLoggedIn() || empty($record['upload_published']) || !empty($record['upload_banned']) )
            return;

        $menu['playlist_menu'] = 
                     array(  'menu_text'  => _('Add to Playlist'),
                             'weight'     => 130,
                             'group_name' => 'playlist',
                             'access'     => CC_MUST_BE_LOGGED_IN,
                        );
        $parent_id = 'playlist_menu_' . $record['upload_id'];
        $menu['playlist_menu']['parent_id'] = $parent_id;
        $menu['playlist_menu']['action'] = "javascript://{$parent_id}";
        $menu['playlist_menu']['id']     = 'commentcommand';
        $menu['playlist_menu']['class']  = "cc_playlist_button";
        require_once('cchost_lib/ccextras/cc-playlist.inc');
        $pls = new CCPlaylists();
        $menu['playlist_menu']['mi'] =& $pls->_playlist_with($record['upload_id']);

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

    function _get_type_for_user( $user_id, $type, $auto_create, $name )
    {
        $sql = "SELECT cart_id FROM cc_tbl_cart WHERE cart_user = '{$user_id}' and cart_subtype = '{$type}'";
        $cart_id = CCDatabase::QueryItem($sql);
        if( empty($cart_id) && $auto_create)
        {
            require_once('cchost_lib/ccextras/cc-playlist.inc');
            $api = new CCPlaylists();
            //global $CC_GLOBALS;
            //$name = sprintf( _("%s's Favorites"), $CC_GLOBALS['user_real_name'] );
            $cart_id = $api->_create_playlist($type,$name);
        }
        return $cart_id;
    }

}

function cc_is_player_embedded()
{
    global $CC_GLOBALS;

    return $CC_GLOBALS['embedded_player'] != 'ccskins/shared/players/player_none.php';
}

function cc_playlist_enabled()
{
    global $CC_GLOBALS;

    return !empty($CC_GLOBALS['enable_playlists']);
}

?>
