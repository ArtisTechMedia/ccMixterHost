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
* $Id: update_v_5_1_button.inc 12624 2009-05-18 15:47:40Z fourstones $
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
class v_5_1_button extends CCUpdate
{
    function Update()
    {
        global $CC_GLOBALS;
        
        require_once('cchost_lib/cc-page.php');
        $cfg =& CCConfigs::GetTable();
        $skin_settings = $cfg->GetConfig('skin-settings');
        $page =& CCPage::GetPage();        
        $page->vars['button_style'] =   /* the page is probably already built, so that will be used in cached headers */
        $CC_GLOBALS['button_style'] =    /* just to be safe, put it in the globals */
        $skin_settings['button_style'] = 'ccskins/shared/layouts/button_browser.php'; /* this we'll write to disk */
        $cfg->SaveConfig( 'skin-settings', $skin_settings,'',false );
        $page->Prompt( sprintf(_('Installed new buttons') ) );
    }
}

?>
