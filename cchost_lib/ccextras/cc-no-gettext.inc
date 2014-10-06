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
* $Id: cc-no-gettext.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Module for PHP installations without get_text installed
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

//CCDebug::Log("Function gettext doesn't exist, using '" . basename(__FILE__) . '"');

/*
* Someday this should work
*
* require_once('cchost_lib/php-gettext/gettext.inc');
*/


// alias to gettext and what we prefer
function _($t)
{
    return $t;
}

// call for gettext plurals
function ngettext($t1,$t2,$num)
{
    return $num == 1 ? $t1 : $t2;
}

function bindtextdomain()
{
}

function textdomain()
{
}


?>
