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
* $Id: cc-filetagger-admin.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Admin UI for handling ID3 file tagging
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

/**
* Admin form for upload ID3 tagging rules
*/
class CCAdminTaggerForm  extends CCEditConfigForm
{
    /**
    * Constructor
    *
    * Every module in the system has the opportunity to participate in the ID3 tagging
    * rules by responding to CC_EVENT_GET_MACROS event (triggered by this method).
    * In this case the $record field will be blank and therefore the documentation
    * for each tagging macro is expected back.
    *
    */
    function CCAdminTaggerForm()
    {
        $this->CCEditConfigForm('id3-tag-masks');

        $fields = array();

        require_once('cchost_lib/cc-filetagger.php');
        $standard_tags =& CCID3Tagger::_get_standard_tags();
        $this->AddFormFields( $standard_tags );

        // Help...
        $patterns['%title%'] = _("Title");
        $patterns['%site%']  = _("Site name");
        $dummy = array();
        CCEvents::Invoke( CC_EVENT_GET_MACROS, array( $dummy, $dummy, 
                                                      &$patterns, $dummy ) );
        ksort($patterns);
        $this->CallFormMacro('macro_patterns','show_macro_patterns',$patterns);
        $this->SetModule( ccs(__FILE__) );
    }

}

?>
