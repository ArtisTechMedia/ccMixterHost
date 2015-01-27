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
* $Id: cc-user-page.php 12594 2009-05-11 19:31:53Z fourstones $
*
*/

/**
* @package cchost
* @subpackage user
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*
*/
class CCUserPage
{
    function People( $username='', $tab='' )
    {
        if( !empty($username) )
            $username = CCUtil::Strip($username);
        if( !empty($tab) )
            $tab = CCUtil::Strip($tab);

        require_once('cchost_lib/cc-page.php');

        $page =& CCPage::GetPage();
        $configs =& CCConfigs::GetTable();
        $settings = $configs->GetConfig('settings');

        $uid = CCUser::IDFromName($username);
        if( $username && empty($uid) )
        {
            CCUtil::Send404(false);
            $page->SetTitle('str_people');
            $page->Prompt( array( 'str_dont_know_user', $username ) );
            return;
        }

        if( empty($username) )
        {
            $this->BrowseUsers();
            return;
        }

        $custom_template = $page->LookupMacro('custom_user_profile',true);
        if( !empty($custom_template) )
        {
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $args = $query->ProcessAdminArgs('t=custom_user_profile&user='.$username);
            $query->Query($args);
            return;
        }
        
        $fun = cc_fancy_user_sql();
        $user_real_name = CCDatabase::QueryItem("SELECT {$fun} FROM cc_tbl_user WHERE user_name ='{$username}'");
        $page->SetArg('sub_tab_prefix',$user_real_name . ': ');

        $originalTab = $tab;
        $tabs = $this->_get_tabs($username,$tab);
        $tagfilter = '';
        $page->PageArg('sub_nav_tabs',$tabs);
        if( empty($tabs['tabs'][$originalTab]) )
        {
            // HACK
            // for legacy reasons, we treat this like an upload tag query
            $tagfilter = $originalTab; 
        }
        $cb_tabs = $tabs['tabs'][$tab];
        if( !empty($cb_tabs['user_cb_mod']) )
            require_once($cb_tabs['user_cb_mod']);
        if( is_array($cb_tabs['user_cb']) && is_string($cb_tabs['user_cb'][0]) )
            $cb_tabs['user_cb'][0] = new $cb_tabs['user_cb'][0]();

        call_user_func_array( $cb_tabs['user_cb'], array( $username, $tagfilter ) );

        if( empty($this->_no_feeds) )
            $this->_show_feed_links($username, $cb_tabs['tags'] != 'uploads');

    }

