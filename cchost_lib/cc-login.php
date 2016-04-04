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
* $Id: cc-login.php 12619 2009-05-14 18:36:22Z fourstones $
*
*/

/**
* Module for handling user login
*
* @package cchost
* @subpackage user
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-user.inc');
require_once('cchost_lib/cc-seckeys.php');

function generator_recaptcha2($form, $varname, $value='',$class='')
{
    $html = '<div class="g-recaptcha" data-sitekey="6Le3ihwTAAAAAPk3emDQWuFkttPgN8FhQx5wbs3n"></div>';
    return $html;
}


function validator_recaptcha2($form,$fieldname)
{
    $key = file_get_contents('./cchost_lib/captcha.txt');
    require_once('cchost_lib/snoopy/Snoopy.class.php');
    $snoopy = new Snoopy();
    global $CC_GLOBALS;
    
    if( !empty($CC_GLOBALS['curl-path']) )
    {
        $snoopy->curl_path = $CC_GLOBALS['curl-path'];
    }
    $snoopy->maxredirs = 8;
    $snoopy->offsiteok = true;
    
    $value = $form->GetFormValue($fieldname);
    $link = 'https://www.google.com/recaptcha/api/siteverify';

    @$snoopy->submit($link, array( 'secret' => $key,
                                   'response' => $value,
                                   'remoteip' => '151.30.80.166' )); // $_SERVER['REMOTE_ADDR']));

    if( !empty($snoopy->results) && (strstr($snoopy->results,'"success": true') !== FALSE) ) {
        return true;
    }

    $form->SetFieldError($fieldname, _("Yea.... no"));
    return false;

}

/**
* Registeration form
*/
class CCNewUserForm extends CCUserForm
{
    /**
    * Constructor
    */
    function CCNewUserForm()
    {
        global $CC_GLOBALS;

        $this->CCUserForm();

        $fields = array( 
                    'user_name' =>
                        array( 'label'  => 'str_login_name',
                               'formatter'  => 'newusername',
                               'form_tip' => 'str_login_this_must_consist',
                               'flags'      => CCFF_REQUIRED  ),
                    'user_email' =>
                       array( 'label'       => 'str_e_mail',
                               'formatter'  => 'email',
                               'form_tip' => 'str_login_this_address_will',
                               'flags'      => CCFF_REQUIRED ),
                );

        $has_mail = !empty($CC_GLOBALS['reg-type']) && ($CC_GLOBALS['reg-type'] != CC_REG_NO_CONFIRM);

        if( !$has_mail )
        {
            $fields += array(
                    'user_password' =>
                       array(  'label'     => 'str_login_password',
                               'formatter'     => 'password',
                               'form_tip'  => 'str_login_this_must_be',
                               'flags'         => CCFF_REQUIRED )
                );
        }

        $fields += array(
                'g-recaptcha-response' =>
                    array( 'label' => '',
                            'formatter' => 'recaptcha2',
                            'form_tip' => '',
                            'flags' => CCFF_NOUPDATE | CCFF_REQUIRED )
            );
        /*
        $fields += array( 
                    'user_mask' =>
                       array( 'label'       => '',
                               'formatter'  => 'securitykey',
                               'form_tip'   => '',
                               'flags'      => CCFF_NOUPDATE),
                    'user_confirm' =>
                       array(  'label'       => 'str_security_key',
                               'formatter'  => 'securitymatch',
                               'autocomp'   => 'off',
                               'class'      => 'cc_form_input_short',
                               'form_tip'   => CCSecurityVerifierForm::GetSecurityTipStr(),
                               'flags'      => CCFF_REQUIRED | CCFF_NOUPDATE)
            );
        */

        if( $has_mail )
        {
            $fields += array(
                    '_lost_password' =>
                       array(  'label'      => '',
                               'formatter'  => 'button',
                               'url'        => ccl('lostpassword'),
                               'value'      => 'str_login_lost_password',
                               'flags'      => CCFF_NONE | CCFF_NOUPDATE  | CCFF_STATIC),
                        );
        }

        $this->AddFormFields( $fields );
        $this->SetSubmitText('str_login_register');        
    }

}

