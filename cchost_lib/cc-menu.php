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
* $Id: cc-menu.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* Module for handling menus
*
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* API for handling menus 
*
*/
class CCMenu
{
    /**
    * Gets (and builds if it has to) the current main menu
    * 
    * The menu is built in two phases:
    *<ol><li>
    *Event: {@link CC_EVENT_MAIN_MENU} During this phase menus are
    * built for caching so all data is assumed to be static. Typical handler:
    *<code>
    *function OnBuildMenu()
    *{
    *    $items = array( 
    *        'submitforms' => array(   
    *                             'menu_text'  => _('Submit Files'),
    *                             'menu_group' => 'artist',
    *                             'access'     => CC_MUST_BE_LOGGED_IN,
    *                             'weight'     => 6,
    *                             'action'     => ccp('submit') 
    *                            ), 
    *        );
    *    
    *    CCMenu::AddItems($items);
    *}
    *</code></li>
    *<li>Event: {@link CC_EVENT_PATCH_MENU} This called per session and gives
    * an opportunity for dynamically changing values in the menu. Example:
    *<code>
    *function OnPatchMenu(&$menu)
    *{
    *    $current_user_name = $this->CurrentUserName();
    *    $menu['artist']['action']  =  str_replace('%login_name%',
    *                                              $current_user_name,
    *                                              $menu['artist']['action']);
    *}
    *</code>
    *</li>
    *</ol>
    * If $force is set to true this method will ignore any cached data and 
    * build the latest version of the menu
    *
    * @param boolean $force 
    */
    public static function GetMenu($force = false)
    {
        static $_menu;
        if( $force || !isset($_menu) )
            $_menu = CCMenu::_build_menu();
        return( $_menu );
    }


    /**
    * Occasionally the menu needs to be reset (e.g. a user logs out)
    *
    */
    public static function Reset()
    {
        CCMenu::_menu_data(true);
        CCMenu::GetMenu(true);
    }

    /**
    * Force the cached url maps and menus to be rebuilt
    *
    * You can invoke by URL: ?ccm=/media/admin/menu/killcache
    */
    public static function KillCache()
    {
        $configs =& CCConfigs::GetTable();
        $configs->DeleteType('urlmap',CC_GLOBAL_SCOPE);
        CCEvents::GetUrlMap(true);
        CCMenu::Reset();
        $page =& CCPage::GetPage();
        $page->Prompt(_('Menu/URL cache has been cleared'));
    }

    /**
    * Add items to the main menu for the current virtual config root
    *
    * Typically called during a handler {@link CC_EVENT_BUILD_MENU} event,
    * in which case you do NOT want to set save_now to true.
    *
    * If calling outside a build menu event (e.g. installing a 
    * plug-in) then save_now must be set to 'true' to preserve
    * your changes between sessions.
    *
    * @param array $items Array of menu items
    * @param bool $save_now Writes the menu items to current configs
    */
    public static function AddItems( $items, $save_now = false )
    {
        global $CC_CFG_ROOT;

        $menu_items =& CCMenu::_menu_items();
        $menu_items = array_merge($menu_items,$items);
        if( $save_now )
        {
            $configs =& CCConfigs::GetTable();

            // we don't want to add items to a config that
            // doesn't have a menu
            if( ($CC_CFG_ROOT != CC_GLOBAL_SCOPE) && 
                !$configs->ScopeHasType( 'menu', $CC_CFG_ROOT ) )
            {
                $menu = $configs->GetConfig('menu', CC_GLOBAL_SCOPE );
                $items = array_merge($menu,$items);
            }

            $configs->SaveConfig( 'menu',   $items,  '',  true);
        }
    }

