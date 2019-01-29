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
* $Id: cc-renderflash.php 12641 2009-05-23 17:14:26Z fourstones $
*
*/

/**
* @package cchost
* @subpackage video
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* @package cchost
* @subpackage video
*/
class CCRenderFlash 
{
    function OnFilterUploads(&$records)
    {
        $info = array();
        $keys = array_keys($records);
        $c = count($keys);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$keys[$i]];
            if (isset($R['files'][0])) {
                $F = $R['files'][0];
                if( !empty($F['file_format_info']['format-name']) &&
                         ($F['file_format_info']['format-name'] == 'video-swf-swf' ) &&
                         !empty($F['file_format_info']['dim']) )
                {
                    $R['flash_id'] = 'flash_play_' . $R['upload_id'];
                    list( $w, $h ) = $F['file_format_info']['dim'];
                    $info[] = array( 'url' => $R['download_url'],
                                     'w' => $w, 'h' => $h, 'id' => $R['flash_id'],
                                     'title' => $R['upload_name']  );
                }
            }
        }

        if( !empty($info) )
        {
            $page =& CCPage::GetPage();
            $page->PageArg('flash_popup_infos',$info,'flash_popup_play');
        }
    }
    
}


?>
