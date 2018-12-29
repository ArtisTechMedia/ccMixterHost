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
* $Id: cc-filerename-admin.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Admin UI for fancy macro macro-based file renaming 
*
* @package cchost
* @subpackage admin
*/


if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

/**
* Admin form for upload renaming rules
*/
class CCAdminRenameForm  extends CCEditConfigForm
{
    /**
    * Constructor
    *
    * Every module in the system has the opportunity to participate in the renaming
    * rules by responding to CC_EVENT_GET_MACROS event (triggered by this method).
    * In this case the $record field will be blank and therefore the documentation
    * for each mask and renaming tagging macro is expected back.
    *
    */
    function CCAdminRenameForm()
    {
        $this->CCEditConfigForm('name-masks');
        
        $patterns['%title%'] = _("Title");
        $patterns['%site%']  = _("Site name");
        $dummy = array();
        $masks = array();
        CCEvents::Invoke( CC_EVENT_GET_MACROS, array( &$dummy, &$dummy, &$patterns, &$masks ) );
        ksort($patterns);
        $this->CallFormMacro('macro_patterns','show_macro_patterns',$patterns);

        $fields = array();

        foreach( $masks as $mask => $label )
        {
            $fields[$mask] = 
                        array( 'label'      => $label,
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE );
        }

        $fields['upload-replace-sp'] =
                        array( 'label'      => _("Replace space with '_'"),
                               'formatter'  => 'checkbox',
                               'flags'      => CCFF_POPULATE );

        $this->AddFormFields($fields);
        $this->SetModule( ccs(__FILE__) );
    }
}

?>
