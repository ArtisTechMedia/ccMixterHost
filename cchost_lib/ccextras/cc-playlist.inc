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
* $Id: cc-playlist.inc 13103 2009-07-26 23:07:55Z fourstones $
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

define('TEST_UPLOAD_YES', true);
define('TEST_UPLOAD_NO',  false);
define('TEST_PLAYLIST_YES', true);
define('TEST_PLAYLIST_NO', false);
define('TEST_OWNER_YES',   true);
define('TEST_OWNER_NO', false);

/**
*
*/
require_once('cchost_lib/ccextras/cc-cart-table.inc');

class CCPlaylists
{
    function OnMapUrls()
    {
        /* new API */

        CCEvents::MapUrl( ccp('api','playlist','create'),
            array( 'CCPlaylists', 'APICreatePlaylist'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '', _('Create a new playlist'), CC_AG_PLAYLIST );
    
        CCEvents::MapUrl( ccp('api','playlist','create','dynamic'),
            array( 'CCPlaylists', 'APICreateDynamic'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__) ,
            '', _('Create a new dynamic playlist (ajax api)'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','delete'),
            array( 'CCPlaylists', 'APIDelete'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Delete playlist'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','update'),
            array( 'CCPlaylists', 'APIUpdate'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Update playlist field'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','update','dynamic'),
            array( 'CCPlaylists', 'APIUpdateDynamic'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Update playlist dynamic query field'), CC_AG_PLAYLIST );


        CCEvents::MapUrl( ccp('api','playlist','reorder'),
            array( 'CCPlaylists', 'APIReorder'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{playlist_id}', _('Reorder playlist tracks'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','removetrack'),
            array( 'CCPlaylists', 'APIRemove'),  CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
             '{upload_id},{playlist_id}', _('Remove upload from playlist'), CC_AG_PLAYLIST );
    
        CCEvents::MapUrl( ccp('api','playlist','feature'),
            array( 'CCPlaylists', 'APIFeature'),  CC_ADMIN_ONLY,   ccs(__FILE__),
             '{playlist_id}', _('toggle feature status of playlist'), CC_AG_PLAYLIST );

        /* legacy */

        CCEvents::MapUrl( ccp('api','playlist','add'),
            array( 'CCPlaylists', 'Add'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{upload_id},{playlist_id}', _('Add upload to playlist'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','type'),
            array( 'CCPlaylists', 'GetType'),  CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '{user_name}/{type}', _('Get a specific type of playlist (e.g. favorites'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('api','playlist','bump'),
            array( 'CCPlaylists', 'Bump'),    CC_DONT_CARE_LOGGED_IN, ccs(__FILE__) ,
            '{update_id}', _('Bump playlist count'), CC_AG_PLAYLIST ); 

        CCEvents::MapUrl( ccp('api','playlist','with'),
            array( 'CCPlaylists', 'With'),    CC_MUST_BE_LOGGED_IN,   ccs(__FILE__) ,
            '{upload_id}', _('Return playlists with-w/o this upload'), CC_AG_PLAYLIST ); 

        CCEvents::MapUrl( ccp('api','playlist','remove'),
            array( 'CCPlaylists', 'Remove'),  CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
             '{upload_id},{playlist_id}', _('Remove upload from playlist'), CC_AG_PLAYLIST );

        CCEvents::MapUrl( ccp('playlist','create'),
            array( 'CCPlaylists', 'Create'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__) ,
            '', _('Display create playlist form'), CC_AG_PLAYLIST );
       
        CCEvents::MapUrl( ccp('playlist','save'),
            array( 'CCPlaylists', 'Save'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__) ,
            '', _('Save dynamic playlist'), CC_AG_PLAYLIST );
    
        CCEvents::MapUrl( ccp('playlist','editquery'),
            array( 'CCPlaylists', 'EditQuery'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__) ,
            '', _('Display edit query form'), CC_AG_PLAYLIST ); 

        CCEvents::MapUrl( ccp('playlist','editorder'),
            array( 'CCPlaylists', 'EditOrder'),     CC_MUST_BE_LOGGED_IN,   ccs(__FILE__) ,
            '', _('Display reorder playlist form'), CC_AG_PLAYLIST ); 

        CCEvents::MapUrl( ccp('playlist','browse'),
            array( 'CCPlaylistBrowse', 'Browse'),   CC_DONT_CARE_LOGGED_IN,   
            'cchost_lib/ccextras/cc-playlist-browse.inc',
            '{playlist_id}', _('Display browse playlist UI'), CC_AG_PLAYLIST ); 

        CCEvents::MapUrl( ccp('playlist','empty'),
            array( 'CCPlaylistManage', 'EmptyPL'),   CC_MUST_BE_LOGGED_IN,   
            'cchost_lib/ccextras/cc-playlist-forms.inc' ,
            '{playlist_id}', _('Display empty playlist UI'), CC_AG_PLAYLIST );
        
        CCEvents::MapUrl( ccp('playlist','delete'),
            array( 'CCPlaylistManage', 'DeletePL'),  CC_MUST_BE_LOGGED_IN,   
            'cchost_lib/ccextras/cc-playlist-forms.inc' ,
            '{playlist_id}', _('Display delete playlist form'), CC_AG_PLAYLIST );
        
        CCEvents::MapUrl( ccp('playlist','edit'),
            array( 'CCPlaylistManage', 'Edit'), CC_MUST_BE_LOGGED_IN,   
            'cchost_lib/ccextras/cc-playlist-forms.inc' ,
            '{playlist_id}', _('Display edit playlist props form'), CC_AG_PLAYLIST );
        
        CCEvents::MapUrl( ccp('admin','playlists'),
            array( 'CCPlaylistManage', 'Admin'), CC_ADMIN_ONLY,   
            'cchost_lib/ccextras/cc-playlist-forms.inc' ,
            '', _('Display admin playlist form'), CC_AG_PLAYLIST ); 

        CCEvents::MapUrl( ccp('admin','playlists','recalc'),
            array( 'CCPlaylistManage', 'Recalc'), CC_ADMIN_ONLY,   
            'cchost_lib/ccextras/cc-playlist-forms.inc' ,
            '', _('Playlist api'), CC_AG_PLAYLIST ); 

        CCEvents::MapUrl( ccp('admin','playlists','cleanup'),
            array( 'CCPlaylistManage', 'CleanUp'), CC_ADMIN_ONLY,   
            'cchost_lib/ccextras/cc-playlist-forms.inc' ,
            '', _('Clean up playlists attached to dead accounts'), CC_AG_PLAYLIST ); 

        /* depricated use api/playlist/create instead */
        CCEvents::MapUrl( ccp('api','playlist','new'),
            array( 'CCPlaylists', 'NewPL'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '[upload_id]', _('Create a new playlist, optionally starting with upload_id'), CC_AG_deprecated );

        CCEvents::MapUrl( ccp('api','playlist','getfavorite'),
            array( 'CCPlaylists', 'GetFavorite'),  CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
             '', _(''), CC_AG_deprecated );

    }


    function APICreatePlaylist() {
        $msg = '';
        $playlist =& $this->_new_pl('',$msg);
        CCUtil::ReturnAjaxObj($playlist);
    }

    function APICreateDynamic()
    {
        $qstring  = $this->_serializeQueryString();
        $new_name = $_REQUEST['title'];
        $tags     = empty($_REQUEST['tags']) ? '' : $_REQUEST['tags'];
        $row      = $this->_create_playlist('playlist',$new_name,'',0,$qstring,$tags);
        
        CCUtil::ReturnAjaxObj($row);        
    }

    function APIUpdateDynamic($playlist_id) 
    {
        $this->_test_pl(0,$playlist_id, TEST_UPLOAD_NO, TEST_PLAYLIST_YES, TEST_OWNER_YES);
        $string = $this->_serializeQueryString();
        $carts  =& CCPlaylist::GetTable();
        $values = array( 'cart_id' => $playlist_id, 
                         'cart_dynamic' => $string );
        $carts->Update($values);
        $status = array( 'status' => 'ok' );
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIUpdate($playlist_id)
    {
        $this->_test_pl(0,$playlist_id, TEST_UPLOAD_NO, TEST_PLAYLIST_YES, TEST_OWNER_YES);
        $carts =& CCPlaylist::GetTable();
        $values = array( 'cart_id' => $playlist_id );
        CCUtil::Strip($_REQUEST);
        if( !empty($_REQUEST['name'] ) ) {
            $values['cart_name'] = substr($_REQUEST['name'],0,60);
        }
        if( !empty($_REQUEST['tags'] ) ) {
            $values['cart_tags'] = $_REQUEST['tags'];
        }
        if( !empty($_REQUEST['description'] ) ) {
            $values['cart_desc'] = $_REQUEST['description'];
        }
        $carts->Update($values);

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('f=js&dataview=playlist_head&ids='.$playlist_id);
        $query->Query($args);
    }

    function APIFeature($playlist_id) 
    {
        $status = array();
        $status['status'] = 'invalid user';
        if( CCUser::IsAdmin() )
        {
            $this->_test_pl(0,$playlist_id, TEST_UPLOAD_NO, TEST_PLAYLIST_YES, TEST_OWNER_NO);
            $carts =& CCPlaylist::GetTable();
            $sub_type = $carts->QueryItemFromKey('cart_subtype', $playlist_id); 
            $w['cart_id'] = $playlist_id;
            if( $sub_type == 'featured') {
                $w['cart_subtype'] = '';
            } else {
                $w['cart_subtype'] = 'featured';
            }
            $carts->Update($w);
            $status['status'] = 'ok';
        }
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIDelete($playlist_id)
    {
        $this->_test_pl(0,$playlist_id, TEST_UPLOAD_NO, TEST_PLAYLIST_YES, TEST_OWNER_YES);
        $this->_empty_playlist($playlist_id);
        $carts =& CCPlaylist::GetTable();
        $carts->DeleteKey($playlist_id);
        $status = array( 'status' => 'ok' );
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIReorder($playlist_id)
    {
        $this->_test_pl(0,$playlist_id, TEST_UPLOAD_NO, TEST_PLAYLIST_YES, TEST_OWNER_YES);
        $this->_perform_reorder($playlist_id);        
        $status = array( 'status' => 'ok' );
        CCUtil::ReturnAjaxObj($status);        
    }

    function APIRemove($upload_id='',$playlist_id='')
    {
        $this->_remove_track($upload_id,$playlist_id);
        $status = array( 'status' => 'ok' );
        CCUtil::ReturnAjaxObj($status);        
    }

    /*
        API HELPERS
    */

    function _remove_track($upload_id,$playlist_id)
    {
        $this->_test_pl($upload_id,$playlist_id, TEST_UPLOAD_YES, TEST_PLAYLIST_YES, TEST_OWNER_YES);
        $cart_items =& CCPlaylistItems::GetTable();
        $w['cart_item_upload'] = $upload_id;
        $w['cart_item_cart'] = $playlist_id;
        $cart_items->DeleteWhere($w);
        $carts =& CCPlaylist::GetTable();
        $carts->Dec('cart_num_items',$playlist_id);
        $this->_sync_upload($upload_id);        
    }

    function & _create_playlist($sub_type='',$title_str = '',$desc ='',$user=0,$query='',$tags='')
    {
        global $CC_GLOBALS;

        $carts =& CCPlaylist::GetTable();
        $new_row['cart_id']      = $carts->NextID();
        $new_row['cart_user']    = $user ? $user : CCUser::CurrentUser();
        $new_row['cart_type']    = 'playlist';
        $new_row['cart_subtype'] = $sub_type;
        $new_row['cart_name']    = $title_str;
        $new_row['cart_desc']    = $desc;
        $new_row['cart_date']    = date('Y-m-d H:i:s');
        $new_row['cart_dynamic'] = $query;
        $new_row['cart_tags']    = $tags;
        $carts->Insert($new_row);
        return $new_row;
    }

    // copied from cc-playlists-forms
    function _empty_playlist($playlist_id)
    {
        $cartitems =& CCPlaylistItems::GetTable();
        $w['cart_item_cart'] = $playlist_id;
        $cartitems->DeleteWhere($w);
        $carts =& CCPlaylist::GetTable();
        $wx['cart_num_items'] = 0;
        $wx['cart_id'] = $playlist_id;
        $carts->Update($wx);
    }

    function _serializeQueryString() 
    {
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $_GET;
        if( !empty($args['title']) ) { unset($args['title']); }
        if( !empty($args['limit']) ) { unset($args['limit']); }
        if( !empty($args['u'] ) ) { 
            $args['user'] = $args['u'];
        }
        $qstring = $query->SerializeArgs($args);
        
        if( empty($qstring) )
            CCUtil::ReturnAjaxMessage("Error: empty query string");

        return $qstring;
    }

    function _perform_reorder($playlist_id)
    {
        // there are much more efficient ways of doing this, but by using
        // the exact same dataview as the edit screen, we are assured
        // fidelity between the two (e.g. skipping moderated and hidden tracks)
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        
        $args = $query->ProcessAdminArgs('dataview=playlist_reorder&f=php&playlist='.$playlist_id);
        list( $recs, $m ) = $query->Query($args);
        $ids = array();
        foreach( $recs as $rec )
            $ids[] = $rec['cart_item_id'];
            
        $ups = array();
        foreach( $_GET['fo'] as $new_pos => $old_pos )
        {
            // $old_pos is '1' indexed
            $id = $ids[$old_pos-1];
            $sql = "UPDATE cc_tbl_cart_items SET cart_item_order = $new_pos WHERE cart_item_id = $id";
            CCDatabase::Query($sql);
        }

    }

    function & _new_pl($upload_id='',&$msg)
    {
        if( !empty($_REQUEST['cart_name']) ) {
            $new_name = CCUtil::Strip($_REQUEST['cart_name']);
        } else if( !empty($_REQUEST['name']) ) {
            $new_name = CCUtil::Strip($_REQUEST['name']);
        }

        if( empty($new_name) )
            $new_name = $this->_gen_name();

        $desc = empty($_REQUEST['cart_desc']) ? '' : CCUtil::Strip($_REQUEST['cart_desc']);

        $new_row =& $this->_create_playlist('',$new_name,$desc);
        $playlist_id = $new_row['cart_id'];

        if( !empty($upload_id) )
        {
            $this->_test_pl($upload_id,$playlist_id,true,true,true);
            $this->_add($upload_id,$playlist_id);
            $msg = 'str_pl_and_track_added';
        }
        return $new_row;
    }

    function _gen_name()
    {
        global $CC_GLOBALS;

        $carts =& CCPlaylist::GetTable();
        $wuser['cart_user'] = CCUser::CurrentUser();
        $num = $carts->CountRows($wuser);
        return sprintf( _("%s's Collection (%d) "),$CC_GLOBALS['user_real_name'], $num + 1);
    }

    function _test_pl($upload_id,$playlist_id,$test_upload,$test_playlist,$test_owner)
    {
        $ok = true;
        if( $test_upload )
        {
            $upload_id = sprintf('%0d',CCUtil::Strip($upload_id));
            $ok = !empty($upload_id) && ($upload_id > 0);
        }
        if( $ok && $test_playlist )
        {
            $playlist_id = sprintf('%0d',CCUtil::Strip($playlist_id));
            $ok = !empty($playlist_id) && ($playlist_id > 0);
            if( $ok )
                $ok = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_cart WHERE cart_id='.$playlist_id);
        }
        if( $ok && $test_owner )
        {
            $owner = CCDatabase::QueryItem('SELECT cart_user FROM cc_tbl_cart WHERE cart_id='.$playlist_id);
            $ok = !empty($owner) && ($owner == CCUser::CurrentUser());
        }
        if( !$ok )
        {
            CCUtil::Send404();
        }
        return $ok;
    }

    function _add($upload_id='',$playlist_id='')
    {
        $cart_items =& CCPlaylistItems::GetTable();
        $check['cart_item_upload']   = $upload_id;
        $check['cart_item_cart'] = $playlist_id;
        $check_row = $cart_items->CountRows($check);
        if( $check_row )
        {
            CCUtil::ReturnAjaxMessage('str_pl_upload_is_already',CC_AJAX_WARNING);
        }

        $carts =& CCPlaylist::GetTable();
        $carts->Inc('cart_num_items',$playlist_id);

        $cart_items =& CCPlaylistItems::GetTable();
        $new_row['cart_item_cart'] = $playlist_id;
        $count = $cart_items->CountRows($new_row);
        $new_row['cart_item_upload'] = $upload_id;
        $new_row['cart_item_order'] = $count;
        $cart_items->Insert($new_row);

        $this->_sync_upload($upload_id);
    }

    function _sync_upload($upload_id)
    {
        $sql =<<<EOF
        UPDATE cc_tbl_uploads SET upload_num_playlists = (
            SELECT COUNT( * )
            FROM cc_tbl_cart_items
            WHERE cart_item_upload = upload_id
            )
        WHERE upload_id = {$upload_id}
EOF;
        CCDatabase::Query($sql);
    }


    /********************************************************************
         legacy
    *********************************************************************/
    function Create()
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        require_once('cchost_lib/cc-query.php');
        require_once('cchost_lib/zend/json-encoder.php');

        $page->SetTitle('str_pl_create_dynamic_playlist');
        $args = array();
        $args['promo_tag'] = empty($CC_GLOBALS['playlist_promo_tag']) ? '' : $CC_GLOBALS['playlist_promo_tag'];
        $query = new CCQuery();
        $qargs = array_filter($query->ProcessUriArgs());
        $args['edit_query'] = CCZend_Json_Encoder::encode($qargs);
        $args['submit_url'] = ccl('api','query');
        $args['submit_text'] = 'str_pl_create_playlist';
        $page->PageArg( 'plargs', $args, 'playlist.xml/playlist_create_dyn');
    }

    function EditQuery($playlist_id='',$commit='')
    {
        $this->_test_pl(0,$playlist_id,false,true,true);
        $this->_build_bread_crumb_trail($playlist_id,
                        array(ccl('playlist','edit',$playlist_id),'str_pl_edit_properties'),'str_pl_edit_dynamic_playlist');
        $cart =& CCPlaylist::GetTable();
        $row = $cart->QueryKeyRow($playlist_id);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle(array('str_pl_edit_dynamic_playlist_s',$row['cart_name']));
        if( empty($commit) )
        {
            parse_str($row['cart_dynamic'],$qargs);
            require_once('cchost_lib/zend/json-encoder.php');
            $args['edit_query'] = CCZend_Json_Encoder::encode($qargs);
            $args['promo_tag'] = '';
            $args['submit_url'] = ccl('playlist','editquery',$playlist_id,'commit');
            $args['submit_text'] = 'str_pl_save_playlist_query';
            $page->PageArg( 'playlist_create_dyn', 'playlist.xml/playlist_create_dyn' );
            $page->PageArg( 'plargs', $args, 'playlist_create_dyn' );
        }
        else
        {
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $pargs = $query->ProcessUriArgs(array( 'format' => 'playlist' ) );
            $qargs = array_filter($pargs);
            $qargs = $query->SerializeArgs($qargs);
            $up['cart_id'] = $playlist_id;
            $up['cart_dynamic'] = $qargs;
            if( !empty($_REQUEST['tags'] ) ) {
                $up['cart_tags'] = $_REQUEST['tags'];
            }
            $cart->Update($up);
            $url = ccl('playlist','browse',$playlist_id);
            CCUtil::SendBrowserTo($url);
        }
    }

    function _args_to_json($args)
    {
            // user: '${pluser}', upload: ${upid},  
        require_once('cchost_lib/zend/json-encoder.php');
        return CCZend_Json_Encoder::encode($args);
    }


    function GetType($user_name,$type,$name='')
    {
        $name = empty($name) ? $type : urldecode($name);
        $user_id = CCUser::IDFromName($user_name);
        $cart_id = CCPlaylistHV::_get_type_for_user($user_id,$type,true,$name);
        $cart_items =& CCPlaylistItems::GetTable();
        $w['cart_item_cart'] = $cart_id;
        $ids = $cart_items->QueryItems('cart_item_upload',$w);
        $obj = array( 'cart_id' => $cart_id, 'upload_ids' => $ids );
        CCUtil::ReturnAjaxData($obj); // this will exit
    }
    
    function GetFavorite()
    {
        $user_name = CCUser::CurrentUserName();
        $pname = empty($_GET['pname']) ? $user_name . "'s Favorites" : urldecode($_GET['pname']);
        $this->GetType($user_name,'favorites', $pname); // todo stringize
    }
    
    function Save()
    {
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $qstring = $query->SerializeArgs($_GET);
        if( empty($qstring) )
            CCUtil::Send404();
        $new_name = $this->_gen_name();
        $row = $this->_create_playlist('playlist',$new_name,'',0,$qstring);
        CCUtil::SendBrowserTo( ccl('playlist','browse',$row['cart_id']));
    }


    function Bump($upload_encoded='')
    {
        exit; 
    }

    function _build_bread_crumb_trail($playlist_id,$cmd='',$cmd2='')
    {
        require_once('cchost_lib/ccextras/cc-playlist-browse.inc');
        $browserapi = new CCPlaylistBrowse();
        $browserapi->_build_bread_crumb_trail($playlist_id,$cmd,$cmd2);
    }

    function EditOrder($playlist_id='',$cmd='')
    {
        $this->_test_pl(0,$playlist_id,false,true,true);
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        if( empty($cmd) )
        {
            require_once('cchost_lib/cc-page.php');
            $page =& CCPage::GetPage();
            $this->_build_bread_crumb_trail($playlist_id,'str_pl_edit_playlist_order');
            $page->SetTitle('str_pl_edit_playlist_order');
            $page->PageArg('playlist_id',$playlist_id); // there should be a better way to do this
            $args = $query->ProcessAdminArgs('t=playlist_reorder&playlist='.$playlist_id);
            $query->Query($args);
        }
        else
        {
            $this->_perform_reorder($playlist_id);
            $url = ccl('playlist','browse',$playlist_id);
            CCUtil::SendBrowserTo($url);
        }
    }


    function Remove($upload_id='',$playlist_id='')
    {
        $this->_remove_track($upload_id,$playlist_id);
        $args['command'] = 'delete';
        $args['upload_id'] = $upload_id;
        $args['cart_id'] = $playlist_id;
        $args[CC_AJAX_MESSAGE] = 'str_pl_track_has_been_removed';
        CCUtil::ReturnAjaxData($args);
    }


    function With($upload_id='')
    {
        $this->_test_pl($upload_id,0,true,false,false);
        $ret =& $this->_playlist_with($upload_id);
        $this->_output($ret);
    }

    function & _playlist_with($upload_id)
    {
        $cart = new CCPlaylist();
        $wuser['cart_user'] = $user = CCUser::CurrentUser();
        $wuser['cart_dynamic'] = '';
        $all_my_carts = $cart->QueryKeys($wuser);
        if( empty($all_my_carts) )
        {
            global $CC_GLOBALS;

            $title = sprintf( _("%s's Favorites"), $CC_GLOBALS['user_real_name'] );
            $ret['without'][] = $this->_create_playlist('favorites',$title);
            $ret['with'] = array();
        }
        else
        {
            $where = "cart_item_upload = {$upload_id} AND cart_item_cart IN (" . join(',',$all_my_carts) . ")";
            $cart_items =& CCPlaylistItems::GetTable();
            $carts_with = $cart_items->QueryItems( 'cart_item_cart', $where );
            if( empty($carts_with) )
            {
                $carts_with = array();
                $carts_without = $all_my_carts;
            }
            else
            {
                $carts_without = array_diff( $all_my_carts, $carts_with );
            }
            $ret['without'] = $cart->QueryKeyRows($carts_without);
            $ret['with']    = $cart->QueryKeyRows($carts_with);
        }
        $ret['upload_id'] = $upload_id;
        return $ret;
    }


    function Add($upload_id='',$playlist_id='')
    {
        $this->_test_pl($upload_id,$playlist_id,true,true,true);
        $this->_add($upload_id,$playlist_id);
        CCUtil::ReturnAjaxMessage('str_pl_track_added');
    }
 

    function NewPL($upload_id='')
    {
        $msg = 'str_pl_new_playlist_created';

        $this->_new_pl($upload_id,$msg);

        CCUtil::ReturnAjaxMessage($msg);
    }

    function _output($obj,$pmacro='',$merge=false)
    {
        $format = CCUtil::Strip($_GET['f']);
        if( empty($format) )
            $format = empty($pmacro) ? 'json' : 'html';

        switch( $format )
        {
            case 'json':
                require_once('cchost_lib/zend/json-encoder.php');
                $text = CCZend_Json_Encoder::encode($obj);
                header( "X-JSON: $text");
                header( 'Content-type: text/plain');
                print($text);
                break;

            case 'html':
                require_once('cchost_lib/cc-template.php');
                $template = CCUtil::Strip($_GET['t']);
                if( empty($template) )
                    $template = 'playlist.xml';
                else if( strpos( $template, '.xml' ) === false )
                    $template .= '.xml';
                $macro = CCUtil::Strip($_GET['m']);
                if( !empty($macro) )
                {
                    $macro = '/' . $macro;
                }
                $tobj = new CCSkinMacro($template . $macro);
                if( empty($tobj) )
                {
                    print(" $template / $macro not found <br />\n" );
                    CCUtil::Send404();
                }

                $targs['macro'] = $macro;
                $targs['root-url'] = cc_get_root_url() . '/';
                $targs['home-url'] = ccl();
                if( $merge )
                    $targs = array_merge($targs,$obj);
                else
                    $targs['args'] = $obj;
                $tobj->SetAllAndPrint($targs);
                break;
        }
        exit;
    }

    function _send_prompt($msg)
    {
        $url = ccl('people', CCUser::CurrentUserName(), 'playlists' );
        $link = "<a href=\"$url\">";
        $link2 = '</a>';
        $page =& CCPage::GetPage();
        $page->Prompt(array('str_pl_return_to',$msg,$link,$link2));
    }


    /**
    * Event hander for {@link CC_EVENT_DELETE_UPLOAD}
    * 
    * @param array $record Upload database record
    */
    function OnUploadDelete(&$record)
    {
        $sql =<<<EOF
            SELECT cart_item_cart
            FROM cc_tbl_cart_items
            WHERE cart_item_upload = {$record['upload_id']}
EOF;
        $carts = CCDatabase::QueryItems($sql);
        if( !empty($carts) )
        {
            $cart_set = join(',',$carts);
            $sql =<<<EOF
                UPDATE cc_tbl_cart 
                SET cart_num_items = cart_num_items-1
                WHERE cart_id IN ({$cart_set})
EOF;
            CCDatabase::Query($sql);
            $sql =<<<EOF
                DELETE FROM cc_tbl_cart_items
                WHERE cart_item_upload = {$record['upload_id']}
EOF;
            CCDatabase::Query($sql);
        }
    }
}

/*
    function _get_playlist_info($playlist_id,$is_popup=false)
    {
        $carts = new CCPlaylist(true);
        $args['playlist'] = $carts->QueryKeyRow($playlist_id);
        if( empty($args['playlist']) )
            return null;
        $args['playlist']['artist_page_url'] = ccl('people',$args['playlist']['user_name']);
        if( $args['playlist']['cart_dynamic'] || ($args['playlist']['cart_subtype'] == 'default') )
        {
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            if( $args['playlist']['cart_subtype'] == 'default' )
                $qstring = $query->ProcessUriArgs(array('format' => 'playlist'));
            else
                $qstring = $args['playlist']['cart_dynamic'];
            $qargs = $query->ProcessAdminArgs($qstring,array('format'=>'php'),false);
            list( $args['records'] ) = $query->Query($qargs);
            $args['feed_q'] = url_args( ccl('api','query'), $qstring . '&f=rss' );
        }
        else
        {

            $purl = ccl('files') . '/';
            $ccp = ccl('people') . '/';
            $sql =<<<EOF
                SELECT upload_name, user_name, user_real_name, upload_id, upload_contest,
                  CONCAT('$ccp',user_name) as artist_page_url ,
                  CONCAT('$purl',user_name,'/',upload_id) as file_page_url
                  FROM cc_tbl_cart_items
                  JOIN cc_tbl_uploads on cart_item_upload = upload_id
                  JOIN cc_tbl_user ON upload_user = user_id 
                  WHERE cart_item_cart = $playlist_id
                  ORDER BY cart_item_order ASC
EOF;

            $args['records'] =& CCDatabase::QueryRows($sql);
            $args['feed_q'] = url_args( ccl('api','query'), 'f=rss&playlist=' . $playlist_id );
        }
        $dv = new CCDataView();
        $filters = array( 'e' => array( CC_EVENT_FILTER_FILES, CC_EVENT_FILTER_DOWNLOAD_URL, CC_EVENT_FILTER_PLAY_URL ) ); // sic
        $dv->FilterRecords($args['records'],$filters);
        return $this->_add_menu($args,$playlist_id,$is_popup);
    }

    function _get_default_playlist($id_only=false)
    {
        $ccp = ccl('people');

        $cart_fields =<<<EOF
cc_tbl_cart.*, user_real_name,
                  CONCAT('$ccp','/',user_name) as artist_page_url,
                  DATE_FORMAT(cart_date, '%W, %M %e, %Y @ %l:%i %p') as cart_date_format
EOF;

        if( $id_only )
        {
            $sql =<<<EOF
                SELECT cart_id
                  FROM cc_tbl_cart
                  JOIN cc_tbl_user ON cart_user = user_id 
                  WHERE cart_type = 'playlist' AND cart_subtype = 'default'
                  LIMIT 1
EOF;
            $id = CCDatabase::QueryItem($sql);
            return $id;
        }
        else
        {
            $sql =<<<EOF
                SELECT {$cart_fields}
                  FROM cc_tbl_cart
                  JOIN cc_tbl_user ON cart_user = user_id 
                  WHERE cart_type = 'playlist' AND cart_subtype = 'default'
                  LIMIT 1
EOF;
            $row = CCDatabase::QueryRow($sql);
            if( $row )
                return $row;

        }

        // find an admin
        $users =& CCUsers::GetTable();
        $w2['user_name'] = 'admin';
        $admin_id = $users->QueryKey($w2);
        if( empty($admin_id) )
        {
            $config =& CCConfigs::GetTable();
            $settings = $config->GetConfig('settings');
            $admins = preg_split('/(\W|,)/', $settings['admins'] );
            $w2['user_name'] = $admins[0];
            $admin_id = $users->QueryKey($w2);
            if( empty($admin_id) )
                CCUtil::Send404();
        }
    
        $newrow = $this->_create_playlist('default','str_pl_dynamic_playlist','',$admin_id);

        if( $id_only )
            return $new_row['cart_id'];

        $sql =<<<EOF
            SELECT {$cart_fields}
              FROM cc_tbl_cart
              JOIN cc_tbl_user ON cart_user = user_id 
              WHERE cart_id = {$newrow['cart_id']}
              LIMIT 1
EOF;
        $row = CCDatabase::QueryRow($sql);
        return $row;
    }

*/
?>
