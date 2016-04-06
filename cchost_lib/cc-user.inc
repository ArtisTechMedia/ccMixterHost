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
* $Id: cc-user.inc 12611 2009-05-13 19:24:00Z fourstones $
*
*/

/**
* @package cchost
* @subpackage user
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');
require_once('cchost_lib/cc-seckeys.php');

/**
*/
class CCSecurityVerifierForm extends CCForm
{
    function CCSecurityVerifierForm()
    {
        $this->CCForm();
    }

    /**
     * Handles generation of input fields for security key
     *
     * @see CCLogin::OnSecurityCallback()
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_securitymatch($varname,$value='',$class='')
    {
        $html = $this->generator_textedit($varname,$value,$class);
        $html = str_replace( '<input ', '<input autocomplete="off" ',$html);
        return $html;
    }

    function validator_securitymatch( $fieldname )
    {
        return $this->validator_textedit($fieldname);
    }

    /**
     * Handles generation of &lt;img's and a hidden $id field
     *
     * The img tags are actually stuff with '/s/#' URLs that call back to
     * this module and return a bitmap corresponding to the security key's
     * id. The '#' is combination of id the index into the key in question
     * 
     * @see CCLogin::OnSecurityCallback()
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_securitykey($varname,$value='',$class='')
    {
        $label = $this->GetFormFieldItem($varname,'label');
        if( empty($label) )
            $this->SetFormFieldItem($varname,'label','&nbsp;');
        $keys =& CCSecurityKeys::GetTable();
        $hash = $keys->GenKey();
        $id = $keys->AddKey($hash);
        $len = strlen($hash);
        $html = "<table><tr><td>";
        for( $i = 0; $i < $len; $i++ )
        {
            $url = ccl('s', ($id * 100) + $i);
            $html .= "<img src=\"$url\" />";
        }
        $html .= "</td></tr></table><input type=\"hidden\" name=\"$varname\" id=\"$varname\" value=\"$id\" />";
        return($html);
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Validates that the user typed in the proper security code.
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @returns bool $ok true means field validates, false means there were errors in user input
    */
    function validator_securitykey($fieldname)
    {
        $id = $this->GetFormValue('user_mask');
        $hash = CCUtil::StripText($_POST['user_confirm']);
        $keys =& CCSecurityKeys::GetTable();
        $retval = $keys->IsMatch( $hash, $id );
        if( !$retval )
        {
            $this->SetFieldError($fieldname,'str_seckey_does_not_match');
        }
        return( $retval );
    }

    /**
    * Static function to return standard form tip for security field.
    */
    function GetSecurityTipStr()
    {
        return 'str_security_tip';
    }
}

class CCUserForm extends CCSecurityVerifierForm
{
    var $record;

    function CCUserForm()
    {
        $this->CCSecurityVerifierForm();
    }

    /**
     * Handles generation of &lt;input type='password' HTML field 
     * 
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_matchpassword($varname,$value='',$class='')
    {
        return( $this->generator_password($varname,$value,$class) );
    }

    function validator_matchpassword($fieldname)
    {
        if( !empty($this->record) )
        {
            $value = $this->GetFormValue($fieldname);

            $password = md5( $value );

            if( $this->record['user_password'] != $password )
            {
                $this->SetFieldError($fieldname,'str_login_does_not_match');
                return(false);
            }

            return( true );
        }

        return( false );
    }


    /**
     * Handles generation of &lt;input type='text' HTML field 
     * 
     * 
     * @param string $varname Name of the HTML field
     * @param string $value   value to be published into the field
     * @param string $class   CSS class (rarely used)
     * @returns string $html HTML that represents the field
     */
    function generator_username($varname,$value='',$class='')
    {
        return( $this-> generator_textedit($varname,$value,$class) );
    }

    function validator_username($fieldname)
    {
        if( $this->validator_must_exist($fieldname) )
        {
            $value = $this->GetFormValue($fieldname);

            if( empty($value) )
                return(true);

            $users =& CCUsers::GetTable();
            $this->record = CCDatabase::QueryRow('SELECT * FROM cc_tbl_user WHERE user_name=\''.$value.'\'' );

            if( empty($this->record) )
            {
                $this->SetFieldError($fieldname,'str_cannot_find_username');
                return(false);
            }
            
            return( true );
        }

        return( false );
    }

}


class CCUserProfileForm extends CCUploadForm
{
    var $record;

    function _ok_to_edit_description($user_id)
    {
        $users =& CCUsers::GetTable();

        $row = $users->QueryKeyRow($user_id);

        if( $row['user_num_uploads'] > 0 ) {
            return true;
        }

        return strtotime($row['user_registered']) < strtotime('36 hours ago');
    }

