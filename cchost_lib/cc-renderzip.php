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
* $Id: cc-renderzip.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage archive
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-render.php');

/**
* @package cchost
* @subpackage archive
*/
class CCRenderZip 
{

    function OnFilterMacros(&$records)
    {
        $k = array_keys($records);
        $c = count($k);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$k[$i]];
            if( !CCUploads::InTags('zip',$R) )
                continue;
            $need_macro = false;
            $kf = array_keys($R['files']);
            $kc = count($kf);
            for( $ki = 0; $ki < $kc; $ki++ )
            {
                $F =& $R['files'][$kf[$ki]];
                if( empty($F['file_format_info']['zipdir'] ) )
                    continue;
                $R['zipdirs'][] = array( 'dir' => &$F['file_format_info']['zipdir'],
                                              'name' => $F['file_nicname']
                                            );
                $need_macro = true;
            }
            if( $need_macro )
                $R['file_macros'][] = 'show_zip_dir';
        }
    }
}


?>
