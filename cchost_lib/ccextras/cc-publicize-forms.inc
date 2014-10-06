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
* $Id: cc-publicize-forms.inc 12570 2009-05-07 21:51:18Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-mail.inc');

class CCShareLinkForm extends CCSecurityVerifierForm
{
    function CCShareLinkForm($type,$record)
    {
        global $CC_GLOBALS;

        $this->CCSecurityVerifierForm();

        $fields['mail_to'] = array(
                'label'       => _('To email address'),
                 'value'      => '',
                 'flags'      => CCFF_REQUIRED,
                 'formatter'  => 'email' );

        if( CCUser::IsLoggedIn() )
            $user_from = $CC_GLOBALS;

        if( empty($user_from) )
        {
            $fields['mail_from'] = array(
                        'label'       => _('From'),
                        'formatter'   => 'email',
                        'form_tip'    => _('Your email address'),
                        'flags'      => CCFF_REQUIRED );
        }
        else
        {
            $from = $user_from['user_real_name'] . ' (' . $user_from['user_name'] . ')';

            $fields['mail_from_STATIC'] = array(
                            'label'       => _('From'),
                            'value'      => $from,
                            'flags'      => CCFF_STATIC | CCFF_NOUPDATE,
                            'formatter'  => 'statictext' );

            $this->SetHiddenField('mail_from_id',$user_from['user_id']);
        }

        $configs =& CCConfigs::GetTable();
        $ttags = $configs->GetConfig('ttag');
        $site_name = $ttags['site-title'];

        if( $type == 'user' )
        {
            $about = $record['user_real_name'];
            $about_url = $record['artist_page_url'];
            $body = sprintf( _("Check out %s's profile at %s!"), $about, $site_name ) . "\n\n" . $about_url;
        }
        elseif( $type == 'upload' )
        {
            $user  = $record['user_real_name'];
            $about = $record['upload_name'];
            $about_url = $record['file_page_url'];
            $body = sprintf( _('Check out "%s" by %s at %s!'), $about, $user, $site_name ) . "\n\n" . $about_url;
        }
        elseif( $type == 'playlist' )
        {
            $user  = $record['user_real_name'];
            $about = $record['cart_name'];
            $about_url = ccl('playlist','browse',$record['cart_id']);
            $body = sprintf( _('Check out %s\'s Playlist "%s" at %s!'), $user, $about, $site_name ) . "\n\n" . $about_url;
        }

        $subject = sprintf( _("Check out %s at %s"), $about, $site_name );

        $fields += array( 
                    'mail_subject' => array(
                            'label'       => _('Subject'),
                            'formatter'   => 'textedit',
                            'value'      => $subject,
                            'flags'      => CCFF_NONE ),
                    'mail_body_static' => array(
                            'label'       => _('Message'),
                            'formatter'   => 'statictext',
                            'value'       => $body, 
                            'flags'      => CCFF_STATIC | CCFF_NOUPDATE ),
                    'mail_body' => array(
                            'label'       => _('Personal Message'),
                            'formatter'   => 'textarea',
                            'value'       => '',
                            'maxlength'   => 1000,
                            'form_tip'    => _('Add your personal message here. (Limited to 1,000 characters)'),
                            'flags'      => CCFF_NONE ),
                    'user_mask' =>
                       array( 'label'       => '',
                               'formatter'  => 'securitykey',
                               'form_tip'   => '',
                               'flags'      => CCFF_NOUPDATE),
                    'user_confirm' =>
                       array( 'label'       => _('Security Key'),
                               'formatter'  => 'securitymatch',
                               'class'      => 'cc_form_input_short',
                               'form_tip'   => CCSecurityVerifierForm::GetSecurityTipStr(),
                               'flags'      => CCFF_REQUIRED | CCFF_NOUPDATE)
            );

        $this->AddFormFields($fields);

        $this->SetHiddenField('mail_stock_body',urlencode($body));

        $help = sprintf( _('Use this form to tell your friends about %s'), '<b>' . $about . '</b>' );

    }

}

?>
