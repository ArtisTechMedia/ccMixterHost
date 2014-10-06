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
* $Id: cc-inc-extras.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

$cc_extras_dirs = CCUtil::SplitPaths($cc_extras_dirs);

foreach( $cc_extras_dirs as $cc_extras_dir )
{
    if (is_dir($cc_extras_dir)) 
    {
        if ($cc_dh = opendir($cc_extras_dir)) 
        {
           while (($cc_file = readdir($cc_dh)) !== false) 
           {
               if( preg_match('/.*\.php$/',$cc_file) )
                   require_once( $cc_extras_dir . '/' . $cc_file);
           }
           closedir($cc_dh);
        }
    }
}

?>