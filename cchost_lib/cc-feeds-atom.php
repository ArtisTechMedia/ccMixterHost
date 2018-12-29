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
* $Id: cc-feeds-atom.php 10398 2008-07-04 23:12:25Z fourstones $
*
*/

/**
* RSS Module feed generator
*
* @package cchost
* @subpackage api
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-feed.php');

/**
* RSS Feed generator 
*/
class CCFeedsAtom
{
    function OnApiQuerySetup( &$args, &$queryObj, $validate)
    {
        $f = $args['format'];

        if( ($f == 'atom') || ($args['limit'] == 'feed') )
            $queryObj->ValidateLimit('max-feed');

        if( $f != 'atom' )
            return;

        $args['template'] = 'atom_10.php';
        $queryObj->GetSourcesFromTemplate($args['template']);
    }

    function OnApiQueryFormat( &$records, $args, &$result, &$result_mime )
    {
        if( $args['format'] != 'atom' )
            return;

        $skin = new CCSkinMacro('atom_10.php',false);

        $targs['channel_title'] = cc_feed_title($args,$skin);
        $qstring = $args['queryObj']->SerializeArgs($args);
        $targs['feed_url'] = /* what's the difference again?? */
        $targs['raw_feed_url'] = htmlentities(url_args(ccl('api','query'),$qstring));
        $targs['atom-pub-date'] = CCUtil::FormatDate(CC_RFC3339_FORMAT,time());

        $k = array_keys($records);
        $c = count($k);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$k[$i]];
            $R['upload_description_plain'] = cc_feed_encode($R['upload_description_plain']);
            $R['upload_name']              = cc_feed_encode($R['upload_name']);
            $R['user_real_name']           = cc_feed_encode($R['user_real_name']);
        }

        $targs['records'] =& $records;

        require_once('cchost_lib/cc-template.php');

        header("Content-type: text/xml; charset=" . CC_ENCODING); 
        $skin->SetAllAndPrint($targs,false);
        exit;
    }

    function OnAddPageFeed(&$page,$feed_info)
    {
        cc_feed_add_page_links($page,$feed_info,'feed-atom16x16.png','Atom 1.0','atom','feed_atom');
    }

}


?>
