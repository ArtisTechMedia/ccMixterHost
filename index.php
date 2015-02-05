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
* $Id: index.php 13849 2009-12-27 22:47:13Z fourstones $
*
*/

libxml_disable_entity_loader(true);

if( !empty($_GET['ccm']) && preg_match('/\.(gif|png|ico|jpg|mp3|jpeg|___)$/i',$_GET['ccm']) )
{
    header("HTTP/1.0 404 Not Found");
    exit;
}

$CC_GLOBALS   = array();
$CC_CFG_ROOT  = '';
$cc_error_level = E_ALL | E_STRICT; //  & ~E_STRICT;
$_sql_time = 0;

error_reporting($cc_error_level); 

/*
*  ccHost can't connect to the database without this 
*  the right version of this file. 
*  If not present, it probably means we haven't installed
*  or upgraded properly yet.
*/
if( !file_exists('cc-host-db.php') )
{
    if( file_exists('cc-config-db.php') )
    {
        /* NOT TRANSLATED BECAUSE LANG, NOT INITIALIZED YET */
        die('<html><body>ccHost has not been properly upgraded. 
            Please <a href="ccadmin/">
            follow these steps</a> for a successful
            upgrade.</body></html>');
    }

    die('<html><body>ccHost has not been properly installed. 
        Please <a href="ccadmin/">
        follow these steps</a> for a successful
        setup.</body></html>');
}


/*
*  All ccHost includes require this define to prevent direct 
*  web access to them.
*/
if( !defined('IN_CC_HOST') )
    define('IN_CC_HOST', true);

/*
*  The .cc-ban.txt file is written by doing 'Account Management' 
*  from the user's profile. We don't assume that ccHost is 
*  running under Apache, otherwise we would do this through 
*  Deny in .htaccess
*/
if( file_exists('.cc-ban.txt') )        
    require_once('.cc-ban.txt');        

/*
* We make a special include for debug so that modules can turn 
* it on as quickly as possible.
*/
require_once('cchost_lib/cc-debug.php');

global $ptimer;
$ptimer = 0;
CCDebug::Chronometer($ptimer);

/*
* Logging errors to a file this will help ccHost developers
* when things go wrong on your site
*/
CCDebug::LogErrors( $cc_error_level );

/*
*  We catch errors and handle them according log file settings
*/
CCDebug::InstallErrorHandler(true);     

require_once('mixter-lib/d.inc'); // turns on debugging and shuts off mail

/*
*  Internaitionalization requires (for now) that gettext be 
*  compiled into PHP
*/
if( !function_exists('gettext') )
   require_once('cchost_lib/ccextras/cc-no-gettext.inc');

/*
*  Include core modules and extras that come with the 
*  ccHost package
*/
require_once('cchost_lib/cc-includes.php');
$cc_extras_dirs = 'cchost_lib/ccextras';
include('cchost_lib/cc-inc-extras.php');
require_once('cchost_lib/cc-custom.php');
require_once('cchost_lib/cc-template-api.php');

/*
* Configuration initialized here
*/
CCConfigs::Init();

/*
*  We don't want to encourage ccHost installations to have 
*  their installation directories open to the public. We 
*  check it here after Configs::Init because the admin can 
*  disable the site while doing other work (like a SVN 
*  update)
*/
if( file_exists('ccadmin') )
{
    die('<html><body>' . _('ccHost installation is not complete.') . ' ' . 
        _('For security reasons, you should rename "ccadmin".') .  
        '</body></html>');
}

/*
*  Pick up 3rd party PHP modules
*/
if( !empty($CC_GLOBALS['extra-lib']) )
{
    $cc_extras_dirs = $CC_GLOBALS['extra-lib'];
    include('cchost_lib/cc-inc-extras.php');
}

/*
*  User is logged in after this call
*/
CCUser::InitCurrentUser();             

/*
* Don't generate the page if the browser already has
* the latest version
*/
cc_check_if_modified();


/*
*  Let all the modules know that config is set
*  and user has been logged in.
*/
CCEvents::Invoke(CC_EVENT_APP_INIT);

/*
*  Process incoming URL
*/
CCEvents::PerformAction();

/*
*  Show the resulting page
*/
require_once('cchost_lib/cc-page.php');
$page =& CCPage::GetPage();
$page->Show();           

/*
*  Shut down the session
*/
CCDebug::InstallErrorHandler(false); 
CCEvents::Invoke(CC_EVENT_APP_DONE);    

?>
