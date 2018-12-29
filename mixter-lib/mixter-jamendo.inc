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
* $Id$
*
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-pool-generic.php');

class CCJamendoPool extends CCPoolGeneric
{
    function LocalSearch($pool_id,$text,$type)
    {
        global $CC_GLOBALS;

        $text = CCUtil::StripText( urldecode($text) );
        if( empty($text) )
            return( array() );

        $url = "http://api.jamendo.com/get2/id+stream/track/xspf/?searchquery=" . urlencode($text);

        require_once('cchost_lib/snoopy/Snoopy.class.php');
        $snoopy = new Snoopy();        
        $ok = @$snoopy->fetch($url);
        return $ok ? str_replace('><',">\n<",$snoopy->results) : array();
    }

}

?>