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
* $Id: cc-run-once.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_RENDER_PAGE, 'cc_run_once' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCRunOnce' , 'OnGetConfigFields'), 'cchost_lib/ccextras/cc-run-once.inc' );

/**
* Run a page once per user
*/

function cc_run_once()
{
    global $CC_GLOBALS;

    if( !empty($_POST) )
    {
        // in case user is posting a form we let this one go
        return;
    }

    if( !empty($_REQUEST['run_once']) )
    {
        // we are already in a redirect for run_once, don't recurse
        return;
    }

    if( !CCUser::IsLoggedIn() )
    {
        return;
    }

    if( empty($CC_GLOBALS['run_once']) )
    {
        return;
    }

    require_once('cchost_lib/ccextras/cc-run-once.inc');
    $run_once = new CCRunOnce();
    $run_once->RunOnce();
}

?>
