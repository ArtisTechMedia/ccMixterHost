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
* $Id: cc-renderimage.php 12642 2009-05-24 00:44:39Z fourstones $
*
*/

/**
* @package cchost
* @subpackage image
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* @package cchost
* @subpackage image
*/
class CCRenderImage
{
    function OnFilterUploads(&$records)
    {
        global $CC_GLOBALS;
        
        $info = array();
        $keys = array_keys($records);
        $c = count($keys);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$keys[$i]];
            if (isset($R['files'][0])) {
                if (isset($R['files'][0]['file_name'])) {
                    $F = $R['files'][0];
                    if( !empty($F['file_format_info']['media-type']) &&
                             ($F['file_format_info']['media-type'] == 'image' ) &&
                             !empty($F['file_format_info']['dim']) )
                    {
                        $R['image_id'] = 'image_show_' . $R['upload_id'];
                        list( $w, $h ) = $F['file_format_info']['dim'];
                        //if( !empty($CC_GLOBALS['thumbnail-on']) )
                        {
                            $R['thumbnail'] = ccl('thumbnail',$R['upload_id']);
                        }
                        $info[] = array( 'url' => $R['download_url'],
                                         'w' => $w, 'h' => $h, 'id' => $R['image_id'],
                                         'title' => $R['upload_name'] );
                    }
                }
            }
        }

        if( !empty($info) )
        {
            $page =& CCPage::GetPage();
            $page->PageArg('image_popup_infos',$info,'image_popup');
        }
    }

    function _err_out($msg)
    {
        die($msg);
    }
    
    function Thumbnail($upload_id='')
    {
        global $CC_GLOBALS;

        $upload_id = sprintf('%d',$upload_id);
        if( empty($upload_id) )
            $this->_erro_out('bad upload_id');
        //if( empty($CC_GLOBALS['thumbnail-on']) )
        //    CCUtil::Send404(true,'','','thumbnails not on');
        if( empty($CC_GLOBALS['thumbnail-exec']) )
            $this->_erro_out('thumbnail-exec empty');

        $this->_ensure_globals();
        $tsql = $this->_thumb_name_sql();
        $sql =<<<EOF
          SELECT user_name, {$tsql}, file_name
          FROM cc_tbl_files
          JOIN cc_tbl_uploads on file_upload=upload_id
          JOIN cc_tbl_user on upload_user=user_id
          WHERE file_order = 0 AND upload_id = {$upload_id}
EOF;
        $info = CCDatabase::QueryRow($sql);
        if( empty($info) )
            $this->_erro_out('no upload rec');
        $thumbdir = cca( $CC_GLOBALS['user-upload-root'], $info['user_name'], 'thumbs' );
        CCUtil::MakeSubdirs($thumbdir);
        $thumbfile = $thumbdir . '/' . $info['thumb_name'];
        if( !file_exists($thumbfile) )
        {
            $srcfile = cca( $CC_GLOBALS['user-upload-root'], $info['user_name'], $info['file_name']);
            $cmd = str_replace( '%file_in%', '"'.$srcfile.'"', $CC_GLOBALS['thumbnail-exec'] );
            $cmd = str_replace( '%file_out%', $thumbfile, $cmd );
            exec($cmd);
            chmod($thumbfile,cc_default_file_perms());
        }
        if( !file_exists($thumbfile) )
            $this->_erro_out('file never generated');
        header ("Content-Type: {$CC_GLOBALS['thumbnail-mime']}");
        readfile($thumbfile);
        exit;                    
    }

    function OnDeleteUpload(&$R)
    {
        $this->_ensure_globals();
        $upload_id = $R['upload_id'];
        $tsql = $this->_thumb_name_sql();
        $sql =<<<EOF
          SELECT user_name, {$tsql}
          FROM cc_tbl_files
          JOIN cc_tbl_uploads on file_upload=upload_id
          JOIN cc_tbl_user on upload_user=user_id
          WHERE file_order = 0 AND upload_id = {$upload_id}
EOF;
        $this->_del_thumb($sql);
    }
    
    function OnDeleteFile($file_id)
    {
        $this->_ensure_globals();
        $tsql = $this->_thumb_name_sql();
        $sql =<<<EOF
          SELECT user_name, {$tsql}
          FROM cc_tbl_files
          JOIN cc_tbl_uploads on file_upload=upload_id
          JOIN cc_tbl_user on upload_user=user_id
          WHERE file_order = 0 AND file_id = {$file_id}
EOF;
        $this->_del_thumb($sql);
    }

    function _ensure_globals()
    {
        global $CC_GLOBALS;
        if( empty($CC_GLOBALS['thumbnail-ext']) )
            $CC_GLOBALS['thumbnail-ext'] = 'jpg';
        else
            $CC_GLOBALS['thumbnail-ext'] = trim('jpg','.');
        if( empty($CC_GLOBALS['thumbnail-mime']) )
            $CC_GLOBALS['thumbnail-mime'] = 'image/jpeg';
    }
    
    function _thumb_name_sql()
    {
        global $CC_GLOBALS;
        
        return "CONCAT(file_id,'.{$CC_GLOBALS['thumbnail-ext']}') as thumb_name";
    }
    
    function _del_thumb($sql)
    {
        global $CC_GLOBALS;
        
        $info = CCDatabase::QueryRow($sql);
        if( empty($info) )
            return;
        $thumbdir = cca( $CC_GLOBALS['user-upload-root'], $info['user_name'], 'thumbs' );
        $thumbfile = $thumbdir . '/' . $info['thumb_name'];
        if( !file_exists($thumbfile) )
            return;
        @unlink($thumbfile);
    }

    
    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('thumbnail'), array('CCRenderImage','Thumbnail'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{upload_id}', _('Display thumbnail'), CC_AG_RENDER );
        CCEvents::MapUrl( ccp('admin','thumbnail'), array('CCRenderImage','Admin'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Display thumbnail admin form'), CC_AG_RENDER );
    }

    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        require_once('cchost_lib/cc-renderimage-form.php');
        $page =& CCPage::GetPage();
        $title = _("Edit Thumbnail Properties");
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);
        $form = new CCAdminThumbnailForm();
        $page->AddForm( $form->GenerateForm() );
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items, $scope)
    {
        if( $scope != CC_GLOBAL_SCOPE )
            return;

        $items += array( 
            'thumbnails'=> array( 'menu_text'  => _('Thumbnails'),
                                'menu_group' => 'configure',
                                'access'     => CC_ADMIN_ONLY,
                                'help'       => _('Configure thumbnails handling (for image uploads)'),
                                'weight'     => 160,
                                'action'     => ccl('admin','thumbnail') )
                        );
    }  
}


?>
