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
* $Id: cc-mediahost.php 12715 2009-06-04 05:12:11Z fourstones $
*
*/

/**
* Main module that handles uploads
*
* @package cchost
* @subpackage upload
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Main API for media blogging
*/
class CCMediaHost
{
    /*-----------------------------
        MAPPED TO URLS
    -------------------------------*/

    /**
    * Handles /files URL
    *
    * @param string $username (er...)
    * @param integer $fileid Database ID of single file to display
    * @param string $title Force a title on the display
    */
    function Media($username ='', $upload_id = '', $title='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $upload_id = sprintf("%0d",$upload_id);
        $this->_build_bread_crumb_trail($username,$upload_id);

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();

        if( empty($username) )
        {
            $page->SetTitle('str_file_browse_uploads');
            $args = $query->ProcessAdminArgs(array('title' => _('Latest Files')));
            $query->Query($args); 
        }
        else
        {
            if( !empty($upload_id) )
            {
                $uploads =& CCUploads::GetTable();
                list( $name, $published, $banned, $owner_id ) = CCDatabase::QueryRow(
                            'SELECT upload_name,upload_published,upload_banned,upload_user FROM cc_tbl_uploads WHERE upload_id='.$upload_id,false);
            }

            require_once('cchost_lib/cc-page.php');
            if( empty($name) )
            {
                $page->SetTitle('str_file_unknown');
                $page->Prompt('str_file_cannot_be_found');
                $page->Prompt('str_file_it_may_have');
                CCUtil::Send404(false);
                return;
            }

            if( !$published || $banned )
            {
                if( CCUser::IsAdmin() || (CCUser::CurrentUser() == $owner_id) )
                {
                    CCUtil::SendBrowserTo( ccl('people',$username,'hidden') );
                    return;
                }
                $page->SetTitle($name);
                $page->Prompt('str_file_this_upload_is');
                return;
            }

            $title = empty($title) ? $name : $title;
            $args = $query->ProcessAdminArgs( 't=list_file&ids=' . $upload_id . '&title=' . urlencode($title) );
            $query->Query($args);
        }
    }

    /**
    * Generic handler for submitting original works
    *
    * Displays and process new submission form and assign tags to upload
    *
    * @param string $page_title Caption for page
    * @param string $tags System tags to apply to upload
    * @param string $form_help String to display with this form
    * @param string $username Login name of user doing the upload
    * @param array  $etc Extra data for this operation (current supports $etc['suggested_tags'])
    */
    function SubmitOriginal($submit_meta,$username)
    {
        $page_title = $submit_meta['text'];
        $tags       = $submit_meta['tags'];
        $form_help  = $submit_meta['form_help'];
        $avail_lics = $submit_meta['licenses'];
        $suggested_tags = $submit_meta['suggested_tags'];

        if( empty($avail_lics) )
            $avail_lics = 'attribution_3';
            
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle($page_title);
        if( empty($username) )
        {
            $uid = CCUser::CurrentUser();
            $username = CCUser::CurrentUserName();
        }
        else
        {
            CCUser::CheckCredentials($username);
            $uid = CCUser::IDFromName($username);
        }

        require_once('cchost_lib/cc-upload-forms.php');

        $form = new CCNewUploadForm($uid,true,$avail_lics);

        $form->SetSubmitFormType($submit_meta);
        
        if( !empty($suggested_tags) )
            $form->AddSuggestedTags($suggested_tags);

        $this->_add_publish_field($form);

        if( !empty($_POST['newupload']) )
        {
            if( $form->ValidateFields() )
            {
                $upload_id = CCUpload::PostProcessNewUploadForm( $form, 
                                               $tags,
                                               $this->_get_upload_dir($username) );

                if( $upload_id )
                {
                    CCUpload::ShowAfterSubmit($upload_id);
                    return;
                }
            }
        }
        
        if( !empty($form_help) )
            $form->SetFormHelp($form_help);

        $page->AddForm( $form->GenerateForm() );
    }

