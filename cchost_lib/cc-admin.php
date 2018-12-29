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
* $Id: cc-admin.php 12633 2009-05-20 22:26:49Z fourstones $
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

require_once('cchost_lib/cc-form.php');

/**
 * Derive from this class to let the user modify the app's config 
 *
 * There are many derivations of this, one for each group of config variables.
 * When you derive from this form, it will save the values here into the config
 * table for use in all subsequent sessions. The derivations
 * do not have to perform any action on user submit. The global config affects
 * all users so typically only administrators will see derivations of this form.
 *  
 * <code>
 *
 * // Derive from the base 
 *class CCMyAdminForm extends CCEditConfigForm
 *{
 *  function CCMyAdminForm()
 *  {
 *    $type_name = 'my-settings-type';
 *    $this->CCEditConfigForm($type_name); 
 *    $fields = array( 
 *     'mySettting' =>  // name of the setting
 *      array(  
 *       'label'     => 'Set this setting',
 *       'form_tip'  => 'make it good',
 *       'value'     => 'Admin',
 *       'formatter' => 'textedit',
 *       'flags'     => CCFF_POPULATE | CCFF_REQUIRED ),
 *      );
 *
 *    $this->SetModule(__FILE__);
 *    $this->AddFormFields($fields);
 *   }
 *}
 *
 *
 * // Then later in the code when it's time to call it up, simply do:
 *function ShowMyAdminForm()
 *{
 *  CCPage::SetTitle('My Admin Form');
 *  $form = new CCMyAdminForm();
 *  CCPage::AddForm( $form->GenerateForm() );
 *}
 *
 * // Still later you can retreive the user's setting:
 *function DoMyStuff()
 *{
 *  $configs =& CCConfigs::GetTable();
 *  $settings = $configs->GetConfig('my-settings-type');
 *
 *  $value = $settings['mySetting'];
 *
 *  ///...
 *}
 *</code>
 *
 *
 */
class CCEditConfigForm extends CCForm
{
    /**#@+
    /* @access private
    /* @var string
    */
    var $_typename;
    var $_scope;
    /**#@-*/

    /**
    /* @access private
    /* @var boolean
    */
    var $_merge;

    /**
     * Constructor
     *
     * @param string $config_type The name of the settings group (i.e. 'menu')
     * @param string $scope CC_GLOBAL_SCOPE or a specific vroot (blank means current)
     */
    function CCEditConfigForm($config_type,$scope='',$merge=true)
    {
        $this->CCForm();
        $this->SetHandler( ccl('admin', 'save') );
        $this->StorePageInfo();
        $this->SetHiddenField( '_name', get_class($this), CCFF_HIDDEN | CCFF_NOUPDATE );
        $this->_typename = $config_type;
        $this->_scope = $scope;
        $this->_merge = $merge;
    }

    /**
     * Config forms might be in modules not currently loaded. This method allows forms to specify 
     * which module to load during POST
     *
     * @param string $module name of file to load to process form POST
     */
    function SetModule($module)
    {
        $this->SetHiddenField('_file', $module, CCFF_HIDDEN | CCFF_NOUPDATE );
    }

    /**
     * Overrides base class in order to populate fields with current contents of environment's config.
     *
     * @param boolean $hiddenonly 
     */
    function GenerateForm($hiddenonly = false)
    {
        if( empty($_POST) )
        {
            $configs =& CCConfigs::GetTable();
            $values = $configs->GetConfig($this->_typename,$this->_scope);
            //CCDebug::PrintVar($values);
            if( $values )
                $this->PopulateValues($values);
        }
        return parent::GenerateForm($hiddenonly);
    }

    /**
    * Sets the config type (e.g. menu, settings, navpages, etc.) for the data (also done from ctor)
    * 
    * 
    * @param string $config_type The name of the settings group (i.e. 'menu')
    */
    function SetConfigType($config_type)
    {
        $this->_typename = $config_type;
    }

    /**
    * Saves this forms data to the proper type of the current scope
    * 
    * @see CCConfigs::SaveConfig()
    */
    function SaveToConfig()
    {
        $configs =& CCConfigs::GetTable();
        $this->GetFormValues($values);
        $configs->SaveConfig($this->_typename, $values, $this->_scope, $this->_merge);
    }

