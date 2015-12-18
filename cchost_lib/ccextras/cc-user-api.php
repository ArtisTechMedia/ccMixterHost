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

CCEvents::AddHandler(CC_EVENT_MAP_URLS, array( 'CCUserRemoteAPI',  'OnMapUrls'));

class CCUserRemoteAPI
{
    function CurrentUser()
    {
        if(!CCUser::IsLoggedIn()) {
            //$empty = array();
            //CCUtil::ReturnAjaxData($empty,false);
            header( 'Content-type: text/plain');
            print ('[]');
            exit;
        } else {
        
            global $CC_GLOBALS;

            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $username = CCUser::CurrentUserName();
            $args = $query->ProcessAdminArgs('f=js&t=user_profile&u='.$username);
            $query->Query($args);
        }
    }

    /**
    * Event handler for mapping urls to methods
    *
    * @see CCEvents::MapUrl
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','user','current'), array('CCUserRemoteAPI','CurrentUser'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Ajax callback for currently logged in user'), CC_AG_USER);
    }

}
?>
