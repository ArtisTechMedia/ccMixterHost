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
* $Id: cc-config.php 12675 2009-05-28 22:03:52Z fourstones $
*
*/

/**
* Base configuration and initialization 
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*  Wrapper for config table
*
*/
class CCConfigs extends CCTable
{
    /**
    * Constructor (should not be used, use GetTable() instead)
    *
    * @see GetTable
    */
    function CCConfigs()
    {
        $this->CCTable('cc_tbl_config','config_id');
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        static $table;
        if( !isset($table) )
            $table = new CCConfigs();
        return($table);
    }

    /*
    * @access private
    */
    function & _cache()
    {
        static $_cache = array();
        return $_cache;
    }

    function Preload()
    {
        global $CC_CFG_ROOT;
        if( empty($scope) )
            $scope = $CC_CFG_ROOT;
        $cache =& $this->_cache();
        $all_configs = $this->QueryRows( "config_scope = '{$scope}' AND config_type <> 'config'" );

        $c = count($all_configs);
        $k = array_keys($all_configs);
        for( $i = 0; $i < $c; $i++ )
        {
            $r =& $all_configs[$k[$i]];
            $type = $r['config_type'];
            $data = unserialize($r['config_data']);
            $cache[$scope][$type] = $data;
        }
    }
        
    /**
    * Get the configuration settings of a type for a given scope
    *
    * Configuration settings are grouped by 'type' within a given 'scope'
    *
    * Type can be 'config' (which only applies to the global scope) or
    * things like 'menu', 'licenses', 'formats-allowed', or whatever
    * a given module wants to store here.
    *
    * Scope is either CC_GLOBAL_SCOPE or a custom scope determined
    * by the user (typically the site's admin). The scope is determined
    * by the first part of the url after the base.
    *
    * http://example.com/myscope/somecommand/param
    *
    * In this case 'myscope' is the scope used to retrive the given
    * settings values.
    *
    * The global scope is called 'main' in the URL.
    *
    * If a given type is requested for a non-global scope then the
    * values for that type in CC_GLOBAL_SCOPE (main) is used.
    *
    * @param string $type Type of data being requested
    * @param string $scope Scope being requested. If null the current scope is used.
    * @returns array Array containing variables matching parameter's request
    */
    function GetConfig($type,$scope = '')
    {
        global $CC_CFG_ROOT;
        if( empty($scope) )
            $scope = $CC_CFG_ROOT;

        $cache =& $this->_cache();

        if( empty($cache[$scope][$type]) )
        {
            $where['config_type'] = $type;
            $where['config_scope'] = $scope;
            $arr = $this->QueryItem('config_data',$where);
            if( $arr )
            {
                $arr = unserialize($arr);
            }
            elseif( $scope != CC_GLOBAL_SCOPE )
            {
                $arr = $this->GetConfig($type,CC_GLOBAL_SCOPE);
            }
            else
            {
                $arr = array();
            }
            
            $cache[$scope][$type] = $arr;
        }

        return $cache[$scope][$type];
    }

    /**
    * Save an array of settings of a given type and assign it to a scope
    *
    * @see GetConfig
    * 
    * @param string $type Type of data being saved (e.g. 'config', 'menu', etc.)
    * @param array  $arr  Name/value pairs in array to be saved
    * @param string $scope Scope to assigned to. If null the current scope is used. If $type is 'config' it is ALWAYS saved to CC_GLOBAL_SCOPE
    * @param boolean $merge true means merge this array with existing values, false means delete all previous settings.
    */
    function SaveConfig($type,$arr,$scope='',$merge = true)
    {
        global $CC_CFG_ROOT;

        if( $type == 'config' )
            $scope = CC_GLOBAL_SCOPE;
        elseif( empty($scope) )
            $scope = $CC_CFG_ROOT;

        $where['config_type'] = $type;
        $where['config_scope'] = $scope;
        $key = $this->QueryKey($where);
        $original = $arr;
        $old = '';
        $where['config_data'] = serialize($arr);
        if( $key )
        {
            $where['config_id'] = $key;
            if( $merge )
            {
                $old = $this->QueryItemFromKey('config_data', $key);
                $old = unserialize($old);
                $where['config_data'] = serialize(array_merge($old,$arr));
            }

            $this->Update($where);
        }
        else
        {
            $this->Insert($where);
        }

        $cache =& $this->_cache();
        if( $merge && !empty($cache[$where['config_scope']][$where['config_type']]))
        {
            $loc =& $cache[$where['config_scope']][$where['config_type']];
            $loc = array_merge($loc,$original);
        }
        else
        {
            $cache[$where['config_scope']][$where['config_type']] = $original;
        }

        if( class_exists( 'CCEvents' ) )
            CCEvents::Invoke( CC_EVENT_CONFIG_CHAGNED, array( &$where, &$old, &$arr ) );
        
    }

