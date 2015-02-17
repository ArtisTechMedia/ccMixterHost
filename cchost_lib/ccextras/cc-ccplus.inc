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
* $Id: cc-bpm.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage audio
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*
*
*/
class CCCCPlus
{

    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields['do-ccplus'] =
               array(  'label'      => _('Include CC Plus in Upload Forms'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);
        }
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_DONE}
    * 
    * @param integer $upload_id ID of upload row
    * @param string $op One of {@link CC_UF_NEW_UPLOAD}, {@link CC_UF_FILE_REPLACE}, {@link CC_UF_FILE_ADD}, {@link CC_UF_PROPERTIES_EDIT'} 
    * @param array &$parents Array of remix sources
    */
    function OnUploadDone($upload_id, $op)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['do-ccplus']) )
            return;

        if( ($op == CC_UF_NEW_UPLOAD || $op == CC_UF_PROPERTIES_EDIT) )
        {
            $newtag = '';
            $oldtag = '';
            if(  array_key_exists('upload_ccplus',$_POST) )
            {
                $newtag = 'ccplus';
                $oldtag = '';
            }
            else
            {
                $newtag = '';
                $oldtag = 'ccplus';
            }
            require_once('cchost_lib/cc-uploadapi.php');
            CCUploadAPI::UpdateCCUD($upload_id,$newtag,$oldtag);
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
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['do-ccplus']) )
            return;

        if( is_subclass_of($form,'CCUploadMediaForm') ||
                    is_subclass_of($form,'ccuploadmediaform') )
        {
            /*
            *  Add CCPLus to file uploads
            */
            if( empty($fields['upload_ccplus']) )
            {
                $fields['upload_ccplus'] = 
                            array( 'label'  => _('CC Plus'),
                                   'form_tip'   => _('Add CC Plus license (<a href="http://tunetrack.net/mnodes/29673/download/">terms</a>)'),
                                    'class'     => '',
                                   'formatter'  => 'checkbox',
                                   'flags'      => CCFF_NOUPDATE);
            }
        }
    }

    /**
    * Event handler for {@link CC_EVENT_FORM_POPULATE}
    * 
    * @param object &$form CCForm object
    * @param array &$values Current values being applied to form fields
    */
    function OnFormPopulate(&$form,&$values)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['do-ccplus']) )
            return;

        if( !is_subclass_of($form,'CCUploadMediaForm') &&
                    !is_subclass_of($form,'ccuploadmediaform') )
        {
            return;
        }
        require_once('cchost_lib/cc-tags.php');
        $form->SetFormValue('upload_ccplus', CCTag::InTag('ccplus', $values['upload_tags']) ? 1 : 0 );
    }


    function OnSourcesChanged($remixid,&$remix_sources)
    {
        if( CCUpload::HasTag($remixid,'ccplus') )
        {
            if( !CCRemix::AreAllSourcesTaggedWith('ccplus',$remix_sources) )
            {
                CCUploadAPI::UpdateCCUD($remixid,'','ccplus');
            }            
        }
    }

}



?>