    function SavePrompts($save_prompt)
    {
        $page =& CCPage::GetPage();
        $args = $this->GetPageInfo();
        if( empty($args['bread_crumbs']) )
        {
            $global = $this->_scope == CC_GLOBAL_SCOPE;
            $admin = new CCAdmin();
            $admin->BreadCrumbs($global, array('url'=> ccl('admin','save'),'text' => $args['title']) );
        }
        else
        {
            $page->AddBreadCrumbs($args['bread_crumbs']);
        }

        $page->SetTitle($args['title']);
        
        if( $save_prompt )
        {
            if( empty($args['ret_link']) )
            {
                $msg = $page->String('str_changes_were_saved');
            }
            else
            {
                $msg = array( _('str_changes_saved_goto_s'), $args['ret_link'] );

            }        
            $page->Prompt($msg);
        }
    }
}

/**
* Displays global configuration options.
*
*/
class CCAdminConfigForm extends CCEditConfigForm
{
    /**
    * Constructor
    * 
    * This will invoke the CC_EVENT_GET_CONFIG_FIELDS 
    * with CC_GLOBAL_SCOPE to allow any
    * module to store/retrieve/edit site-wide settings 
    * 
    */
    function CCAdminConfigForm()
    {
        $this->CCEditConfigForm('config');
        $fields = array();
        CCEvents::Invoke( CC_EVENT_GET_CONFIG_FIELDS, array( CC_GLOBAL_SCOPE, &$fields ) );
        $this->AddFormFields($fields);
        $this->SetModule( ccs(__FILE__) );
   }
}

/**
* Displays local configuration options.
*
*/
class CCAdminSettingsForm extends CCEditConfigForm
{
    /**
    * Constructor
    * 
    * This will invoke the CC_EVENT_GET_CONFIG_FIELDS 
    * with CC_LOCAL_SCOPE to allow any
    * module to store/retrieve/edit settings in just this config root
    * 
    */
    function CCAdminSettingsForm()
    {
        $this->CCEditConfigForm('settings');
        $fields = array();
        CCEvents::Invoke( CC_EVENT_GET_CONFIG_FIELDS, array( CC_LOCAL_SCOPE, &$fields ) );
        $this->AddFormFields($fields);
        $this->SetModule( ccs(__FILE__) );
   }
}

/**
* Allows admins to create new virtual/config roots
*
*/
class CCAdminMakeCfgRootForm extends CCForm
{
    /**
    * Constructor
    *
    */
    function CCAdminMakeCfgRootForm()
    {
        $this->CCForm();

        $fields = array(
            'newcfgroot' => array(
                'label'       => _('Name of virtual root'),
                'form_tip'    => _('Must be characters and numbers only'),
                 'class'      => 'cc_form_input_short',
                 'flags'      => CCFF_REQUIRED,
                 'formatter'  => 'cfg_root' ),
             );

        $this->AddFormFields($fields);
        $url = ccl( 'viewfile', 'howtovirtual.xml' );
        $this->SetHelpText(sprintf(_("Read more about why you would want to do this and what the consequences are <a href=\"%s\">here</a>."), $url));
    }

    /**
    * noop generator, required because we have a special validator
    * 
    * @see CCAdminMakeCfgRootForm::validataor_cfg_root()
    * @see CCForm::generator_textedit()
    *
    * @param string $varname Name of the HTML field
    * @param string $value   value to be published into the field
    * @param string $class   CSS class (rarely used)
    * @return string $html HTML that represents the field
    */
    function generator_cfg_root($varname,$value='',$class='')
    {
        return( $this->generator_textedit($varname,$value,$class) );
    }

    /**
    * Handles validator for HTML field, called during ValidateFields()
    * 
    * Use the 'maxlenghth' field to limit user's input
    * 
    * On user input error this method will set the proper error message
    * into the form
    * 
    * @see CCForm::ValidateFields()
    * 
    * @param string $fieldname Name of the field will be passed in.
    * @return bool $ok true means field validates, false means there were errors in user input
    */
    function validator_cfg_root($fieldname)
    {
        if( $this->validator_must_exist($fieldname) )
        {
            $value = $this->GetFormValue($fieldname);
            if( preg_match('/[^a-z0-9]/i',$value) || (strlen($value) > 25) )
            {
                $this->SetFieldError($fieldname, _('Must be characters and numbers, no more than 25'));
                return(false);
            }
            $configs =& CCConfigs::GetTable();
            $where['config_scope'] = $value;
            if( file_exists($value) || $configs->CountRows($where) > 0 )
            {
                $this->SetFieldError($fieldname, _('That virtual root already exists'));
                return(false);
            }
            return(true);
        }
        return(false);
    }
}

/**
* Basic admin API and system event watcher.
* 
*/
class CCAdmin
{

