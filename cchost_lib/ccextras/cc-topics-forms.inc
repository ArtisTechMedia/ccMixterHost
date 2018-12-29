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
* $Id: cc-topics-forms.inc 12570 2009-05-07 21:51:18Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


require_once('cchost_lib/cc-user.inc');

define( 'CC_TTF_NOT_VISIBLE', 10 );
define( 'CC_TTF_STATIC', 20 );
define( 'CC_TTF_EDIT', 30 );

class CCConfirmTopicDeleteForm extends CCForm
{
    function CCConfirmTopicDeleteForm($pretty_name)
    {
        $this->CCForm();
        $this->SetHelpText(_("This action cannot be reversed...") );
        // this line was different in svn:
        $this->SetSubmitText(sprintf(_('Are you sure you want to delete "%s"?'),
                                     $pretty_name));
    }
}

class CCTopicForm extends CCSecurityVerifierForm
{
    function CCTopicForm($label_text,$submit_text,$title_type_flag=CC_TTF_EDIT,$show_xlat = true)
    {
        $this->CCSecurityVerifierForm();

        $fields = array();

        $fields += array( 
                    'topic_text' => array(
                            'label'         => $label_text,
                            'formatter'     => 'textarea',
                               'want_formatting' => true,
                            'flags'         => CCFF_REQUIRED | CCFF_POPULATE),
                    );

        if( CCUser::IsAdmin() && $show_xlat )
        {
            $fields += array( 
                        'topic_can_xlat' => array(
                                'label'         => _('Allow Translations'),
                                'formatter'     => 'checkbox',
                                'flags'         => CCFF_POPULATE)
                        );
        }

        // NOTE: when we moved from PHP 3 to 5 
        // these fields started showing up (which
        // is what was supposed to happen) but
        // now seems very heavy handed so we 
        // remove them here

        /*        
        $fields += array( 
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
        */

        $this->AddFormFields($fields);
        $this->SetTitleType($title_type_flag);
        $this->SetSubmitText($submit_text);
    }

    function SetTitleType($title_type_flag)
    {
        if( $title_type_flag == CC_TTF_NOT_VISIBLE )
            return;

        if( $title_type_flag == CC_TTF_STATIC )
        {
            $flags = CCFF_STATIC | CCFF_NOUPDATE | CCFF_POPULATE;
            $type = 'statictext';
        }
        elseif( $title_type_flag == CC_TTF_EDIT )
        {
            $flags = CCFF_REQUIRED | CCFF_POPULATE;
            $type = 'textedit';
        }
        else
        {
            st();
            trigger_error('unknown title type flag');
        }

        $f['topic_name'] = array(
                            'label'       => _('Title'),
                            'formatter'   => $type,
                            'flags'       => $flags
                            );

        $this->InsertFormFields( $f, 'top' );
    }
}

class CCTopicReplyForm extends CCTopicForm
{
    function CCTopicReplyForm()
    {
        $this->CCTopicForm(_('Reply'), _('Submit Reply'), CC_TTF_NOT_VISIBLE );
    }
}

class CCTopicTranslateForm extends CCTopicForm
{
    function CCTopicTranslateForm($title)
    {
        $this->CCTopicForm(_('Translate'), _('Submit Translation'), CC_TTF_NOT_VISIBLE, false );

        $fields = array( 

        'topic_i18n_language' => array(
                'label'         => _('Language'),
                'form_tip'      => _('Your language, in your language (e.g. Italiano, Magyar, etc.)'),
                'formatter'     => 'textedit',
                'flags'         => CCFF_REQUIRED | CCFF_POPULATE),
            );

        $this->InsertFormFields( $fields, 'after', 'topic_name' );
        $this->SetFormValue('topic_name',htmlentities($title));
    }
}

class CCTopicEditForm extends CCTopicForm
{
    function CCTopicEditForm($static_title=true)
    {
        $this->CCTopicForm(_('Text'), 'Submit Changes', $static_title ? CC_TTF_STATIC : CC_TTF_EDIT );
    }
}


?>
