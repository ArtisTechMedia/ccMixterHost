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
* $Id: cc-mail.inc 12611 2009-05-13 19:24:00Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define('CC_MAIL_NOTALLOWED',   '1' );
define('CC_MAIL_ALLOWED',      '2' );
define('CC_MAIL_FORWARD_ONLY', '4' );
define('CC_MAIL_THROTTLED',    '8' );

require_once('cchost_lib/cc-admin.php');
require_once('cchost_lib/cc-user.inc');

class CCMailAdminForm extends CCEditConfigForm
{
    function CCMailAdminForm()
    {
        $this->CCEditConfigForm('config');

        $options = array(
                CC_MAIL_NOTALLOWED    => _('Do not allow email'),
                CC_MAIL_ALLOWED       => _('Allow (unrestricted)'),
                CC_MAIL_THROTTLED     => _('Allow (throttled)' ),
               );

        $fields = array(
            
            'mail_sender' => 
                array(
                        'label'       => _('Admin email address'),
                        'flags'      => CCFF_POPULATE | CCFF_REQUIRED,
                        'form_tip'   => _('Address used as return address for automated mail.'),
                        'formatter'  => 'textedit' 
                    ),

/*
            'mail_disabled' => 
                array(
                        'label'       => _('Disable All EMail'),
                        'form_tip'   => _('Disables mail for all attempts'),
                        'flags'      => CCFF_POPULATE,
                        'formatter'  => 'checkbox',
                    ),

            'mail_disabled_msg' => 
                array(
                        'label'      => _('Disable eMail Message'),
                        'form_tip'   => _('Message to users when email is diabled'),
                        'flags'      => CCFF_POPULATE,
                        'formatter'  => 'textarea',
                    ),
*/
            'mail_anon' =>
                array(
                        'label'      => _('Anonymous Users'),
                        'flags'      => CCFF_POPULATE,
                        'form_tip'   => _('Rule applied to users not logged in.'),
                        'options'    => $options,
                        'formatter'  => 'select',
                    ),

            'mail_uploaders' =>
                array(
                        'label'      => _('Users With Uploads'),
                        'form_tip'   => _('Rule applied to users that have uploaded media to this site.'),
                        'flags'      => CCFF_POPULATE,
                        'options'    => $options,
                        'formatter'  => 'select',
                    ),

            'mail_registered' =>
                array(
                        'label'       => _('Users Without Uploads'),
                        'form_tip'   => _('Rule that applies to registered users who have not uploaded anything.'),
                        'flags'      => CCFF_POPULATE,
                        'options'    => $options,
                        'formatter'  => 'select',
                    ),


            'mail_throttle' =>
                array(
                        'label'       => _('E-mail Flood Throttle'),
                        'flags'      => CCFF_POPULATE,
                        'formatter'  => 'textedit',
                        'class'      => 'cc_form_input_short',
                        'form_tip'   => _('Minimum allowed time (in minutes) between emails'), 
                    ),

            'mail_to_admin' =>
                array(
                        'label'       => _('Mail to Admin Acct'),
                        'form_tip'   => _('Who can send mail to admin accounts?'),
                        'flags'      => CCFF_POPULATE,
                        'options'    => array(
                                            CC_DONT_CARE_LOGGED_IN => _('Everyone'),
                                            CC_MUST_BE_LOGGED_IN   => _('Logged in users only')
                                             ),
                        'formatter'  => 'select',
                    ),

            'mail_add_params' =>
                array(
                        'label'       => _('SendMail Parameters '),
                        'form_tip'   => _('Additional params, like -f (Advanced)'),
                        'flags'      => CCFF_POPULATE,
                        'formatter'  => 'textedit',
                    ),
            );

        $this->AddFormFields($fields);

        $url   = ccl('admin','massmail');
        $link1 = "<a href=\"$url\"><b>";
        $link2 = "</b></a>";
        $fmt   = _('Click %shere%s to send a mass mailing.');
        $help  = sprintf($fmt,$link1,$link2);
        $this->SetHelpText($help);
        $this->SetModule( ccs(__FILE__) );
    }
}