    /**
    * Generic handler for submitting remixes
    *
    * Displays and process new submission form and assign tags to upload
    *
    * @param string $page_title Caption for page
    * @param string $tags System tags to apply to upload
    * @param string $form_help String to display with this form
    * @param string $username Login name of user doing the upload
    * @param array  $etc Extra data for this operation (current supports $etc['suggested_tags'])
    */
    function SubmitRemix($submit_meta)
    {
        global $CC_GLOBALS;

        $page =& CCPage::GetPage();

        $title = $submit_meta['text'];
        $tags  = $submit_meta['tags'];
        $form_help = $submit_meta['form_help'];
        $suggested_tags = $submit_meta['suggested_tags'];
        $url_extra = empty($submit_meta['url_extra']) ? '' : $submit_meta['url_extra'];
        
        $username = CCUser::CurrentUserName();
        $userid   = CCUser::CurrentUser();
        require_once('cchost_lib/cc-remix-forms.php');
        $form     = new CCPostRemixForm($userid);

        $form->SetSubmitFormType($submit_meta);

        $this->_add_publish_field($form);

        if( !empty($suggested_tags) )
            $form->AddSuggestedTags($suggested_tags);

        $page->SetTitle($title);

        if(!empty($form_help) )
            $form->SetFormHelp($form_help);

        if( empty($_POST['postremix']) )
        {
            if( !empty($_GET['pool']) )
            {
                $pool = CCUtil::Strip($_GET['pool']);
                $pool_id = sprintf('%d',$pool);
                if( empty($pool_id) )
                {
                    $pool_id = CCDatabase::QueryItem('SELECT pool_id FROM cc_tbl_pools WHERE pool_short_name=\''.$pool . '\'');
                }

                if( !empty($pool_id) )
                    $form->SetFormFieldItem('sources','pool_id',$pool_id);
            }
            if( !empty($_GET['sourcesof']) )
            {
                $form->SetFormFieldItem('sources','sourcesof',$_GET['sourcesof']);
            }
            $form->SetFormFieldItem('sources','remix_id',$url_extra);

            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $upload_dir = $this->_get_upload_dir($username);
            
            require_once('cchost_lib/cc-remix.php');
            CCRemix::OnPostRemixForm($form, $upload_dir, $tags );
        }
    }


    /**
    * Handles the URL media/publish
    *
    * Allows a user or admin to publish/unpublish (hide/unhide) a given upload
    *
    * @param string $username Login name of file owner
    * @param integer $fileid Database ID of file to hide/unhide
    */
    function Publish($username,$fileid)
    {
        $fileid = intval($fileid);
        $username = CCUtil::StripText($username);
        if( !CCUser::IsAdmin() )
        {
            require_once('cchost_lib/cc-upload.php');
            CCUpload::CheckFileAccess($username,$fileid);
        }

        $uploads =& CCUploads::GetTable();
        $row = $uploads->QueryKeyRow($fileid);
        if( $row['upload_published'] )
            $value = 0;
        else
            $value = 1;
        $where['upload_published'] = $value;
        $where['upload_id'] = $fileid;
        $uploads->Update($where);
        
        CCEvents::Invoke( CC_EVENT_UPLOAD_DONE, array( $fileid, CC_UF_PROPERTIES_EDIT, array(&$row) ) );

        if( $value )
            $this->Media( $username, $fileid );
        else
            CCUtil::SendBrowserTo( ccl('people',$username,'hidden') );
    }

    /*-----------------------------
        HELPERS
    -------------------------------*/

    /**
    * Internal: Get the directory to upload this user's files to
    * @access private
    */
    function _get_upload_dir($username)
    {
        global $CC_GLOBALS;
        $upload_root = CCUser::GetPeopleDir();
        return( $upload_root . '/' . $username );
    }

