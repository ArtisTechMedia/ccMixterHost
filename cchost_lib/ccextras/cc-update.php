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
* $Id: cc-update.php 12559 2009-05-06 19:54:43Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


CCEvents::AddHandler(CC_EVENT_APP_INIT, array( 'CCUpdate', 'UpdateSite') );

/**
*/
class CCUpdate
{
    function UpdateSite()
    {
        global $CC_GLOBALS;

        if( !CCUser::IsAdmin() || empty($_REQUEST['update']) )
            return;

        require_once('cchost_lib/cc-page.php');
        $updates = array();
        $dirs = split(';',$CC_GLOBALS['extra-lib']);
        array_unshift($dirs,'cchost_lib/ccextras');
        foreach( $dirs as $dir )
            $this->_fetch_updates(trim($dir),$updates);

        $prompts = array();
        foreach( $updates as $update )
        {
            $prompts[] = $this->_do_update($update);
        }

        $prompts = join(', ',$prompts);
        CCPage::Prompt(_('Updates already installed:') . ' ' . $prompts);

        CCMenu::KillCache();
        CCTemplate::ClearCache();
    }

    function _fetch_updates($dir,&$updates)
    {
        $g = glob($dir . '/update_*.inc');
        if( !empty($g) )
            $updates = array_merge($updates,$g);
    }
    
    function _do_update($fname)
    {
        global $CC_GLOBALS;
        
        preg_match('/update_([^\.]+)\.inc$/',$fname,$m);
        $name = $m[1];
        if( empty($CC_GLOBALS[$name])  )
        {
            require_once($fname);
            $updater = new $name;
            $updater->Update();
            $this->_write_config_flag($name);
        }
        return $name;
    }

    function _write_config_flag($flag)
    {
        global $CC_GLOBALS;
        $CC_GLOBALS[$flag] = 1;
        $configs =& CCConfigs::GetTable();
        $args[$flag] = 1;
        $configs->SaveConfig('config', $args, CC_GLOBAL_SCOPE, true );
    }

    function _table_exists($tablename)
    {
        $tables = CCDatabase::ShowTables();
        return in_array( $tablename, $tables );
    }

    /**
    * Check for existance of a database column and create one if it doesn't exist.
    *
    * For a tutorial on using this method see {@tutorial cchost.pkg#new Create a new database column}
    *
    */
    function _check_for_field($tablename,$fieldname, $desc)
    {
        if( is_object($tablename) )
            $tablename = $tablename->_table_name;

        $fields = CCDatabase::QueryRows('DESCRIBE ' . $tablename);
        $found = false;
        foreach( $fields as $field )
        {
            if( $field['Field'] == $fieldname )
            {
                $found = true;
                break;
            }
        }

        if( !$found )
        {
            $sql = "ALTER TABLE `$tablename` ADD `$fieldname` $desc";
            CCDatabase::Query($sql);
        }

        return($found);
    }

}
?>
