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
* $Id: cc-template.inc 12595 2009-05-12 00:37:13Z fourstones $
*
*/

/**
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');


/**
* @package cchost
* @subpackage admin
*/
class CCExtrasForm extends CCEditConfigForm
{
    function CCExtrasForm($populate=false)
    {
        $this->CCEditConfigForm('extras','',false);

        $fields['macros'] = array(
                'label'     => '',
                'formatter' => 'template',
                'macro'     => 'extras_drag.tpl/macros',
                'flags'     => CCFF_NONE,
                );

        if( $populate )
        {
            $configs =& CCConfigs::GetTable();
            $saved = $configs->GetConfig('extras');

            // these are macros in order as
            // saved last time as filenames
            $macros = $saved['macros'];

            // these are the file names -> friendly names
            require_once('cchost_lib/cc-template.inc');
            $extras = CCTemplateAdmin::GetExtras();

            // just the file names...
            $file_names = array_keys($extras);
                        
            $source = array();
            $target = array();
            $map = array();

            $mac_keys = empty($macros) ? array() : array_keys($macros);
            // we have to preserve the order in
            // the drop target area
            for( $i = 0; $i < count($mac_keys); $i++ )
            {
                $macro = $macros[$mac_keys[$i]];
                $target['targetmacros_'.($i+1)] = $extras[$macro];

                $map[$i+1] = $macro;

                // remove it from what will be the sources
                unset($extras[$macro]);
            }

            // put what's left into the sources area
            foreach( $extras as $filename => $name )
            {
                $source['draglistmacros_'.($i+1)] = $name;
                $map[$i+1] = $filename;
                ++$i;
            }
                                
            $fields['macros']['mac_source'] = $source;
            $fields['macros']['mac_target'] = $target;
            $fields['macros']['mac_map']    = $map;
            $msg = '<b><i>%(edit_extra)%</i> is no longer valid. Change it to %call(\'extras_drop\')%</b>';
            CCPage::PageArg('edit_extra',$msg);

            $help = _('Drag items from here to sidebar (and back) to add (and remove) items. '.
                      'Drag items within sidebar to reorder.');
            $this->SetFormHelp($help);            
        }
        
        $this->AddFormFields($fields);
        $this->SetSubmitText(_('Save'));
        $this->SetModule( ccs(__FILE__) );
    }
}

/**
* @package cchost
* @subpackage admin
*/
class CCAdminTemplateTagsForm extends CCEditConfigForm
{
    function CCAdminTemplateTagsForm()
    {
        $this->CCEditConfigForm('ttag');

        $configs =& CCConfigs::GetTable();
        $ttags = $configs->GetConfig('ttag');

        $fields = array();
        foreach( $ttags as $K => $V )
        {
            $fields[$K] =
               array(  'label'       => '%(' . $K . ')%',
                       'form_tip'    => '',
                       'formatter'   => strlen($V) > 80 ? 'textarea' : 'textedit',
                       'value'       => htmlspecialchars($V),
                       'flags'       => CCFF_NOSTRIP );

        }
        $this->AddFormFields($fields);
        $this->SetSubmitText("Submit Changes");
        $newtaglink = ccl('admin', 'templatetags', 'new' );
        global $CC_CFG_ROOT;
        $this->SetHelpText(
            sprintf(_('These values are used on each page for %s. If you have customized the templates you can create new tags by %sclicking here%s.'), $CC_CFG_ROOT, "<a href=\"$newtaglink\">", "</a>"));
        $this->SetModule( ccs(__FILE__) );
    }
}

/**
* @package cchost
* @subpackage admin
*/
class CCBannerLogoForm extends CCUploadForm
{
    function CCBannerLogoForm($avatar_dir,$file)
    {
        $this->CCUploadForm();


        $fields['src'] =
           array(  'label'       => _('Banner Logo File'),
                   'form_tip'    => '',
                   'upload_dir'  => $avatar_dir,
                   'formatter'   => 'avatar',
                   'value'       => $file,
                   'flags'       => CCFF_POPULATE );

        $this->AddFormFields($fields);
        $this->SetSubmitText("Submit Banner Logo");
    }
}

/**
* @package cchost
* @subpackage admin
*/
class CCNewTemplateTagForm extends CCForm
{
    function CCNewTemplateTagForm()
    {
        $this->CCForm();

        $fields['newtag'] =
                   array(  'label'       => _('Tag Name'),
                           'formatter'   => 'textedit',
                           'flags'       => CCFF_REQUIRED | CCFF_NOUPDATE) ;

        $this->AddFormFields($fields);
    }
}

