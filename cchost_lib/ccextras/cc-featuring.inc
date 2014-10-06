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
* $Id: cc-featuring.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*
*
*/
class CCFeaturing
{
    /**
    * Event handler for {@link CC_EVENT_GET_MACROS}
    *
    * @param array &$record Upload record we're getting macros for (if null returns documentation)
    * @param array &$file File record we're getting macros for
    * @param array &$patterns Substituion pattern to be used when renaming/tagging
    * @param array &$mask Actual mask to use (based on admin specifications)
    */
    function OnGetMacros( &$record, &$file, &$patterns, &$mask )
    {
        if( empty($record) )
        {
            $patterns['%feat%'] = _('Collaborating artists');
            return;
        }

        if( !empty( $record['upload_extra']['featuring'] ) )
            $feat = $record['upload_extra']['featuring'];
        elseif( !empty($_POST['upload_featuring']) ) // hack!!
            $feat = $_POST['upload_featuring'];
        else
            $feat = '';

        if( empty($feat) )
            $patterns['%feat%'] = '';
        else
            $patterns['%feat%'] = sprintf( _('(ft. %s)'), $feat );
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
        if( ($op == CC_UF_NEW_UPLOAD || $op == CC_UF_PROPERTIES_EDIT) &&
            array_key_exists('upload_featuring',$_POST)
          )
        {
            $uploads =& CCUploads::GetTable();
            $featuring = $_POST['upload_featuring'];
            $uploads->SetExtraField($upload_id,'featuring',$featuring);
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

        if( is_subclass_of($form,'CCUploadMediaForm') ||
                    is_subclass_of($form,'ccuploadmediaform') )
        {
            /*
            *  Add 'featured' to file uploads
            */
            if( empty($fields['upload_featuring']) && !empty($fields['upload_name']))
            {
                $F = array();
                $F['upload_featuring'] = 
                            array( 'label'      => 'str_feat_artists',
                                   'form_tip'   => 'str_feat_list_any_collob',
                                   'formatter'  => 'textedit',
                                   'flags'      => CCFF_NOUPDATE);
                $form->InnerInsertFormFields($fields,$F,'after','upload_name');
                //CCDebug::PrintVar($fields);
            }
        }
    }

    /**
    * Event handler for {@link CC_EVENT_FORM_VERIFY}
    * 
    * @param object &$form CCForm object
    * @param boolean &$retval Set this to false if fields fail to verify
    */
    function OnFormVerify(&$form,&$retval)
    {
        if( !is_subclass_of($form,'CCUploadMediaForm') &&
                    !is_subclass_of($form,'ccuploadmediaform') )
        {
            return;
        }
        
        if( !array_key_exists('upload_featuring', $_POST) )
            return;

        $featuring = CCUtil::StripText($_POST['upload_featuring']);

        if( empty($featuring) )
            return;

        $form->SetFormValue('upload_featuring',$featuring );

        return true;
    }

    /**
    * Event handler for {@link CC_EVENT_FORM_POPULATE}
    * 
    * @param object &$form CCForm object
    * @param array &$values Current values being applied to form fields
    */
    function OnFormPopulate(&$form,&$values)
    {
        if( !is_subclass_of($form,'CCUploadMediaForm') &&
                    !is_subclass_of($form,'ccuploadmediaform') )
        {
            return;
        }

        if( !empty($values['upload_extra']['featuring']) )
            $form->SetFormValue('upload_featuring',$values['upload_extra']['featuring']);
    }
}

?>
