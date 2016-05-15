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

require_once( 'mixter-lib/lib/user.php');

define('USER_INVALID_CAPTCHA', 'invalid captcha');

CCEvents::AddHandler(CC_EVENT_MAP_URLS, array( 'CCEventsUser',  'OnMapUrls'));

class CCEventsUser
{
    /**
    * Event handler for mapping urls to methods
    *
    * @see CCEvents::MapUrl
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','user','current'), array('CCAPIUser','CurrentUser'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Ajax callback for currently logged in user'), CC_AG_USER);
        CCEvents::MapUrl( ccp('api','user','login'), array('CCAPIUser','Login'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Perform login'), CC_AG_USER);
        CCEvents::MapUrl( ccp('api','user','logout'), array('CCAPIUser','Logout'),
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Perform logout'), CC_AG_USER);
        CCEvents::MapUrl( ccp('api','user','profile'), array('CCAPIUser','Profile'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Return user profile'), CC_AG_USER);
    }

}

class CCAPIUser
{
    function CurrentUser()
    {
        $lib = new CCLibUser();
        $status = $lib->CurrentUser();
        CCUtil::ReturnAjaxObj($status);
    }

    function Login() {
        CCUtil::Strip($_REQUEST);
        /*
        if( empty($_REQUEST['captcha']) || !$this->_pass_captcha($_REQUEST['captcha']) ) {
            $status = _make_err_status(USER_INVALID_CAPTCHA)
        }
        */
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $remember = $_REQUEST['remember'];
        $lib = new CCLibUser();
        $status = $lib->Login($username,$password,$remember);
        CCUtil::ReturnAjaxObj($status);        
    }

    function Logout() {
        $lib = new CCLibUser();
        $status = $lib->Logout();
        CCUtil::ReturnAjaxObj($status);
    }

    function Profile($username='') {
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $username = empty($username) ? CCUser::CurrentUserName() : $username;
        if( empty($username) ) {
            $status = _make_err_status(USER_MISSING_NAME);
        } else {
            $args = $query->ProcessAdminArgs('f=php&t=user_profile&u='.$username);
            list( $value, $mime ) = $query->Query($args);
            $status = _make_ok_status($value);
        }
        CCUtil::ReturnAjaxObj($status);
    }

    function _pass_captcha($value) {
        $key = file_get_contents('cchost_lib/captcha.txt');
        $snoopy = CCUtil::HTTPClient();                
        $link = 'https://www.google.com/recaptcha/api/siteverify';

        @$snoopy->submit($link, array( 'secret' => $key,
                                       'response' => $value,
                                       'remoteip' => $_SERVER['REMOTE_ADDR']));

        return !empty($snoopy->results) && (strstr($snoopy->results,'"success": true') !== FALSE);
    }

}
?>