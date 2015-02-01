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
* $id$
*
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

//CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,  array( 'CCFacebook',    'OnAdminMenu');
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCFacebook' , 'OnGetConfigFields' ));

CCEvents::AddHandler(CC_EVENT_FORM_FIELDS,    array( 'CCFacebook', 'OnFormFields'));
CCEvents::AddHandler(CC_EVENT_MAP_URLS,       array( 'CCFacebook',  'OnMapUrls'));
/*
define('FB_BASE_DIR','cchost_lib/facebook/src/');

require_once( FB_BASE_DIR . 'Facebook/FacebookSession.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookRedirectLoginHelper.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookRequest.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookResponse.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookSDKException.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookRequestException.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookAuthorizationException.php' );
require_once( FB_BASE_DIR . 'Facebook/GraphObject.php' );

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
*/

require_once('cchost_lib/cc-page.php');

class CCFacebook
{
    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields['facebook_allow_login'] =
               array(  'label'      => _('Allow Facebook login'),
                       'form_tip'   => _('Check this to allow users to login via Facebook account'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE );
        }
    }
    
    /**
    * Event handler for {@link CC_EVENT_FORM_FIELDS}
    *
    * @param object &$form CCForm object
    * @param object &$fields Current array of form fields
    */
    function OnFormFields(&$form,&$fields)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['facebook_allow_login']) )
            return;
        
        if( is_a($form,'CCUserLoginForm') || is_subclass_of($form,'CCUserLoginForm') ||
                    is_subclass_of($form,'ccuserloginform') )
        {

            if( empty($fields['facebook-login']) )
            {
                $fields['facebook-login'] = 
                            array( 'label'  => 'facebook login',
                                   'form_tip'   => 'log in using your FaceBook account',
                                   'formatter'  => 'metalmacro',
                                   'macro'      => 'facebook.tpl/facebook_login',
                                   'flags'      => CCFF_NOUPDATE);
            }
        }
    }

    function _get_fb_user_object()
    {
        $accesstoken = $_POST['fbaccessid'];
        $userid = $_POST['fbuserid'];
        
        $session = new FacebookSession($accesstoken);
        $request = new FacebookRequest($session, 'GET', '/' . $userid);
        $response = $request->execute();
        $userObject = $response->getGraphObject(GraphUser::className());         
        return $userObject;
    }
    
    function Login()
    {
        $ret = array();
        $userObject = $this->_get_fb_user_object();    
        $email = $userObject->getEmail();
        
        $users =& CCUser::GetTable();
        $row = $users->Query( array( 'user_email' => $email ) );
        
        if( empty($row['user_id']) )
        {
            $ret['status'] = 'error';
            $ret['problem'] = 'no such user';
        }
        else
        {
            require_once('cchost_lib/cc-login.php');
            $login = new CCLogin();
            $login->_create_login_cookie(true,$row['user_name'],$row['user_password']);
            $ret['user_id'] = $row['user_id'];
            $ret['user_name'] = $row['user_name'];            
            $ret['status'] = 'OK';
        }
        
        return CCUtil::ReturnAjaxData($ret);
    }
    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','fb', 'login'), array('CCFacebook','Login'), 
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), 
                          ' POST args: fbaccessid, fbuserid', 
                          _('Log in a Facebook authenticated user'),
                          CC_AG_CCUSER );
        CCEvents::MapUrl( ccp('fbconnect'), array('CCFacebook','API'), 
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), 
                          '[attemptlogin/{email}],[])', 
                          _('Facebook login flow'),
                          CC_AG_CCUSER );
    }

}
?>