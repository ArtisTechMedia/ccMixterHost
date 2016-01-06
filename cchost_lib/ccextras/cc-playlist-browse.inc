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
* $Id: cc-playlist-browse.inc 12909 2009-07-13 18:24:11Z fourstones $
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

/**
*
*/
require_once('cchost_lib/ccextras/cc-cart-table.inc');

class CCPlaylistBrowse
{
    /**
    * Callback for 'Playlist' tab in user profile nav tabs
    */
    function User($user)
    {
        CCUtil::SendBrowserTo('http://playlists.ccmixter.org/people/' . $user . '/playlists');
        /*
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $fancy_name = cc_fancy_user_sql();
        $user_name = CCDatabase::QueryItem('SELECT ' . $fancy_name . ' FROM cc_tbl_user WHERE user_name ="' . $user . '"');
        $title = array('str_pl_browse_user_s',$user_name);
        $q = 't=playlist_2_browse&u='.$user;
        $page->SetTitle($title);
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs($q);
        $query->Query($args);
        */
    }


    function _bbt_user($user,$user_real_name,$title)
    {
        $trail = array();
        $trail[] = array( 'url' => ccl(),  'text' => 'str_home' );
        $trail[] = array( 'url' => ccl('people'),  'text' => 'str_people' );
        $trail[] = array( 'url' => ccl('people',$user),  'text' => $user_real_name );
        $trail[] = array( 'url' => ccl('people',$user,'playlists'),  'text' => 'str_playlists' );
        $trail[] = array( 'url' => '',  'text' => $title);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    function _bbt_user_upload($upload_id, $upload_name, $user, $user_real_name, $title)
    {
        $trail = array();
        $trail[] = array( 'url' => ccl(),  'text' => 'str_home' );
        $trail[] = array( 'url' => ccl('people'),  'text' => 'str_people' );
        $trail[] = array( 'url' => ccl('people',$user),  'text' => $user_real_name );
        $trail[] = array( 'url' => ccl('files',$user,$upload_id),  'text' => $upload_name );
        $trail[] = array( 'url' => '',  'text' => $title);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    function _bbt_tags($title)
    {
        $trail = array();
        $trail[] = array( 'url' => ccl(),  'text' => 'str_home' );
        $trail[] = array( 'url' => ccl('view/media/playlists'),  'text' => 'str_playlists' );
        $trail[] = array( 'url' => ccl('view/media/playlists/browse'),  'text' => 'str_pl_browse_playlists' );
        $trail[] = array( 'url' => '',  'text' => $title);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    function _build_bread_crumb_trail($playlist_id,$cmd='',$cmd2='')
    {
        list( $user_id, $playlist_name ) = CCDatabase::QueryRow(
                'SELECT cart_user, cart_name FROM cc_tbl_cart WHERE cart_id='.$playlist_id,false);
        list( $user_name, $user_real_name ) = CCDatabase::QueryRow(
            'SELECT user_name, user_real_name FROM cc_tbl_user WHERE user_id='.$user_id,false);

        $trail = array();
        $trail[] = array( 'url' => ccl(),                                'text' => 'str_home' );
        $trail[] = array( 'url' => ccl('people'),                        'text' => 'str_people' );
        $trail[] = array( 'url' => ccl('people',$user_name),             'text' => $user_real_name );
        $trail[] = array( 'url' => ccl('people',$user_name,'playlists'), 'text' => 'str_playlists' );
        if( empty($cmd) )
        {
            $trail[] = array( 'url' => '', 'text' => $playlist_name );
        }
        else
        {
            $trail[] = array( 'url' => ccl('playlist','browse',$playlist_id), 'text' => $playlist_name );
            if( is_array($cmd) )
            {
                $trail[] = array( 'url' => $cmd[0], 'text' => $cmd[1]);
                $trail[] = array( 'url' => '', 'text' => $cmd2 );
            }
            else
            {
                $trail[] = array( 'url' => '', 'text' => $cmd );
            }
        }

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    function OnFilterCartMenu(&$records)
    {
        if( empty($records[0]['cart_id']) )
            return;
        $this->_add_menu($records[0],$records[0]['cart_id']);
    }
    
    function OnFilterCartNSFW(&$records)
    {
        if( empty($records[0]['cart_id']) )
            return;
        $val = $this->_calc_is_nsfw($records[0]['cart_id']);
        $records[0]['cart_nsfw'] = $val;
    }

    function _calc_is_nsfw($playlist_id)
    {
        $sql =<<<EOF
            SELECT upload_extra
            FROM cc_tbl_uploads
            JOIN cc_tbl_cart_items ON upload_id=cart_item_upload
            JOIN cc_tbl_cart ON cart_item_cart=cart_id
            WHERE cart_id = $playlist_id
EOF;

        $extas = CCDatabase::QueryItems($sql);
        foreach( $extas as $ex ) {
            $ex = unserialize($ex);
            if( !empty($ex['nsfw'])) {
                return '1';
            }
        }
        
        return '0';

    }

    function _add_menu(&$row,$playlist_id,$is_popup=false)
    {
        $is_dynamic = !empty($row['cart_dynamic']);

        $row['menu'] = array();
        if( $is_dynamic || !empty($row['cart_num_items']) )
        {
            $row['menu'][] = array( 'url'   => ccl( 'playlist', 'play', $playlist_id ),
                                     'class' => 'cc_playlist_playlink',
                                     'id'    => '_pla_' . $playlist_id,
                                     'text'  => 'str_pl_play_all_tracks' );
            if( empty($_GET['popup']) )
            {
                $row['menu'][] = array( 'url'   => 'javascript://play in window',
                                         'class' => 'cc_playlist_playwindow',
                                         'id'    => '_plw_' . $playlist_id,
                                         'text'  => 'str_pl_play_in_window' );
            }
        }

        $row['is_logged_in'] = false;
        $row['is_owner'] = false;

        if( CCUser::IsLoggedIn() )
        {
            $user = CCUser::CurrentUser();
            $row['is_logged_in'] = true;
            if( !$is_popup )
            {
                if( $row['cart_user'] ==  $user )
                {
                    $row['is_owner'] = true;
                    $row['menu'][] = array( 'url' => ccl( 'playlist', 'edit', $playlist_id ),
                                                'class' => '',
                                                'id'    => '',
                                                'text' => 'str_pl_edit_properties' );

                    if( !$is_dynamic && !empty($row['records']) )
                    {
                        $row['menu'][] = array( 'url' => ccl( 'playlist', 'editorder', $playlist_id ),
                                                                'class' => '',
                                                                'id'    => '',
                                                                'text' => 'str_pl_reorder playlist' );

                        $row['menu'][] = array( 'url' => ccl( 'playlist', 'empty', $playlist_id ),
                                                                'class' => '',
                                                                'id'    => '',
                                                                'text' => 'str_pl_empty_playlist');
                    }

                    if( $row['cart_subtype'] != 'favorites' )
                    {
                        $row['menu'][] = array( 'url' => ccl( 'playlist', 'delete', $playlist_id ),
                                                'class' => '',
                                                'id'    => '',
                                                'text' => 'str_pl_delete_playist');
                    }
                }
            }
        }

        return $row;
    }


    /**
    * Top level browse playlist(s) - mapped to playlist/browse/{playlist_id}
    *
    */
    function Browse($playlist_id='')
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-query.php');

        $query = new CCQuery();
        $args = $_GET;
        $query->_arg_alias_ref($args);

        if( !empty($args['user']) ) {
            CCUtil::SendBrowserTo('http://playlists.ccmixter.org/people/' . $args['user']);
        }

        if( !empty($playlist_id) )
            $playlist_id = sprintf("%0d",CCUtil::Strip($playlist_id));
        elseif( !empty($args['ids']) )
            $playlist_id = sprintf("%0d",CCUtil::Strip($args['ids']));

        if( !empty($playlist_id) )
        {
            if(  $playlist_id < 1 ) 
                $playlist_id = 0;
            else
                $playlist_id = CCDatabase::QueryItem('SELECT cart_id FROM cc_tbl_cart WHERE cart_id='.$playlist_id);
        }

        if( empty($playlist_id) )
        {
            // browsing multiple playlists 
            //
            $template = 'playlist_2_browse';

            if( !empty($args['upload']) )
            {
                // browsing for occurrances of a specific 
                // upload 
                //
                $upload_id = sprintf('%0d',CCUtil::Strip($args['upload']));

                if( empty($upload_id) || ($upload_id < 1) )
                    CCUtil::Send404();
                $sql =<<<EOF
                    SELECT user_real_name,user_name,upload_name
                    FROM cc_tbl_uploads
                    JOIN cc_tbl_user ON upload_user=user_id
                    WHERE upload_id={$upload_id}
EOF;
                list($user_real_name,$user_name,$upload_name) = CCDatabase::QueryRow($sql,false);
                if( empty($upload_name) )
                    CCUtil::Send404();
                $title = array('str_pl_browse_playlists_s',$upload_name);
                $this->_bbt_user_upload($upload_id,$upload_name,$user_name,$user_real_name,$title);
            }
            elseif( !empty($args['tags']) )
            {
                // browsing for occurrances of tags in playlist
                //
                $title = array('str_pl_tagged_playlists',$args['tags']);
                $this->_bbt_tags($title);
            }
            elseif( !empty($args['sinced']) )
            {
                // browsing for recent playlists
                $title = 'str_pl_new_playlists';
            }
            elseif( !empty($args['user']) )
            {
                $fancy_name = cc_fancy_user_sql('fancy');
                $sql = "SELECT {$fancy_name}, user_real_name, user_name FROM cc_tbl_user WHERE user_name='{$args['user']}'";
                list( $fancy, $user_real_name, $user_name ) = CCDatabase::QueryRow($sql,false);
                // $title = array('str_pl_browse_user_s',$user_real_name);
                $title = array('str_pl_user_title',$fancy);
                $template = 'playlist_2_uploads';  // we're browsing uploads, not playlists
                $ex['sort'] = 'num_playlists';
                $this->_bbt_user($user_name,$user_real_name,$title);
            }
            else
            {
                $title ='str_pl_browse_playlists';
            }

        }
        else
        {
            $url = 'http://playlists.ccmixter.org/playlist/browse/' . $playlist_id;
            CCUtil::SendBrowserTo($url);

            /*
            // displaying a specific playlist
            //
            $this->_build_bread_crumb_trail($playlist_id);
            $cart_name = CCDatabase::QueryItem('SELECT cart_name FROM cc_tbl_cart WHERE cart_id='.$playlist_id);
            if( empty($cart_name) )
                CCUtil::Send404();
            $title = array(_('str_pl_playlist_s'),$cart_name);
            $_GET['playlist'] = $playlist_id;
            if( isset($_GET['ids']) )
                unset($_GET['ids']);
            $template = 'playlist_2_show_one';
            */
        }

        $ex['template'] = $template;
        $ex['title'] = $title;
        $args = $query->ProcessUriArgs($ex);
        $query->Query($args);
    }

}

?>