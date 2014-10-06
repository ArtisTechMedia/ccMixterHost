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
* $Id: cc-last-edit.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage audio
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,    array( 'CCLastEdit', 'OnUploadDone') );
CCEvents::AddHandler(CC_EVENT_DELETE_FILE,    array( 'CCLastEdit', 'OnDeleteFile') );

/**
*
*
*/
class CCLastEdit
{
    /**
    * Event handler for {@link CC_EVENT_UPLOAD_DONE}
    * 
    * @param integer $upload_id ID of upload row
    * @param string $op One of {@link CC_UF_NEW_UPLOAD}, {@link CC_UF_FILE_REPLACE}, {@link CC_UF_FILE_ADD}, {@link CC_UF_PROPERTIES_EDIT'} 
    * @param array &$parents Array of remix sources
    */
    function OnUploadDone($upload_id, $op)
    {
        if( ($op == CC_UF_FILE_ADD) || ($op == CC_UF_FILE_REPLACE) )
        {
            $ops = array( CC_UF_FILE_ADD => 'add', 
                          CC_UF_FILE_REPLACE => 'replace' );
            $this->_stamp_upload($upload_id, $ops[$op] );
        }
    }

    function OnDeleteFile($file_id)
    {
        $files =& CCFiles::GetTable();
        $upload_id = $files->QueryItemFromKey('file_upload',$file_id);
        $this->_stamp_upload($upload_id, 'del');
    }

    function _stamp_upload($upload_id,$op)
    {
        $uploads =& CCUploads::GetTable();
        $args['upload_id'] = $upload_id;
        $args['upload_last_edit'] = date('Y-m-d H:i:s',time());
        $uploads->Update($args);
        $uploads->SetExtraField($upload_id,'last_op',$op);
    }

}



?>