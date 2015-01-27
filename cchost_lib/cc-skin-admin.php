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
* $Id: cc-skin-admin.php 12639 2009-05-22 21:07:25Z fourstones $
*
*/

/**
* Base classes and general user admin interface
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

class CCSkinProfilesForm extends CCEditConfigForm
{
    function CCSkinProfilesForm()
    {
        $this->CCEditConfigForm('skin-settings');

        require_once('cchost_lib/cc-template.inc');

        $fields['skin_profile'] = array(
                'label'     => _('Skin Profile'),
                'formatter' => 'select',
                'options'   => CCTemplateAdmin::GetProfiles(),
                'flags'     => CCFF_POPULATE,
                );

        $this->SetHandler( ccl('admin','skins','profiles') ); // reset the handler back to us
        $this->SetHelpText(_('Selecting a new profile here will destroy any skin settings you have not saved to the current profile'));
        $this->AddFormFields($fields);
        $this->SetSubmitText(_('Load this Skin Profile'));
    }
}

class CCSkinProfileSaveForm extends CCForm
{
    function CCSkinProfileSaveForm()
    {
        $this->CCForm();

        require_once('cchost_lib/cc-template.inc');
        $user_paths = CCTemplateAdmin::GetUserPaths('profiles');

        $fields['profile-name'] = array(
                'label'     => _('Name'),
                'form_tip'  => _('Your current skin profile settings will be saved to this name (file safe characters only)'),
                'formatter' => 'textedit',
                'flags'     => CCFF_REQUIRED,
                );
        $fields['desc'] = array(
                'label'     => _('Description'),
                'form_tip'  => _('A one-line description of this skin profile'),
                'formatter' => 'textedit',
                'flags'     => CCFF_REQUIRED,
                );
        $fields['target_dir'] = array(
                'label'     => _('Target Directory'),
                'form_tip'  => _('This profile will be saved here'),
                'formatter' => 'select',
                'options'   => $user_paths,
                'flags'     => CCFF_NONE,
                );

        $this->AddFormFields($fields);
        $this->SetSubmitText(_('Save this Skin Profile'));
    }
}

class CCSkinCreateForm extends CCForm
{
    function CCSkinCreateForm()
    {
        $this->CCForm();

        require_once('cchost_lib/cc-template.inc');
        $skins      = CCTemplateAdmin::GetSkins(true);
        $user_paths = CCTemplateAdmin::GetUserPaths();

        $fields['skin-file'] =
            array( 'label'       => _('Clone this Skin Template'),
                   'form_tip'    => _('Your new skin will start as a clone of this.'),
                   'formatter'   => 'select',
                   'options'     => $skins,
                   'flags'       => CCFF_POPULATE );
        $fields['target-dir'] =
            array( 'label'       => _('Target Directory'),
                   'form_tip'    => _('Your new skin will be created here.'),
                   'formatter'   => 'select',
                   'options'     => $user_paths,
                   'flags'       => CCFF_POPULATE );
        $fields['skin-name'] =
            array( 'label'       => _('Name'),
                   'form_tip'    => _('The name of your new skin will be called.'),
                   'formatter'   => 'textedit',
                   'flags'       => CCFF_POPULATE | CCFF_REQUIRED);

        $this->AddFormFields($fields);
        $this->SetSubmitText(_('Create new skin'));
    }
}

class CCSkinSettingsForm extends CCEditConfigForm
{
    function CCSkinSettingsForm()
    {
        $this->CCEditConfigForm('skin-settings');

        require_once('cchost_lib/cc-template.inc');
        $ffiles = CCTemplateAdmin::GetMultipleTypes( array( 'skin', 'list', 'page', 
                                                            'head', 'string_profile',
                                                            'paging_style' ) );
        $skins             = $ffiles['skin'];
        $list_format_files = $ffiles['list'];
        $page_format_files = $ffiles['page'];
        $heads             = $ffiles['head'];
        $paging_styles     = $ffiles['paging_style'];
        $str_profiles      = $ffiles['string_profile'];

        $fields['string_profile'] =
            array( 'label'       => _('String Profile'),
                   'form_tip'    => _('Default profile for display text in tabs, prompts, menus, etc.'),
                   'formatter'   => 'select',
                   'options'     => $str_profiles,
                   'flags'       => CCFF_POPULATE );
        $fields['list_file'] =
            array( 'label'       => _('Upload Page Format'),
                   'form_tip'    => _('Use this template when showing a single upload page'),
                   'formatter'   => 'raw_select',
                   'options'     => $page_format_files,
                   'value'       => 'ccskins/shared/formats/upload_page_wide.php',
                   'flags'       => CCFF_POPULATE_WITH_DEFAULT );
        $fields['list_files'] =
            array( 'label'       => _('Upload Listing Format'),
                   'form_tip'    => _('Use this template when listing multiple files'),
                   'formatter'   => 'raw_select',
                   'options'     => $list_format_files,
                   'value'       => 'ccskins/shared/formats/upload_list_wide.tpl',
                   'flags'       => CCFF_POPULATE_WITH_DEFAULT );
        $fields['max-listing'] =
            array( 'label'       => _('Max Items Per Page'),
                   'form_tip'    => _('Maximum number of uploads, users in a listing'),
                   'class'       => 'cc_form_input_short',
                   'formatter'   => 'textedit',
                   'flags'       => CCFF_POPULATE | CCFF_REQUIRED);
        $fields['paging_style'] =
            array( 'label'       => _('Paging Style'),
                   'form_tip'    => _('Select the default paging buttons ("prev"/"next")'),
                   'formatter'   => 'select',
                   'options'     => $paging_styles,
                   'flags'       => CCFF_POPULATE );
        $fields['head-type'] =
            array( 'label'       => _('Optimized HEAD'),
                   'form_tip'    => _('Combine scripts and styles in common files'),
                   'formatter'   => 'select',
                   'options'     => $heads,
                   'flags'       => CCFF_POPULATE );
        $fields['skin-file'] =
            array( 'label'       => _('Base Skin Template'),
                   'form_tip'    => _('Default skin template for this profile (Advanced)'),
                   'formatter'   => 'select',
                   'options'     => $skins,
                   'flags'       => CCFF_POPULATE );

        $this->AddFormFields($fields);
        $this->SetSubmitText(_('Submit Basic Skin Settings'));
        $this->SetModule(ccs(__FILE__));
        $page =& CCPage::GetPage();
        $page->AddScriptLink('js/skin_editor.js');
    }
}

/**
 *
 */
