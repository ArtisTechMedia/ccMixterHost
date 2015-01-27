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
* $Id: update_v_5_openid2.inc 12730 2009-06-06 05:42:47Z fourstones $
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
class v_5_openid2 extends CCUpdate
{
    function Update()
    {
        if( $this->_table_exists('cc_tbl_openids') )
        {
            $this->_check_for_field('cc_tbl_openids','openid_show',"int(1)  NOT NULL default '0'");
        }
        $cfg =& CCConfigs::GetTable();
        $CC_GLOBALS['openid-type'] =    /* just to be safe, put it in the globals */
        $arg['openid-type'] = true; /* this we'll write to disk */
        $cfg->SaveConfig( 'config', $arg,'', true );
        $page =& CCPage::GetPage(); $page->Prompt('OpenID Flag is set');
    }
}

?>