/**
*/
class CCMailer
{
    var $_to;
    var $_from;
    var $_cc;
    var $_bcc;
    var $_subject;
    var $_message;

    function CCMailer()
    {
        $this->_to          = "";
        $this->_from        = "";
        $this->_replyto     = "";
        $this->_cc          = array();
        $this->_bcc         = array();
        $this->_subject     = "";
        $this->_message     = "";
    }

    function Message($msg)
    {
        $this->_message = $msg;
    }

    function Body($msg)
    {
        $this->_message = $msg;
    }

    function To($to)
    {
        $this->_to = $to;
    }

    function CC($cc)
    {
        $this->_cc[] = $cc;
    }

    function BCC($bcc)
    {
        if( empty($bcc) )
            $this->_bcc = array();
        else
            $this->_bcc[] = $bcc;
    }

    function Subject($subject)
    {
        $this->_subject = $subject;
    }

    function From($from)
    {
        $this->_from = $from;
    }

    function ReplyTo($replyto)
    {
        $this->_replyto = $replyto;
    }

    function DefaultFrom()
    {
        global $CC_GLOBALS;

        return( $CC_GLOBALS['mail_sender'] );
    }

    function Send()
    {
        global $CC_GLOBALS;

        if( empty($this->_from) && empty($CC_GLOBALS['mail_sender']) )
        {
            print 'failed';
            $this->_confim_install();
            return;
        }

        $default_from = $this->DefaultFrom();

        $configs =& CCConfigs::GetTable();
        $ttags = $configs->GetConfig('ttag');

        $subject = '[' . $ttags['site-title'] . '] ' . $this->_subject;
        $from    = empty($this->_from) ? $default_from : $this->_from;
        $replyto = empty($this->_replyto) ? $from : $this->_replyto;
        $bcc     = empty($this->_bcc) ? '' : implode( ', ', $this->_bcc );
        $cc      = empty($this->_cc)  ? '' : implode( ', ', $this->_cc  );
        $to      = $this->_to;
        $message = $this->_message; // do doubt this needs to be messaged...
        
        if( !empty($ttags['mail-tag-line']) ) {
            $message .= "\n\n" . $ttags['mail-tag-line'];
        }

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/plain; charset=utf-8\r\n";
        $headers .= "From: $from\r\n";
        $headers .= "Reply-To: $replyto\r\n";

        /*
        stuff to consider:

        "Return-Path: " . $board_config['board_email'] . 
        "\nMessage-ID: <" . md5(uniqid(time())) . "@" . $board_config['server_name'] . ">".
        "\nContent-transfer-encoding: 8bit" .
        "\nDate: " . date('r', time()) . 
        "\nX-Priority: 3" .
        "\nX-MSMail-Priority: Normal" .
        "\nX-Mailer: PHP" .
        "\nX-MimeOLE: Produced By ccHost\n" . 
        */

        if (!empty($cc) )
            $headers .= "Cc: $cc\r\n";

        if (!empty($bcc)) 
            $headers .= "Bcc: $bcc\r\n";
        
        $add_params = empty($GLOBALS['mail_add_params']) ? '' : $GLOBALS['mail_add_params'];

        if( function_exists('cc_mailer_func') )
            return @cc_mailer_func( $to, $subject, $message, $headers, $add_params );
        
        return @mail( $to, $subject, $message, $headers, $add_params );

    }
} 