    /**
    * Internal helper for initializes globals
    *
    */
    public static function Init()
    {
        global $CC_GLOBALS, $CC_CFG_ROOT;

        $configs =& CCConfigs::GetTable();

        $CC_GLOBALS = $configs->GetConfig('config', CC_GLOBAL_SCOPE);

        CCConfigs::_check_version($CC_GLOBALS['cc-host-version']);

        // First argument in ccm is the current virtual root (aka config root)
        //
        // note: ?ccm= will be in _REQUEST even if pretty urls are turned on,
        // the pretty urls are translated before executing php
        //
        $regex = '%/([^/\?]+)%';
        preg_match_all($regex,CCUtil::StripText($_REQUEST['ccm']),$a);
        $A =& $a[1];

        $configs->SetCfgRoot( empty($A[0]) ? CC_GLOBAL_SCOPE : $A[0] );

        $configs->Preload();

        $CC_GLOBALS['home-url'] = ccl();

        $skin_settings = $configs->GetConfig('skin-settings');

        if( empty($skin_settings) )
        {
            // old install? hack through this for now...
            require_once('cchost_lib/cc-skin-admin.php');
            CCSkinAdmin::_load_profile('ccskins/shared/profiles/profile_cc5.php');
        }
        $settings = $configs->GetConfig('settings');
        $CC_GLOBALS = array_merge($CC_GLOBALS,$settings,$skin_settings);

        // allow admins to turn off user interface
        //
        if( $CC_GLOBALS['site-disabled'] )
            cc_check_site_enabled();
    }

    /**
    * Sets the global $CC_CFG_ROOT and browser cookie to a valid root.
    * 
    * If $rootname is blank sets the config root to CC_GLOBAL_SCOPE
    * (which is also the default root). Validates that the parameter
    * is a valid root created by an admin.
    * 
    * The vast majority of the time the name comes from the calling
    * URL (e.g. Given the URL http://cchost.org/thevroot/viewfile/home.xml
    * this method will be called with 'thevroot'). However there are
    * times where it must be set programmatically, especially when
    * interfacing with third party software.
    * 
    * This function has to be set very early in the processing of
    * of the site since much of the site depends on configuration
    * information locked into the 'current' config root.
    * 
    * The cookie is set so that 3rd party software (like blogs ands
    * forums) running on this server can get a hint where ccHost is
    * at.
    * 
    * @param string $rootname Name of config root to set to
    */
    function SetCfgRoot($rootname)
    {
        global $CC_CFG_ROOT;
        if( empty($rootname) )
        {
            $rootname = CC_GLOBAL_SCOPE;
        }
        else
        {
            $where['config_scope'] = $rootname;
            $rows = $this->QueryRows($where, 'config_scope');
            if( empty($rows) || ($rows[0]['config_scope'] != $rootname) ) // check for case
            {
                $_REQUEST['ccm'] = '/' . CC_GLOBAL_SCOPE . $_REQUEST['ccm'];
                $rootname = CC_GLOBAL_SCOPE;
            }
        }
        $CC_CFG_ROOT = $rootname;
    }

    /**
    * Sets a specific values into a particular configuration.
    * 
    * (I think this method may be a bug waiting to happen)
    * 
    * If scope is not specified this value will be pushed
    * into ALL the configurations for the given 'type'.
    * 
    * @param string $type Category of setting (i.e. menu)
    * @param string $name Name of the setting
    * @param mixed  $value Value to be set
    * @param string $scope Specific scope to modify, or if null, ALL scopes
    */
    function SetValue($type,$name,$value,$scope='')
    {
        // If you don't do this through SaveConfig, you
        // bypass this session's config cache and translations

        $arr[$name] = $value;
        $this->SaveConfig($type,$arr,$scope,true); // true means merge
    }

    /**
    * Check if a particular scope has a category of config settings.
    *
    * This method helps determine if a given setting will override
    * the global settings.
    * 
    * @param string $type Category of config setting (i.e. menu)
    * @param string $scope Scope to be checked
    * @returns bool $has_type true/false
    */
    function ScopeHasType($type,$scope)
    {
        $where['config_scope'] = $scope;
        $where['config_type']  = $type;
        $count = $this->CountRows($where);
        return !empty($count);
    }