/**
* @package cchost
* @subpackage admin
*/
class CCTemplateAdmin
{
    function GetPropType($dir,$type,$ret_files=true)
    {
        $fp = new CCFileProps(); return $fp->GetProps($dir,$type,$ret_files);
    }
    
    function GetFormats($type)
    {
        $fp = new CCFileProps(); return $fp->GetProps('formats',$type);
    }

    function GetProfiles()
    {
        $fp = new CCFileProps(); return $fp->GetProps('profiles','profile',true);
    }

    function GetStringProfiles()
    {
        $fp = new CCFileProps(); return $fp->GetProps('strings','string_profile');
    }

    function GetLayouts($type)
    {
        $fp = new CCFileProps(); return $fp->GetProps('layouts',$type,false);
    }

    function GetMultipleLayouts( $types )
    {
        $fp = new CCFileProps();
        // sigh, this should optimized
        $ret = array();
        foreach( $types as $type )
        {
            $ret[$type] = $fp->GetProps('layouts',$type,false);
        }
        return $ret;
    }

    function GetFonts()
    {
        $fp = new CCFileProps(); return $fp->GetProps('colors','font',false);
    }

    function GetFontSizes()
    {
        $fp = new CCFileProps(); return $fp->GetProps('colors','fontsize',false);
    }

    function GetColors()
    {
        $fp = new CCFileProps(); return $fp->GetProps('colors','color',false);
    }

    function GetStyles($type)
    {
        $fp = new CCFileProps(); return $fp->GetProps('css',$type);
    }

    function GetExtras($user_only=false)
    {
        $fp = new CCFileProps(); return $fp->GetProps('extras','extras',true,'',$user_only?'allow_user':'');
    }

    function GetSkins($skip_shared=false)
    {
        $fp = new CCFileProps(); return $fp->GetProps('','skin');
    }

    function GetHeads()
    {
        $fp = new CCFileProps(); return $fp->GetProps('','head');
    }

    function GetContentTemplates()
    {
        $fp = new CCFileProps(); return $fp->GetProps('pages','content_page');
    }

    function GetContentPages()
    {
        global $CC_GLOBALS;
        $fp = new CCFileProps(); return $fp->GetProps('','dynamic_content_page',true,$CC_GLOBALS['files-root']);
    }

    function GetMultipleTypes( $types )
    {
        $fp = new CCFileProps(); return $fp->GetMultipleProps($types);
    }

