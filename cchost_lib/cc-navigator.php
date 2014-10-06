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
* $Id: cc-navigator.php 12608 2009-05-13 15:44:59Z fourstones $
*
*/

/**
* Module for displaying navigator tabs
*
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Tab navigator API
*
*/
class CCNavigator
{

    /**
    * Display a tabbed navigator and the selected page
    *
    * @param string $page_name Name of tab page to display, or if null, uses the default page for the config root
    * @param string $default_tab_name Name of the selected tab, or if null, uses the first tab on the page
    * @param string $sub_tab_name Name of the selected sub tab, or if null, uses the first sub tab on the page
    */
    function View($page_name='',$default_tab_name='',$sub_tab_name='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->ShowTabs($page,true,$page_name,$default_tab_name,$sub_tab_name);
    }

    /**
    * Display sub tab (A tab set embedded in the page)
    *
    * @param string $tabsetname Name of tab set
    * @param string $selected_tab Name of the tab to highlight and execute
    * @param string $base_url Base url to use prepend to tab names 
    * @param boolean $execute (not used)
    */
    function ShowSubTabs($tabsetname,$selected_tab,$base_url,$execute)
    {
        if( $this->_get_selected_page($tabsetname,$selected_tab,$sub_page) )
        {
            $default_tab = array();
            $sub_tab_info = array();

            $this->_setup_page(   $selected_tab,
                                  $sub_page, 
                                  $base_url,
                                  $execute,
                                  $default_tab,
                                  $sub_tab_info );

            $page_out =& CCPage::GetPage();
            $page_out->PageArg('sub_nav_tabs',$sub_tab_info);
            
            return true;
        }

        return false;
    }

    /**
    * Display main navigation tab set
    *
    * @param object &$page_out CCPage object to output tabs
    * @param boolean $execute true means execute underlying URL
    * @param string $page_name Name of tabset to display
    * @param string $default_tab_name Name of selected tab
    * @param string $sub_tab_name Name of subtabs to embed in page
    */
    function ShowTabs(&$page_out,$execute=false,$page_name='',$default_tab_name='',$sub_tab_name='')
    {
        global $CC_CFG_ROOT;

        // Step 1. Figure out what page we're loading
        if( !$this->_get_selected_page($page_name,$default_tab_name,$page) )
            return;
        // Step 2. Get the Page ready for display
        //
        $base_url = ccl( 'view', $page_name );
        $default_tab = array();
        $tab_info = array();

        $this->_setup_page(   $default_tab_name,
                              $page, 
                              $base_url,
                              $execute,
                              $default_tab,
                              $tab_info );

        // Step 3. This displays the tab on the page
        $page_out->AddTabNaviator( $tab_info, 'page_tabs' );

        if( empty($default_tab) )
            return;

        $caption = $qname = $page_out->String($default_tab['text']);

        if( $default_tab['function'] == 'sub' )
        {
            if( $this->_get_selected_page($default_tab['tags'],$sub_tab_name,$sub_page) )
            {
                $base_url = ccl( 'view', $page_name, $default_tab_name );
                $default_tab = array();
                $sub_tab_info = array();

                $this->_setup_page(   $sub_tab_name,
                                      $sub_page, 
                                      $base_url,
                                      $execute,
                                      $default_tab,
                                      $sub_tab_info );

                $page_out->PageArg('sub_nav_tabs',$sub_tab_info);

                $caption .= ' :: ' . $page_out->String($default_tab['text']);
            }
        }

        if( !$page_out->GetPageArg('page-caption') )
            $page_out->PageArg('page-caption',$caption);

        if( $execute )
        {
            // Step 4. Execute the currently selected tab

            if( $default_tab['function'] == 'url' )
            {
                // 4a. The tab is an internal URL to execute, translate it into
                //     action (method + args psuedo-closure) and perform it.

                $url = $default_tab['tags'];
                if( strtolower(substr($url,0,7)) == 'http://' )
                {
                    CCUtil::SendBrowserTo($url);
                }

                $pieces = explode('?',$url);
                if( !empty($pieces[1]) )
                {
                    parse_str($pieces[1],$qargs);
                    $_GET = array_merge($_GET,$qargs);
                }

                $action = CCEvents::ResolveUrl( $pieces[0], true );
                
                if( empty($action) )
                    CCPage::Prompt("The naviation tab could not resolve the url: $url");
                else
                   CCEvents::PerformAction($action);
                
            }
            else
            {
                // 4b. Everything else is a type of query
                //

                $qstring = $default_tab['tags'];
                parse_str($default_tab['tags'],$args);
                $args['feed'] = $qname;
                $args['qstring'] = $qstring;
                $args['title'] = $caption;
                if( empty($args['limit']) )
                    $args['limit'] = empty($_GET['limit']) ? 'page' : $_GET['limit'];

                require_once('cchost_lib/cc-query.php');

                $query = new CCQuery();
                $args = $query->ProcessAdminArgs($args);
                $query->Query($args);
            }
        }
    }

