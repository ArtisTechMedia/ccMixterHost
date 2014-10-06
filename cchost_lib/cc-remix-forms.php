<?php
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
* $Id: cc-remix-forms.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/** 
* Module for handling Remix UI
*
* @package cchost
* @subpackage upload
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-upload-forms.php');

/**
 * Base class for uploading remixes form
 *
 * Note: derived classes must call SetHandler()
 * @access public
 */
class CCPostRemixForm extends CCNewUploadForm
{
    /**
     * Constructor
     *
     * Sets up form as a remix form. Initializes 'remix search' box.
     
     * @access public
     * @param integer $userid The remix will be 'owned' by owned by this user
     */
    function CCPostRemixForm($userid,$remix_id='')
    {
        $this->CCNewUploadForm($userid,false);

        $fields['sources'] = array(    'label'      => 'str_sources',
                           'form_tip'   => 'str_sources_tip',
                           'formatter'  => 'metalmacro',
                           'macro'      => 'remix_search',
                           'remix_id'   => $remix_id,
                           'close_box'  => 1,
                           'flags'      => CCFF_POPULATE,
                        );

        $this->SetHiddenField( 'upload_license', '', CCFF_HIDDEN | CCFF_POPULATE );
        $this->InsertFormFields( $fields, 'before', 'upload_name');
        $this->DisableSubmitOnInit();

    }
}


class CCEditRemixesForm extends CCForm
{
    /**
     * Constructor
     *
     * Sets up form as a remix editing form. Initializes 'remix search' box.
     *
     */
    function CCEditRemixesForm($sources_for_this)
    {
        $this->CCForm();

        $fields['sources'] = array(    
                           'label'      => 'str_sources',
                           'form_tip'   => 'str_sources_tip',
                           'formatter'  => 'metalmacro',
                           'macro'      => 'remix_search',
                           'sourcesof'  => $sources_for_this,
                           'flags'      => CCFF_POPULATE,
                        );

        $this->SetHiddenField( 'upload_license', '', CCFF_HIDDEN | CCFF_POPULATE );
        $this->AddFormFields($fields);
        $this->DisableSubmitOnInit();
        $this->SetSubmitText(_('Done Editing'));
    }
}


?>