/**
 * Handles generation of &lt;input type='text' HTML field 
 * 
 * 
 * @param string $varname Name of the HTML field
 * @param string $value   value to be published into the field
 * @param string $class   CSS class (rarely used)
 * @returns string $html HTML that represents the field
 */
function generator_newusername($form, $varname,$value='',$class='')
{
    return( $form->generator_textedit($varname,$value,$class) );
}


/**
* Handles validator for HTML field, called during ValidateFields()
* 
* Validates uniqueness of name as well as character checks and length.
* 
* @see CCForm::ValidateFields()
* 
* @param string $fieldname Name of the field will be passed in.
* @returns bool $ok true means field validates, false means there were errors in user input
*/
function validator_newusername($form, $fieldname)
{
    if( $form->validator_must_exist($fieldname) )
    {
        $value = $form->GetFormValue($fieldname);

        if( preg_match('/[^A-Za-z0-9_]/', $value) )
        {
            $form->SetFieldError($fieldname, array('str_login_this_must_letters') );
            return(false);
        }

        if( strlen($value) > 25 )
        {
            $form->SetFieldError($fieldname, array('str_login_this_must_be_less') );
            return(false);
        }

        $user = CCDatabase::QueryItem('SELECT user_id FROM cc_tbl_user WHERE user_name=\''.$value.'\'');

        if( empty($user) )
        {
            require_once('cchost_lib/cc-tags.inc');
            $tags =& CCTags::GetTable();
            $user = $tags->QueryKeyRow($value);
        }

        if( $user )
        {
            $form->SetFieldError($fieldname,array('str_login_that_username_is'));
            return(false);
        }


        return( true );
    }

    return( false );
}


/**
* Login form 
*/
class CCUserLoginForm extends CCUserForm
{
    /**
    * Constructor
    */
    function CCUserLoginForm()
    {
        $this->CCUserForm();

        $fields = array( 
                    'user_name' =>
                        array( 'label'      => 'str_login_name',
                               'formatter'  => 'username',
                               'flags'      => CCFF_REQUIRED ),

                    'user_password' =>
                       array(  'label'       => 'str_login_password',
                               'formatter'  => 'matchpassword',
                               'flags'      => CCFF_REQUIRED ),

                    'user_remember' =>
                       array(  'label'      => 'str_login_remember_me',
                               'formatter'  => 'checkbox',
                               'flags'      => CCFF_NONE ),

                    '_new_user' =>
                       array(  'label'       => '',
                               'formatter'  => 'button',
                               'url'        => ccl('register'),
                               'value'      => 'str_login_new_user',
                               'flags'      => CCFF_NONE | CCFF_NOUPDATE  | CCFF_STATIC),
                        );

        $has_mail = empty($CC_GLOBALS['reg-type']) || ($CC_GLOBALS['reg-type'] == CC_REG_NO_CONFIRM);

        if( $has_mail )
        {
            $fields += array( 
                    '_lost_password' =>
                       array( 'label'       => '',
                               'formatter'  => 'button',
                               'url'        => ccl('lostpassword'),
                               'value'      => 'str_login_lost_password',
                               'flags'      => CCFF_NONE | CCFF_NOUPDATE  | CCFF_STATIC),
                    );
        }

        $this->AddFormFields( $fields );
        $this->SetSubmitText('str_log_in');
    }
}

/**
* Form for when user need a password reminder
*/
class CCLostPasswordForm extends CCUserForm
{
    /**
    * Constructor
    */
    function CCLostPasswordForm()
    {
        $this->CCUserForm();

        $fields = array( 
                    'user_name' =>
                        array( 'label'      => 'str_login_name',
                               'formatter'  => 'username',
                               'flags'      => CCFF_REQUIRED ),

                    '_new_user' =>
                       array( 'label'       => '',
                               'formatter'  => 'button',
                               'value'      => 'str_login_new_user',
                               'url'        => ccl('register'),
                               'flags'      => CCFF_NOUPDATE  | CCFF_STATIC),
                        );

        $this->AddFormFields( $fields );
        $this->SetSubmitText('str_login_retrieve_password');
    }
}