    function Profile($username)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $fun = cc_fancy_user_sql();
        $user_real_name = CCDatabase::QueryItem("SELECT {$fun} FROM cc_tbl_user WHERE user_name ='{$username}'");
        if( !$user_real_name )
        {
            $page->Prompt('str_the_system_doesnt');
            CCUtil::Send404(false);
        }
        else
        {
            $page->SetTitle($user_real_name);
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $args = $query->ProcessAdminArgs('t=user_profile&u='.$username);
            $query->Query($args);
        } 
    }

    function Uploads($username,$tagfilter='')
    {
        //CCDebug::StackTrace();
        $page =& CCPage::GetPage();
        $page->PageArg('user_tags_user',$username,'user_tags');
        $tags = array_unique(preg_split('/[\s]?,[\s]?/',$tagfilter,-1,PREG_SPLIT_NO_EMPTY));
        sort($tags);
        $this->_tag_filter = join(' ',$tags);
        $tagfilter_commas = join(',',$tags);
        $page->PageArg('user_tags_tag',$tagfilter_commas);
        $where['user_name'] = $username;
        $users =& CCUsers::GetTable();
        $q = 'title=' . $users->QueryItem('user_real_name',$where);
        if( !empty($tagfilter) )
            $q .= ' (' . $tagfilter_commas .')&tags=' . $tagfilter;
        $q .= '&user=' . $username . '&t=list_files&f=page&limit=page';
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $query->ProcessAdminArgs($q);
        $query->Query();
    }

    function Hidden($username)
    {
        $page =& CCPage::GetPage();
        $txt = $page->String('str_non_public_files');
        $q = 'title=' . $txt;
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('title=str_hidden_list&t=unpub&f=page&unpub=1&mod=1&user='.$username);
        $query->Query($args);
        $this->_no_feeds = true;
        $page->SetArg('qstring','');
    }

    function _show_feed_links($username,$uploads=true)
    {
        require_once('cchost_lib/cc-page.php');

        $page =& CCPage::GetPage();

        $user_real_name = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_name=\''.$username .'\'');

        // why would you ever care if this is the uploads tab??
        //if( $uploads )
        {
            $query = 'user=' . $username . '&title=' . urlencode($user_real_name);
            $title = $user_real_name;
            if( !empty($this->_tag_filter) )
            {
                $query .= '&tags=' . $this->_tag_filter;
                $title .= ' (' . $this->_tag_filter . ')';
            }
            $page->AddFeedLink($query, $title, $title);
        }

        $title = $page->String(array('str_remixes_of_s',$user_real_name));
        $query = '&remixesof=' .$username .'&title=' . urlencode($title);
        $page->AddFeedLink( $query, $title, $title, 'feed_remixes_of' );

    }

    function _get_tabs($user,&$default_tab_name)
    {
        global $CC_GLOBALS;

        $fun = cc_fancy_user_sql();
        $record = CCDatabase::QueryRow(
             "SELECT user_id, user_name, user_real_name, user_num_uploads, user_num_reviews, user_num_reviewed," .
             "user_num_scores,user_num_posts,{$fun} "
             . "FROM cc_tbl_user WHERE user_name = '{$user}'");

        $tabs = array();

        if( $record['user_num_uploads'] )
        {
            $tabs['uploads'] = array (
                    'text' => 'str_uploads',
                    'help' => 'str_uploads',
                    'tags' => "uploads",
                    'limit' => '',
                    'access' => 4,
                    'function' => 'url',
                    'user_cb' => array( $this, 'Uploads' ),
                    );
        }

        $tabs['profile'] = array (
                    'text' => 'str_profile2',
                    'help' => 'str_profile2',
                    'tags' => "profile",
                    'limit' => '',
                    'access' => 4,
                    'function' => 'url',
                    'user_cb' => array( $this, 'Profile' ),
            );
    
        CCEvents::Invoke( CC_EVENT_USER_PROFILE_TABS, array( &$tabs, &$record ) );

        $isadmin = CCUser::IsAdmin();

        if( $isadmin )
        {
            $tabs['admin'] = array (
                        'text' => 'Admin',
                        'help' => 'Admin',
                        'tags' => "admin",
                        'limit' => '',
                        'access' => 4,
                        'function' => 'url',
                        'user_cb' => array( 'CCUserAdmin', 'Admin' ),
                        'user_cb_mod' => 'cchost_lib/cc-user-admin.php',
                     );
        }

        if( $record['user_num_uploads'] )
        {
            $itsme = CCUser::CurrentUserName() == $user;
            if( $itsme || $isadmin )
            {
                $userid = CCUser::IDFromName($user);
                $sql = 'SELECT COUNT(*) FROM cc_tbl_uploads WHERE (upload_published=0 OR upload_banned=1) AND upload_user='.$userid;
                $hidden = CCDatabase::QueryItem($sql);
                if( $hidden )
                {
                    $tabs['hidden'] = array (
                                'text' => 'str_hidden',
                                'help' => 'str_hidden_list',
                                'tags' => "hidden",
                                'limit' => '',
                                'access' => 4,
                                'function' => 'url',
                                'user_cb' => array( $this, 'Hidden' ),
                        );
                }
            }
        }

        $keys = array_keys($tabs);
        $user_real = $record['fancy_user_name'];
        for( $i = 0; $i < count($keys); $i++ )
        {
            $T =& $tabs[$keys[$i]];
            $T['tags'] = str_replace('%user_name%',$user,$T['tags']);
            $T['help'] = str_replace('%user_name%',$user_real,$T['help']);
            $T['text'] = str_replace('%user_name%',$user_real,$T['text']);
        }

        if( empty($default_tab_name) && !empty($CC_GLOBALS['user_extra']['prefs']['default_user_tab']) )
            $default_tab_name = $CC_GLOBALS['user_extra']['prefs']['default_user_tab'];

        require_once('cchost_lib/cc-navigator.php');
        $navapi = new CCNavigator();
        $url = ccl('people',$user);
        $navapi->_setup_page($default_tab_name, $tabs, $url, true, $default_tab, $tab_info );
        return $tab_info;
    }

    function BrowseUsers()
    {
        $alpha           = '';
        $qargs           = 't=user_list';
        $sqlargs['where'] = 'user_num_uploads > 0';

        if( !isset($_GET['p']) )
        {
            $qargs .= '&sort=date&ord=DESC';
        }
        else
        {
            $alpha = CCUtil::StripText($_GET['p']);
            $sqlargs['where'] .= " AND (user_name LIKE '{$alpha}%')";
            $qargs .= '&sort=user_name&ord=ASC';
        }

        $sql =<<<END
                SELECT DISTINCT LOWER(SUBSTRING(user_name,1,1)) c
                   FROM `cc_tbl_user` 
                   WHERE user_num_uploads > 0
                ORDER BY c
END;

        $burl = ccl('people');
        $chars = CCDatabase::QueryItems($sql);
        $len = count($chars);
        $alinks = array();
        for( $i = 0; $i < $len; $i++ )
        {
            $c = $chars[$i];
            if( $c == $alpha )
            {
                $alinks[] = array( 
                                'url' => '', 
                                'text' => "<b>$c</b>" );
            }
            else
            {
                $alinks[] = array( 
                                'url' => $burl . '?p=' . $c, 
                                'text' => $c );
            }
        }

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        require_once('cchost_lib/cc-query.php');

        $page->PageArg('user_index',$alinks);
        $query = new CCQuery();
        $qargs .= '&limit=20';
        $args = $query->ProcessAdminArgs($qargs);
        $query->QuerySQL($args,$sqlargs);
    }

    function OnFilterUserProfile(&$records)
    {
        require_once('cchost_lib/cc-tags.php');
        require_once('cchost_lib/cc-page.php');

        $row =& $records[0];

        $row['user_homepage_html'] = '';
        if( !empty($row['user_homepage']) )
        {
            $row['user_homepage_html'] = "<a href=\"{$row['user_homepage']}\">{$row['user_homepage']}</a>";
        }

        $user_fields = array( 'str_user_about_me'  => 'user_description_html',
                              'str_user_home_page' => 'user_homepage_html',
                              );

        $row['user_fields'] = array();
        foreach( $user_fields as $name => $uf  )
        {
            if( empty($row[$uf]) )
                continue;
            $row['user_fields'][] = array( 'label' => $name, 'value' => $row[$uf], 'id' => $uf );
        }

        $feed_url = url_args( ccl('api','query'), 't=user_feeds&u='.$row['user_name'] );
        $feed_link = array('str_user_feed_link',
                                   '<a href="' . $feed_url . '">',
                                   '</a>',
                                   $row['user_real_name']);
                                   
        $row['user_fields'][] = array( 'label' => 'str_user_feeds',
                                       'value' => $feed_link ,
                                       'id' => 'user_feeds' );

        if( CCUser::IsLoggedIn() && ($row['user_id'] != CCUser::CurrentUser()) )
        {
            $current_favs = strtolower(CCUser::CurrentUserField('user_favorites'));
            $favs = CCTag::TagSplit($current_favs);
            
            $favurl = ccl('people','addtofavs',$row['user_name']);
            $link = "<a href=\"{$favurl}\">{$row['user_real_name']}</a>";

            if( in_array( strtolower($row['user_name']), $favs ) )
                $msg = array('str_favorites_remove_s',$link);
            else
                $msg = array('str_favorites_add_s',$link);

            $row['user_fields'][] = array( 'label' => 'str_favorites',
                                           'value' => $msg,
                                           'id'    => 'fav' );
        }

        $row['user_tag_links'] = array();

        $favs = CCTag::TagSplit($row['user_favorites']);
        if( !empty($favs) )
        {
            $links = array();
            foreach( $favs as $fav )
                $links[] = "(user_name = '$fav')";
            $where = join(' OR ' ,$links);
            $baseurl = ccl('people') . '/';
            $sql =<<<END
                SELECT REPLACE(user_real_name,' ','&middot;') as tag, 
                       CONCAT('$baseurl',user_name) as tagurl
                FROM cc_tbl_user
                WHERE $where
END;
            $links = CCDatabase::QueryRows($sql);
            $row['user_tag_links']['links0'] = array( 'label' => 'str_favorites',
                                              'value' => $links );
        }

        CCTag::ExpandOnRow($row,'user_whatilike',ccl('search/people', 'whatilike'), 'user_tag_links',
                                'str_prof_what_i_like');
        CCTag::ExpandOnRow($row,'user_whatido',  ccl('search/people', 'whatido'),'user_tag_links', 
                                'str_prof_what_i_pound_on');
        CCTag::ExpandOnRow($row,'user_lookinfor',ccl('search/people', 'lookinfor'),'user_tag_links',
                                'str_prof_what_im_looking_for', true);
//CCDebug::PrintVar($row);
    }


}

?>
