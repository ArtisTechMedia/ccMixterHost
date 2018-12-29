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
* $Id: stateshirt.inc 13831 2009-12-25 12:35:42Z fourstones $
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
class StateShirt
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
            $fields['do-stateshirt'] =
               array(  'label'      => _('Do Stateshirt Checkbox'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);

            $fields['stateshirt-text'] =
               array(  'label'      => _('Text for State Shirt label'),
                       'value'      => '',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE);
            $fields['stateshirt-tip'] =
               array(  'label'      => _('Text for State Shirt help tip'),
                       'value'      => '',
                       'formatter'  => 'textedit',
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

        if( empty($CC_GLOBALS['do-stateshirt']) )
            return;

        if( ($op == CC_UF_NEW_UPLOAD || $op == CC_UF_PROPERTIES_EDIT) )
        {
            $uploads =& CCUploads::GetTable();
            $old_ss = $uploads->GetExtraField($upload_id,'ss');
            $do_ss = array_key_exists('upload_stateshirt',$_POST);
            if( $old_ss != $do_ss )
            {
                $old_tag = $old_ss ? 'stateshirt' : '';
                $new_tag = $do_ss  ? 'stateshirt' : '';
                $uploads->SetExtraField($upload_id,'ss',$do_ss);
                CCUploadAPI::UpdateCCUD($upload_id,$new_tag,$old_tag);
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
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['do-stateshirt']) )
            return;

        if( is_subclass_of($form,'CCUploadMediaForm') ||
                    is_subclass_of($form,'ccuploadmediaform') )
        {
            if( empty($fields['upload_stateshirt']) )
                $fields['upload_stateshirt'] = 
                            array( 'label'      => $CC_GLOBALS['stateshirt-text'],
                                   'form_tip'   => $CC_GLOBALS['stateshirt-tip'],
                                   'formatter'  => 'checkbox',
                                   'flags'      => CCFF_NOUPDATE);
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

        if( empty($CC_GLOBALS['do-stateshirt']) )
            return;

        if( !is_subclass_of($form,'CCUploadMediaForm') &&
                    !is_subclass_of($form,'ccuploadmediaform') )
        {
            return;
        }

        if( !empty($values['upload_extra']['ss']) )
            $form->SetFormValue('upload_stateshirt',true);
    }


    /**
    * Event handler for {@link CC_EVENT_FORM_VERIFY}
    * 
    * @param object &$form CCForm object
    * @param boolean &$retval Set this to false if fields fail to verify
    */
    function OnFormVerify(&$form,&$retval)
    {
        return true;
    }

}



?>
