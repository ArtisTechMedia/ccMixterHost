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
* $Id:$
*
*/

function show_main_home_page()
{
    require_once('index.php');
    exit;
}

if( !empty($_GET['ccm'])  )
{
    show_main_home_page();
}

error_reporting(E_ALL & ~E_STRICT);
 
$CC_GLOBALS   = array();

if( !defined('IN_CC_HOST') )
{
    define('IN_CC_HOST', true);
}
require_once( 'cchost_lib/cc-non-ui.php');
require_once( 'cchost_lib/cc-user.php' );

CCUser::InitCurrentUser();             

if( CCUser::IsLoggedIn() )
{
    show_main_home_page();
}

require_once( 'mixter-files/splash/ccmixter_splash.html' );

?>
