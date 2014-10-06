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
* $Id: cc-if-modified.php 12624 2009-05-18 15:47:40Z fourstones $
*
*/

/**
* Sets if-modifed date on various user activity
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define('CC_IF_MOD_FLAG','mod-stamp');

function cc_set_if_modified()
{
    global $CC_GLOBALS;

    $time = gmmktime(); 
    if( !isset($CC_GLOBALS[CC_IF_MOD_FLAG]) || ($CC_GLOBALS[CC_IF_MOD_FLAG] != $time) )
    {
        $cfg =& CCConfigs::GetTable();

        $cnt = $cfg->CountRows('');
        if( !empty($cnt) ) { // bugfix: make sure we aren't doing a cfg import
            $CC_GLOBALS['in_if_modified'] = true;
            $cfg->SetValue('config', CC_IF_MOD_FLAG, $time, CC_GLOBAL_SCOPE);
            unset($CC_GLOBALS['in_if_modified']);
            $CC_GLOBALS[CC_IF_MOD_FLAG]  = $time;
            // _clog('^^^^  setting if-mod: ' . $time . ' ^^^^^^^^^');
        }
    }
}

//if( !function_exists('d') ) { function d(&$x) { CCDebug::Enable(true); CCDebug::PrintVar($x); } }

function cc_check_if_modified()
{
    $is_static = false; // preg_match( '/(strings_js.php)/', cc_current_url() );

    if( (CCUser::IsLoggedIn() || ($_SERVER['REQUEST_METHOD'] !== 'GET')) && !$is_static )
    {
        // We don't play around if the user is logged in because
        // they would likely see a bunch of pages as if they
        // were logged out. (drupal has the same rationale,
        // not sure if that's a good thing but we're going with it)
        //
        cc_send_no_cache_headers();
        return;
    }

    global $CC_GLOBALS;

    if( empty($CC_GLOBALS[CC_IF_MOD_FLAG]) )
    {
        //
        // This will happen exactly once per installation
        // (or whenever developer changes CC_IF_MOD_FLAG value)
        //
        cc_set_if_modified();
    }

    $last_modified = gmdate('D, d M Y H:i:s', $CC_GLOBALS[CC_IF_MOD_FLAG]) .' GMT';
    $etag = '"'.md5($last_modified).'"';

    $if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;
    $if_none_match     = isset($_SERVER['HTTP_IF_NONE_MATCH'])     ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])     : false;

    // _clog("IS($is_static)  INM($if_none_match) ET($etag) IFM($if_modified_since) LM($last_modified)");
    
    if( $is_static || ($if_modified_since && $if_none_match && ($if_none_match == $etag) && ($if_modified_since == $last_modified)) )
    {
        // _clog('======= Sending 304 =========');
        header('HTTP/1.1 304 Not Modified');
        // All 304 responses must send an etag if the 200 response for the same object contained an etag
        header("Etag: $etag");
        exit();
    }

    // _clog("**** Sending page  *****");
    header("Last-Modified: $last_modified"); 
    header("ETag: $etag");
    // force validation on this page
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: must-revalidate");
}

function cc_send_no_cache_headers()
{
    // _clog('##### Clearing headers ######');

    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    // always modified
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
     
    // HTTP/1.1
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);

    // HTTP/1.0
    header("Pragma: no-cache");
}


function _clog($msg)
{
    $debug = CCDebug::Enable(true);
    $uri = empty($_SERVER['REQUEST_URI']) 
                ? str_replace(ccl(),'',cc_current_url()) . $_SERVER['QUERY_STRING'] 
                : $_SERVER['REQUEST_URI'];
    $url = preg_replace('#ccm=/[^&]+#','',$uri);
    CCDebug::Log( $uri . ' ' . $msg);
    CCDebug::Enable($debug);
}

?>
