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
class CCBPM
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
            $fields['do-bpm'] =
               array(  'label'      => _('Include BPM in Upload Forms'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);
        }
    }

    function CalcBPMTag($bpm)
    {
        $bpm = intval($bpm);

        if( !$bpm )
            return null;

        if( $bpm < 60 )
            return 'bpm_below_60';

        if( $bpm > 180 )
            return 'bpm_above_180';

        $low = $bpm - ($bpm % 5);
        $hi  = $low + 5;

        if( $low < 100 )
            $low = '0' . $low;
        if( $hi < 100 )
            $hi = '0' . $hi;

        return "bpm_{$low}_{$hi}";
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

        if( empty($CC_GLOBALS['do-bpm']) )
            return;

        if( ($op == CC_UF_NEW_UPLOAD || $op == CC_UF_PROPERTIES_EDIT) &&
            array_key_exists('upload_bpm',$_POST)
          )
        {
            $uploads =& CCUploads::GetTable();
            $old_bpm = $uploads->GetExtraField($upload_id,'bpm');
            $old_tag = $this->CalcBPMTag($old_bpm);
            $bpm = $_POST['upload_bpm'];
            $uploads->SetExtraField($upload_id,'bpm',$bpm);
            $tag = $this->CalcBPMTag($bpm);
            if( $tag )
                CCUploadAPI::UpdateCCUD($upload_id,$tag,$old_tag);
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

        if( empty($CC_GLOBALS['do-bpm']) )
            return;

        if( is_subclass_of($form,'CCUploadMediaForm') ||
                    is_subclass_of($form,'ccuploadmediaform') )
        {
            /*
            *  Add BPM to file uploads
            */
            if( empty($fields['upload_bpm']) )
                $fields['upload_bpm'] = 
                            array( 'label'  => 'str_bpm',
                                   'form_tip'   => 'str_bpm_tempo',
                                    'class'     => 'form_input_short',
                                   'formatter'  => 'textedit',
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

        if( empty($CC_GLOBALS['do-bpm']) )
            return;

        if( !is_subclass_of($form,'CCUploadMediaForm') &&
                    !is_subclass_of($form,'ccuploadmediaform') )
        {
            return;
        }
        if( !empty($values['upload_extra']['bpm']) )
            $form->SetFormValue('upload_bpm',$values['upload_extra']['bpm']);
    }


    /**
    * Event handler for {@link CC_EVENT_FORM_VERIFY}
    * 
    * @param object &$form CCForm object
    * @param boolean &$retval Set this to false if fields fail to verify
    */
    function OnFormVerify(&$form,&$retval)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['do-bpm']) )
            return;

        if( !is_subclass_of($form,'CCUploadMediaForm') &&
                    !is_subclass_of($form,'ccuploadmediaform') )
        {
            return;
        }
        
        if( !array_key_exists('upload_bpm', $_POST) )
            return;

        $bpm = CCUtil::StripText($_POST['upload_bpm']);
        if( empty($bpm) )
            return;

        if( !intval($bpm) || $bpm < 10 || $bpm > 300 )
            $bpm = 0;

        $form->SetFormValue('upload_bpm',$bpm );

        return true;
    }

}



?>