class CCSkinLayoutForm extends CCEditConfigForm
{
    /**
     * Constructor
     */
    function CCSkinLayoutForm()
    {
        $this->CCEditConfigForm('skin-settings');

        require_once('cchost_lib/cc-template.inc');

        $ffiles = CCTemplateAdmin::GetMultipleTypes( array( 'button_style','formfields_layout','gridform_layout' ) );

        $fields = array();

        $fields['formfields_layout'] =
            array( 'label'       => _('Form Fields Style'),
                   'form_tip'    => _('Choice the formatting of regular submit and user profile forms'),
                   'formatter'   => 'select',
                   'value'       => 'form_fields.tpl/form_fields',
                   'options'     => $ffiles['formfields_layout'],
                   'flags'       => CCFF_POPULATE_WITH_DEFAULT );

        $fields['gridform_layout'] =
            array( 'label'       => _('Grid Form Fields Style'),
                   'form_tip'    => _('Choice the formatting of grid forms'),
                   'formatter'   => 'select',
                   'value'       => 'form_fields.tpl/grid_form_fields',
                   'options'     => $ffiles['gridform_layout'],
                   'flags'       => CCFF_POPULATE_WITH_DEFAULT );

        $fields['button_style'] = array(
                'label'     => _('Button Style'),
                'formatter' => 'select',
                'value'       => 'layouts/button_browser.php',
                'options'   => $ffiles['button_style'],
                'flags'     => CCFF_POPULATE_WITH_DEFAULT ,
                );
                

        $ffiles = CCTemplateAdmin::GetMultipleLayouts( array( 'tab_pos', 'box_shape', 'layout',  ) );

        $fields['tab_pos'] = array(
                'label'     => _('Tab Positions'),
                'formatter' => 'metalmacro',
                'macro'     => 'skin_editor.php/edit_layouts',
                'scroll'    => false,
                'props'     => $ffiles['tab_pos'],
                'flags'     => CCFF_POPULATE,
                );

        $fields['box_shape'] = array(
                'label'     => _('Box Shapes'),
                'formatter' => 'metalmacro',
                'macro'     => 'skin_editor.php/edit_layouts',
                'scroll'    => false,
                'props'     => $ffiles['box_shape'],
                'flags'     => CCFF_POPULATE,
                );

        $fields['page_layout'] = array(
                'label'     => _('Page Layout'),
                'formatter' => 'metalmacro',
                'macro'     => 'skin_editor.php/edit_layouts',
                'scroll'    => true,
                'props'     => $ffiles['layout'],
                'flags'     => CCFF_POPULATE,
                );

        $this->AddFormFields($fields);
        $this->SetSubmitText(_('Submit Skin Layout Changes'));
        $this->SetModule(ccs(__FILE__));

        $page =& CCPage::GetPage();
        $page->AddScriptLink('js/skin_editor.js',true);
    }
}