    function BreadCrumbs($global)
    {
        global $CC_CFG_ROOT;

        $args = func_get_args();
        array_shift($args);
        $k[] = array( 'url' => ccl(), 'text' => 'str_home' );
        if( $global )
            $k[] = array( 'url' => ccl('admin','site','global'), 'text' => _('Global Settings') );
        else
            $k[] = array( 'url' => ccl('admin','site'), 'text' => _('Manage Site') . ': ' . $CC_CFG_ROOT );
        foreach( $args as $arg )
            $k[] = $arg;

        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($k);
    }

    function _check_access($args)
    {
        if( CCUser::IsSuper() )
            return $args;
        
        $cleaned = array();
        foreach( $args as $K => $mi )
        {
            if( CCEvents::CheckAccess($mi['action']) )
                $cleaned[$K] = $mi;
        }

        return $cleaned;
    }

    function _setup_global(&$args)
    {
        $global_items = array();
        CCEvents::Invoke(CC_EVENT_ADMIN_MENU, array( &$global_items, CC_GLOBAL_SCOPE ) );
        uasort($global_items,'cc_weight_sorter');
        //CCDebug::PrintVar($global_items,false);
        $args['global_title'] = ''; // _('Global Site Settings');
        $args['global_help']  = null; // _('These settings affect the entire site');
        $args['global_items'] = $this->_check_access($global_items);
        $args['do_global'] = true;
        CCPage::SetTitle(_('Global Settings'));
    }

    function _setup_local(&$args)
    {
        global $CC_CFG_ROOT;

        $local_items = array();
        CCEvents::Invoke(CC_EVENT_ADMIN_MENU, array( &$local_items, CC_LOCAL_SCOPE) );
        uasort($local_items,'cc_weight_sorter');
        $args['local_title'] = ''; // _('Virtual Root Settings');
        $configs =& CCConfigs::GetTable();
        $roots = $configs->GetConfigRoots();
        $root_list = array();
        foreach( $roots as $root )
        {
            $root_list[] = array( 'text' => $root['scope_name'] . ' (' . $root['config_scope'] . ')' ,
                                  'cfg' => $root['config_scope'],
                                  'selected' => $root['config_scope'] == $CC_CFG_ROOT );
        }
        $args['config_roots'] = $root_list;
        $args['local_help']  = _('Edit the settings for virtual root: ');
        if( $CC_CFG_ROOT == CC_GLOBAL_SCOPE )
        {
            $args['local_hint'] = _('Some of these settings may have been over written in other virtual roots');
        }
        else
        {
            $config_names = array_keys($local_items);
            $star = ' <span class="config_star">*</span>';
            foreach( $config_names as $config_name )
            {
                $where['config_type'] = $config_name;
                $where['config_scope'] = $CC_CFG_ROOT;
                if( $configs->CountRows($where) )
                    $local_items[$config_name]['menu_text'] = $star . $local_items[$config_name]['menu_text'];
            }
            $args['local_hint'] = "$star " . 
	        _("This setting over writes the main site's values");
        }

        $args['local_items'] = $this->_check_access($local_items);
        $args['delete_url'] = $CC_CFG_ROOT == CC_GLOBAL_SCOPE ? '' : url_args( ccl( 'admin/cfgroot' ), 'vroot=del' );
        $args['do_local'] = true;
        CCPage::SetTitle(_('Manage Site'));
    }

    function _add_tabs($subtab)
    {
        $tabs = array();

        $tabs[ 'global' ] = 
            array(  'text'     => _('Global Settings'),
                    'help'     => _('Global settings'),
                    'tabname'  => 'global',
                    'url'      => ccl('admin','site','global'),
                );

        $tabs[ 'local' ] = 
            array(  'text'     => _('Manage Site'),
                    'help'     => _('Manage site'),
                    'tabname'  => 'local',
                    'url'     => ccl('admin','site','local'),
                );

        $tabs[ $subtab ]['selected'] = true;
        $normal = $subtab == 'global' ? 'local' : 'global';
        $tabs[ $normal ]['normal'] = true;

        $tabinfo = array(
                'num_tabs' => 2,
                'tab_width' => '50%',
                'selected_text' => '',
                'tags' => ccl('admin','site',$subtab),
                'function' => 'url',
                'tabs' => $tabs,
                );

        //$page =& CCPage::GetPage();
        CCPage::PageArg('sub_nav_tabs',$tabinfo);
    }

