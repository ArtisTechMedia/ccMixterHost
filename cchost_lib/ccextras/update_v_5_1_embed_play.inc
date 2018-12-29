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
* $Id: update_v_5_1_embed_play.inc 12729 2009-06-06 05:42:01Z fourstones $
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
class v_5_1_embed_play extends CCUpdate
{
    function Update()
    {
        global $CC_GLOBAL;

        if( empty($CC_GLOBALS['embededd_player']) )
        {
            $configs =& CCConfigs::GetTable();
            $CC_GLOBALS['embedded_player'] = 
            $config['embedded_player'] = 'ccskins/shared/players/player_native.php';
            $configs->SaveConfig('config',$config,'',true);
        }
        CCPage::Prompt( _('Multiple embedded player installed') );

    }
}

?>
