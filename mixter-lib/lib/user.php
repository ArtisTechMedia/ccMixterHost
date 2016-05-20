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
* $Id: cc-user-hook.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* Module for handling remote user queries
*
* @package cchost
* @subpackage user
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once( 'mixter-lib/lib/status.inc' );

class CCLibUser
{
    function CurrentUser() {
        $name = CCUser::CurrentUserName();
        return empty($name) ? _make_status(USER_NOT_LOGGED_IN) : _make_ok_status($name);
    }

    function Login($username='',$password='',$remember=true) {
        $status = null;
        if( empty($password) ) {
            $status = _make_err_status(USER_MISSING_PASSWORD); 
        } else if( empty($username) ) {
            $status = _make_err_status(USER_MISSING_NAME); 
        } else {
            $password = md5( $password );
            $sql = "SELECT user_id, user_password FROM cc_tbl_user WHERE user_name = '{$username}'";
            $row = CCDatabase::QueryRow($sql);
            if( empty($row) ) {
                $status = _make_err_status(USER_UNKNOWN_USER); 
            } else {
                if( $row['user_password'] != $password ) {
                    $status = _make_err_status(USER_INVALID_PASSWORD); 
                } else {
                    $status = _make_ok_status($username);
                }
            }
        }
        if( $status->ok() ) {
            $this->CreateLoginCookie($username,$password,$remember);
        }
        return $status;
    }

    function Logout() {
        cc_setcookie(CC_USER_COOKIE,'',time());
        unset($_COOKIE[CC_USER_COOKIE]);        
        return _make_ok_status();
    }

    function CreateLoginCookie($username,$password,$remember) {
        if( $remember )
            $time = time()+60*60*24*30;
        else
            $time = null;
        $val = serialize(array($username,$password));
        cc_setcookie(CC_USER_COOKIE,$val,$time);
        return _make_ok_status();
    }

    function Followers($useridOrName='') {
        if( empty($useridOrName) ) {
            $username = CCUser::CurrentUserName();
        } else {
            $username = CCUser::NameForID($useridOrName);
        }
        if( empty($username) ) {
            return _make_err_status(USER_UNKNOWN_USER);
        }
        $followers = CCDatabase::QueryItems( "SELECT user_name FROM cc_tbl_user WHERE LOWER(CONCAT(',',user_favorites,',')) LIKE LOWER('%,{$username},%')" );
        return _make_ok_status($followers);
    }

}
?>