    function Site($subtab='')
    {
        // CCPage::SetTitle(_('Administer ccHost Site'));

        if( empty($subtab) )
            $subtab = 'local';

        $args = array();

        $args['do_global'] = $args['do_local'] = false;

        if( $subtab == 'global' )
            $this->_setup_global($args);
        if( $subtab == 'local' )
            $this->_setup_local($args);

        $args['subtab'] = '/' . $subtab;

        if( $subtab )
            $this->_add_tabs($subtab);

        CCPage::PageArg('admin_menu', $args, 'admin_menu_page' );
    }

    /**
    * This form edits the raw configation data
    *
    * This is not on any menu, admins can reach it via admin/edit
    */
    function Deep()
    {
        CCUtil::SendBrowserTo( ccd( 'cchost_lib/cc-config-repair.php' ) );
    }

    /**
    * Prompts the user for and creates a new config root.
    *
    */
    function NewConfigRoot()
    {
        if( !CCUser::IsAdmin() )
            return;

        $is_del = !empty($_GET['vroot']) && ($_GET['vroot'] == 'del');
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = $is_del ? _('Delete a Virtual Root') : _('Create New Virtual Root');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);


        if( !$is_del )
        {
            $form = new CCAdminMakeCfgRootForm();
            if( !empty($_POST['adminmakecfgroot']) && $form->ValidateFields() )
            {
                $form->GetFormValues($fields);
                $new_cfg_root = $fields['newcfgroot'];
                $configs =& CCConfigs::GetTable();
                $settings = $configs->GetConfig('settings');
                $configs->SaveConfig('settings',$settings,$new_cfg_root);
                CCUtil::SendBrowserTo( ccc( '/' . $new_cfg_root, 'admin', 'settings' ) );
            }

            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            global $CC_CFG_ROOT;

            if( $CC_CFG_ROOT == CC_GLOBAL_SCOPE )
                return;

            if( empty($_POST['confirmdelete']) )
            {
                require_once('cchost_lib/cc-upload-forms.php');
                $form = new CCConfirmDeleteForm($CC_CFG_ROOT);
                CCPage::AddForm( $form->GenerateForm() );
            }
            else
            {
                $url = ccc( '/' . CC_GLOBAL_SCOPE, 'admin', 'site', 'local' );
                $configs =& CCConfigs::GetTable();
                $w['config_scope'] = $CC_CFG_ROOT;
                $configs->DeleteWhere($w);
                CCUtil::SendBrowserTo($url);
            }
        }
    }

    /**
    * @access private
    */
    function _wheres_home()
    {
        global $CC_CFG_ROOT;
        
        $me = $_SERVER['REQUEST_URI'];
        if( !empty($me) )
        {
            if( preg_match( "%^(.+/)$CC_CFG_ROOT%", $me, $m ) )
            {
                $base = $m[1];
            }
        }

        if( empty($base) )
            $base = '/';

        return $base;
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
            $surl = CCAdmin::_wheres_home();

            $pretty_help = _("In order to enable Rewrite rules ('pretty URLs'), you must include the following lines in your Apache configuration (virtual host or .htaccess): \n");

            $pretty_help .=<<<END
<div style="white-space:pre;font-family:Courier New, courier, serif">
RewriteEngine On
RewriteBase $surl
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ {$surl}index.php?ccm=/$1 [L,QSA]
</div>
END;
    
            $perms = array(
                    0777 => _('World can access (0777)'),
                    0775 => _('Owners and group only (0775)'),
                    0755 => _('Owners only (0755)') );

            $fields['cookie-domain'] =
               array( 'label'       => 'Cookie Domain',
                       'form_tip'      => _('This is the name used to set cookies on the client machine. Recommend  to leave this blank unless you are having problems.'),
                       'value'      => '',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE  ); // do NOT require cookie domain, blank is legit

            $fields['file-perms'] =
               array( 'label'       => _('Default File Permissions'),
                       'form_tip'      => 
                                 _('chmod() access mask to use when writing new files. Do not edit this unless you understand UNIX permissions, Apache, PHP CGI mode, etc. Changing this value will affect future writes.'),
                       'value'      => '',
                       'options'    => $perms,
                       'formatter'  => 'select',
                       'flags'      => CCFF_POPULATE  ); // do NOT require cookie domain, blank is legit

            $fields['pretty-urls-help'] = 
               array( 'label'       => '',
                       'value'      => $pretty_help,
                       'formatter'  => 'statictext',
                       'flags'      => CCFF_STATIC | CCFF_NOUPDATE);

            $fields['pretty-urls'] = 
               array( 'label'       => _('Use URL Rewrite Rules'),
                       'form_tip' 
                               => _('Check this if you want to use mod_rewrite for \'pretty\' URLs.'),
                       'value'      => 0,
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);
        }
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
                'adminhelp'   => array( 'menu_text'  => 'Admin Help',
                                 'menu_group' => 'configure',
                                 'help'      => _('Help on configuring the site'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 1,
                                 'action' =>  ccl('viewfile','adminhelp.xml')
                                 ),
                'inisettings'   => array( 'menu_text'  => 'Check PHP Settings',
                                 'menu_group' => 'configure',
                                 'help'      => _('Check your PHP environment settings'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 2,
                                 'action' =>  ccl('ini_settings')
                                 ),
                'adminadvanced'   => array( 'menu_text'  => 'Global Setup',
                                 'menu_group' => 'configure',
                                 'help'  => _('Cookies, admin email, 3rd party add ins, misc. settings.'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 3,
                                 'action' =>  ccl('admin','setup')
                                 ),
                'virtualhost'   => array( 'menu_text'  => 'Virtual ccHost',
                                 'menu_group' => 'configure',
                                 'help' => _('Create a new virtual root'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 1003,
                                 'action' =>  ccl('admin','cfgroot')
                                 ),
                    );
        }
        else
        {
            $items += array(
                'settings'   => array( 'menu_text'  => 'Settings',
                                 'menu_group' => 'configure',
                                 'help' => _('Admins, editors, etc.'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 1,
                                 'action' =>  ccl('admin','settings')
                                 ),
                );
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
            'configpage'   => array( 'menu_text'  => _('Manage Site'),
                             'menu_group' => 'configure',
                             'access' => CC_ADMIN_ONLY,
                             'weight' => 1,
                             'action' =>  ccp('admin','site')
                             ),
            'configsite'   => array( 'menu_text'  => _('Global Settings'),
                             'menu_group' => 'configure',
                             'access' => CC_ADMIN_ONLY,
                             'weight' => 1,
                             'action' =>  ccp('admin','site','global')
                             ),
                );

        CCMenu::AddItems($items);

        $groups = array(
                    'configure' => array( 'group_name' => _('Admin'),
                                          'weight'    => 100 ),
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
        CCEvents::MapUrl( 'admin/save',     array('CCAdmin', 'SaveConfig'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Common form post callback'), CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/site',     array('CCAdmin', 'Site'),       
            CC_ADMIN_ONLY, ccs(__FILE__), '[local|global]', 
            _('Joint virtual root and global settings menu') , CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/setup',    array('CCAdmin', 'Setup'),      
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Global settings menu'), CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/settings', array('CCAdmin', 'Settings'),   
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Virtual root settings menu'), CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/cfgroot',  array('CCAdmin', 'NewConfigRoot'),       
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Display form to create a new virtual root'), CC_AG_CONFIG );
    }

    /**
    * Handler for /admin/setup
    *
    * @see CCAdminConfigForm::CCAdminConfigForm()
    */
    function Setup()
    {
        require_once('cchost_lib/cc-page.php');
        $title = _('Global Site Setup');
        $this->BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);
        $form = new CCAdminConfigForm();
        CCPage::AddForm( $form->GenerateForm() );
    }

    /**
    * Handler for /admin/settings
    *
    * @see CCAdminConfigForm::CCAdminConfigForm()
    */
    function Settings()
    {
        global $CC_CFG_ROOT;

        require_once('cchost_lib/cc-page.php');
        $title = _("Edit Settings:") . " '$CC_CFG_ROOT'";
        $this->BreadCrumbs(false,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);

        $form = new CCAdminSettingsForm();
        CCPage::AddForm( $form->GenerateForm() );
    }

    /**
    * Method called when the user submits a config editing form.
    *
    * On rare occasions you may want to do special processing on user 
    * submit of an admin/config. At some point you call this to
    * save the new config values. 
    * @see CCEditConfigForm::CCEditConfigForm()
    */
    function SaveConfig($form = '')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        if( empty($_POST) )
	    {	
            $page->Prompt(_("Error Saving"));
            return;
	    }

        if( empty($form) )
        {
            $form_name = CCUtil::StripText($_REQUEST['_name']);
            if( !class_exists($form_name) )
            {
                $file = CCUtil::StripText($_REQUEST['_file']);
                require_once($file);
            }
            $form = new $form_name();
        }

        if( $form->ValidateFields() )
        {
            $form->SaveToConfig();
            $form->SavePrompts(true);
        }
        else
        {
            $form->SavePrompts(false);
            $page->AddForm( $form->GenerateForm() );
        }
    }

}


?>
