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

CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCFacebook' , 'OnGetConfigFields' ));
CCEvents::AddHandler(CC_EVENT_MAP_URLS,       array( 'CCFacebook',  'OnMapUrls'));

define('FB_BASE_DIR','cchost_lib/facebook/src/');

require_once( FB_BASE_DIR . 'Facebook/FacebookSession.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookRedirectLoginHelper.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookRequest.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookResponse.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookSDKException.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookRequestException.php' );
require_once( FB_BASE_DIR . 'Facebook/FacebookAuthorizationException.php' );
require_once( FB_BASE_DIR . 'Facebook/GraphObject.php' );
require_once( FB_BASE_DIR . 'Facebook/GraphUser.php' );
require_once( FB_BASE_DIR . 'Facebook/Entities/AccessToken.php' );

require_once( FB_BASE_DIR . 'Facebook/HttpClients/FacebookHttpable.php' );
require_once( FB_BASE_DIR . 'Facebook/HttpClients/FacebookCurl.php' );
require_once( FB_BASE_DIR . 'Facebook/HttpClients/FacebookCurlHttpClient.php' );
require_once( FB_BASE_DIR . 'Facebook/Entities/AccessToken.php' );
require_once( FB_BASE_DIR . 'Facebook/Entities/SignedRequest.php' );

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\GraphUser;
use Facebook\HttpClients\FacebookHttpable;
use Facebook\HttpClients\FacebookCurl;
use Facebook\HttpClients\FacebookCurlHttpClient;

require_once('cchost_lib/cc-page.php');
require_once('cchost_lib/cc-form.php');
require_once('cchost_lib/cc-login.php');

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
                       
            $fields['facebook-appid'] =                                    
                array( 'label'  => 'Facebook app id',
                       'form_tip'   => '',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE);
                               
            $fields['facebook-secret'] =                                    
                array( 'label'  => 'Facebook app secret',
                       'form_tip'   => '',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE);
        }
    }
    
    function _get_fb_user_object()
    {
        $accesstoken = $_REQUEST['fbaccessid'];
        $userid = $_REQUEST['fbuserid'];
        global $CC_GLOBALS;
        FacebookSession::setDefaultApplication($CC_GLOBALS['facebook-appid'], $CC_GLOBALS['facebook-secret']);
        $session = new FacebookSession($accesstoken);
        $request = new FacebookRequest($session, 'GET', '/' . $userid);
        $response = $request->execute();
        $userObject = $response->getGraphObject(GraphUser::className());         
        return $userObject;
    }
    
    function Attach($model_id)
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Attatch Facebook Account'));
        $users =& CCUsers::GetTable();
        $email = $users->QueryItemFromKey( 'user_email', $model_id);
        $rows = $users->QueryRows( array( 'user_email' => $email ) );
        $form = new CCFBAssociateAccountForm($rows);

        if( empty($_POST['fbassociateaccount']) )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            foreach( $rows as $R )
            {
                $users->UnsetExtraField($R['user_id'],'facebook-account');
            }
            $form->GetFormValues($fields);
            $user_id = $_REQUEST['user_id'];
            $row = $users->QueryKeyRow($user_id);
            require_once('cchost_lib/cc-login.php');
            $login = new CCLogin();
            $login->_create_login_cookie(true,$row['user_name'],$row['user_password']);
            if( $fields['make_perm'] == 1 )
            {
                $users->SetExtraField($user_id,'facebook-account',1);
            }
            CCUtil::SendBrowserTo( ccl('people',$row['user_name']) );
        }
    }
    
    function CreateAccount()
    {
        global $CC_GLOBALS;
        
        $page =& CCPage::GetPage();

        $form = new CCFBCreateAccountForm("",'');
        
        if( empty($_POST['fbcreateaccount']) || !$form->ValidateFields() )
        {
            $userObject = $this->_get_fb_user_object();    
            $firstname = $userObject->getFirstName();
            $lastname = $userObject->getLastName();
        
            $name = strtolower( $firstname . $lastname );
            $name = preg_replace('/[^A-Za-z0-9]/', '_', $name);
            $email = $userObject->getEmail();
            $rname = $firstname . ' ' . $lastname;
            $form->SetHiddenField( 'user_real_name', $rname, CCFF_HIDDEN  );
            $form->SetHiddenField( 'user_email', $email, CCFF_HIDDEN  );
            $form->SetHiddenField( 'fbaccessid', $_REQUEST['fbaccessid'], CCFF_HIDDEN  );
            $form->SetHiddenField( 'fbuserid', $_REQUEST['fbuserid'], CCFF_HIDDEN  );
            
            $form->PopulateValues( array( 'user_name' => $name, 
                                          'user_email' => $email, 
                                          'user_real_name' => $rname,
                                          'fbaccessid' => $_REQUEST['fbaccessid'],
                                           'fbuserid' => $_REQUEST['fbuserid']) );
            $page->AddForm($form->GenerateForm());
        }
        else
        {
            $form->GetFormValues($values);
            $login = new CCLogin();
            $users =& CCUsers::GetTable();
            $args = array(
                'user_real_name' => $_REQUEST['user_real_name'],
                'user_email' => $_REQUEST['user_email'],
                'user_name' => $values['user_name'],
                'user_password' => $login->_make_new_password(),
                'user_registered' => date( 'Y-m-d H:i:s' )
            );
            $args['user_id'] = $users->NextID();
            $users->Insert($args);
            $users->SetExtraField($args['user_id'],'facebook-account',1);
            $login->_create_login_cookie(true,$args['user_name'],$args['user_password']);
            CCUtil::SendBrowserTo( $CC_GLOBALS['home-url'] );
        }
    }
    
    function Login()
    {
        $ret = array();
        $userObject = $this->_get_fb_user_object();    
        $email = $userObject->getEmail();
        
        $users =& CCUsers::GetTable();
        $rows = $users->QueryRows( array( 'user_email' => $email ) );
        if( count($rows) > 1 )
        {
            foreach( $rows as $R )
            {
                if( $users->GetExtraField($R['user_id'], 'facebook-account') )
                {
                    $rows = array( $R );
                    break;
                }
            }
        }
        $ret = array();
        if( empty($rows) )
        {
            $ret['status'] = 'error';
            $ret['problem'] = 'no such user';
            $ret['email'] = $email;
            $ret['fbaccessid'] = $_REQUEST['fbaccessid'];
            $ret['fbuserid'] = $_REQUEST['fbuserid'];
            $ret['num_users'] = 0;
        }
        else if( count($rows) > 1 )
        {
            $ret['num_users'] = count($rows);                
            $ret['users'] = array();
            foreach( $rows as $R)
            {
                $ret['users'][] = array( 'user_id' => $R['user_id'],
                                         'user_name' => $R['user_name'] );
            }
            $ret['status'] = 'action required';
            $ret['problem'] = 'multiple matches';
        }
        else
        {
            $row = $rows[0];
            require_once('cchost_lib/cc-login.php');
            $login = new CCLogin();
            $login->_create_login_cookie(true,$row['user_name'],$row['user_password']);
            $ret['num_users'] = 1;
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
                          CC_AG_USER );
        CCEvents::MapUrl( ccp('fbcreate'), array('CCFacebook','CreateAccount'), 
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), 
                          '', 
                          _('Facebook login flow'),
                          CC_AG_USER );
        CCEvents::MapUrl( ccp('fbattach'), array('CCFacebook','Attach'), 
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), 
                          '/{users_id_like_this}', 
                          _('Facebook login flow'),
                          CC_AG_USER );
    }

}