    function OnAdminExtras()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _("Sidebar Extras Content");
        CCAdmin::BreadCrumbs(false,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);
        $form = new CCExtrasForm(true);
        CCPage::AddForm( $form->GenerateForm() );
    }

    function OnPeopleCustomize($username)
    {
        require_once('cchost_lib/cc-page.php');
        if( empty($_POST) )
        {
            CCPage::SetTitle(_("Custom Skin"));
            $form = new CCPickStyleSheetForm();
            $form->SetHandler( ccl('people','customize',$username) ); // otherwise it's global
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            $css = CCUtil::StripText($_POST['style-sheet']);
            
            cc_setcookie('style-sheet-' . CCUser::CurrentUserName(),$css,time() + (60*60*24*30) );

            if( empty($_POST['http_referer']) )
                CCPage::SetStyleSheet($css);
            else
                CCUtil::SendBrowserTo();
        }
    }

    function OnAdminTags($menu='')
    {
        require_once('cchost_lib/cc-page.php');
        if( !empty($menu) )
        {
            if($menu == 'menu')
                return $this->OnAdminTemplateMenu();
            if($menu == 'bannerlogo')
                return $this->OnBannerLogo();
        }

        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _("Edit Template Tags");
        CCAdmin::BreadCrumbs(false,array('url'=>ccl('admin','templatetags','menu'),'text'=>_('Edit Banners and Footers')),
                                   array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);

        $form = new CCAdminTemplateTagsForm();
        CCPage::AddForm( $form->GenerateForm() );
    }

    function OnBannerLogo()
    {
        global $CC_GLOBALS;

        $configs =& CCConfigs::GetTable();
        $upload_dir = $CC_GLOBALS['image-upload-dir'];
        $ttags = $configs->GetConfig('site-logo');
        $filename = empty($ttags['src']) ? '' : basename($ttags['src']);
        $form = new CCBannerLogoForm($upload_dir,$filename);

        if( !empty($_POST['bannerlogo']) && $form->ValidateFields() )
        {
            $form->FinalizeAvatarUpload('src', $upload_dir);
            $form->GetFormValues($fields);
            if( $fields['src'] )
            {
                $args['src'] = ccp($upload_dir,$fields['src']);
                list( $args['w'], $args['h'] ) = @getimagesize($args['src']);
            }
            else
            {
                $args['src'] = 0;
            }
            $configs->SaveConfig('site-logo',$args);
            CCUtil::SendBrowserTo(ccl('admin','templatetags','menu'));
        }
        else
        {
            require_once('cchost_lib/cc-page.php');
            require_once('cchost_lib/cc-admin.php');
            $title = _('Set Banner Image Logo');
            CCAdmin::BreadCrumbs(false,array('url'=>ccl('admin','templatetags','menu'),'text'=>_('Edit Banners and Footers')),
                                       array('url'=>'','text'=>$title));
            CCPage::SetTitle($title);
            CCPage::AddForm( $form->GenerateForm() );
        }
    }

    function OnAdminTemplateMenu()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _('Edit Banners and Footers');
        CCAdmin::BreadCrumbs(false,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);

        $args[] = array( 'action'    => ccl('admin','templatetags','profiles'),
                         'menu_text' => _('Banner Text, Footers, etc.'),
                         'help'      => _('Edit the text of the banner, footer and other elements on every page') );

        $args[] = array( 'action'    => ccl('admin','templatetags','bannerlogo'),
                         'menu_text' => _('Banner Logo'),
                         'help'      => _('Upload a logo graphic for the banner') );

        $args[] = array( 'action'    => ccl('admin','templatetags','new'),
                         'menu_text' => _('New Page Element'),
                         'help'      => _('Web developers: Create a new element for page template skin') );

        CCPage::PageArg('client_menu',$args,'print_client_menu');
    }

    function OnNewTags()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _("Add a New Template Tag");
        CCAdmin::BreadCrumbs(false,array('url'=>ccl('admin','templatetags','menu'),'text'=>_('Edit Banners and Footers')),
                                   array('url'=>'','text'=>$title));
        $form = new CCNewTemplateTagForm();
        if( empty($_POST['newtemplatetag']) )
        {
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            $newtagname = $_REQUEST['newtag'];
            $newtag[$newtagname] = '';
            $configs =& CCConfigs::GetTable();
            $configs->SaveConfig('ttag',$newtag);
            CCUtil::SendBrowserTo(ccl('admin','templatetags'));
        }
    }

    function GetUserPaths($append='')
    {
        global $CC_GLOBALS;

        if( $append )
            $append  = '/' . $append;

        $use_paths = CCUtil::SplitPaths( $CC_GLOBALS['template-root']);
        $paths = array();
        foreach( $use_paths as $P )
        {
            if( strstr($P,'ccskins') )
                continue;
            $paths[$P . $append] = $P . $append;
        }
        return $paths;
    }

    function GetFirstUserPath()
    {
        global $CC_GLOBALS;

        $use_paths = CCUtil::SplitPaths( $CC_GLOBALS['template-root']);
        foreach( $use_paths as $P )
        {
            if( strstr($P,'ccskins') )
                continue;
            return $P;
        }
        return '';
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
            return;

        $items += array( 
            'ttag'   => array('menu_text'   => _('Banner and Footers'),
                              'menu_group'  => 'configure',
                              'help'        => _('Edit what the banner and footer says on each page.'),
                              'weight'      => 50,
                              'action'      =>  ccl('admin','templatetags','menu'),
                              'access'      => CC_ADMIN_ONLY
                             ),
            /*
            'usercss'   => array( 'menu_text'  => 'Your Skin',
                             'menu_group' => 'configure',
                             'weight' => 50,
                             'action' =>  ccl('people','customize',CCUser::CurrentUserName()),
                             'access' => CC_MUST_BE_LOGGED_IN
                             ),
            */
            'extras'  => array( 'menu_text'  => _('Sidebar Extras'),
                             'menu_group'   => 'configure',
                             'help'         => _('Pick what features are available on the side bar.'),
                             'weight'       => 53,
                             'action'       =>  ccl('admin','extras'),
                             'access'       => CC_ADMIN_ONLY
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
        CCEvents::MapUrl( 'admin/templatetags',     array('CCTemplateAdmin','OnAdminTags'),         
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _("Display form to edit variables in templates"), CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/extras',          array('CCTemplateAdmin','OnAdminExtras'),      
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _("Displays 'Sidebar' form, let's the admin select modules to display on every page."), 
            CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/templatetags/new', array('CCTemplateAdmin','OnNewTags'),           
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _("Display 'Create a new template tag' form"), CC_AG_CONFIG );

        CCEvents::MapUrl( 'people/customize',array('CCTemplateAdmin','OnPeopleCustomize'),   
            CC_ADMIN_ONLY, ccs(__FILE__) );

    }

}

?>
