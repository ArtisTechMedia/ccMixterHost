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
* $Id: cc-url.php 12641 2009-05-23 17:14:26Z fourstones $
*
*/

/**
* @package cchost
* @subpackage util
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Makes links to virtual commands in the current config environment
*
*/
function ccl()
{
    global $CC_GLOBALS;
    global $CC_CFG_ROOT;

    $vroot = $CC_CFG_ROOT == CC_GLOBAL_SCOPE ? '' : $CC_CFG_ROOT . '/';

    $arg = $CC_GLOBALS['pretty-urls'] ? '' : '/?ccm=';
    $args = func_get_args();
    $cmdurl = "/$vroot" . implode('/',$args);
    return( cc_get_root_url() . $arg . $cmdurl );
}

/**
* Makes links to virtual commands, first argument is 
* assumed to be virtual config
*
*/
function ccc()
{
    global $CC_GLOBALS;

    $arg = $CC_GLOBALS['pretty-urls'] ? '' : '/?ccm=';
    $args = func_get_args();
    $cmdurl = implode('/',$args);
    return( cc_get_root_url() . $arg . $cmdurl );
}

function cc_current_url()
{
    $args = preg_replace('#^/?' . CC_GLOBAL_SCOPE . '#', '', $_REQUEST['ccm'] );
    return ccc($args);
}

/**
* Simple concat of args with URL path separator
*/
function ccp()
{
    $args = func_get_args();
    $dirs = array();
    foreach( $args as $arg )
        $dirs = array_merge($dirs,preg_split('#(/|\\\\)#',$arg));
    $dirs = array_filter($dirs);
    return( implode('/',$dirs) );
}

/**
* Concat args onto an URL (adds '?' if not already there)
*/
function url_args($url,$args)
{
    if( strstr( $url, '?' ) !== false )
        return( $url . '&' . $args );
    return( $url . '?' . $args );
}

/**
* Appends args directly to root url
*
*/
function ccr()
{
    global $CC_GLOBALS;

    $arg = $CC_GLOBALS['pretty-urls'] ? '' : '/?ccm=';
    $args = func_get_args();
    $cmdurl = '/' . implode('/',$args);
    return( cc_get_root_url() . $arg . $cmdurl );
}


/**
* Makes links to real files in the system
*
*/
function ccd()
{
    $args = func_get_args();
    $url = implode('/',$args);
    return( cc_get_root_url() . '/' . $url );
}

/**
* For real server paths 
*
*/
function cca()
{
    $args = func_get_args();
    $url = implode('/',$args);
    return( getcwd()  . '/' . $url );
}

/**
* Relative source code paths
*/
function ccs($file)
{
    return str_replace( str_replace('\\','/',getcwd()) . '/', '', str_replace('\\','/',$file) );
}

/**
* Internal helper for getting root pretty url
*
*/
function cc_get_root_url()
{
    static $_root_url;
    require_once('cchost_lib/cc-config.php');
    if( !isset($_root_url) )
    {
        $configs =& CCConfigs::GetTable();
        $ttags = $configs->GetConfig('ttag');
        $_root_url = CCUtil::CheckTrailingSlash( $ttags['root-url'], false );
    }
    return( $_root_url );
}

/**
* Internal helper for getting root pretty url
*
*/
function cc_calling_url()
{
    if( !empty($_REQUEST['ccm']) )
    {
        $ccm = $_REQUEST['ccm'];
        if( $ccm == '/ccm=')
        {
            $ccm = '';
        }
        else
        {
            if( $ccm{0} == '/' )
                $ccm = substr($_REQUEST['ccm'],1,strlen($_REQUEST['ccm'])-1);
        }
    }
    else
    {
        $ccm = '';
    }
    return ccr($ccm);
}

?>
