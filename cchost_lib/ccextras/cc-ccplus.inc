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
                    '_ccplus_tag' =>
                       array(  'label'       => '',
                               'formatter'  => 'button',
                               'url'        => ccl('admin','user','ccplus', 'tagfiles',$user_id),
                               'value'      => _('Tag all uploads'),
                               'flags'      => CCFF_NONE | CCFF_NOUPDATE  | CCFF_STATIC),
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
                ccPlusOptInUser($username,false);
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
    
    function ccPlusUserFilesTag($user_id)
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
        
        $results =  ccPlusTagAllUploadsForUser($user_id);
        
        $str = sprintf( _('Tagged %d samples and pells, %d remixes'), count($results[SAMPLES_TAGGED]), count($results[REMIXES_TAGGED]) );
        
        $this->ccPlusUserAdmin($user_id,$str);
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
            if(  array_key_exists('upload_ccplus',$_POST) && !empty($_POST['upload_ccplus']) )
            {
                ccPlusMarkUpload($upload_id);
            }
            else
            {
                ccPlusUnmarkUpload($upload_id);
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
                            array( 'label'  => _('CC Plus'),
                                   'form_tip'   => _('Add CC Plus license (<a href="http://tunetrack.net/mnodes/29673/download/">terms</a>)'),
                                    'class'     => '',
                                   'formatter'  => 'checkbox',
                                   'flags'      => CCFF_NOUPDATE);
            }
        }
        else if( is_a($form,'CCAdminUploadForm') )
        {
            $str = ',' . $fields['ccud']['value'] . ',';
            $value = '';
            if( strstr($str,',ccplus_verified') )
            {
                $value = 'verified';
            }
            else if( strstr($str,',ccplus_verify,') )
            {
                $value = 'verify';
            }
            else if( strstr($str,',ccplus_nooptin,') )
            {
                $value = 'nooptin';
            }
            else if( strstr($str,',ccplus,') )
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
                'options'    => array( 'none' => 'No ccPlus',
                                        'ccplus' => 'Regular ccPlus',
                                        'verified' => 'Verified ccPlus (for remixes with non-ccPlus sources)',
                                        'verify' => 'This upload awaiting verification',
                                        'nooptin' => 'The artist is not opted in'
                                    ),
                'flags'      => CCFF_POPULATE );
        }
    }

    function _cleanccPlusTags($str)
    {
        require_once('cchost_lib/cc-tags.inc');
        $tags = CCTag::TagSplit($str);
        $ccplustags = array( 'ccplus','ccplus_nooptin','ccplus_verify','ccplus_verified');
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
            if( !CCRemix::AreAllSourcesTaggedWith('ccplus',$remix_sources,'attribution_3') )
            {
                ccPlusUnmarkUpload($remixid);
            }            
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
        CCEvents::MapUrl( 'admin/user/ccplus/tagfiles',     array('CCCCPlus', 'ccPlusUserFilesTag'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '{user_id}', _('ccPlus tag user files'), CC_AG_USER );
    }

}



?>