class CCContactMailerForm extends CCSecurityVerifierForm
{
    function CCContactMailerForm($user_to,$user_from)
    {
        $this->CCSecurityVerifierForm();

        $to = $user_to['user_real_name'] . ' (' . $user_to['user_name'] . ')';

        $fields['mail_to'] = array(
                'label'       => 'str_email_to',
                 'value'      => $to,
                 'flags'      => CCFF_STATIC | CCFF_NOUPDATE,
                 'formatter'  => 'statictext' );

        if( empty($user_from) )
        {
            $fields['mail_from'] = array(
                        'label'       => 'str_email_from',
                        'formatter'   => 'email',
                        'form_tip'    => 'str_email_your_address',
                        'flags'      => CCFF_REQUIRED );
        }
        else
        {
            $from = $user_from['user_real_name'] . ' (' . $user_from['user_name'] . ')';

            $fields['mail_from_STATIC'] = array(
                            'label'       => 'str_email_from',
                            'value'      => $from,
                            'flags'      => CCFF_STATIC | CCFF_NOUPDATE,
                            'formatter'  => 'statictext' );

            $this->SetHiddenField('mail_from_id',$user_from['user_id']);
        }


        $fields += array( 
                    'mail_subject' => array(
                            'label'       => 'str_email_subject',
                            'formatter'   => 'textedit',
                            'flags'      => CCFF_NONE ),
                    'mail_body' => array(
                            'label'       => 'str_email_message',
                            'formatter'   => 'textarea',
                            'maxlength'   => 1000,
                            'form_tip'    => 'str_email_msg_limited', 
                            'flags'      => CCFF_REQUIRED )
                            );
                            
        if( !CCUser::IsLoggedIn() )
        {
            $fields += array(                            
                        'user_mask' =>
                           array( 'label'       => '',
                                   'formatter'  => 'securitykey',
                                   'autocomp'   => 'off',
                                   'form_tip'   => '',
                                   'flags'      => CCFF_NOUPDATE),
                        'user_confirm' =>
                           array( 'label'       => 'str_security_key',
                                   'formatter'  => 'securitymatch',
                                   'class'      => 'cc_form_input_short',
                                   'form_tip'   => CCSecurityVerifierForm::GetSecurityTipStr(),
                                   'flags'      => CCFF_REQUIRED | CCFF_NOUPDATE)
                );
        }

        $this->AddFormFields($fields);
    }

}

class CCMassMailerForm extends CCContactMailerForm
{
    function CCMassMailerForm($user_to,$user_from)
    {
        $this->CCContactMailerForm($user_to,$user_from);
    }    
}

class CCMailerAPI
{

    function IsMailEnabled()
    {
        global $CC_GLOBALS;

        return empty($CC_GLOBALS['mail_disabled']);
    }