    /**
    * Add group items to the main menu for the current virtual config root
    *
    * Typically called during a handler CC_EVENT_BUILD_MENU event,
    * in which case you do NOT want to set save_now to true.
    *
    * If calling outside a build menu event (e.g. installing a 
    * plug-in) then save_now must be set to 'true' to preserve
    * your changes between sessions.
    *
    * @param array $items Array of group
    * @param bool $save_now Writes the menu items to current configs
    */
    public static function AddGroups($items,$save_now = false)
    {
        $groups =& CCMenu::_menu_groups();
        $groups = array_merge($groups,$items);
        if( $save_now )
        {
            $configs =& CCConfigs::GetTable();
            $configs->SaveConfig( 'groups',   $items,  '',  true);
        }
    }

    /**
    * Removed a menu item from current virtual config root
    * 
    * @param string $item_name The name of the menu item to remove
    * @param bool   $permanent Write this change to the config
    * @return bool $removed true = menuitem was found and removed, false = menu item not found
    */
    public static function RemoveItem( $item_name, $permanent = true )
    {
        $configs =& CCConfigs::GetTable();
        $menu = $configs->GetConfig( 'menu ');
        if( !empty($menu[$item_name]) )
        {
            unset($menu[$item_name]);
            if( $permanent )
                $configs->SaveConfig( 'menu', $menu, '', false );
            return(true);
        }
        return(false);
    }

    /**
    * Returns a mask of bits that represents the current user's access level
    *
    * @returns integer $mask Mask of CC_ bits (e.g. CC_MUST_BE_LOGGED_IN)
    */
    public static function GetAccessMask()
    {
        if( CCUser::IsLoggedIn() )
        {
            $mask = CC_MUST_BE_LOGGED_IN | CC_DONT_CARE_LOGGED_IN;

            if( CCUser::IsAdmin() )
                $mask |= CC_ADMIN_ONLY;

            if( CCUser::IsSuper() )
                $mask |= CC_SUPER_ONLY;
        }
        else
        {
            $mask = CC_ONLY_NOT_LOGGED_IN | CC_DONT_CARE_LOGGED_IN;
        }
        return( $mask );
    }

    /**
    * Internal: go out there and build the main menu
    * @access private
    */
    static function _build_menu()
    {
        $mask        =  CCMenu::GetAccessMask();
        $groups      =  CCMenu::_menu_groups();
        $menu_items  =& CCMenu::_menu_items(); 

        foreach( $menu_items as $name => $item )
        {
            if( ($item['access'] & $mask) != 0 )
            {
                if( strpos($item['action'],'http://',0) === false )
                    $item['action'] = ccl($item['action']);
                if( array_key_exists($item['menu_group'],$groups) )
                    $groups[$item['menu_group']]['menu_items'][] = $item;
            }
        }

        $menu = array();
        foreach( $groups as $groupname => $group  )
        {
            if( !empty($group['menu_items']) )
            {
                usort( $group['menu_items'], 'cc_weight_sorter' );
                $group['group_id'] = $groupname . "_group";
                $menu[] = $group;
            }
        }

        return( $menu );
    }

    /**
    * Internal: get the menu from the cache and apply dynamic pathes to it
    * @access private
    */
    static function &_menu_data($force = false, $action = CC_MENU_DISPLAY )
    {
        static $_menu_data;
        if( $force || !isset($_menu_data) )
        {
            $configs =& CCConfigs::GetTable();
            $_menu_data['items']  = $configs->GetConfig('menu');
            $_menu_data['groups'] = $configs->GetConfig('groups');

            if( empty($_menu_data['items']) )
            {
                //
                // ::::: Weirdass side effect warning :::::
                //
                // event handlers responding to this event will
                // fill the _menu_data var through calls to
                // CCMenu::AddMenuItem()
                //
                CCEvents::Invoke(CC_EVENT_MAIN_MENU, array( $action ));
                uasort($_menu_data['groups'],'cc_weight_sorter');
                $configs->SaveConfig( 'menu',   $_menu_data['items'],  '',  false);
                $configs->SaveConfig( 'groups', $_menu_data['groups'], '',  false);
            }

            CCEvents::Invoke(CC_EVENT_PATCH_MENU, array( &$_menu_data['items'] ));

        }

        return( $_menu_data );
    }

