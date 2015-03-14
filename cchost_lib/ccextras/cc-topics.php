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
* $Id: cc-topics.php 12881 2009-07-08 03:34:45Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define( 'CC_TOPIC_FORMAT_BB', 0 );
define( 'CC_TOPIC_FORMAT_HTML', 1 );
define( 'CC_TOPIC_FORMAT_PLAIN', 2 );

CCEvents::AddHandler(CC_EVENT_MAP_URLS, array( 'CCTopic',  'OnMapUrls'), 'cchost_lib/ccextras/cc-topics.inc');

CCEvents::AddHandler(CC_EVENT_USER_DELETED,  array( 'CCTopic' , 'OnUserDelete'), 'cchost_lib/ccextras/cc-topics.inc');


CCEvents::AddHandler( CC_EVENT_API_QUERY_SETUP, 'xlat_ApiQuerySetup' );

function xlat_ApiQuerySetup( &$args, &$queryObj, $requiresValidation )
{
    //d($args);
    if( !empty($args['xlat']) )
        $queryObj->where[] = 'topic_can_xlat = 1';

}

?>