/**
 *
 */
class CCAdminColorSchemesForm extends CCEditConfigForm
{
    /**
     * Constructor
     */
    function CCAdminColorSchemesForm()
    {
        $this->CCEditConfigForm('skin-settings');

        require_once('cchost_lib/cc-template.inc');

        $fields['font_scheme'] = array(
                'label'     => _('Fonts'),
                'formatter' => 'metalmacro',
                'macro'     => 'skin_editor.php/edit_font_schemes',
                'scroll'    => false,
                'props'     => CCTemplateAdmin::GetFonts(),
                'flags'     => CCFF_POPULATE,
                );

        $fields['font_size'] = array(
                'label'     => _('Font Size'),
                'formatter' => 'metalmacro',
                'macro'     => 'skin_editor.php/edit_font_schemes',
                'scroll'    => false,
                'props'     => CCTemplateAdmin::GetFontSizes(),
                'flags'     => CCFF_POPULATE,
                );

        $fields['color_scheme'] = array(
                'label'     => _('Color Scheme'),
                'formatter' => 'metalmacro',
                'macro'     => 'skin_editor.php/edit_color_schemes',
                'scroll'    => true,
                'props'     => CCTemplateAdmin::GetColors(),
                'flags'     => CCFF_POPULATE,
                );

        $this->AddFormFields($fields);
        $this->SetSubmitText(_('Submit Skin Appearance Changes'));
        $this->SetModule(ccs(__FILE__));

        $page =& CCPage::GetPage();
        $page->AddScriptLink('js/skin_editor.js',true);
    }

}


/**
* Edit and maintain color schemes
* 
*/
class CCSkinAdmin
{
    /**
    * @access private
    */
    function _build_bread_crumb_trail($text,$cmd=false)
    {        
        require_once('cchost_lib/cc-admin.php');
        $admin = new CCAdmin();
        $trail2 = array( 'url' => '', 'text' => _($text) );
        
        if( $cmd )
        {
            $trail1 = array( 'url' => ccl('admin','skins'), 'text' => _('Configure Skins') );
            $admin->BreadCrumbs(false,$trail1,$trail2);
        }
        else
        {
            $admin->BreadCrumbs(false,$trail2);
        }
    }