    /**
    * Internal: pump a 'publish' check box into form
    * @access private
    */
    function _add_publish_field(&$form)
    {
        if( CCUser::IsAdmin() || $this->_is_auto_pub() )
        {
            $fields = array( 
                'upload_published' =>
                            array( 'label'      => _('Publish Now'),
                                   'formatter'  => 'checkbox',
                                   'flags'      => CCFF_NONE,
                                   'value'      => 'on'
                            )
                        );
            
            $form->AddFormFields( $fields );

        }

    }

    /**
    * Internal: Returns the current state of the admin's preference for auto-publish
    * @access private
    */
    function _is_auto_pub()
    {
        $configs =& CCConfigs::GetTable();
        $settings = $configs->GetConfig('settings');
        return( $settings['upload-auto-pub']  );
    }

    /**
    * Internal: Returns the upload page's URL
    * @access private
    */
    function _get_file_page_url(&$record)
    {
        //CCDebug::StackTrace();
        //return( ccc($record['upload_config'],'files',$record['user_name'],$record['upload_id'])  );
        return( ccl('files',$record['user_name'],$record['upload_id'])  );
    }


    /**
    * Event handler for {@link CC_EVENT_GET_MACROS}
    *
    * @param array &$record Upload record we're getting macros for (if null returns documentation)
    * @param array &$file File record we're getting macros for
    * @param array &$patterns Substituion pattern to be used when renaming/tagging
    * @param array &$mask Actual mask to use (based on admin specifications)
    */
    function OnGetMacros(&$record, &$file, &$patterns, &$mask)
    {
        if( empty($record) )
        {
            $patterns['%source_title%']  = _("'Sampled from' title");
            $patterns['%source_artist%'] = _("'Sampled from' artist");
            $patterns['%url%']           = _('Download URL');
            $patterns['%song_page%']     = _('File page URL');
            $patterns['%unique_id%']     = _('Guaranteed to be unique number');
            $mask['song']  = _("Pattern to use for original works");
            $mask['remix'] = _("Pattern to use for Remixes");
            return;
        }

        $configs =& CCConfigs::GetTable();
        $masks = $configs->GetConfig('name-masks');

        if( !CCUploads::InTags(CCUD_MEDIA_BLOG_UPLOAD,$record) )
            return;

        if( empty($record['remix_sources']) )
        {
            $patterns['%source_title%']  = 
            $patterns['%source_artist%'] = '';
        }
        else
        {
            $parent = $record['remix_sources'][0];
            if( empty($parent['user_real_name']) )
                $parent['user_real_name'] =CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_id = ' . $parent['upload_user']);
            $patterns['%source_title%'] = $parent['upload_name'];
            $patterns['%source_artist%'] = $parent['user_real_name'];
            if( empty($mask) )
                $mask = $masks['remix'];
        }

        if( empty($mask) )
            $mask = $masks['song'];

        if( !empty($record['download_url']) )
            $patterns['%url%'] = $record['download_url'];

        if( !empty($record['upload_id']) )
        {
            if( empty($record['user_name']) )
                $record['user_name'] = CCUser::GetUserName($record['upload_user']);
            $patterns['%song_page%'] = ccl('files',$record['user_name'],$record['upload_id']);
        }

        if( !empty($file['file_id']) )
            $patterns['%unique_id%'] = $file['file_id'];
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_MENU}
    * 
    * The handler is called when a menu is being displayed with
    * a specific record. All dynamic changes are made here
    * 
    * @param array $menu The menu being displayed
    * @param array $record The database record the menu is for
    * @see CCMenu::GetLocalMenu()
    */
    function OnUploadMenu(&$menu,&$record)
    {
        $menu['editupload'] = 
                     array(  'menu_text'  => 'str_file_edit',
                             'weight'     => 100,
                             'group_name' => 'owner',
                             'id'         => 'editcommand',
                             'access'     => CC_DYNAMIC_MENU_ITEM );

        $menu['managefiles'] = 
                     array(  'menu_text'  => 'str_files_manage',
                             'weight'     => 101,
                             'group_name' => 'owner',
                             'id'         => 'managecommand',
                             'access'     => CC_DYNAMIC_MENU_ITEM );

        $menu['manageremixes'] = 
                     array(  'menu_text'  => 'str_files_manage_remixes',
                             'weight'     => 102,
                             'group_name' => 'owner',
                             'id'         => 'manageremixcommand',
                             'access'     => CC_DYNAMIC_MENU_ITEM );

        $menu['publish'] =
                    array( 'menu_text' => 'str_file_publish',
                           'group_name' => 'owner',
                            'id'        => 'publishcommand',
                           'weight'    => 103,
                           'access'    => CC_DYNAMIC_MENU_ITEM );

        $menu['deleteupload'] = 
                     array(  'menu_text'  => 'str_file_delete',
                             'weight'     => 104,
                             'group_name' => 'owner',
                             'id'         => 'deletecommand',
                             'access'     => CC_DYNAMIC_MENU_ITEM );

        $menu['uploadadmin'] = 
                     array(  'menu_text'  => _('Admin'),
                             'weight'     => 1010,
                             'group_name' => 'admin',
                             'id'         => 'admincommand',
                             'access'     => CC_ADMIN_ONLY );

        $isowner = CCUser::CurrentUser() == $record['user_id'];
        $isadmin = CCUser::IsAdmin();

        if( $isadmin )
        {
            $menu['uploadadmin']['action'] = ccl( 'admin', 'upload', $record['upload_id'] );
            $menu['uploadadmin']['access'] = CC_ADMIN_ONLY;
            
            $menu['deleteupload']['group_name']  = 'admin';
            $menu['publish']['group_name']       = 'admin';
        }
        else
        {
            $menu['uploadadmin']['access'] = CC_DISABLED_MENU_ITEM;
        }


        if( empty($record['upload_banned']) )
        {
        }
        else
        {
            // This upload is banned!!

            if( $isowner || $isadmin )
            {
                $menu['deleteupload']['action'] = ccl( 'files', 'delete', $record['upload_id']);
                $menu['deleteupload']['access']  = CC_MUST_BE_LOGGED_IN;
                $menu['managefiles']['action'] = ccl( 'file', 'manage', $record['upload_id']);
                $menu['managefiles']['access']  = CC_MUST_BE_LOGGED_IN;
                $menu['manageremixes']['action'] = ccl( 'file', 'remixes', $record['upload_id']);
                $menu['manageremixes']['access']  = CC_MUST_BE_LOGGED_IN;

            }
            else
            {
                $menu['deleteupload']['access'] = CC_DISABLED_MENU_ITEM;
                $menu['managefiles']['access'] = CC_DISABLED_MENU_ITEM;
                $menu['manageremixes']['access'] = CC_DISABLED_MENU_ITEM;
            }

            $menu['editupload']['access']    = CC_DISABLED_MENU_ITEM;
            $menu['publish']['access']       = CC_DISABLED_MENU_ITEM;

            return; // BAIL
        }

        if( $isowner || $isadmin )
        {
            $menu['editupload']['access']  = CC_MUST_BE_LOGGED_IN;
            $menu['editupload']['action']  = ccl('files','edit', $record['user_name'],
                                                    $record['upload_id']);

            $menu['managefiles']['access']  = CC_MUST_BE_LOGGED_IN;
            $menu['managefiles']['action']  = ccl('file','manage', $record['upload_id']);

            $menu['manageremixes']['access']  = CC_MUST_BE_LOGGED_IN;
            $menu['manageremixes']['action']  = ccl('file','remixes', $record['upload_id']);

            $menu['deleteupload']['access'] = CC_MUST_BE_LOGGED_IN;
            $menu['deleteupload']['action'] = ccl( 'files', 'delete', $record['upload_id']);
        }
        else
        {
            $menu['editupload']['access']   = CC_DISABLED_MENU_ITEM;
            $menu['deleteupload']['access'] = CC_DISABLED_MENU_ITEM;
            $menu['managefiles']['access'] = CC_DISABLED_MENU_ITEM;
            $menu['manageremixes']['access'] = CC_DISABLED_MENU_ITEM;
        }

        $ismediablog = CCUploads::InTags(CCUD_MEDIA_BLOG_UPLOAD,$record) || $isadmin;

        if( $ismediablog && (($isowner && $this->_is_auto_pub()) || $isadmin) )
        {
            if( $record['upload_published'] )
            {
                $classid = 'unpublishcommand';
                $text = _('Unpublish');
            }
            else
            {
                $classid = 'publishcommand';
                $text = _('Publish');
            }

            $menu['publish']['menu_text'] = $text;
            $menu['publish']['id']        = $classid;
            $menu['publish']['access']   |= CC_MUST_BE_LOGGED_IN;
            $menu['publish']['action']    = ccl( 'files', 'publish', 
                                                    $record['user_name'],
                                                    $record['upload_id']);
        }
        else
        {
            $menu['publish']['access'] = CC_DISABLED_MENU_ITEM;
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('files'),                array('CCMediaHost','Media'),     
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '[user_name]/[upload_id]', 
            _('List files'), CC_AG_UPLOAD );

        CCEvents::MapUrl( ccp('files','publish'),      array('CCMediaHost','Publish'),   
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{user_name}/{upload_id}', 
            _('Publish/unpublish an upload'), CC_AG_UPLOAD  );

        CCEvents::MapUrl( ccp('files','edit'),         array('CCPhysicalFile','Edit'),   
            CC_MUST_BE_LOGGED_IN, 'cchost_lib/cc-files.php', 
            '{user_name}/{upload_id}', 
            _('Edit properties for an upload'), CC_AG_UPLOAD  );

        CCEvents::MapUrl( ccp('files','delete'),       array('CCUpload','Delete'),       
            CC_MUST_BE_LOGGED_IN, 'cchost_lib/cc-upload.php', 
            '{upload_id}', _('Show confirm delete choice'), CC_AG_UPLOAD  );

        CCEvents::MapUrl( ccp('admin','upload'),       array('CCUpload','AdminUpload'),  
            CC_ADMIN_ONLY, 'cchost_lib/cc-upload.php', 
            '{upload_id}', _('Show admin upload form'), CC_AG_UPLOAD  );

    }

    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope != CC_GLOBAL_SCOPE )
        {
            $fields['upload-auto-pub'] =
                       array( 'label'       => _('Auto Publish Uploads'),
                               'form_tip'   => _('Uncheck this if you want to verify uploads before they are made public.'),
                               'value'      => true,
                               'formatter'  => 'checkbox',
                               'flags'      => CCFF_POPULATE );
        }

    }

    /**
    * @access private
    */
    function _build_bread_crumb_trail($username,$upload_id)
    {
        $trail[] = array( 'url' => ccl(), 'text' => 'str_home' );
        
        if( empty($username) )
        {
            $trail[] = array( 'url' => ccl('media','files'), 
                              'text' => 'str_uploads' );
        }
        else
        {
            $trail[] = array( 'url' => ccl('people'), 
                              'text' => 'str_people');

            $users =& CCUsers::GetTable();
            $user_real_name = $users->QueryItem('user_real_name',
                                                "user_name = '$username'");
            if( !empty($user_real_name) )
            {
                $trail[] = array( 'url' => ccl('people',$username), 
                                           'text' => $user_real_name );
                if( !empty($upload_id) )
                {
                    require_once('cchost_lib/cc-upload-table.php');

                    $uploads =& CCUploads::GetTable();
                    $upload_name = $uploads->QueryItemFromKey('upload_name',
                                                              $upload_id);
                    if( !empty($upload_name) )
                    {
                        $upload_name = '"' . $upload_name . '"';
                        $trail[] = array( 'url' => ccl('files',$username,
                                                       $upload_id), 
                                           'text' => $upload_name );
                    }
                }
            }
        }

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

}


?>
