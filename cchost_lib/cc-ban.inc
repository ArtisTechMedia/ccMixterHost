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
* $Id: cc-ban.inc 10504 2008-07-14 20:49:21Z fourstones $
*
*/

/** 
* Admin user interface for banning users
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

class CCBanAdminForm extends CCEditConfigForm
{
    function CCBanAdminForm()
    {
        $this->CCEditConfigForm('config',CC_GLOBAL_SCOPE);

        $fields['ban-message'] =
           array(  'label'      => _('Ban Message'),
                   'form_tip'   => _('Message displayed to owner of a banned upload'),
                   'value'      => '',
                   'formatter'  => 'textarea',
                   'flags'      => CCFF_POPULATE | CCFF_NOSTRIP | CCFF_HTML);
        $fields['ban-email-enable'] =
           array(  'label'      => _('Email owner when banned'),
                   'form_tip'   => _('Send an email message to owner when an upload is banned.'),
                   'value'      => '',
                   'formatter'  => 'checkbox',
                   'flags'      => CCFF_POPULATE);
        $fields['ban-email'] =
           array(  'label'      => _('Ban Email Message'),
                   'form_tip'   => _('Email Message sent to owner when banned'),
                   'value'      => '',
                   'formatter'  => 'textarea',
                   'flags'      => CCFF_POPULATE);
        $fields['ban-reasons'] =
           array(  'label'      => _('Ban Reasons'),
                   'form_tip'   => _('Reasons why an upload might be moderated, separate with semi-colon \';\''),
                   'value'      => '',
                   'formatter'  => 'textarea',
                   'flags'      => CCFF_POPULATE);

        $this->AddFormFields($fields);
        $this->SetModule( ccs(__FILE__) );
    }
}

class CCBanForm extends CCForm
{
    function CCBanForm()
    {
        $this->CCForm();

        global $CC_GLOBALS;

        $ban_reasons = empty($CC_GLOBALS['ban-reasons']) ? array() : split(';',$CC_GLOBALS['ban-reasons']);
        $reasons = array( '0' => _('(Skip the reason)') );
        foreach($ban_reasons as $BR)
        {
            if( empty($BR) )
                continue;
            $hash = sprintf('%08X',crc32($BR));
            $reasons[$hash] = CC_strchop($BR,30);
        }

        $fields['ban-email-enable'] =
           array(  'label'      => _('Email owner'),
                   'form_tip'   => _('Send an email message to upload owner now.'),
                   'value'      => !empty($GLOBALS['ban-email-enable']),
                   'formatter'  => 'checkbox',
                   'flags'      => CCFF_POPULATE);
        $fields['ban-reason'] =
           array(  'label'      => _('Ban Reason'),
                   'form_tip'   => _('Reason why this upload was moderated'),
                   'options'    => array_filter($reasons),
                   'class'      => 'cc_form_input_short',
                   'formatter'  => 'select',
                   'flags'      => CCFF_POPULATE);
        $fields['ban-email'] =
           array(  'label'      => _('Ban Email Message'),
                   'form_tip'   => _('Email message (in addition to the reason above)'),
                   'value'      => empty($GLOBALS['ban-email']) ? '' : $GLOBALS['ban-email'],
                   'formatter'  => 'textarea',
                   'flags'      => CCFF_POPULATE);


        $this->AddFormFields($fields);
    }
}

/**
* Ban API used by admins to moderate uploads
*
*/
class CCBan
{
    /**
    * Handles amdin/ban URLs
    * 
    * Toggles the ban/unban flag on an upload record.
    *
    * @param integer $upload_id File id to ban
    */
    function Ban($upload_id)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['ban-reasons']) )
        {
            $this->_do_ban($upload_id);
            return;
        }

        if( empty($_POST) )
        {
            $uploads =& CCUploads::GetTable();
            $banned = CCDatabase::QueryItem('SELECT upload_banned FROM cc_tbl_uploads WHERE upload_id='.$upload_id);
            if( $banned ) // we're unbanning
            {
                $this->_do_ban($upload_id);
                return;
            }
        }

        require_once('cchost_lib/cc-page.php');
        CCPage::SetTitle(_('Moderate Upload'));
        $form = new CCBanForm($upload_id);
        if( empty($_POST['ban']) || !$form->ValidateFields() )
        {
            $form->PopulateValues($CC_GLOBALS);
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $CC_GLOBALS = array_merge($CC_GLOBALS,$values); // a bit of cheat
            $this->_do_ban($upload_id);
        }
    }

    function _do_ban($upload_id)
    {
        global $CC_GLOBALS;

        // international strings are a little weird, esp. wrt per-user
        // Specifically: All these strings will be translated to the *moderators* choice
        // of language, not the users

        $row = CCDatabase::QueryRow(
            'SELECT upload_banned, upload_name, user_name, user_email FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$upload_id);
        $uploads =& CCUploads::GetTable();
        $new_ban_flag = $row['upload_banned'] ^= 1;
        $args['upload_id'] = $upload_id;
        $args['upload_banned'] = $new_ban_flag;
        $uploads->Update($args);
        require_once('cchost_lib/cc-page.php');
        CCPage::SetTitle(sprintf(_("Banning upload: '%s'"),$row['upload_name']));
        $yn = array( _("no longer banned"), _("banned") );
        CCEvents::Invoke( CC_EVENT_UPLOAD_MODERATED, array( &$row, $yn[ $new_ban_flag ]  ) );
        CCPage::Prompt("The upload has been marked as " . $yn[ $new_ban_flag ] );

        if( $new_ban_flag && !empty($CC_GLOBALS['ban-email-enable']) )
        {
            $text = str_replace('%title%',$row['upload_name'],$CC_GLOBALS['ban-email']);

            if( !empty($CC_GLOBALS['ban-reason']) )
            {
                $reasons = split(';',$CC_GLOBALS['ban-reasons']);
                foreach( $reasons as $BR )
                {
                    if( $CC_GLOBALS['ban-reason'] == sprintf('%08X',crc32($BR)) )
                        break;
                }
                $text .= "\n\n" . _('Reason') . ": " . $BR . "\n\n";
            }

            if( file_exists('cchost_lib/ccextras/cc-mail.inc') )
            {
                require_once('cchost_lib/ccextras/cc-mail.inc');
                $mailer = new CCMailer();
                $mailer->To($row['user_email']);
                $mailer->Subject( _('Your upload has been moderated') ); 
                $mailer->Body($text);
                $ok = $mailer->Send();
                CCPage::Prompt(_('Moderation email notificaiton sent'));
            }
        }
    }
    
    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _('Configure Moderation');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);
        $form = new CCBanAdminForm();
        CCPage::AddForm( $form->GenerateForm() );
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        if( $scope != CC_GLOBAL_SCOPE )
            return;

        $items += array( 
            'ban'   => array('menu_text'   => _('Moderation/Flagging'),
                              'menu_group'  => 'configure',
                              'help'        => _('Configure moderation (Ban) and upload flagging'),
                              'weight'      => 350,
                              'action'      =>  ccl('admin','ban','admin'),
                              'access'      => CC_ADMIN_ONLY
                             ),
            );
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','ban'),   array('CCBan','Ban'),  CC_ADMIN_ONLY, ccs(__FILE__), '{upload_id}', 
            _('Ban/unban an upload') , CC_AG_USER );
        CCEvents::MapUrl( ccp('admin','ban','admin'),   array('CCBan','Admin'),  CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Show Moderation (ban) admin form') , CC_AG_USER );
    }
}





?>
