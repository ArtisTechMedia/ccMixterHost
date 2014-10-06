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
* $Id: cc-flag.inc 12570 2009-05-07 21:51:18Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/

require_once('cchost_lib/ccextras/cc-topics.inc');
require_once('cchost_lib/cc-user.inc');

/**
*/
class CCFlagContentForm extends CCSecurityVerifierForm
{
    function CCFlagContentForm($type,$id,$name,$user_from)
    {
        global $CC_GLOBALS;

        $this->CCSecurityVerifierForm();
        require_once('cchost_lib/cc-page.php');
        $page = CCPage::GetPage();
        $type = $page->String('str_flag_' . $type);

        $fields = array( 
                    'mail_from' => array(
                            'label'       => 'str_email_from',
                            'formatter'   => 'textedit',
                            'value'       => $user_from,
                            'form_tip'    => 'str_email_your_address_opt',
                            'flags'      => CCFF_NONE ),
                    'mail_subject' => array(
                            'label'       => 'str_email_subject',
                            'formatter'   => 'statictext',
                            'value'      => $page->String(array('str_flag_s_s',$type,$name)),
                            'flags'      => CCFF_STATIC | CCFF_NOUPDATE ),
                    'mail_body' => array(
                            'label'       => 'str_email_message',
                            'formatter'   => 'textarea',
                             'value'      => $page->String(array('str_flag_this_s_contains',$type)),
                            'flags'      => CCFF_REQUIRED ),
                    'user_mask' =>
                       array( 'label'       => '',
                               'formatter'  => 'securitykey',
                               'form_tip'   => '',
                               'flags'      => CCFF_NOUPDATE),
                    'user_confirm' =>
                       array(  'label'       => 'str_security_key',
                               'formatter'  => 'securitymatch',
                               'class'      => 'cc_form_input_short',
                               'form_tip'   => CCSecurityVerifierForm::GetSecurityTipStr(),
                               'flags'      => CCFF_REQUIRED | CCFF_NOUPDATE)
            );

        if( !empty($CC_GLOBALS['flag_msg']) )
            $this->SetHelpText($CC_GLOBALS['flag_msg']);

        $this->AddFormFields($fields);
    }
}

