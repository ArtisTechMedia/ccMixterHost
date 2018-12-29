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
* $Id: cc-user-pref.inc 12611 2009-05-13 19:24:00Z fourstones $
*
*/

/**
* @package cchost
* @subpackage extras
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


class CCUserPreferences
{
    function SetPreferences()
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-form.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_pref_title');
        CCUser::AddUserBreadCrumbs('str_pref_title');

        $form = new CCGenericForm();

        /* default user tab */

        $tabs['uploads'] = 'str_uploads';
        $tabs['profile'] = 'str_profile';
        $record = array();
        CCEvents::Invoke( CC_EVENT_USER_PROFILE_TABS, array( &$tabs, &$record ) );

        $ex =& $CC_GLOBALS['user_extra'];

        $fields['settab'] = array(
                    'label' => 'str_pref_default_tab',
                    'form_tip' => 'str_pref_setting_this', 
                    'formatter' => 'select',
                    'value' => empty($ex['prefs']['default_user_tab']) ? 'uploads' : $ex['prefs']['default_user_tab'],
                    'options' => $tabs,
                    'flags' => CCFF_NONE
            );

        /* side bar preferences */

        $configs =& CCConfigs::GetTable();
        $admin_extras = $configs->GetConfig('extras');
        $found_admin = false;
        foreach( $admin_extras['macros'] as $admin_extra )
        {
            if( basename($admin_extra) == 'extras_user_pref.tpl' )
            {
                $found_admin = true;
                break;
            }
        }

        if( $found_admin )
        {
            require_once('cchost_lib/cc-template.inc');
            $extras = CCTemplateAdmin::GetExtras(true);
            $prev = empty($ex['prefs']['extras']) ? null : $ex['prefs']['extras'];
            foreach( $extras as $file => $text )
            {
                $fields['extras['.$file.']'] = array(
                            'label' => $text,
                            'form_tip' => 'str_pref_sidebar', 
                            'formatter' => 'checkbox',
                            'value' => $prev && in_array($file,$prev),
                            'flags' => CCFF_POPULATE
                    );
            }
        }

        $fields['anon_ok'] = array(
                'label' => 'str_email_allow_anon',
                'form_tip' => 'str_email_allow_anon_tip',
                 'formatter' => 'checkbox',
                 'flags' => CCFF_NONE,
                 'value' => !empty($ex['anon_ok']),
            );


        $page =& CCPage::GetPage();
        $url = ccl('people','notify','edit');
        $fields['notify_link'] = array(
                'label' => 'str_notify_edit',
                'form_tip' => '',
                 'formatter' => 'statictext',
                 'flags' => CCFF_STATIC | CCFF_NOUPDATE,
                 'value' => $page->String(array( 'str_notify_link', "<a href=\"$url\">",'</a>' ))
            );

        $form->AddFormFields($fields);
        
        if( empty($_POST['generic']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $prefs['extras'] = empty($_POST['extras']) ? array() : array_keys($_POST['extras']);
            $prefs['default_user_tab'] = $values['settab'];
            $user_id = CCUser::CurrentUser();
            $user_extra = CCDatabase::QueryItem('SELECT user_extra FROM cc_tbl_user WHERE user_id='.$user_id);
            $user_extra = unserialize($user_extra);
            $user_extra['prefs'] = $prefs;
            $user_extra['anon_ok'] = !empty($_POST['anon_ok']);
            $args['user_extra'] = serialize($user_extra);
            $args['user_id'] = $user_id;
            $user = new CCUsers();
            $user->Update($args);
            CCUtil::SendBrowserTo(ccl('people',CCUser::CurrentUserName()));
        }

    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('preferences'),  array( 'CCUserPreferences', 'SetPreferences'), 
                          CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Show user preferences form'), CC_AG_USER );
    }
}

?>
