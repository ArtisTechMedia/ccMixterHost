<?

/**
* @package cchost
* @subpackage license
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
*
*
*/
class CCPellTagsHack
{

    /**
    * Event handler for {@link CC_EVENT_FORM_FIELDS}
    *
    * @param object &$form CCForm object
    * @param object &$fields Current array of form fields
    */
    function OnFormFields(&$form,&$fields)
    {
        global $CC_GLOBALS;

        if( is_subclass_of($form,'CCUploadMediaForm')  ) 
        {
            if( empty($fields['suggested_tags']) || !in_array('melody',$fields['suggested_tags']['tags']) ) {
                return;
            }
            $form->_form_fields['suggested_tags']['tags'] = array_diff($fields['suggested_tags']['tags'],
                                                     array('melody','rap','spoken_word'));

            $pfields = array();
            $pfields['pell_tags'] =
              array(
                'label'  => _('Pell type'),
                'value'      => 'melody',
                'formatter'  => 'radio',
                'options'    => array( 'melody' => 'melody',
                                        'rap' => 'rap',
                                        'spoken_word' => 'spoken word'
                                    ),
                'flags'      => CCFF_POPULATE );

            $form->InsertFormFields( $pfields, 'before', 'upload_tags' );
        }
    }

    function OnFormVerify(&$form,&$retval)
    {
       if( empty($form->_form_fields['pell_tags'])) {
            return;
       }
       $comma = empty($form->_form_fields['upload_tags']) ? '' : ',';
       $form->_form_fields['upload_tags']['value'] .= ($comma . $form->_form_fields['pell_tags']['value']);
        return true;
    }


}

CCEvents::AddHandler(CC_EVENT_EXTRA_FORM_FIELDS,   array( 'CCPellTagsHack', 'OnFormFields'), 'cchost_lib/ccextras/cc-pelltags.php' );
CCEvents::AddHandler(CC_EVENT_FORM_VERIFY,   array( 'CCPellTagsHack', 'OnFormVerify'), 'cchost_lib/ccextras/cc-pelltags.php'  );


?>