    function CCUserProfileForm($userid,$avatar_dir)
    {
        global $CC_GLOBALS;

        $this->CCUploadForm();
        $this->record = CCDatabase::QueryRow('SELECT * FROM cc_tbl_user WHERE user_id='.$userid);
        $this->record['user_extra'] = unserialize($this->record['user_extra']);

        // print_r( $CC_GLOBALS['language']->GetPossibleLanguages() );

        $fields = array( 
                    'user_real_name' =>
                        array( 'label'      => 'str_prof_full_name',
                               'form_tip'   => 'str_prof_your_display',
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE ),

                    'user_password' =>
                       array(  'label'  => 'str_login_password',
                               'formatter'  => 'password',
                               'form_tip'   => 'str_prof_leave_this_blank',
                               'flags'      => CCFF_SKIPIFNULL ),

                    'user_email' =>
                       array(  'label'      => 'str_e_mail',
                               'form_tip'   => 'str_login_this_address_will',
                               'formatter'  => 'email',
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED ),

                    'user_image' =>
                       array(  'label'      => 'str_prof_image',
                               'formatter'  => 'avatar',
                               'form_tip'   => 'str_prof_image_file',
                               'upload_dir' => $avatar_dir,
                               'maxwidth'   => 93,
                               'maxheight'  => 94,
                               'flags'      => CCFF_POPULATE | CCFF_SKIPIFNULL  )
                    );

        if( $this->_ok_to_edit_description($userid) ) {
            $fields += array(
                'user_description' =>
                    array( 'label'      => 'str_prof_about_you',
                           'formatter'  => 'textarea',
                           'want_formatting' => true,
                           'flags'      => CCFF_POPULATE ),
                    'user_homepage' =>
                       array(  'label'      => 'str_prof_home_page_url',
                               'form_tip'   => 'str_prof_make_sure_it',
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE ),
                );
        } else {
            $fields += array(
               array( 'label'       => 'str_prof_about_you',
                       'value'      => _('Come back later to edit your details...'),
                       'formatter'  => 'statictext',
                       'flags'      => CCFF_STATIC | CCFF_NOUPDATE)
               );
        }

        if( !empty($CC_GLOBALS['lang_per_user']) )
        {
            $fields += array( 
                    'user_language' => 
                        array(  'label'      => 'str_lang_default',
                            'formatter'  => 'select',
                            'options'    => $CC_GLOBALS['language']->GetPossibleLanguages(), 
                            'flags'      => CCFF_POPULATE ),
                );
        }

        $fields += array( 
                    'user_whatido' =>
                        array( 'label'      => 'str_prof_what_i_pound_on',
                               'form_tip'   => 'str_prof_vinyl_guitar',
                               'formatter'  => 'tagsedit',
                               'flags'      => CCFF_POPULATE ),

                    'user_whatilike' =>
                        array( 'label'      => 'str_prof_what_i_like',
                               'form_tip'   => 'str_prof_django_old_skool',
                               'formatter'  => 'tagsedit',
                               'flags'      => CCFF_POPULATE ),

                    'user_lookinfor' =>
                        array( 'label'      => 'str_prof_what_im_looking_for',
                               'form_tip'   => 'str_prof_list_attributes',
                               'formatter'  => 'tagsedit',
                               'flags'      => CCFF_POPULATE ),


                        );

        $this->AddFormFields( $fields );
        $this->EnableSubmitMessage(false);
    }

}


class CCUserAPI
{
    function EditProfile($username='')
    {
        global $CC_GLOBALS;

        if(!CCUser::IsLoggedIn())
            return;

        if( !empty($username) )
            $this->CheckCredentials($username);
        else
            $username = CCUser::CurrentUserName();

        if( empty($CC_GLOBALS['avatar-dir']) )
            $upload_dir = CCUser::GetUploadDir($username);
        else
            $upload_dir = $CC_GLOBALS['avatar-dir'];

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_prof_edit_your_settings');
        CCUser::AddUserBreadCrumbs('str_prof_edit_your_settings');
        $id    = CCUser::IDFromName($username);
        $form  = new CCUserProfileForm($id, $upload_dir );
        $ok    = false;

        $old_password = $form->record['user_password'];

        if( empty($_POST['userprofile']) )
        {
            $form->PopulateValues( $form->record );
        }
        else
        {
            if( $form->ValidateFields() )
            {

                $form->FinalizeAvatarUpload('user_image', $upload_dir);
                $form->GetFormValues($fields);

                $fields['user_id'] = $id;
                if( empty($fields['user_real_name']) )
                    $fields['user_real_name'] = $username;
                $users =& CCUsers::GetTable();

                if( !empty($fields['user_password']) && ( $fields['user_password'] != $old_password ) )
                {
                    require_once('cchost_lib/cc-login.php');
                    $login = new CCLogin();
                    $login->_create_login_cookie(1,$username,$fields['user_password']);
                }
                elseif( isset($fields['user_password']) )
                {
                    unset($fields['user_password']);
                }

                $old_info = CCDatabase::QueryRow('SELECT * FROM cc_tbl_user WHERE user_id='.$id);
                $old_info['user_extra'] = unserialize($old_info['user_extra']);
                $users->Update($fields);
                CCEvents::Invoke(CC_EVENT_USER_PROFILE_CHANGED, array( $id, &$old_info));
                
                if( !empty($CC_GLOBALS['lang_per_user']) )
                    CCEvents::Invoke(CC_EVENT_TRANSLATE);

                $url = ccl('people',$username, 'profile');
                CCUtil::SendBrowserTo($url);
            }
        }

        if( !$ok )
            $page->AddForm( $form->GenerateForm() );
    }