    function _confirm_install()
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['mail_sender']) )
        {
            if( CCUser::IsAdmin() )
            {
                $url = ccl('admin','mail');
                $link1 = "<a href=\"$url\">";
                $link2 = "</a>";
                $fmt = _('Mail has not been properly configured on the this system.') . ' ' . _('Set an admin return mail address in %sConfigure Mail%s');
                $msg = sprintf( $fmt, $link1, $link2 );
            }
            else
            {
                $msg = _('Mail has not been properly configured on this system.') . ' ' . _('Contact your administrator through regular email.');
            }

            $page =& CCPage::GetPage();
            $page->SystemError( $msg );
            return false;
        }

        return true;
    }

    function _ok_to_contact($userto)
    {
        global $CC_GLOBALS;

        $rule = 0;
        $ret = array(
                'ok'   => false,
                'msg'  => '',
                'anon' => CCUser::IsLoggedIn(),
                );

        /*-----------------------
        * Early bail outs
        -------------------------*/
        if( empty($CC_GLOBALS['mail_to_admin']) )
        {
            // admin forgot to run update

            $ret['msg'] = _('Mail update is not properly installed');
            return $ret;
        }
        if( CCUser::IsAdmin() )
        {
            // this is admin, let it fly

            $ret['ok'] = true;
            return $ret;
        }

        /*-----------------------
        * Validate request
        -------------------------*/

        if( CCUser::IsAdmin($userto) )
        {
            $access = $CC_GLOBALS['mail_to_admin'];

            if( ($access == CC_DONT_CARE_LOGGED_IN) || CCUser::IsLoggedIn()  )
            {
                $ret['ok'] = true;
            }
            else
            {
                $ret['msg'] = _('Sorry, only logged in users may send mail to the admins.');
            }
        }
        else // Mail is not addressed to admin:
        {
            if( CCUser::IsLoggedIn() )
            {
                $user_id = CCUser::CurrentUser();
                $uploads = new CCUploads();
                $where['upload_user'] = $user_id;
                $count = $uploads->CountRows($where);
                if( empty($count) )
                {
                    $rule = $CC_GLOBALS['mail_registered'];
                }
                else
                {
                    $rule = $CC_GLOBALS['mail_uploaders'];
                }
            }
            else // user not logged in:
            {
                $ex = CCDatabase::QueryItem("SELECT user_extra FROM cc_tbl_user WHERE user_name = '$userto'");
                $ex = unserialize($ex);
                $rule = empty($ex['anon_ok']) ? $CC_GLOBALS['mail_anon'] : CC_MAIL_ALLOWED;
            }

            /*-----------------------------------------
            *
            * We have the rule for the current user
            *
            *-----------------------------------------*/

            if( ($rule & CC_MAIL_NOTALLOWED) == CC_MAIL_NOTALLOWED )
            {
                $ret['msg'] = _('You are not authorized to send mail.');
            }
            elseif( ($rule & CC_MAIL_THROTTLED) == CC_MAIL_THROTTLED )
            {
                $curr_time = time();
                if( empty($CC_GLOBALS['user_extra']['last_email_send']) )
                {
                    $ret['ok'] = true;
                }
                else
                {
                    $throttle = empty($CC_GLOBALS['mail_throttle']) ? (2) 
                                    : $CC_GLOBALS['mail_throttle'];

                    $last_email = $CC_GLOBALS['user_extra']['last_email_send'];

                    if( ($curr_time - $last_email) < intval(60 * $throttle)  )
                    {
                        $ret['msg'] = _('You have exceeded the temporary quota of emails allowed.' );
                    }            
                    else
                    {
                        $ret['ok'] = true;
                    }
                }   
            }
            elseif( ($rule & CC_MAIL_ALLOWED) == CC_MAIL_ALLOWED )
            {
                $ret['ok'] = true;
            }
        }
        
        return $ret;
    }

    function _mark_user_send()
    {
        if( CCUser::IsLoggedIn() )
        {
            $row = array();            
            $row['user_extra'] = CCUser::CurrentUserField('user_extra');
            if( !is_array($row['user_extra']) )
                $row['user_extra'] = unserialize($row['user_extra']);
            $row['user_extra']['last_email_send'] = time();
            $row['user_extra'] = serialize($row['user_extra']);
            $row['user_id'] = CCUser::CurrentUser();
            $users =& CCUsers::GetTable();
            $users->Update($row);
        }
    }

    function Contact($userto='')
    {
        global $CC_GLOBALS;

        if( !$this->_confirm_install() )
            return;

        if( empty($userto) )
            return;

        $users =& CCUsers::GetTable();
        $where['user_name'] = $userto;
        $user_to = $users->QueryRow($where);
        if( empty($user_to) )
            return;

        $title = sprintf(_('Send Mail to %s'), $userto);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle($title);
        CCUser::AddUserBreadCrumbs($title,null,$user_to);

        $verify = $this->_ok_to_contact($userto);

        if( !$verify['ok'] )
        {
            $page->Prompt( $verify['msg'] );
            return;
        }

        // mail form expects records in the to/from
        $user_from = CCUser::IsLoggedIn() ? $CC_GLOBALS : '';

        $form = new CCContactMailerForm($user_to,$user_from);

        if( empty( $_REQUEST['contactmailer'] ) || !$form->ValidateFields() )
        {
            if( $verify['msg'] )
                $form->SetHelpText($verify['msg']);

            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $this->_mark_user_send();

            $form->GetFormValues($fields);
            
            global $CC_MAILER;

            if( empty($CC_MAILER) )
                $mailer = new CCMailer();
            else
                $mailer = $CC_MAILER;

            $from_email = empty($user_from) ? $fields['mail_from'] : $user_from['user_email'];
            $from_name  = empty($user_from) ? $fields['mail_from'] : $user_from['user_name'];

            $body = "\n\nMail sent to account: {$user_to['user_real_name']} ({$user_to['user_name']})\n";
            if( !empty($user_from) )
            {
                $body .= "Mail sent from account: {$user_from['user_real_name']} ({$user_from['user_name']})\n";
            }
            $body .= "------------------------------------------------------------------------\n\n";
            $body .= $fields['mail_body'];

            $mailer->From( $from_email );
            $mailer->To( $user_to['user_email'] );
            $mailer->Subject( $fields['mail_subject'] );
            $mailer->Body( $body );
            $mailer->Send();

            $page->Prompt( _('Mail sent') );

            // enabled debug to force log message
            $wasEnabled = CCDebug::Enable(true);
            CCDebug::Log("Mail sent from $from_name -- to $userto");
            CCDebug::Enable($wasEnabled);
        }
    }

    function MassMail()
    {
        if( !CCUser::IsAdmin() )
            die('Welcome to ccHost');
            
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _("Send Mail to Everybody");
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        if( !$this->_confirm_install() )
            return;

        $user_to['user_name']        = _('really');
        $user_to['user_real_name']   = _('EVERYONE');
        $user_from['user_name']      = _('really');
        $user_from['user_real_name'] = _('You');
        $user_from['user_id']        = CCUser::CurrentUser();

        $form = new CCMassMailerForm($user_to,$user_from);

        if( empty( $_REQUEST['massmailer'] ) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($fields);
            
            global $CC_MAILER;

            if( empty($CC_MAILER) )
                $mailer = new CCMailer();
            else
                $mailer = $CC_MAILER;
            
            $MAX_GET = 100;
            $users = new CCTable('cc_tbl_user', 'user_email');
            $users->GroupOnKey();
            $total = $users->CountRows();

            $from = $mailer->DefaultFrom();
            $mailer->From( $from );
            $mailer->To( $from );
            $mailer->Subject( $fields['mail_subject'] );
            $mailer->Body( $fields['mail_body'] );

            $sent = 0;
            for( $offset = 0; $offset < $total; $offset += $MAX_GET )
            {
                $users->SetOffsetAndLimit(  $offset, $MAX_GET );
                $rows = $users->QueryRows('1','user_email');
                $count = count($rows);
                for( $i = 0; $i < $count; $i++ )
                {
                    $addr = $rows[$i]['user_email'];
                    if( $addr )
                        $mailer->BCC( $addr );
                }
                $ok = $mailer->Send();
                if( !$ok )
                    break;
                $sent += $count;
                $mailer->BCC('');
            }

            $msg = sprintf(_("Mass mail: Sent %s Messages"), $sent);

            $page->Prompt($msg);
        }
    }

    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _('Configure Mail');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);
        $form = new CCMailAdminForm();
        $page->AddForm( $form->GenerateForm() );
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('people','contact'),  array('CCMailerAPI', 'Contact'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '[username]', 
            _('Display email contact form'), CC_AG_USER ); 

        CCEvents::MapUrl( ccp('admin', 'massmail'), array('CCMailerAPI', 'MassMail'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Display mass email contact form'), CC_AG_MISC_ADMIN ); 

        CCEvents::MapUrl( ccp('admin', 'mail'),     array('CCMailerAPI', 'Admin'),    
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Display admin mail settings form'), CC_AG_MISC_ADMIN ); 
    }


    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $items += array(
                'emailadmin'   => array( 
                                 'menu_text'  => _('Email'),
                                 'menu_group' => 'configure',
                                 'help' => _('Configure system email address and access'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 65,
                                 'action' =>  ccl('admin', 'mail')
                                 ),
                );

        }
    }


}




?>
