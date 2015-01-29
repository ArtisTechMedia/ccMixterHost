<?

/*
  $Id: mixter-lost-password.inc 13835 2009-12-25 13:19:34Z fourstones $
*/

require_once('cchost_lib/cc-login.php');

class ATMLogin extends CCLogin
{
    function _send_login_info($user_name,$title,$why,$new_password,$email_addr)
    {
        if( empty($email_addr) )
        {
            $id = CCUser::IDFromName($user_name);
            CCUtil::SendBrowserTo(ccl('confirm_email',$id));
            exit;
        }
        
        return parent::_send_login_info($user_name,$title,$why,$new_password,$email_addr);
    }

    function ConfirmEmail($user_id='')
    {
        $user_id = sprintf( '%0d', CCUtil::Strip($user_id) );
        if( empty($user_id) )
            die('invalid user id'); // this is bot
        $user_name = CCUser::GetUserName($user_id);
        
        require_once('cchost_lib/cc-form.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('Confirm E-mail');
        
        $hash = CCDatabase::QueryItem('SELECT email_hash_hash FROM cc_tbl_email_hash WHERE email_hash_user = '.$user_id);
        
        if( empty($hash) )
        {
            $text =<<<EOF
Sorry, the <b>{$user_name}</b> account does not have an e-mail address associated with it so we can not generate a new password.
EOF;
            $page->Prompt($text);
            return;
        }
        
        $form = new CCForm();
        $text =<<<EOF
<p>We need to confirm the email address for <b>{$user_name}</b></p>
EOF;
        $form->SetHelpText($text);
        $tip = 'E-mail address for the <b>%s</b> account. The new password will be sent here.';
        $ftip = sprintf($tip,$user_name);
        $form->AddFormField('email', array( 'label' => 'e-mail address',
                                             'form_tip' => $ftip,
                                             'hash' => $hash,
                                             'user_id' => $user_id,
                                             'formatter'=> 'lost_email_confirm',
                                             'flags' => CCFF_REQUIRED,
                                              )
                            );

        if( empty($_POST) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $users = new CCTable('cc_tbl_user','user_id');
            $new_password = $this->_make_new_password();

            $args['user_id'] = $user_id;
            $args['user_password'] = md5($new_password);
            $users->Update($args);
            CCEvents::Invoke(CC_EVENT_LOST_PASSWORD, array( $user_id, &$row)); // not caught anywhere?

            $values = $form->GetFormValue('email');
            $why = 'str_login_you_are_rec2';
            $this->_send_login_info($user_name,'str_login_recover_lost_password',$why,$new_password,$values);
            $page->Prompt('str_login_new_password');
        }
    }
    
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'confirm_email',   array('ATMLogin','ConfirmEmail'), CC_ONLY_NOT_LOGGED_IN, ccs(__FILE__));
        CCEvents::MapUrl( 'atmlostpassword', array('ATMLogin','LostPassword'), CC_ONLY_NOT_LOGGED_IN, ccs(__FILE__));
    }
    
}

function generator_lost_email_confirm(&$form,$varname,$value='',$classname='')
{
    return $form->generator_textedit($varname,$value,$classname);
}

function validator_lost_email_confirm(&$form,$fieldname)
{
    if( $form->validator_textedit($fieldname) )
    {
        $hash    = $form->GetFormFieldItem($fieldname,'hash');
        $email   = $form->GetFormValue($fieldname);
        $e_hash  = md5($email);
    
        if( $e_hash != $hash )
        {
            $form->SetFieldError( $fieldname, "That email does match our records" );
            return false;
        }
        
        return true;
    }
    
    return false;
}


?>