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
* $Id: cc-collab-forms.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
require_once('cchost_lib/cc-form.php');
require_once('cchost_lib/ccextras/cc-topics-forms.inc');

class CCCollabForm extends CCForm
{
    function CCCollabForm()
    {
        $this->CCForm();

        $fields = array( 
                    'collab_name' =>
                       array( 'label'       => _('Name of collaboration project'),
                               'formatter'  => 'textedit',
                               'form_tip'   => '',
                               'flags'      => CCFF_REQUIRED | CCFF_POPULATE),
                          
                    'collab_desc' =>
                       array( 'label'       => _('Description'),
                               'formatter'  => 'textarea',
                               'want_formatting' => true,
                               'flags'      => CCFF_POPULATE),
                    );

        $this->AddFormFields($fields);
    }
}

class CCCollabTopicForm extends CCTopicForm
{
    function CCCollabTopicForm()
    {
        $this->CCTopicForm(_('Collobration Discussion'),'Submit Topic');
    }
}



?>
