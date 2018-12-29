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
* $Id: cc-menu-admin.inc 11245 2008-11-16 10:54:05Z fourstones $
*
*/

/**
* Module for admin UI for menus
*
* This module is <i>not</i> automatically included.
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

/**
* Admin form for editing menus
*/
class CCAdminMenuForm extends CCGridForm
{
    /**
    * Constructor
    */
    function CCAdminMenuForm($menu,$groups)
    {
        $this->CCGridForm();

        global $CC_CFG_ROOT;

        $configs =& CCConfigs::GetTable();
        if( $configs->ScopeHasType('menu',$CC_CFG_ROOT) )
        {
            $revert_link = ccl('admin','menu','revert');

            $rlink1 = "<a class=\"small_button\" href=\"$revert_link\"><b><u>";
            $rlink2 = "</b></u></a>";

            if( $CC_CFG_ROOT == CC_GLOBAL_SCOPE )
            {
                $help = _('You can revert to factory defaults menu by clicking %shere%s (This will erase the customization you have done to the main menu, but not affect any virtual ccHosts menus that you have customized.');
                $help = sprintf($help,$rlink1,$rlink2);
            }
            else
            {
                $help = _('You can revert to your main configuration menu by clicking %shere%s. This will erase the customization you have done to the %s menu.');
                $help = sprintf($help,$rlink1, $rlink2,"<b>$CC_CFG_ROOT</b>");
            }

        }
        else
        {
            if( $CC_CFG_ROOT == CC_GLOBAL_SCOPE )
            {
                $help = _('You are now editing the menu for the main configuration. (This menu will be used by any virtual ccHost that you have not customized.)');
            }
            else
            {
                $help = _('You are now editing the menu for the %s virtual CCHost. Any changes here will only be reflected in %s.');
                $croot = "<b>$CC_CFG_ROOT</b>";
                $help = sprintf($help,$croot,$croot);
            }
        }

        if( CCLanguage::IsEnabled() )
        {
            $exhelp =  _('WARNING: If you change the global language setting all customizations made here will be destroyed. Therefore, if you plan to set the language to something other than the default you should do it before editing here.');

            $this->SetFormHelp($exhelp);
        }

        $this->SetHelpText($help);

        //CCDebug::PrintVar($menu);
        uasort($menu,'cc_sort_user_menu');
        uasort($groups,'cc_weight_sorter');

        $heads = array( _('Menu Text'), _('Group'), _('Weight'), _('Action'), _('Id'), _('Access'), _('Delete') );
        $this->SetColumnHeader($heads);

        $group_select = array();
        foreach( $groups as $groupname => $groupinfo )
            $group_select[$groupname] = $groupinfo['group_name'];

        $access_ops = array( CC_MUST_BE_LOGGED_IN   => _('Logged in users only'),
                           CC_ONLY_NOT_LOGGED_IN  => _('Anonymous users only'),
                           CC_DONT_CARE_LOGGED_IN => _('Everyone'),
                           CC_ADMIN_ONLY          => _('Administrators only'),
                           CC_DISABLED_MENU_ITEM  => _('Hide'), );

        foreach( $menu as $keyname => $menuitem )
        {
            $a = array(
                  array(
                    'element_name'  => "mi[$keyname][menu_text]",
                    'value'      => $menuitem['menu_text'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED ),
                  array(
                    'element_name'  => "mi[$keyname][menu_group]",
                    'value'      => $menuitem['menu_group'],
                    'formatter'  => 'select',
                    'options'    => &$group_select,
                    'flags'      => CCFF_NONE ),
                  array(
                    'element_name'  => "mi[$keyname][weight]",
                    'value'      => $menuitem['weight'],
                    'formatter'  => 'textedit',
                    'class'      => 'cc_form_input_short',
                    'flags'      => CCFF_REQUIRED ),
                  array(
                    'element_name'  => "mi[$keyname][action]",
                    
                    'value'      => $menuitem['action'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED ),
                  array(
                    'element_name'  => "mi[$keyname][id]",
                    'value'      => empty($menuitem['id']) ? $this->makeid($menuitem['menu_text']) : $menuitem['id'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED ),
                  array(
                    'element_name'  => "mi[$keyname][access]",
                    'value'      => $menuitem['access'],
                    'formatter'  => 'select',
                    'options'    => $access_ops,
                    'flags'      => CCFF_NONE ),
                  array(
                    'element_name'  => "mi[$keyname][delete]",
                    'value'      => '',
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_NONE ),
                );

            $this->AddGridRow( $keyname, $a );
            /*
            $this->SetHiddenField( "mi[$keyname][action]", 
                                htmlspecialchars(urlencode($menuitem['action'])) );
            */
        }

        $S = 'new[%i%]';
        $a = array(
              array(
                'element_name'  => $S . "[menu_text]",
                'value'      => '',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . "[menu_group]",
                'value'      => '',
                'formatter'  => 'raw_select',
                'options'    => $group_select,
                'flags'      => CCFF_NONE ),
              array(
                'element_name'  => $S . "[weight]",
                'value'      => '',
                'formatter'  => 'textedit',
                'class'      => 'cc_form_input_short',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . "[action]",
                'value'      => '',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . "[id]",
                'value'      => 'mi_%i%',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . "[access]",
                'value'      => '',
                'formatter'  => 'raw_select',
                'options'    => $access_ops,
                'flags'      => CCFF_NONE ),
              array(
                'element_name'  => $S . "[delete]",
                'value'      => '',
                'formatter'  => 'checkbox',
                'flags'      => CCFF_NONE ),
            );

        $this->AddMetaRow($a,_('Add Menu Item'));
        $this->SetSubmitText(_('Submit Menu Changes'));
    }

    function makeid($text) 
    {
        return 'mi_' . strtolower(preg_replace('/[^a-z0-9]+/i', '', $text));
    }
}

/**
* Admin form for editing Menu groups
*/
class CCAdminMenuGroupsForm extends CCGridForm
{
    /**
    * Constructor
    *
    */
    function CCAdminMenuGroupsForm($groups)
    {
        $this->CCGridForm();

        $heads = array( _('Name'), _('Weight'), _('Delete') );
        $this->SetColumnHeader($heads);

        foreach( $groups as $keyname => $group )
        {
            $a = array(
                  array(
                    'element_name'  => "grp[$keyname][group_name]",
                    'value'      => $group['group_name'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED ),
                  array(
                    'element_name'  => "grp[$keyname][weight]",
                    'value'      => $group['weight'],
                    'formatter'  => 'textedit',
                    'class'      => 'cc_form_input_short',
                    'flags'      => CCFF_REQUIRED ),
                  array(
                    'element_name'  => "grp[$keyname][delete]",
                    'value'      => '',
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_NONE ),
                );

            $this->AddGridRow( $keyname, $a );
        }
        
        $S = 'new[%i%]';
        $a = array(
              array(
                    'element_name'  => $S . "[group_name]",
                    'value'      => '',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED ),
                  array(
                    'element_name'  => $S . "[weight]",
                    'value'      => '',
                    'formatter'  => 'textedit',
                    'class'      => 'cc_form_input_short',
                    'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => $S . "[delete]",
                'value'      => '',
                'formatter'  => 'checkbox',
                'flags'      => CCFF_NONE ),
            );

        $this->AddMetaRow($a,_('Add New Group'));
        $this->SetSubmitText(_('Submit Group Changes'));
    }
}


/**
* API for handling menus of links
*
*/
class CCMenuAdmin
{
    /**
    * Checks for the existance of a menu item
    * 
    * Allows 3rd party plug in writers to see if their
    * menu items are in the current configuration.
    * 
    * @param string $menu_item_name Name of the menu item
    * @returns bool $yes true if menu item is in the current configuration
    */
    function ItemExists($menu_item_name)
    {
        $menu_items =& CCMenu::_menu_items();
        return( array_key_exists($menu_item_name,$menu_items) );
    }

    /**
    * Checks for the existance of a menu group
    * 
    * Allows 3rd party plug in writers to see if their
    * menu groups are in the current configuration.
    * 
    * @param string $group_name Name of the group (internal name)
    * @returns bool $yes true if menu item is in the current configuration
    */
    function GroupExists($group_name)
    {
        $groups =& CCMenu::_menu_groups();
        return( array_key_exists($group_name,$groups) );
    }


    /**
    * Displays and processes a form that allows admins to edit the main menu
    */
    function Admin($revert='')
    {
        if( !empty($revert) && ($revert == 'revert') )
        {
            CCMenu::RevertToParent();
            return;
        }
        
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _('Edit Menus');
        CCAdmin::BreadCrumbs(false,array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        $configs =& CCConfigs::GetTable();

        if( empty($_POST['adminmenu']) )
        {
            $groups      =  $configs->GetConfig('groups');
            $menu_items  =  $configs->GetConfig('menu');
            $form = new CCAdminMenuForm($menu_items,$groups);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $menu_items = $_POST['mi'];
            if( !empty($_POST['new']) )
                $menu_items += $_POST['new'];
            CCUtil::StripSlash($menu_items);
            $copy = array();
            foreach( $menu_items as $name => $edits )
            {
                if( array_key_exists('delete',$edits ) )
                    continue;
                $id   = CCUtil::StripText($edits['id']);
                $text = CCUtil::StripText($edits['menu_text']);

                // here's some legacy hacking...
                if( preg_match( '/(additem|^[0-9]+$)/', $name) )
                {
                    $name = $basename = strtolower(preg_replace('/[^a-z0-9]+/i','_',$text));
                    $ix = 1;
                    while( array_key_exists($name,$menu_items) )
                        $name = $basename . '_' . $ix++;
                }
                $copy[$name]['menu_text']  = $text;
                $copy[$name]['menu_group'] = CCUtil::StripText($edits['menu_group']);
                $copy[$name]['weight']     = CCUtil::StripText($edits['weight']);
                $copy[$name]['access']     = CCUtil::StripText($edits['access']) ;
                $copy[$name]['id']         = $id;
                $copy[$name]['action']     = htmlspecialchars(urldecode($edits['action'])) ;
            }

            $configs->SaveConfig( 'menu', $copy, '', false);

            $page->Prompt(_("Menu changes have been saved."));
        
            CCMenu::Reset();
        }            

    }

    /**
    * Displays and processes a form that allows admins to edit the main menu's groups
    */
    function AdminGroup()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _('Edit Menu Groups');
        CCAdmin::BreadCrumbs(false,array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        $configs =& CCConfigs::GetTable();

        if( empty($_POST['adminmenugroups']) )
        {
            $groups  =  $configs->GetConfig('groups');
            $form = new CCAdminMenuGroupsForm($groups);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $groups = $_POST['grp'];
            if( !empty($_POST['new']) )
                $groups += $_POST['new'];
            CCUtil::Strip($groups);
            $copy = array();
            foreach( $groups as $name => $group )
            {
                if( array_key_exists('delete',$group) )
                    continue;

                // here's some legacy hacking...
                if( preg_match( '/^(extra[1-3]|[0-9]+)$/', $name) )
                {
                    $name = $basename = strtolower(preg_replace('/[^a-z0-9]+/i','_',$group['group_name']));
                    $ix = 1;
                    while( array_key_exists($name,$groups) )
                        $name = $basename . '_' . $ix++;
                }
                $copy[$name]['group_name']  = $group['group_name'];
                $copy[$name]['weight'] = $group['weight'];
            }
            $configs->SaveConfig('groups',$copy,'',false);
            $page->Prompt(_('Menu group changes have been saved'));
        }            

        CCMenu::Reset();
    }
}

?>
