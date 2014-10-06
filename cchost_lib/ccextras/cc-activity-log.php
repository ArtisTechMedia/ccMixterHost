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
* $Id: cc-activity-log.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Manages storing and display of various events for analysis by admins
* 
* @package cchost
* @subpackage admin
*/


if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_APP_INIT,    array( 'CCActivityLogAPIHV' , 'OnAppInit') );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,    array( 'CCActivityLogAPI' ,   'OnMapUrls'),   'cchost_lib/ccextras/cc-activity-log.inc');
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,  array( 'CCActivityLogAPI',    'OnAdminMenu'), 'cchost_lib/ccextras/cc-activity-log.inc');


/**
* Manages storing and display of various events for analysis by admins
* 
*/
class CCActivityLogAPIHV
{

    /**
    * Event handler for {@link CC_EVENT_APP_INIT}
    * 
    * Adds global hook if logging is enabled
    */
    function OnAppInit()
    {
        $configs =& CCConfigs::GetTable();
        $logging = $configs->GetConfig('logging',CC_GLOBAL_SCOPE);
        if( empty($logging) )
            return;

        global $CC_GLOBALS;

        $CC_GLOBALS['logging'] = $logging;
        CCEvents::AddHook( array( &$this, 'Hook' ) );
    }

    /**
    * Implements a event hook
    * 
    * @param mixed $args Event dependent 
    * @see CCEvents::AddHook()
    */
    function Hook($args)
    {
        global $CC_GLOBALS;

        $event_name = $args[0];
        $args = empty($args[1]) ? array() : $args[1];

        if( !in_array( $event_name, $CC_GLOBALS['logging']  ) )
            return;

        require_once('cchost_lib/ccextras/cc-activity-log.inc');
        $api = new CCActivityLogAPI();
        $api->InnerHook($args,$event_name);
    }

}

?>