    /**
    * @access private
    */
    function _setup_page(&$default_tab_name, &$page, $base_url, $execute, &$default_tab, &$tab_info )
    {
        // Step 1. Call any custom handler to allow for dynamic removal
        //         of tabs and other hacks
        if( !empty($page['handler']) )
        {
            if( !empty($page['handler']['module']) )
            {
                $handler = str_replace('cclib_host/','cchost_lib/',$page['handler']['module']); // compat hack
                $handler = str_replace('cclib/','cchost_lib/',$handler); // compat hack hack
                require_once($handler);
            }
            $handler = $page['handler']['method'];
            if( is_array($handler) && is_string($handler[0]))
            {
                $class = $handler[0];
                $method = $handler[1];
                $obj = new $class;
                $handler = array( $obj, $method );
            }
            call_user_func_array($handler,array(&$page));
        }

        // Step 2. Remove tabs that we're not supposed to see
        $tab_keys = array_keys($page);
        $count = count($page);
        $mask = CCMenu::GetAccessMask();

        for( $i = 0; $i < $count; $i++ )
        {
            if( ($page[$tab_keys[$i]]['access'] & $mask) == 0 )
                unset($page[$tab_keys[$i]]);
        }
        
        // Step 3. The keys and count might have changed
        $tab_keys = array_keys($page);
        $count = count($page);

        // Step 4. The 'default' tab might have removed above
        if( empty($page[$default_tab_name]) )
        {
            if( $count == 0 )
            {
                //$this->_signal_error( __LINE__ );
                return;
            }
            $default_tab_name = $tab_keys[0];
        }

        // Step 5. Highlight the selected tab
        for( $i = 0; $i < $count; $i++ )
        {
            $name = $tab_keys[$i];
            if( empty($page[$name]) )
                continue;
            $tab =& $page[$name];
            $tab['url'] = $base_url . '/' . $name;
            if( $execute && $default_tab_name == $name )
            {
                $tab['selected'] = true;
            }
            else
            {
                $tab['normal'] = true;
            }
        }

        // Step 6. Create a tab info structure the way the HTML 
        //         template wants it

        $tab_info['num_tabs']      = $count;
        $tab_info['tab_width']     = intval(100/$count) . '%';

        $default_tab = $page[$default_tab_name];

        $tab_info['selected_text'] = $default_tab['help'];
        $tab_info['tags']          = $default_tab['tags'];
        $tab_info['function']      = $default_tab['function'];
        $tab_info['tabs']          = $page; // array_reverse($page);
    }

    /**
    * Internal: Returns pages current assigned to the current config root
    *
    * @access private
    */
    function  _get_pages()
    {
        $configs =& CCConfigs::GetTable();
        $pages = $configs->GetConfig('tab_pages');
        if( empty($pages) )
        {
            require_once('cchost_lib/cc-navigator-admin.inc');
            $nav_admin_api = new CCNavigatorAdmin();
            $pages['media'] = $nav_admin_api->_get_seed_page();
            $configs->SaveConfig('tab_pages',$pages);
        }
        return( $pages );
    }

    /**
    * Internal: returns the current selected page and tab
    * @access private
    */
    function _get_selected_page(&$page_name,&$default_tab_name,&$page)
    {
        $pages = $this->_get_pages();
        
        $ok = !empty($pages);
        if( $ok )
        {
            if( empty($page_name) )
            {
                $names = array_keys($pages);
                $page_name = $names[0];
            }
            else
            {
                $ok = array_key_exists($page_name,$pages);
            }
        }
        if( $ok )
        {
            $page = $pages[$page_name];
            $ok = !empty($page);
        }
        if( $ok )
        {
            $tab_keys = array_keys($page);
            if( empty($default_tab_name) )
            {
                $default_tab_name = $tab_keys[0];
            }
            else
            {
                $ok = array_key_exists($default_tab_name,$page);
            }
        }
        if( !$ok )
        {
            $this->_signal_error( __LINE__ );
        }

        return( $ok );
    }

    /**
    * Internal: fake out error
    * @access private
    */
    function _signal_error($lineno)
    {
        CCPage::SetTitle(_("System Error") . " (" . CC_HOST_VERSION . ':' . $lineno . ')' );
        CCPage::SystemError(_('Invalid Path'));
        CCUtil::Send404();
    }

}

?>
