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
* $Id: cc-extras-events.php 10399 2008-07-05 04:23:46Z fourstones $
*
*/

/**
* Core defines for the system
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Notification Event: Forum message has been posted
*
* Call back (handler) prototype:
*<code>
* function OnForumPost(&$values)
* </code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_FORUM_POST', 'forumpost' );

/**
* Notification Event: Upload review has been posted
*
* Call back (handler) prototype:
*<code>
* function OnReview(&$row)
* </code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_REVIEW','review');

/**
* Notification Event: Topic has been deleted
*
* Call back (handler) prototype:
*<code>
* function OnTopicDeleted( CCTDF_* flag, $topic_id );
* </code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_TOPIC_DELETE', 'topicdelete');
define('CC_EVENT_POST_TOPIC_DELETE', 'posttopicdelete');

/**
* Notification Event: Topic has been replied to
*
* Call back (handler) prototype:
*<code>
* function OnTopicDeleted( $values_of_reply, $original_topic_record );
* </code>
* @see CCEvents::AddHandler()
*/
define('CC_EVENT_TOPIC_REPLY',  'topicreply');
define('CC_EVENT_TOPIC_ROW',    'topicrow');

define('CC_EVENT_FITLER_REVIEWERS_UNIQUE', 'filterunq' );
define('CC_EVENT_FILTER_REVIEWS', 'filtrev' );
define('CC_EVENT_FILTER_TOPICS',  'filttops');

?>