    function AddToFavs($user_to_add_or_remove)
    {
        $page =& CCPage::GetPage();
        require_once('cchost_lib/cc-tags.php');
        $current_favs = CCUser::CurrentUserField('user_favorites');
        $favs = CCTag::TagSplit($current_favs);

        $msg = '';
        if( in_array( $user_to_add_or_remove, $favs ) )
        {
            $page->SetTitle('str_favorites_removed_from');
            $favs = array_diff($favs,array($user_to_add_or_remove));
            $msg = sprintf(_("%s has been removed from your list of favorites."), $user_to_add_or_remove);
        }
        else
        {
            $page->SetTitle('str_favorites_added_to');
            $favs[] = $user_to_add_or_remove;
            $msg = sprintf(_("%s has been added to your list of favorites."), $user_to_add_or_remove);
        }
        $new_favs = implode(',',$favs);
        $users =& CCUsers::GetTable();
        $args['user_id'] = CCUser::CurrentUser();
        $args['user_favorites'] = $new_favs;
        $users->Update($args);
        $url = ccl('people', CCUser::CurrentUserName() );
        $link1 = "<a href=\"$url\">";
        $url = ccl('people',$user_to_add_or_remove);
        $link1a = "<a href=\"$url\">";
        $link2 = '</a>';
        $msg .= ' ' . sprintf(_("Go back to %syour profile page%s or %s%s's%s"),$link1,$link2,$link1a,$user_to_add_or_remove,$link2);
        $page->Prompt($msg);
    }


    /**
    * Event handler for {@link CC_EVENT_GET_MACROS}
    *
    * @param array &$record Upload record we're getting macros for (if null returns documentation)
    * @param array &$file File record we're getting macros for
    * @param array &$patterns Substituion pattern to be used when renaming/tagging
    * @param array &$mask Actual mask to use (based on admin specifications)
    */
    function OnGetMacros(&$record,&$file,&$patterns,&$mask)
    {
        if( empty($record) )
        {
            $patterns['%artist%']       = _("Artist name");
            $patterns['%login%']        = _("Artist login name");
            $patterns['%artist_page%']  = _("Artist page URL");
        }
        else
        {
            if( empty($record['user_real_name']) )
            {
                list( $record['user_name'], $record['user_real_name'] ) =
                    CCDatabase::QueryRow('SELECT user_name, user_real_name FROM cc_tbl_user WHERE user_id = ' . $record['upload_user'], false);
            }
            $patterns['%artist%']       = $record['user_real_name'];
            $patterns['%login%']        = $record['user_name'];

            $patterns['%artist_page%'] = ccl( 'people', $record['user_name'] );
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAIN_MENU}
    * 
    * @see CCMenu::AddItems()
    */
    function OnBuildMenu()
    {
        $items = array( 
            'artist'   => array( 'menu_text'  => _('Your Page'),
                             'menu_group' => 'artist',
                             'weight' => 10,
                             'action' =>  ccp( 'people' ,'%login_name%' ),
                             'access' => CC_MUST_BE_LOGGED_IN
                             ),
                               
            'editprofile'  => array( 'menu_text'  => _('Edit Your Profile'),
                             'menu_group' => 'artist',
                             'weight' => 11,
                             'action' =>  ccp( 'people' ,'profile' ),
                             'access' => CC_MUST_BE_LOGGED_IN
                             ),
                );

        CCMenu::AddItems($items);

        $groups = array(
                    'visitor' => array( 'group_name' => _('Visitors'),
                                          'weight'    => 1 ),
                    'artist'  => array( 'group_name' => _('Artists'),
                                          'weight'   => 2 )
                    );

        CCMenu::AddGroups($groups);

    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'people',           array('CCUserPage','People'),
            CC_DONT_CARE_LOGGED_IN, 'cchost_lib/cc-user-page.php', '[username]', 
            _('Display people page or user profile'), CC_AG_USER );

        CCEvents::MapUrl( 'people/profile',   array('CCUserAPI','EditProfile'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{username}', 
            _('Edit user profile'), CC_AG_USER );

        CCEvents::MapUrl( 'people/addtofavs', array('CCUserAPI','AddToFavs'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{username}', 
            _('Add/Remove favorite users'), CC_AG_USER );
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
        if( $scope != CC_GLOBAL_SCOPE )
        {
            $fields['admins'] =
               array(  'label'      => _('Site Administrators'),
                       'form_tip'   => _('List login names of site admins. (e.g. admin, fightmaster, sally)'),
                       'value'      => 'Admin',
                       'formatter'  => 'tagsedit',
                       'flags'      => CCFF_POPULATE | CCFF_REQUIRED );

        }

    }

}
?>
