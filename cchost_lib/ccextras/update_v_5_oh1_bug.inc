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
* $Id: update_v_5_oh1_bug.inc 12624 2009-05-18 15:47:40Z fourstones $
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
class v_5_oh1_bug extends CCUpdate
{
    function Update()
    {
        $cfg =& CCConfigs::GetTable();
        $cfg->SetValue('config', 'cc-host-version', CC_HOST_VERSION, CC_GLOBAL_SCOPE);
        $CC_GLOBALS['cc-host-version']  = CC_HOST_VERSION;
        $page =& CCPage::GetPage(); $page->Prompt( sprintf(_('%s Upgrade Complete'),CC_HOST_VERSION));

    }
}

?>