    function Admin()
    {
        require_once('cchost_lib/cc-template.inc');
        $config =& CCConfigs::GetTable();
        $skin_settings = $config->GetConfig('skin-settings');
        if( empty($skin_settings['skin_profile']) )
        {
            $msg = _('There is no skin profile set, this is probably a bad thing');
        }
        else
        {
            $fp = new CCFileProps();
            $props = $fp->GetFileProps($skin_settings['skin_profile']);
            $msg = sprintf(_('Current skin profile: %s'), '<b>' . $props['desc'] . '</b>' );
        }

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        $this->_build_bread_crumb_trail(_('Configure Skins'));

        $page->SetTitle(_('Configure Skins'));

        $args[] = array( 'action'    => ccl('admin','skins','profiles'),
                         'menu_text' => _('Load a Profile'),
                         'help'      => _('Start here to pick from an existing skin profile') );

        $args[] = array( 'action'    => ccl('admin','skins','settings'),
                         'menu_text' => _('Basic Settings'),
                         'help'      => _('String profile, listing choices, etc.') );

        $args[] = array( 'action'    => ccl('admin','skins','layout'),
                         'menu_text' => _('Layouts'),
                         'help'      => _('Page layouts, tab placement, box shapes, etc.') );

        $args[] = array( 'action'    => ccl('admin','colors'),
                         'menu_text' => _('Color, Font, Text size'),
                         'help'      => _('Fonts and colors') );

        $args[] = array( 'action'    => ccl('admin','skins','profile', 'save'),
                         'menu_text' => _('Save this Skin Profile'),
                         'help'      => _('Save the current settings to your own profile') );

        $args[] = array( 'action'    => ccl('admin','skins','create' ),
                         'menu_text' => _('Create Skin Template'),
                         'help'      => _('For web developers: Sets up a new skin template') );

        require_once('cchost_lib/cc-page.php');
        $page->PageArg('client_menu_help', $msg );
        $page->PageArg('client_menu',$args,'print_client_menu');
    }

    function Profiles()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $title = _('Select a New Skin Profile');
        $this->_build_bread_crumb_trail($title,true);

        $form = new CCSkinProfilesForm();
        if( empty($_POST['skinprofiles']) || !$form->ValidateFields() )
        {
            require_once('cchost_lib/cc-page.php');
            $page->SetTitle($title);
            $page->AddForm($form->GenerateForm());
        }
        else
        {
            $form->GetFormValues($values);
            $this->_load_profile($values['skin_profile']);
            CCUtil::SendBrowserTo(ccl('admin','skins'));
        }
    }

    function _load_profile($skin_profile)
    {
        $fp = new CCFileProps();
        $props = $fp->GetFileProps($skin_profile);
        unset($props['type']);
        unset($props['desc']);
        if( empty($props['paging_style']) ) // added in 5.1
            $props['paging_style'] = 'ccskins/shared/layouts/paging_basic.php';

        $props['skin_profile'] = $skin_profile;
        $config =& CCConfigs::GetTable();
        /* wtf was this about??
        $curr_settings = $config->GetConfig('skin-settings'); // for vroots we need a copy of the root values
        $props = array_merge($curr_settings,$props);
        $config->SaveConfig('skin-settings',$props);
        */
        $config->SaveConfig('skin-settings',$props,'',false); // no merge
        global $CC_GLOBALS;
        $CC_GLOBALS = array_merge($CC_GLOBALS,$props);
    }

    function ProfileLoad()
    {
        if( empty($_GET['profile']) )
            die('missing profile argument');
        $file = CCUtil::Strip($_GET['profile']);
        if( empty($file) )
            die('invalid profile argument');
        if( !file_exists($file) )
        {
            $file = 'ccskins/shared/profiles/' . $file;
            if( !file_exists($file) )
                die('can not find profile ' . $file );
        }
        $this->_load_profile($file);
        CCUtil::SendBrowserTo(); // back to referrer
    }

    function ProfileSave()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $title = _('Save Skin Profile');
        $this->_build_bread_crumb_trail($title,true);
        $page->SetTitle($title);
        $form = new CCSkinProfileSaveForm();
        if( empty($_POST['skinprofilesave']) || !$form->ValidateFields() )
        {
            $page->AddForm($form->GenerateForm());
        }
        else
        {
            /*
                just to be different:

                profiles are saved to files, not config. we only save the name here.
            */
            $form->GetFormValues($values);
            $config =& CCConfigs::GetTable();
            $skin_settings = $config->GetConfig('skin-settings');
            $text = '<?/*' . "\n[meta]\n    type = profile\n    desc = _('{$values['desc']}')";
            foreach( array( 'properties', 'skin_profile') as $outme )
                if( isset($skin_settings[$outme]) )
                    unset($skin_settings[$outme]);

            foreach( $skin_settings as $K => $V )
            {
                $text .= "\n    $K  = $V";
            }
            $text .= "\n[/meta]\n*/?" . '>' . "\n";
            CCUtil::MakeSubdirs($values['target_dir']);
            $fname = preg_replace('/[^a-z]+/','',strtolower($values['profile-name']));
            if( empty($fname) )
                $fname = 'madeup_' . rand();
            $fname = 'profile_' . $fname . '.php';
            $target = $values['target_dir'] . '/' . $fname;
            $f = fopen($target,'w');
            fwrite($f,$text);
            fclose($f);
            $skin_settings['skin_profile'] = $target;
            $config->SaveConfig('skin-settings',$skin_settings);
            CCUtil::SendBrowserTo(ccl('admin','skins'));
        }
    }

    function Layout()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $title = _('Configure Skins Layouts');
        $this->_build_bread_crumb_trail($title,true);
        $page->SetTitle($title);
        $form = new CCSkinLayoutForm();
        $help =<<<EOF
    Note that many combinations of layouts will not work together because
    they are simply incompatible. Some times an option you set here
     will simply be ignored by some
    skin templates. Experimentaion is encouraged...    
