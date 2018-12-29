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
* $Id: cc-feeds-xspf.php 10356 2008-07-01 22:38:12Z fourstones $
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


CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP,   array( 'CCFeedsXSPFHV', 'OnApiQuerySetup') ); 
CCEvents::AddHandler(CC_EVENT_API_QUERY_FORMAT,   array( 'CCFeedsXSPFHV', 'OnApiQueryFormat') ); 

class CCFeedsXSPFHV
{
    function OnApiQuerySetup( &$args, &$queryObj, $validate)
    {
        if( $args['format'] != 'xspf' )
            return;
        $args['template'] = 'xspf_10.php';
        $queryObj->GetSourcesFromTemplate($args['template']);
        $queryObj->ValidateLimit('max-feed');
    }

    function OnApiQueryFormat( &$records, $args, &$result, &$result_mime )
    {
        if( $args['format'] != 'xspf' )
            return;

        require_once('cchost_lib/ccextras/cc-feeds-xspf.inc');
        cc_xspf_query_format($records,$args,$result,$result_mime);
    }
} 

?>
