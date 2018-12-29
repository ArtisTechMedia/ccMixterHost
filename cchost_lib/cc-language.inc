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
* $Id: cc-language.inc 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* @package cchost
* @subpackage lang
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

/**
* @package cchost
* @subpackage admin
*/
class CCLanguageAdminForm extends CCForm
{
    function CCLanguageAdminForm()
    {
        global $CC_GLOBALS;
        $this->CCForm();

        $lang_enabled = CCLanguage::IsEnabled() ;

        if( $lang_enabled )
        {

/*
            $configs =& CCConfigs::GetTable();
            $settings = $configs->GetConfig('config');
            $fields['lang_enabled'] = array(
                                    'label'      => _("Enabled language support:"),
                                    'formatter'  => 'checkbox',
                                    'value'      => $CC_GLOBALS['lang_enabled'],
                                    'flags'      => CCFF_POPULATE );
*/
            $fields['lang_locale_pref'] = array(
                                    'label'      => _("Default Locale Preference:"),
                                    'formatter'  => 'select',
                                    'value'      => $CC_GLOBALS['lang_locale_pref'],
                                    'options'    =>
                                            $CC_GLOBALS['language']->GetPossibleLocalePrefs(),
                                    'flags'      => CCFF_POPULATE );

            $fields['lang'] = array(
                                    'label'      => _("Default Language:"),
                                    'formatter'  => 'select',
                                    'value'      => empty($CC_GLOBALS['lang']) ? 
                                                        'default' : $CC_GLOBALS['lang'],
                                    'options'    => $CC_GLOBALS['language']->GetPossibleLanguages(),
                                    'flags'      => CCFF_POPULATE );

            $beta_warning = 'This feature is <b style="color:red">
                             EXPERIMENTAL</b> and should not be used in production
                             installation. It is here for development purposes only.';

            $fields['lang_per_user'] = array(
                                    'label'      => _("Allow User to Set Language:"),
                                    'formatter'  => 'checkbox',
                                    'form_tip'   => $beta_warning,
                                    'value'      => !empty($CC_GLOBALS['lang_per_user']),
                                    'flags'      => CCFF_POPULATE );

            $help = "<p>" . _("Select the default locale preference and a default language and the system will try to translate menus, navigation tabs and anything else you might have customized. If the translation system does recognize the terms used, those menu items and navigation tabs will not change.") . "</p><p>" . 
                        _("The Locale Preference is a specific folder that contains specific language translations. The default is a folder of the same name. Inside these folders are the specific language translations.") . "</p><p>" . 
                    _("To create a specific translation, please copy the default folder with a unique name, and then select it as your Default Locale Preference. Then, edit the .po files in specific language folders for customization.") . "</p>";
            $this->AddFormFields($fields);
        }
        else // no get_text on this installation
        {
            $help = _("Language support requires that the PHP 'gettext' module be enabled on your PHP installation. Please consult your system administrator.");
            $this->SetSubmitText(null);
        }

        $this->SetFormHelp($help);
    }
}

/**
*
* @subpackage admin
*/
class CCLanguageAdmin
{
    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        global $CC_GLOBALS;

        $form = new CCLanguageAdminForm();
        $page->SetTitle(_("Language Support"));

        if( empty($_POST['languageadmin']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            if( !isset($values['lang']) )
                $values['lang'] = CC_LANG;
        
            if ( !isset($values['lang_locale_pref']) )
                $values['lang_locale_pref'] = CC_LANG_LOCALE_PREF;

            $config =& CCConfigs::GetTable();    
            //CCDebug::PrintVar($values);
            $config->SaveConfig('config',$values,CC_GLOBAL_SCOPE,true);
            //CCEvents::Invoke(CC_EVENT_TRANSLATE);
            CCUtil::SendBrowserTo(ccl('admin','language', 'save'));
        }
    }

    /**
     * Displays a page just saying the language options are saved at present.
     */
    function SavePage ()
    {
        $page =& CCPage::GetPage();

        $page->SetTitle(_("Language Support"));
        $page->Prompt(_("Language support options saved."));
    }

    /**
     * This prints a page with diagnostics for the language code and so
     * we can see what is happening on other peoples systems. I only allowed
     * this if one is logged in and an admin.
     */
    function DiagnosticPage ()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        global $CC_GLOBALS;
        $page->SetTitle(_("Diagnostic") . " : " . _("Language") );
        $var_mixed = array("CC_GLOBALS"        => &$CC_GLOBALS, 
                           "_SERVER VARIABLES" => &$_SERVER,
                           "_ENV VARIABLES"    => &$_ENV,
                           "SYSTEM LOCALES"    => $CC_GLOBALS['language']->GetSystemLocales());
        CCDebug::PrintVar($var_mixed);
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $items += array(
                'language'   => array( 
                                 'menu_text'  => _('Language'),
                                 'menu_group' => 'configure',
                                 'help' => _('Configure Language use'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 25,
                                 'action' =>  ccl('admin','language')
                                 ),
                );
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','language'),  array( 'CCLanguageAdmin', 'Admin'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show language admin form'), CC_AG_MISC_ADMIN );

        CCEvents::MapUrl( ccp('admin','language','save'),  array( 'CCLanguageAdmin', 'SavePage'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('language form post callback'), CC_AG_MISC_ADMIN  );

        CCEvents::MapUrl( ccp('admin','language','diagnostic'),  
                            array( 'CCLanguageAdmin', 'DiagnosticPage'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Language diag.'), CC_AG_MISC_ADMIN  );
    }

}


?>
