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
* $Id: cc-import-files.inc 12624 2009-05-18 15:47:40Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


require_once('cchost_lib/cc-upload-forms.php');


/**
*/
class CCImportForm extends CCNewUploadForm
{
    function CCImportForm($uid)
    {
        $all_lics = CCDatabase::QueryItems('SELECT license_id FROM cc_tbl_licenses ORDER by license_name');
        $all_lics = join(',',$all_lics);
        $this->CCNewUploadForm($uid,true,$all_lics);

        $this->SetFormFieldItem( 'upload_file_name', 'formatter', 'importer');
        $this->SetFormFieldItem( 'upload_file_name', 'label', _('Path to file on this server') );
    }

    function generator_importer($varname,$value='',$class='')
    {
        return $this->generator_textedit($varname,$value,$class);
    }

    function validator_importer($fieldname)
    {
        if( $this->validator_textedit($fieldname) )
        {
            $name = $this->GetFormValue($fieldname);
            if( !file_exists($name) || !is_file($name) )
            {
                $this->SetFieldError($fieldname,_('Cannot file a file by that name (if the file exists, it be outside PHP\'s safe directories.)'));
            }
            else
            {
                return true;
            }
        }

        return false ;
    }

}
/**
*
*
*/
class CCImportFiles
{
    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $items += array(
                'importer'   => array( 
                                 'menu_text'  => _('Import Files'),
                                 'menu_group' => 'configure',
                                 'help' => _('Import files from your server into ccHost'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 40,
                                 'action' =>  ccl('admin','import')
                                 ),
                );
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','import'),  array( 'CCImportFiles', 'Import'), CC_ADMIN_ONLY,
            ccs(__FILE__), '', _("Displays 'File Import' form"), CC_AG_UPLOADS );
    }

    /**
    * Generic handler for submitting original works
    *
    * Displays and process new submission form and assign tags to upload
    *
    * @param string $page_title Caption for page
    * @param string $username Login name of user doing the upload
    */
    function Import($username='', $extra='')
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $page_title = _('Import files');
        CCAdmin::BreadCrumbs(true,array('url' => '','text'=>$page_title));
        $tags = CCUD_ORIGINAL;
        $form_help = _('Use this form to import files already on your server into this ccHost system. WARNING: This form will <b>MOVE</b> your file from its original location to one specified by this ccHost system.');

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
        $form = new CCImportForm($uid);

        if( !empty($_POST['import']) )
        {
            require_once('cchost_lib/cc-mediahost.php');

            $media_host = new CCMediaHost();
            if( $form->ValidateFields() )
            {
                $form->GetFormValues($values);
                $current_path = $values['upload_file_name'];
                $new_name     = 'import_' . basename($current_path);
                $user_tags    = $values['upload_tags'];

                // All fields here that start with 'upload_' are 
                // considered to be fields in the CCUploads table
                // so....
                // Destroy the $_FILES object so it doesn't get
                // confused with that 

                unset($values['upload_file_name']);
                require_once('cchost_lib/cc-uploadapi.php');
                $ret = CCUploadAPI::PostProcessNewUpload(   $values, 
                                                            $current_path,
                                                            $new_name,
                                                            array( CCUD_ORIGINAL, 'media'),
                                                            $user_tags,
                                                            $media_host->_get_upload_dir($username),
                                                            null );

                if( is_string($ret) )
                {
                    $form->SetFieldError('upload_file_name',$ret);
                }
                else
                {
global $CC_GLOBALS;
$url = ccl('files',$CC_GLOBALS['user_name'],$ret);
CCUtil::SendBrowserTo($url);
return;
                    $upload_id = $ret;
                    $uploads =& CCUploads::GetTable();
                    $record = $uploads->GetRecordFromID($upload_id);
                    $url = $media_host->_get_file_page_url($record);
                    $page->Prompt(sprintf(_("Import succeeded. Click %s to see results."), "<a href=\"$url\">here</a>"));
                    return;
                }
            }
        }
        
        if( !empty($form_help) )
            $form->SetFormHelp($form_help);

        $page->AddForm( $form->GenerateForm() );
    }

}



?>
