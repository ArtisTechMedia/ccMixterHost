<?


/*
    signup link

    http://recaptcha.net/api/getkey?app=php
*/

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
* $Id$
*
*/

/**
* @package cchost
* @subpackage user
*/



if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*
*
*/
class CCRecaptcha
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
            $fields['do-recaptcha'] =
               array(  'label'      => _('Use ReCaptcha'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);
            $fields['recaptcha-public'] =
               array(  'label'      => _('ReCaptcha Public Key'),
                       'value'      => '',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE);
            $fields['recaptcha-private'] =
               array(  'label'      => _('ReCaptcha Private Key'),
                       'value'      => '',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE);
            $fields['recaptcha-freq'] =
               array(  'label'      => _('Recaptcha Frequency'),
                       'form_tip'      => 'Show how often a user will be asked for reCaptcha (1 means every time - only applies to users logged in)',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE);
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

        if( empty($CC_GLOBALS['do-recaptcha']) )
            return;

        $sec_match_name = '';
        $sec_key_name = '';
 
        foreach( $fields as $N => $F )
        {
            if( empty($F['formatter']))
                continue;
            
            if( $F['formatter'] == 'securitymatch') 
            {
                $sec_match_name = $N;
            }
            elseif( $F['formatter'] == 'securitykey')
            {
                $sec_key_name = $N;
            }
        }
        
        if( empty($sec_key_name) )
            return;
        
        unset($fields[$sec_key_name]);
        unset($fields[$sec_match_name]);

        if( $this->_is_recaptcha_active() )
        {
            $fields['recaptcha'] = 
                        array( 'label'  => 'Captcha',
                               'form_tip'   => '',
                               'formatter'  => 'recaptcha',
                               'flags'      => CCFF_NOUPDATE);
        }
    }

    function _is_recaptcha_active()
    {
        
        $has_uploads = CCUser::CurrentUserField('user_num_uploads');
        return empty($has_uploads);
    }
}



/*
    signup link

    http://recaptcha.net/api/getkey?app=php
*/

function generator_recaptcha()
{
    global $CC_GLOBALS;
        
    require_once('cchost_lib/ccextras/recaptcha/recaptchalib.php');
    $publickey = $CC_GLOBALS['recaptcha-public']; // you got this from the signup page
    return recaptcha_get_html($publickey);
    
    /*
     
	<noscript>
  		<iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br>
  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
	</noscript>';
    */     
}

/*
function generator()
{
    require_once('recaptchalib.php');
    $publickey = "..."; // you got this from the signup page
    echo recaptcha_get_html($publickey);
}
*/

function validator_recaptcha(&$form)
{
    global $CC_GLOBALS;
    
    require_once('cchost_lib/ccextras/recaptcha/recaptchalib.php');
    $privatekey = $CC_GLOBALS['recaptcha-private'];
    $resp = recaptcha_check_answer ($privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);

    if (!$resp->is_valid) {
        $form->SetFieldError('recaptcha',"The reCAPTCHA wasn't entered correctly. Go back and try it again." .
           "(reCAPTCHA said: " . $resp->error . ")");        
    }

    return $resp->is_valid;
}

?>