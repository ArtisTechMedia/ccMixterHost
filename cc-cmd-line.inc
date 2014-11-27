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
* $Id: cc-cmd-line.inc 14211 2010-03-02 21:27:07Z fourstones $
*
*/
/*
    Command line include for cchost utilities.
    
    Four steps for using:
    
      define('CC_HOST_CMD_LINE', 1 );      // define this exact way
      chdir( YOUR_CCHOST_INSTALL_DIR );    // must be run from the cchost install root dir
      $admin_id = 9;                       // user to log in as (OPTIONAL)
      $NO_EXTRANEOUS_OUTPUT = 1;           // supress tagline of this file (OPTIONAL)
      require_once( 'cc-cmd-line.inc' );   // include this file
          
    
    Debug mode is enabled by default
 
*/
error_reporting(E_ALL);

if( !defined('CC_HOST_CMD_LINE') )
    die('Welcome to ccHost');

global $CC_GLOBALS;
global $CC_CFG_ROOT;

$CC_GLOBALS   = array();
$CC_CFG_ROOT  = '';
    
define('IN_CC_HOST',1);
require_once('cchost_lib/cc-debug.php');
require_once('cchost_lib/cc-includes.php');
require_once('cchost_lib/cc-custom.php');
require_once('cchost_lib/cc-template-api.php');
if( !function_exists('gettext') )
   require_once('cchost_lib/ccextras/cc-no-gettext.inc');
$cc_extras_dirs = 'cchost_lib/ccextras';
include('cchost_lib/cc-inc-extras.php');

if( empty($CC_GLOBALS) )
{
    CCConfigs::Init();
}

// CCDebug::Enable(true);
// CCDebug::PrintVar($CC_GLOBALS);

if( !empty($CC_GLOBALS['extra-lib']) )
{
    $cc_extras_dirs = $CC_GLOBALS['extra-lib'];
    include('cchost_lib/cc-inc-extras.php');
}

if( !empty($admin_id) )
{
    $admin_rec = CCDatabase::QueryRow('SELECT * FROM cc_tbl_user WHERE user_id = ' . $admin_id);
    $admin_rec['user_extra'] = unserialize($admin_rec['user_extra']);
    $CC_GLOBALS = array_merge($CC_GLOBALS,$admin_rec);
}

CCDebug::Enable(true);

if( empty($NO_EXTRANEOUS_OUTPUT) )
    print "[cchost command line]\n"

?>