    /**
    * Internal goody
    * @access private
    */
    static function & _menu_items()
    {
        $data =& CCMenu::_menu_data();
        $items =& $data['items'];
        return($items);
    }

    /**
    * Internal goody
    * @access private
    */
    static function & _menu_groups()
    {
        $data =& CCMenu::_menu_data();
        $groups =& $data['groups'];
        return($groups);
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
            'menu'   => array( 'menu_text'  => _('Menus'),
                             'menu_group' => 'configure',
                             'access' => CC_ADMIN_ONLY,
                             'weight' => 60,
                             'help' => _('Edit the menus'),
                             'action' =>  ccl('admin','menu')
                             ),
            'groups' => array( 'menu_text'  => _('Menu Groups'),
                             'menu_group' => 'configure',
                             'access' => CC_ADMIN_ONLY,
                             'help'  => _('Edit the menu groups'),
                             'weight' => 61,
                             'action' =>  ccl('admin','menugroup')
                             ),
            );
    }

    /**
    * Event handler for {@link CC_EVENT_MAIN_MENU}
    * 
    * @see CCMenu::AddItems()
    */
    function OnBuildMenu()
    {
        $groups = array(
                    'extra1' => array( 'group_name' => 'Extra1 (rename me)',
                                      'weight'    => 4 ),
                    'extra2' => array( 'group_name' => 'Extra2 (rename me)',
                                      'weight'    => 4 ),
                    'extra3' => array( 'group_name' => 'Extra3 (rename me)',
                                      'weight'    => 4 ),
                    );

        CCMenu::AddGroups($groups);
    }

    /**
    * Add menu items to the main menu (experimental)
    *
    * Maps to URL ?ccm=/media/admin/menu/additems[/numitems]
    * 
    * @param integer $num Number of items to add
    */
    function AddMenuItems($num=1)
    {
        $num = empty($num) ? 1 : CCUtil::StripText($num);
        for( $i = 0; $i < $num; $i++ )
        {
            $rand = rand();
            $items['additem' . $rand] = 
                array( 'menu_text'  => _('Extra Item') . ' ' . $rand ,
                    'menu_group' => 'artist',
                    'weight' => 1,
                    'action' =>  ccp( _('replace_me') ),
                    'access' => CC_ADMIN_ONLY
                    );
        }

        CCMenu::AddItems($items,true);
        CCUtil::SendBrowserTo(ccl('admin','menu'));
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/menu',            array('CCMenuAdmin', 'Admin'),
            CC_ADMIN_ONLY, 'cchost_lib/cc-menu-admin.inc', '', 
            _('Display admin menu form'), CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/menu/killcache',  array('CCMenu', 'KillCache'),         
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Clear menu/url cache'), CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/menu/additems',   array('CCMenu', 'AddMenuItems'),  
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Add menu items'), CC_AG_CONFIG );

        CCEvents::MapUrl( 'admin/menugroup',       array('CCMenuAdmin', 'AdminGroup'),    
            CC_ADMIN_ONLY, 'cchost_lib/cc-menu-admin.inc', '', 
            _('Display admin menu groups form'), CC_AG_CONFIG  );
    }

    /**
    * Deletes the menu for the current configuration (DESTROYS USRS'S CHANGES!)
    *
    * This will trigger a re-build the next time somebody requests a menu.
    */
    public static function RevertToParent()
    {
        $page =& CCPage::GetPage();
        global $CC_CFG_ROOT;
        $configs =& CCConfigs::GetTable();
        $configs->DeleteType('menu',$CC_CFG_ROOT);
        CCMenu::Reset();
        $page->Prompt( sprintf(
                                 _('Menus have been reset for %s'),  
                                 "<b>$CC_CFG_ROOT</b>"
                              )
                      );
        $page->SetTitle(_('Reset Menus'));
    }

}


function cc_sort_user_menu($a, $b)
{
    if( $a['menu_group'] == $b['menu_group'] )
        return( cc_weight_sorter($a,$b) );
    return( cc_weight_sorter($a,$b) );
}

?>