EOF;
        $form->SetFormHelp($help);
        $page->AddForm($form->GenerateForm());
    }

    function Settings()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $title = _('Configure Skins Settings');
        $this->_build_bread_crumb_trail($title,true);
        $page->SetTitle($title);
        $form = new CCSkinSettingsForm();
        $page->AddForm($form->GenerateForm());
    }

    function Create()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $title = _('Create a Skin Template');
        $this->_build_bread_crumb_trail($title,true);
        $page->SetTitle($title);
        $form = new CCSkinCreateForm();
        if( empty($_POST['skincreate']) || !$form->ValidateFields() )
        {
            $page->AddForm($form->GenerateForm());
        }
        else
        {
            $form->GetFormValues($values);
            $src = dirname($values['skin-file']);
            $safe_name = strtolower(preg_replace('/[^a-z0-9_-]/','',$values['skin-name']));
            $target = rtrim($values['target-dir'],'/') . '/' . $safe_name;
            if( file_exists($target) )
            {
                $form->SetFieldError('skin-name',_('A directory with that name already exists'));
                $page->AddForm($form->GenerateForm());
            }
            else
            {
                $tpl_file = rtrim($target,'/') . '/skin.tpl';
                $this->_deep_copy($src,$target);
                $profile = '<?/*';
                $profile .=<<<EOF
[meta]
    type              = profile
    skin-file         = {$tpl_file}
    desc              = _('{$safe_name}')
    string_profile    = ccskins/shared/strings/all_media.php
    list_file         = ccskins/shared/formats/upload_page_wide.php
    list_files        = ccskins/shared/formats/upload_list_wide.tpl
    form_fields       = form_fields.tpl/form_fields
    grid_form_fields  = form_fields.tpl/grid_form_fields
    tab_pos           = ccskins/shared/layouts/tab_pos_nested.php
    box_shape         = ccskins/shared/layouts/box_none.php
    page_layout       = ccskins/shared/layouts/layout024.php
    font_scheme       = ccskins/shared/colors/font_arial.php
    font_size         = ccskins/shared/colors/fontsize_sz_small.php
    color_scheme      = ccskins/shared/colors/color_mono.php
    paging_style      = ccskins/shared/layouts/paging_basic.php
[/meta]
*/?>
EOF;
                $prof_dir = rtrim(dirname($target),'/') . '/profiles';
                CCUtil::MakeSubdirs($prof_dir, 0777);
                $prof_file = $prof_dir . '/' . $safe_name . '.php';
                $f = fopen( $prof_file , 'w');
                fwrite($f,$profile);
                fclose($f);
                $this->_load_profile($prof_file);
                $msg = sprintf(_('The skin and profile %s has been created sucessfully'),"<b>'" . $target . "'</b>");
                $msg .= '<p>' . sprintf(_('Return to %sSkin Settings%.'),'<a href="' . ccl('admin','skins') .'">', '</a>') . '</p>';
                $text = file_get_contents($tpl_file);
                $text = preg_replace('/(desc\s+=\s+_\(\')([^\']+)\'/','$1'.$tpl_file.'\'',$text);
                $f = fopen($tpl_file,'w');
                fwrite($f,$text);
                fclose($f);
                $page->Prompt($msg);
            }
        }
    }

    function _deep_copy($src,$target)
    {
        if( !file_exists($target) )
        {
            //print("making dir: $target<br />");
            CCUtil::MakeSubdirs($target,0777);
        }

        $dirs = glob($src . '/*', GLOB_ONLYDIR );
        if( $dirs !== false )
        {
            foreach( $dirs as $dir )
            {
                $sub_dir = basename($dir);
                $this->_deep_copy($src . '/' . $sub_dir, $target . '/' . $sub_dir );
            }
        }

        $files = glob($src . '/*.*');
        if( $files !== false )
        {
            foreach( $files as $file )
            {
                $base = basename($file);
                $t = $target . '/' . $base;
                copy( $file, $t );
                chmod( $t, 0777 );
            }
        }
    }

    function ColorSchemes()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $title = _('Manage Color Schemes');
        $this->_build_bread_crumb_trail($title,true);
        $page->SetTitle($title);
        $form = new CCAdminColorSchemesForm();
        $page->AddForm($form->GenerateForm());
    }

    function OnAdminMenu( &$items, $scope )
    {
        if( $scope == CC_GLOBAL_SCOPE )
            return;

        $items += array( 
            'skin-settings'   => array( 'menu_text'  => _('Skin'),
                             'menu_group' => 'configure',
                             'help'      => _('Choose a skin, theme, layout, colors, fonts, etc.'),
                             'access' => CC_ADMIN_ONLY,
                             'weight' => 3,
                             'action' =>  ccl('admin','skins')
                             ),
            );
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/skins',                array('CCSkinAdmin', 'Admin'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show main "Configure Skins" menu)'),  CC_AG_SKINS );
        CCEvents::MapUrl( 'admin/skins/profiles',       array('CCSkinAdmin', 'Profiles'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show "Select New Profile" form'), CC_AG_SKINS );
        CCEvents::MapUrl( 'admin/skins/profile/save',   array('CCSkinAdmin', 'ProfileSave'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show "Save Profile" form'), CC_AG_SKINS );
        CCEvents::MapUrl( 'admin/skins/profile/load',   array('CCSkinAdmin', 'ProfileLoad'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show "Load Profile" form'), CC_AG_SKINS );
        CCEvents::MapUrl( 'admin/skins/settings',       array('CCSkinAdmin', 'Settings'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show "Profile Settings" form'), CC_AG_SKINS );
        CCEvents::MapUrl( 'admin/skins/layout',         array('CCSkinAdmin', 'Layout'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show "Profile Layouts" form'), CC_AG_SKINS );
        CCEvents::MapUrl( 'admin/colors',               array('CCSkinAdmin', 'ColorSchemes'),       
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show "Profile Fonts and Colors" form'), CC_AG_SKINS );
        CCEvents::MapUrl( 'admin/skins/create',         array('CCSkinAdmin', 'Create'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show "Create Skins" form'), CC_AG_SKINS );
    }

}


?>
