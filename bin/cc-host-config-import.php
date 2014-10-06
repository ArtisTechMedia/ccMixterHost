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
* $Id: cc-host-config-import.php 8952 2008-02-11 21:41:24Z fourstones $
*
* Copyright 2006, Creative Commons, www.creativecommons.org.
* Copyright 2006, Victor Stone.
* Copyright 2006, Jon Phillips, jon@rejon.org.
*/

if( empty($fname) )
    if( empty($argv[1]) ) 
        usage();
    else
        $fname = $argv[1];

error_reporting(E_ALL);

if( preg_match( '#[\\\\/]bin$#', getcwd() ) )
    chdir('..');

define('IN_CC_HOST',1);
$no_ui = true;
require_once('cchost_lib/ccextras/cc-export-settings.php');

require_once('cchost_lib/cc-table.php');
require_once('cchost_lib/cc-database.php');
require_once('cchost_lib/cc-config.php');
require_once('cchost_lib/cc-defines.php');
require_once('cchost_lib/cc-debug.php');
require_once('cchost_lib/cc-util.php');
if( !function_exists('gettext') )
    require_once('cchost_lib/ccextras/cc-no-gettext.inc');

$ex = new CCSettingsExporter();
$ex->Import($fname,true);

print('Config imported');

function usage()
{
    global $argv;

    $msg =<<<END
usage:

php-cli {$argv[0]} path_to_exported_config

A configuration from either the browser using
the /media/export or cc-host-config-export script

END;

    print($msg);
    exit;
}
?>