/**
* General log in API and system event handler class
*/
class CCLogin
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
            $fields['reg-type'] =
               array(  'label'      => _('Registration Confirmation'),
                       'form_tip'   => _('What type of registrations confirmation should the system use.'),
                       'value'      => 'usermail',
                       'formatter'  => 'select',
                       'options'    => array( 
                                        CC_REG_USER_EMAIL => 
                                        _('Send user email with new password'),
                                        CC_REG_ADMIN_EMAIL => 
                                        _('Send admin email to confirm new login information'),
                                        CC_REG_NO_CONFIRM => 
                                        _('On screen confirm (no emails used)')
                                        ),
                       'flags'      => CCFF_POPULATE  ); // do NOT require cookie domain, blank is legit
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAIN_MENU}
    * 
    * @see CCMenu::AddItems()
    */
    function OnBuildMenu()
    {
        $items = array(
            'register'  => array( 
                             'menu_text'  => 'str_login_register',
                             'access'  => CC_ONLY_NOT_LOGGED_IN,
                             'menu_group' => 'artist',
                             'weight' => 5,
                             'action' => ccp('register')
                             ),


            'login'  => array( 
                             'menu_text'  => 'str_log_in',
                             'access'  => CC_ONLY_NOT_LOGGED_IN,
                             'id'     => 'menu_item_login',
                             'menu_group' => 'artist',
                             'weight' => 1,
                             'action' => ccp('login')
                             ),

                );
    
        CCMenu::AddItems($items);
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'register',       array( 'CCLogin', 'Register'),        
            CC_ONLY_NOT_LOGGED_IN, ccs(__FILE__), '', _('Show register form'), CC_AG_USER );
        CCEvents::MapUrl( 'login',          array( 'CCLogin', 'Login'),           
            CC_ONLY_NOT_LOGGED_IN, ccs(__FILE__), '', _('Show login form'), CC_AG_USER  );
        CCEvents::MapUrl( 'logout',         array( 'CCLogin', 'Logout'),          
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Logout current user'), CC_AG_USER  );
        CCEvents::MapUrl( 'lostpassword',   array( 'CCLogin', 'LostPassword'),    
            CC_ONLY_NOT_LOGGED_IN, ccs(__FILE__), '', _('Show lost password form'), CC_AG_USER  );
        CCEvents::MapUrl( 's',              array( 'CCLogin', 'OnSecurityCallback'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Security callback'), CC_AG_USER );
    }

    /**
    * Puts up a registration for, handler for /register URL
    */
    function Register()
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        $this->_bread_crumbs_login($page,'str_login_create_acc');
        $page->SetTitle('str_login_create_acc');
        $form = new CCNewUserForm();
//        $form->SetHelpText('str_login_this_site_req');
        
        $help = _('Already have an account? Log in <a href="/login">here</a>.');
        
        if( !empty($CC_GLOBALS['facebook_allow_login']) )
        {
            $help .= '<br /><br />' . _('Either way you can login via Facebook: ') . '<br /><br />' .
                '<fb:login-button scope="public_profile,email" onlogin="fb_check_login_state();"></fb:login-button>' .
                '<br />';
        }
        
        $form->SetHelpText($help);
        
        $show = empty($_POST['newuser']) || !$form->ValidateFields();

        if( !$show )
        {
            $form->GetFormValues($fields);
            $reg_type = empty($CC_GLOBALS['reg-type']) ? null : $CC_GLOBALS['reg-type'];
            if( !empty($reg_type) && $reg_type != CC_REG_NO_CONFIRM )
            {
                $new_password = $this->_make_new_password();
                $fields['user_password'] = md5($new_password);
            }

            $status = array();
            CCEvents::Invoke( CC_EVENT_USER_REGISTERED, array( $fields, &$status ) );
            $show = !empty($status['error']);
            if( $show )
            {
                if( !empty($status['error_field']) )
                {
                    $form->SetFieldError($status['error_field'],$status['error']);
                }
                else
                {
                    $msg= $status['error'];
                    if( CCDebug::IsEnabled() )
                        $msg .= " " . $status['sql_error'];
                    $page->SystemError($msg);
                }
            }
            else
            {
                $fields['user_registered'] = date( 'Y-m-d H:i:s' );
                $fields['user_real_name'] = $fields['user_name'];
                $users =& CCUsers::GetTable();

                if( empty($reg_type) || $reg_type == CC_REG_NO_CONFIRM )
                {
                    $url = ccl('login');
                }
                else
                {
                    if( $reg_type == CC_REG_ADMIN_EMAIL )
                    {
                        $why = _('A new account has been requested by:') . ' ' . $fields['user_name'] . ' ' . _('email') . ': ' .
                                $fields['user_email'] . ' ' . _('from IP:') . ' ' . $_SERVER['REMOTE_ADDR'];
                        $to = $CC_GLOBALS['mail_sender'];
                    }
                    else
                    {
                        $why = 'str_login_you_are_rec';
                        $to =  $fields['user_email'];
                    }

                    $this->_send_login_info($fields['user_name'], 'str_login_new_account',$why,$new_password,$to);
                    $url =  ccl('login','new','confirm');

                }

                $users->Insert($fields);

                // We have to redirect here to the login form
                // because on some servers (IIS) throw away
                // cookie information. I guess we could check 
                // for platform but now we just act the same
                // no matter who you are
                CCUtil::SendBrowserTo($url);
            }
        }

        if( $show )
        {
            $page->AddForm( $form->GenerateForm() );
        }
    }

    /**
    * Handles /logout URL 
    */
    function Logout()
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->_bread_crumbs($page,'str_log_out');
        cc_setcookie(CC_USER_COOKIE,'',time());
        unset($_COOKIE[CC_USER_COOKIE]);
        $page->Prompt('str_log_logged_out');
        $page->SetTitle('str_log_out');
        CCEvents::Invoke( CC_EVENT_LOGOUT, array( $CC_GLOBALS['user_name'] ) );
        unset($CC_GLOBALS['user_name']);
        unset($CC_GLOBALS['user_id']);
    }

    function _bread_crumbs(&$page, $text)
    {
        $trail[] = array( 'url' => ccl(), 'text' => 'str_home' );
        $trail[] = array( 'url' => '',    'text' => $text );
        $page->AddBreadCrumbs($trail);
    }

    function _bread_crumbs_login(&$page, $text)
    {
        $trail[] = array( 'url' => ccl(), 'text' => 'str_home' );
        $trail[] = array( 'url' => ccl('login'),    'text' => 'str_log_in' );
        $trail[] = array( 'url' => '',    'text' => $text );
        $page->AddBreadCrumbs($trail);
    }
    
    /**
    * Handles /login URL, puts up log in form
    */
    function Login($do_ui=true,$confirm='')
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->_bread_crumbs($page,'str_log_in');

        $do_ui = is_string($do_ui) || $do_ui; // sorry bout that

        $form = new CCUserLoginForm();

        if( empty($_POST['userlogin']) || !$form->ValidateFields() )
        {
            
            if( !empty($confirm) )
            {
                $reg_type = empty($CC_GLOBALS['reg-type']) ? null : $CC_GLOBALS['reg-type'];
                if( $reg_type == CC_REG_ADMIN_EMAIL )
                    $rmsg = 'str_login_your_reg_has';
                elseif( $reg_type == CC_REG_USER_EMAIL )
                    $rmsg = 'str_login_your_new_login';
                if( !empty($rmsg) )
                    $page->Prompt($rmsg);
            }

            CCEvents::Invoke(CC_EVENT_LOGIN_FORM,array(&$form));
            require_once('cchost_lib/cc-page.php');
            $page->SetTitle('str_log_in');
            $page->AddForm( $form->GenerateForm() );
            $ok = false;
        }
        else
        {
            $CC_GLOBALS = array_merge($CC_GLOBALS,$form->record);
            CCEvents::Invoke(CC_EVENT_LOGIN, array( $CC_GLOBALS['user_id'] ) );
            
            $remember = $form->GetFormValue('user_remember');
            $this->_create_login_cookie($remember);

            if( $do_ui )
            {
                $ref = $form->GetFormValue('http_referer');
                $this->_do_login_redirect($ref);
            }
            $ok = true;
        }

        return( $ok );
    }

    function _do_login_redirect($ref)
    {
        global $CC_GLOBALS;
        
        if( !empty($ref) )
        {
            $ref = urldecode($ref);
            if( preg_match('/logout$/',$ref ) || ($ref == '/'))
                $ref = '';
        }
        if( empty( $ref ) )
            $url = ccl('people',$CC_GLOBALS['user_name'] );
        else
            $url = $ref;
        CCUtil::SendBrowserTo( $url );
    }
    
    function _create_login_cookie($remember,$user='',$pw='')
    {
        global $CC_GLOBALS;
        
        if( $remember )
            $time = time()+60*60*24*30;
        else
            $time = null;
        if( empty($user) )
            $user = $CC_GLOBALS['user_name'];
        if( empty($pw) )
            $pw = $CC_GLOBALS['user_password'];
        $val = serialize(array($user,$pw));
        cc_setcookie(CC_USER_COOKIE,$val,$time);
    }
    
    /**
    * Handler for /lostpassword URL puts up form an responds to it (not implemented yet)
    */
    function LostPassword()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->_bread_crumbs_login($page,'str_login_recover_lost_password');
        $page->SetTitle('str_login_recover_lost_password');
        $form = new CCLostPasswordForm();
        if( empty($_POST['lostpassword']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($fields);
            $user_name = $fields['user_name'];
            $users =& CCUsers::GetTable();
            $row = $users->QueryRow($fields);
            $new_password = $this->_make_new_password();

            $args['user_id'] = $row['user_id'];
            $args['user_password'] = md5($new_password);
            $users->Update($args);
            CCEvents::Invoke(CC_EVENT_LOST_PASSWORD, array( $args['user_id'] , &$row));

            $why = 'str_login_you_are_rec2';
            $this->_send_login_info($user_name,'str_login_recover_lost_password',$why,$new_password,$row['user_email']);
            $page->Prompt('str_login_new_password');
        }

    }

    function _make_new_password($len=6)
    {
        return( substr( md5(uniqid(rand(),true)), rand() & 7, $len ) );
    }

    function _send_login_info($user_name,$subject,$why,$new_password,$to)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $why     = $page->String($why);
        $subject = $page->String($subject);
        $msg     = $page->String('str_login_email_message');
        
        $configs =& CCConfigs::GetTable();
        $ttags = $configs->GetConfig('ttag');
        $site_name = $ttags['site-title'];

        $url = ccl('login');
        $msg = sprintf(_($msg),$site_name,$why,$url,$user_name,$new_password,
                               $site_name);
        
        require_once('cchost_lib/ccextras/cc-mail.inc');
        $mailer = new CCMailer();
        $mailer->To($to);
        $mailer->Body($msg);
        $mailer->Subject($site_name . ': ' . $subject);
        $mailer->Send();
    }

    /**
    * Handles /s URL
    * 
    * This function does NOT return, it sends an image back to the browser then exits.
    * 
    * @see CCNewUserForm::generator_securitykey()
    * @param integer $s Combination ID and index into a security key
    */
    function OnSecurityCallback($s='')
    {
        $intval = intval(sprintf('%d',$s));
        if( !$intval )
            exit;
        $key = intval($intval / 100);
        $offset = $intval % 100;
        $keys =& CCSecurityKeys::GetTable();
        $hash = $keys->QueryItemFromKey('keys_key',$key);
        $ip   = $keys->QueryItemFromKey('keys_ip',$key);
        if( empty($hash) ) // || ($ip != $_SERVER['REMOTE_ADDR']) )  note: the IP check broke on Buckman's dual DSL 
            exit;
        $ord  = ord($hash[$offset]);
        require_once('cchost_lib/cc-template.php');
        $fname = CCTemplate::SearchStatic( sprintf("images/hex/f%x.png",$ord) );
        header ("Content-Type: image/png");
        readfile($fname);
        exit;
    }

}

?>
