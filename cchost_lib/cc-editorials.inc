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
* $Id: cc-editorials.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Implements Editor's Picks
*
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

/**
* Form for writing editorial picks
*
*/
class CCEditorialForm extends CCForm
{
    /** 
    * Constructor
    *
    */
    function CCEditorialForm($reviewer)
    {
        $this->CCForm();

        $fields = array( 
            'reviewer' =>
                        array( 'label'      => _('Reviewer'),
                               'formatter'  => 'statictext',
                               'flags'      => CCFF_NOUPDATE | CCFF_STATIC,
                               'value'      => $reviewer,
                        ),
            'editorial_review' =>
                        array( 'label'      => _('Review'),
                               'want_formatting' => true,
                               'formatter'  => 'textarea',
                               'flags'      => CCFF_NONE,
                        ),
            'editorial_delete' =>
                        array( 'label'      => _('Delete'),
                               'formatter'  => 'checkbox',
                               'flags'      => CCFF_NONE,
                        ),
                    );
        
        $this->AddFormFields( $fields );
    }
}


?>
