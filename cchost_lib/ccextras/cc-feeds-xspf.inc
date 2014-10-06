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
* $Id: cc-feeds-xspf.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* XSPF Module feed generator
*
* @package cchost
* @subpackage api
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-feed.php');

/**
* XSPF Feed generator and reader for site
*
* @package cchost
* @subpackage api
*/
function cc_xspf_query_format(&$records,$args,&$result,&$result_mime)
{
    $skin = new CCSkinMacro('xspf_10.php',false);

    $targs['channel_title']       = cc_feed_title($args,$skin);
    $targs['root-url']            = htmlentities(ccl());
    $targs['channel_description'] = cc_feed_description();
    $targs['xspf-pub-date']       = CCUtil::FormatDate(CC_RFC822_FORMAT,time());
    $targs['raw_feed_url']        = htmlentities(cc_current_url());

    $k = array_keys($records);
    $c = count($k);
    for( $i = 0; $i < $c; $i++ )
    {
        $R =& $records[$k[$i]];
        $R['upload_name']    = cc_feed_encode($R['upload_name']);
        $R['user_real_name'] = cc_feed_encode($R['user_real_name']);
    }

    $targs['records'] =& $records;

    require_once('cchost_lib/cc-template.php');

    header("Content-type: text/xml; charset=" . CC_ENCODING); 
    $skin->SetAllAndPrint($targs,false);
    exit;
}

?>
