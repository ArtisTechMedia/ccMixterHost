<?

/**
* @package cchost
* @subpackage license
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once( 'cchost_lib/ccextras/cc-ccplus-api.inc');

class CCPlusUserAdminForm extends CCUserForm
{
    /**
    * Constructor
    */
    function CCPlusUserAdminForm($user_id)
    {
        $this->CCUserForm();
        $username = CCUser::GetUserName($user_id);

        $fields = array( 
                    'user_name' =>
                        array( 'label'      => _('Login Name'),
                               'formatter'  => 'statictext',
                               'value'   => $username,
                               'flags'      => CCFF_NOUPDATE | CCFF_STATIC ),
                               
                    'ccplus_optin' =>
                                array( 'label'      => _('Opt-In'),
                                       'formatter'  => 'checkbox',
                                       'flags'      => CCFF_NONE,
                                ),
                    'ccplus_tagfiles' =>
                                array( 'label'      => _('Tag files'),
                                       'formatter'  => 'checkbox',
                                       'flags'      => CCFF_NONE,
                                ),
                        );

        $this->AddFormFields( $fields );
    }

}


/**
*
*
*/
class CCCCPlus
{

    function ccPlusUserAdmin($user_id,$prompt='')
    {
        global $CC_GLOBALS;
        $page =& CCPage::GetPage();
        $title = _("ccPlus User Admin");
        $username = CCUser::GetUserName($user_id);

        $k[] = array( 'url' => ccl(), 'text' => 'str_home' );
        $k[] = array( 'url' => ccl('people'), 'text' => 'str_people' );
        $k[] = array( 'url' => ccl('people', $username ), 'text' => $username );
        $k[] = array( 'url' => ccl('people', $username, 'admin'), 'text' => 'str_admin' );
        $k[] = array( 'url' => '', 'text' => _('ccPlus') );
        $page->AddBreadCrumbs($k);

        $page->SetTitle($title);

        $form = new CCPlusUserAdminForm($user_id);

        if( empty($_POST['plususeradmin']) || !$form->ValidateFields() )
        {
            $form->SetFormValue('ccplus_optin', ccPlusUserIsOptedIn($username) );
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $opt_in = $values['ccplus_optin'];
            if( $opt_in )
            {
                $tag_files = $values['ccplus_tagfiles'];
                ccPlusOptInUser($username,$tag_files);
                $prompt .= _(' Opt-in successful');
            }
            else
            {
                ccPlusOptOutUser($username);
                $prompt .= _(' Opt-out successful');
            }
            $form->SetFormValue('ccplus_optin', $opt_in);
            $page->AddForm( $form->GenerateForm() );
        }
        
        if( !empty($prompt) )
        {
            $page->Prompt($prompt);
        }
    }
    
    function OnUserAdminMenu( $user_id, &$args )
    {
        $args[] = array( 'action' => ccl( 'admin', 'user', 'ccplus', $user_id ), 
                         'menu_text' => _("ccPlus"),
                         'help' => 'Manage ccPlus options for this user' );
    }
    
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
            if( empty($CC_GLOBALS['do-ccplus']) || !ccPlusUserIsOptedIn(CCUser::CurrentUserName()) )
                return;

