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
* $Id: cc-license.php 12626 2009-05-19 17:24:09Z fourstones $
*
*/

/**
* Module for managing Creative Commons licenses
*
* @package cchost
* @subpackage feature
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


function ccRolesAddRole($rolename)
{
        $roles = new CCTable('cc_tbl_roles', 'role_id');
        $id = $roles->NextID();
        $args = array( 'role_id' => $id, 'role_role' => $rolename );
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
}

function ccRolesAssignRoleToUser($user_id, $rolename)
{
    if( !ccRoleUserHasRole($user_id,$rolename) )
    {
        global $CC_GLOBALS;
    
        $userroles = new CCTable('cc_tbl_role_users', 'role_users_user');
        $args['role_users_user'] = $user_id;
        $args['role_users_role'] = $CC_GLOBALS['roles'][$rolename];
        $userroles->Insert($args);
    }
}

function ccRoleGetRolesForUser($user_id)
{
    $userroles = new CCTable('cc_tbl_role_users', 'role_users_user');
    $args['role_users_user'] = $user_id;
    $roleIds = $roles->QueryRows($args);
    $roles = new CCTable('cc_tbl_roles', 'role_id');
    $where = 'role_id IN (' . implode(',',$roldIds) .')';
    return $roles->QueryItems($where);
}

function ccRoleUserHasRole($user_id,$rolename)
{
    $sql = "SELECT COUNT(role_users_role) FROM cc_tbl_role_users JOIN cc_tbl_roles ON role_users_role = role_id " . 
            "WHERE role_role = '{$rolename}' AND role_users_user = {$user_id}";
    return CCDatabase::QueryItem($sql);
}

function ccRoleUsersWithRole($rolename)
{
    $sql = "SELECT role_users_user FROM cc_tbl_role_users JOIN cc_tbl_roles ON role_users_role = role_id " . 
            "WHERE role_role = '{$rolename}' ";
    return CCDatabase::QueryItems($sql);
}

function ccRoleUnassignRoleToUser($user_id,$rolename)
{
    $userroles = new CCTable('cc_tbl_role_users', 'role_users_user');
    $args['role_users_user'] = $user_id;
    $userroles->DeleteWhere($args);    
}
?>
