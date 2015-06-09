<?
/*
 
* Creative Commons has made the contents of this file
* * available under a CC-GNU-GPL license:
* http://creativecommons.org/licenses/GPL/2.0/
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
*
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
* 
* $Id$
*
* 
*/

/* @package cchost
* @subpackage admin
*
*/
if( !defined('IN_CC_HOST') )
    die('Welcome to CC Host');
    
    
/**
*/
class v_5_3_ccplus extends CCUpdate
{
    function Update() {

        $sql = 'ALTER TABLE cc_tbl_roles MODIFY COLUMN role_id INT auto_increment';
        CCDatabase::Query($sql);
        
        $roles = new CCTable('cc_tbl_roles', 'role_id');
        $id = $roles->NextID();
        $args = array( 'role_id' => $id, 'role_role' => 'ccplus' );
        $roles->Insert($args);

        $configs =& CCConfigs::GetTable();
        $rows = $roles->QueryRows('1');
        $roles = array();
        foreach( $rows as $R )
        {
            $roles[ $R['role_role'] ] = $R['role_id'];
        }
        $args['roles'] = $roles;
        $configs->SaveConfig('config', $args, CC_GLOBAL_SCOPE, true );
        
        $page =& CCPage::GetPage();
        $page->Prompt(_('ccplus role installed'));

    }
}

?>