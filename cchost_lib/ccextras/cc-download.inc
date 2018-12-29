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
* $Id: cc-download.inc 12325 2009-04-21 22:23:23Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/zip/zip.lib.php');
require_once('cchost_lib/ccextras/cc-licensefiles.inc');

class CCDownload
{
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('download'), array('CCDownload','Download'), CC_DONT_CARE_LOGGED_IN,
                ccs(__FILE__), '{user_name}/{upload_id}', _('Download files with other files.'),
                CC_AG_UPLOADS );
        CCEvents::MapUrl( ccp('admin','download'), array('CCDownload','Admin'), CC_ADMIN_ONLY,
                ccs(__FILE__), '', _('Admin Download manager.'),
                CC_AG_UPLOADS );
    }

    function Download($name,$id,$opts='')
    {
        global $CC_GLOBALS;

        $sql = 'SELECT file_name, file_num_download, file_upload FROM cc_tbl_files WHERE file_id='.$id;
        list( $file_name, $num_downloads, $upload_id ) = CCDatabase::QueryRow($sql,false);

        if(!empty($CC_GLOBALS['counter']))
        {
            $uargs['file_num_download'] = ++$num_downloads;
            $uargs['file_id'] = $id;
            $files =& CCFiles::GetTable();
            $files->Update($uargs);
            $sql = "SELECT MAX(file_num_download) FROM cc_tbl_files WHERE file_upload = {$upload_id}";
            $upload_count = CCDatabase::QueryItem($sql);
            $uploads = new CCUploads();
            $xarg['upload_dl_count'] = $upload_count;
            $xarg['upload_id'] = $upload_id;
            $uploads->Update($xarg);
        }

        if(!empty($CC_GLOBALS['license']) || !empty($CC_GLOBALS['checksum']))
        {
            $people_dir = empty($CC_GLOBALS['user-upload-root']) ? 'content' :
                               $CC_GLOBALS['user-upload-root'];

            $file_dir = $people_dir . '/' . $name . '/' . $file_name;
            $zip = new zipfile();
            $zip->addFile($file_dir,$file_name);

            if (!empty($CC_GLOBALS['license']))
                $zip->addFile(CCLicensefiles::Licensefiles($name,$id),'license.rdf');

            if (!empty($CC_GLOBALS['checksum']))
                $zip->addFile(CCChecksum::Checksum($file_dir),'checksum.md5');

            echo $zip->file();
            header('Content-Type: application/x-zip; name=' . substr($file_name, 0, strrpos($file_name, ".")) . '.zip');
            header('Content-Disposition: attachment; filename=' . substr($file_name, 0, strrpos($file_name, ".")) . '.zip');
        }
        else
        {
            $upload_root = CCUser::GetPeopleDir();
            $relative = $upload_root . '/' . $name;

            CCUtil::SendBrowserTo( ccd( $relative, $file_name ) );
        }

    }

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
                'download' => array( 
                                 'menu_text'  => _('Download Manager'),
                                 'menu_group' => 'configure',
                                 'help' => 'Count, checksum and license options for downloading',
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 300,
                                 'action' =>  ccl('admin','download')
                                 ),
                );
        }
    }
    
    function Admin()
    {
        global $CC_GLOBALS;
        $page =& CCPage::GetPage();

        require_once('cchost_lib/cc-admin.php');
        $title = _('Configure Download Manager');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        if( empty($CC_GLOBALS['pretty-urls']) )
        {
            $url = ccl('admin/setup');
            $link1 = "<a href=\"{$url}\">";
            $page->Prompt( sprintf(_('Sorry, but "pretty urls" must be enabled in order to use this feature. Read more %shere%s'),$link1,'</a>'));
            return;
        }
        require_once('cchost_lib/ccextras/cc-download-form.inc');
        $form = new CCDownloadAdminForm();
        $page->AddForm( $form->GenerateForm() );
    }
}
?>