/**
*
*
*/
class CCFlag
{
    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('flag'), array('CCFlag','Flag'), CC_DONT_CARE_LOGGED_IN,
                ccs(__FILE__), '(upload|topic)/{upload_id}', _('Users flag an upload or topic.'),
                CC_AG_UPLOADS );
    }

    function _bbc_upload($upload_id,&$trail,$cmd='')
    {
        $trail[] = array( 'url' => ccl('people'), 
                          'text' => 'str_people' );

        $sql = 'SELECT user_real_name, upload_name, user_name FROM cc_tbl_uploads ' .
                'JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$upload_id;

        list( $user_real_name, $upload_name, $user_name ) = CCDatabase::QueryRow($sql, false);

        $trail[] = array( 'url' => ccl('people',$user_name), 
                          'text' => $user_real_name );

        $trail[] = array( 'url' => ccl('files',$user_name, $upload_id), 
                          'text' => '"' . $upload_name . '"' );

        if( $cmd )
        {
            $trail[] = array( 'url' => ccl('reviews',$user_name, $upload_id), 
                              'text' => 'str_reviews' );
        }
    }

    function _build_bread_crumb_trail($type,$id)
    {
        $trail[] = array( 'url' => ccl(), 
                          'text' => 'str_home');
        
        if( $type == 'upload' )
        {
            $this->_bbc_upload($id,$trail);

        }
        elseif( $type == 'topic' )
        {
            list( $type, $upload_id, $thread_id, $topic_name ) = 
                    CCDatabase::QueryRow('SELECT topic_type, topic_upload, topic_thread, topic_name FROM cc_tbl_topics WHERE topic_id='.$id,false);

            if( $upload_id ) // $type == 'review' )
            {
                $this->_bbc_upload($upload_id,$trail,'reviews');

            }
            elseif( $thread_id )
            {
                list( $forum_id, $forum_name, $thread_name ) = CCDatabase::QueryRow('SELECT forum_id, forum_name, forum_thread_name FROM cc_tbl_forums JOIN cc_tbl_forum_threads ON '.  
                                              'forum_thread_forum=forum_id WHERE forum_thread_id='.$thread_id,false);
                $trail[] = array( 'url' => ccl('forums'), 'text' => 'str_forums' );
                $trail[] = array( 'url' => ccl('forums',$forum_id), 'text' => $forum_name );
                $trail[] = array( 'url' => ccl('thread',$thread_id), 'text' => $thread_name );
                $trail[] = array( 'url' => ccl('thread',$thread_id) . '#' . $id, 'text' => $topic_name );
            }
        }

        $trail[] = array( 'url' => '', 'text' => 'str_flag' );

        require_once('cchost_lib/cc-page.php');
        CCPage::AddBreadCrumbs($trail);
    }

    function Flag($type='',$id='')
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        $page->SetTitle('str_flag_content');

        $id = sprintf("%0d",$id);

        if( empty($id) )
            CCUtil::Send404();

        if( $type == 'upload' )
        {
            $uploads =& CCUploads::GetTable();
            if( !$uploads->KeyExists($id) )
                CCUtil::Send404();
            $name = $uploads->QueryItemFromKey('upload_name',$id);
        }
        elseif( $type == 'topic' )
        {
            $topics =& CCTopics::GetTable();
            if( !$topics->KeyExists($id) )
                CCUtil::Send404();
            $name = $topics->QueryItemFromKey('topic_name',$id);
        }

        $this->_build_bread_crumb_trail($type,$id);

        $user_from = empty($CC_GLOBALS['user_email']) ? '' : $CC_GLOBALS['user_email'];
        
        $form = new CCFlagContentForm($type,$id,$name,$user_from);

        if( empty($_POST['flagcontent']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            
            if( $type == 'upload' )
            {
                $ccl = ccl('files') . '/';
                list( $url, $title  ) = CCDatabase::QueryRow("SELECT CONCAT('{$ccl}',user_name,'/',{$id}) as url, upload_name " .
                                           'FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id=' . $id, false );
                $type = 'str_flag_upload_flagged';

            }
            elseif( $type == 'topic' )
            {
                $record = $topics->GetRecordFromKey($id);
                $url = ccl('topics','view',$id);
                $title = $record['topic_name'];
                $type = 'str_flag_topic_flagged';
            }

            $type = $page->String($type);
            $from = empty($values['mail_from']) ? $CC_GLOBALS['mail_sender'] : $values['mail_from'];
            $user_text = $values['mail_body'];
            $flag_lang = $page->String('str_flag_content_has');
            $ip = $_SERVER['REMOTE_ADDR'];
            $user = CCUser::IsLoggedIn() ? CCUser::CurrentUserName() : 'anon';
            $text =<<<END
$flag_lang
$type: $title
URL: $url
IP: $ip
Flagger: $user
-------------------------------------
$user_text
-------------------------------------                
END;
            require_once('cchost_lib/ccextras/cc-mail.inc');
            $mailer = new CCMailer();
            $mailer->To($CC_GLOBALS['mail_sender']);
            $mailer->Subject( $type );
            $mailer->From($from);
            $mailer->Body($text);
            $ok = $mailer->Send();
            if( !$ok )
            {
                CCPage::Prompt('str_flag_err');
            }
            else
            {
                CCPage::Prompt('str_flag_thank_you');
            }
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
        if( (strtolower(get_class($form)) == 'ccbanadminform') && empty($fields['flagging']) )
        {
            $fields['flagging'] =
               array(  'label'      => _('Flagging'),
                       'form_tip'   => _('This allows users to directly flag something as a possible violation of terms of this site.'),
                       'value'      => 0,
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE );
            $fields['flag_msg'] =
               array(  'label'      => _('Flag form message'),
                       'form_tip'   => _('This text will appear on the mail form to users who flag content.'),
                       'value'      => 0,
                       'formatter'  => 'textarea',
                       'flags'      => CCFF_POPULATE );
        }
    }
}

?>