class CCFBCreateAccountForm extends CCForm
{
    /**
    * Constructor
    */
    function CCFBCreateAccountForm()
    {
        global $CC_GLOBALS;

        $this->CCForm();

        $fields = array( 
                    'user_name' =>
                        array( 'label'  => 'Profile URL name',
                               'formatter'  => 'newusername',
                               'formatter_module' => 'cchost_lib/cc-login.php',
                               'form_tip' => 'This name will be used for your profile page: for example: ' .
                                    $CC_GLOBALS['home-url'] . 'people/yournamehere',
                               'flags'      => CCFF_REQUIRED | CCFF_POPULATE )                               
                               );
                               
        $this->AddFormFields($fields);
    }
}
class CCFBAssociateAccountForm extends CCForm
{
    function CCFBAssociateAccountForm($rows)
    {
        $this->CCForm();
        
        $options = array();
        foreach( $rows as $R )
        {
            $uid = $R['user_id'];
            $options[$uid] = $R['user_real_name'] . ' (' . $R['user_name'] . ')';
        }        
        $fields = array( 
                    'user_id' =>
                        array( 'label'      => _('Use this account'),
                               'form_tip'   => _('Select the account to associate with your Facebook login'),
                               'formatter'  => 'select',
                               'value'      => $rows[0]['user_id'],
                               'options'    => $options,
                               'flags'      => CCFF_POPULATE ),
                               
                    'make_perm' => 
                        array( 'label'      => _('Use as default'),
                                'form_tip'  => _('Always use this account'),
                                'formatter' => 'checkbox',
                                'value'     => 1,
                                'flags'     => CCFF_POPULATE )
                    );
                    
        $this->AddFormFields($fields);                            
    }
}
?>