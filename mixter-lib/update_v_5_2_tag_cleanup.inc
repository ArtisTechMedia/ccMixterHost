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
* $Id: update_v_5_2_tag_cleanup.inc 13831 2009-12-25 12:35:42Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
class v_5_2_tag_cleanup extends CCUpdate
{
    function Update()
    {
        
        $sql = array();
        
        $sql[] = "UPDATE cc_tbl_uploads SET upload_tags = CONCAT(',',upload_tags) WHERE SUBSTRING(upload_tags, 1) <> ','";
        $sql[] = "UPDATE cc_tbl_uploads SET upload_tags = CONCAT(upload_tags,',') WHERE SUBSTRING(upload_tags,-1) <> ','";
        
        CCDatabase::Query($sql);
        
        $rip_ids = CCDatabase::QueryItems("SELECT upload_id FROM cc_tbl_uploads WHERE upload_tags LIKE '%,rip_soundtrack,%'");
        require_once('cchost_lib/cc-uploadapi.php');
        foreach( $rip_ids as $upload_id )
        {
            CCUploadAPI::UpdateCCUD($upload_id,'remix','');
        }
        
        $page =& CCPage::GetPage(); $page->Prompt( _('Tag clean-up done') );
    }
}

?>