    /** 
    * Nuke configuration settings of a given type for a specific scope
    *
    * @param string $type Category of config setting (i.e. menu)
    * @param string $scope Scope to be checked
    */
    function DeleteType($type,$scope)
    {
        $where['config_scope'] = $scope;
        $where['config_type']  = $type;
        $this->DeleteWhere($where);
    }

    /*
    * Return an array of known virtual roots
    *
    * @return array $root_records 
    */
    function GetConfigRoots()
    {
        $sql = $this->_get_select('','DISTINCT config_scope');
        $roots = CCDatabase::QueryRows($sql);
        $count = count($roots);
        for( $i = 0; $i < $count; $i++ )
        {
            $scope = $roots[$i]['config_scope'];
            $where['config_scope'] = $scope;
            $where['config_type']  = 'ttag';
            $tags = $this->QueryItem('config_data',$where);
            if( !empty($tags) )
            {
                $tags = unserialize($tags);
                $title = 'cchost site'; // todo: where's that?? $tags['site-title'];
            }
            else
            {
                $title = $scope;
            }
            $roots[$i]['scope_name'] = $title;
        }
        return $roots;
    }

    /*
    * Make sure the global cc-host-version is up to date
    * 
    * @param string $config_in_db Version string to check against code
    */
    static function _check_version($config_in_db)
    {
        global $CC_GLOBALS;

        $cmp = version_compare($config_in_db, CC_HOST_VERSION);

        if( $cmp == 0 )
            return;

        $cmp = version_compare($config_in_db, '5.0');

        if( $cmp < 0 )
        {
            if( empty($CC_GLOBALS['cc_mixter_installed']) )
            {
                die('This installation of ccHost must be upgraded to version 5');
            }
            else
            {
                // sorry but there's a whacko bug in ccMixter where config
                // version is being overwritten with 2.0.RC !!!! trying to 
                // catch it has been a huge pain
                $old_debug = CCDebug::Enable(true);
                CCDebug::Log("CONFIG VERSION IS WRONG: ($config_in_db} {$_SERVER['REQUEST_URI']}");
                CCDebug::Enable($old_debug);
            }
        }

        // we don't have a $this ptr
        $configs =& CCConfigs::GetTable();
        $configs->SetValue('config', 'cc-host-version', CC_HOST_VERSION, CC_GLOBAL_SCOPE);
        $CC_GLOBALS['cc-host-version']  = CC_HOST_VERSION;
    }
}



/**
* @access private
*/
function cc_check_site_enabled()
{
    if( defined('CC_HOST_CMD_LINE') ) {
        return;
    }

    global $CC_GLOBALS;

    $enable_password = $CC_GLOBALS['enable-password'];

    if( !empty($_COOKIE[CC_ENABLE_KEY]) )
    {
        if( $_COOKIE[CC_ENABLE_KEY] == $enable_password  )
        {
            return;
        }
    }

    if( !empty($_POST[CC_ENABLE_KEY]) )
    {
        if( $_POST[CC_ENABLE_KEY] == $enable_password  )
        {
            setcookie( CC_ENABLE_KEY, $enable_password , time()+60*60*24*14, '/' );
            return;
        }
    }

    if( !empty($CC_GLOBALS['disabled-msg']) && file_exists($CC_GLOBALS['disabled-msg']) )
    {
        $msgtext = file_get_contents($CC_GLOBALS['disabled-msg']);
    }
    else
    {
        // Do NOT internalize this string, config is not fully
        // intialized, see the ccadmin installer

        $msgtext = 'Site is under construction.';
    }

    $css_link = '';

    $name = CC_ENABLE_KEY;
    $self = $_SERVER['PHP_SELF'];
    $html = "";
    $html .=<<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>cchost site</title>
    $css_link
</head>
<body>
<div class="cc_all_content" >
    <div class="cc_content">
        <div class="cc_form_about">
    $msgtext        
        </div>
<form action="$self" method="post" class="cc_form" >
<table class="cc_form_table">
    <tr class="cc_form_row">
        <td class="cc_form_label">Admin password:</td>
        <td class="cc_form_element">
            <input type='password' id="$name" name="$name" /></td>
    </tr>
    <tr class="cc_form_row">
        <td class="cc_form_label"></td>
        <td class="cc_form_element">
            <input type='submit' value="submit" /></td>
    </tr>
</table>
</form></div></div>
</body>
</html>
END;
    print($html);
    exit;
}

?>