            /*
            *  Add CCPLus to file uploads
            */
            if( empty($fields['upload_ccplus']) )
            {
                $fields['upload_ccplus'] = 
                            array( 'label'     => _('ccPlus Opt-In'),
                                   'form_tip'  => _('Upload and stems will be marked as ccPlus if eligible'),
                                   'class'     => '',
                                   'formatter' => 'checkbox',
                                   'flags'     => CCFF_NOUPDATE);
            }
        }
        else if( is_a($form,'CCAdminUploadForm') )
        {
            $str = ',' . $fields['ccud']['value'] . ',';
            $value = '';
            if( strstr($str,',ccplus_verified,') )
            {
                $value = 'verified';
            }
            else if( strstr($str,',ccplus_verify,') )
            {
                $value = 'verify';
            }
            else if( strstr($str,',ccplus_stem,') )
            {
                $value = 'stem';
            }
            else if( strstr($str,',ccplus,') ) // this check must be last
            {
                $value = 'ccplus';
            }
            else
            {
                $value = 'none';
            }

            $fields['ccud']['value'] = $this->_cleanccPlusTags($str);
            
            $fields['ccplus'] =
              array(
                'label'  => _('CCPlus options'),
                'value'      => $value,
                'formatter'  => 'radio',
                'options'    => array(  'none'     => 'No ccPlus',
                                        'ccplus'   => 'Regular ccPlus',
                                        'stem'     => 'Stems of this remix are ccPlus (but not the remix)',
                                        'verify'   => 'This upload awaiting verification',
                                        'verified' => 'Verified as ccPlus (even though the sources are not or the artist is not opt-ed in)'
                                    ),
                'flags'      => CCFF_POPULATE );
        }
    }

    function _cleanccPlusTags($str)
    {
        require_once('cchost_lib/cc-tags.inc');
        $tags = CCTag::TagSplit($str);
        $ccplustags = array( 'ccplus','ccplus_verify','ccplus_verified','ccplus_stem');
        $tags = array_diff($tags, $ccplustags);
        return implode(',',$tags);
    }
    
    function OnFormVerify(&$form,&$retval)
    {
        global $CC_GLOBALS;

        if( is_a($form,'CCAdminUploadForm') )
        {
            $value = $form->GetFormValue('ccplus');
            $newtag = '';
            switch( $value )
            {
                case 'verified':
                    $newtag = 'ccplus_verified,ccplus';
                    break;
                case 'verify':
                    $newtag = 'ccplus_verify';
                    break;
                case 'nooptin':
                    $newtag = 'ccplus_nooptin';
                    break;
                case 'stem':
                    $newtag = 'ccplus_stem,ccplus';
                    break;
                case 'ccplus':
                    $newtag = 'ccplus';
                    break;
            }
            
            if( !empty($newtag) )
            {
                require_once('cchost_lib/cc-tags.inc');
                $ccud = CCTag::TagSplit($form->GetFormValue('ccud'));
                $newtag = CCTag::TagSplit($newtag);
                $form->SetFormValue('ccud', implode( ',', array_merge( $ccud, $newtag ) ));
            }            
        }

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
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['do-ccplus']) ) {
            return;
        }

        if( !is_subclass_of($form,'CCUploadMediaForm') &&
                    !is_subclass_of($form,'ccuploadmediaform') )
        {
            return;
        }
        $ok = ccPlusUploadIsOptedIn($values['upload_id']);
        $form->SetFormValue('upload_ccplus', $ok ? '1' : '0' );
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

        if( empty($CC_GLOBALS['do-ccplus']) ) {
            return;
        }

        if( ($op == CC_UF_NEW_UPLOAD || $op == CC_UF_PROPERTIES_EDIT) )
        {
            if(  array_key_exists('upload_ccplus',$_POST) )
            {
                if( empty($_POST['upload_ccplus']) ) {
                    ccPlusOptOutUpload($upload_id);
                } else {
                    ccPlusOptInUpload($upload_id);
                }
            }
        } 
        else 
        {
            // stems changed

            $this->_checkAndVerifyUpload($upload_id);
        }
    }

    function OnDeletedFile($upload_id)
    {
        // stem was deleted
        $this->_checkAndVerifyUpload($upload_id);
    }

    function OnSourcesChanged($remixid,&$remix_sources)
    {
        // remixes sources changed

        $this->_checkAndVerifyUpload($remixid);
    }

    function OnFileChangedType($file_id,$upload_id) {
        $this->_checkAndVerifyUpload($upload_id);
    }

    function _checkAndVerifyUpload($upload_id) {

        if( ccPlusUploadIsOptedIn($upload_id) ) {
            ccPlusUnmarkUpload($upload_id);
            ccPlusVerifyTree($upload_id,false);
        }
    }

    function OnApiQuerySetup( &$args, &$queryObj, $validate)
    {
        if( !empty($args['lic']) && $args['lic'] == 'ccplus' ) 
        {
            unset($args['lic']);
            if( empty($args['reqtags']) ) {
                $args['reqtags'] = '';
            }
            $args['reqtags'] .= ',ccplus';
        }
    }
        
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/user/ccplus',     array('CCCCPlus', 'ccPlusUserAdmin'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '{user_id}', _('ccPlus user admin form'), CC_AG_USER );
    }

}



?>
