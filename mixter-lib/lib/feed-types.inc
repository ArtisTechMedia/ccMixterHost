<?
/**
* Module for managing personal feeds
*
* @package cchost
* @subpackage feature
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define('FEED_VERB_NEW_UPLOAD',      1);
define('FEED_VERB_UPDATE_UPLOAD',   2);
define('FEED_VERB_REVIEW',          3);
define('FEED_VERB_RECOMMEND',       4);
define('FEED_VERB_TOPIC_REPLY',     5);
define('FEED_VERB_FORUM_POST',      6);
define('FEED_VERB_EDPICK',          7);

define('FEED_TYPE_UPLOAD',     1);
define('FEED_TYPE_REVIEW',     2);
define('FEED_TYPE_FORUM_POST', 3);

define('FEED_REASON_REMIXED',     1);
define('FEED_REASON_REVIEWED',    2);
define('FEED_REASON_REPLIED',     3);
define('FEED_REASON_EDPICKED',    4);
define('FEED_REASON_FOLLOW',      5);
define('FEED_REASON_RECOMMENDED', 6);


define('ADMIN_ID',    2);
define('ADMIN_FORUM', 1);


?>