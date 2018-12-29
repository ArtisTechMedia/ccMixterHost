<?

/*
  $Id: mixter-tweet.php 13835 2009-12-25 13:19:34Z fourstones $
*/

$tweet_inc = dirname(__FILE__) . '/mixter-tweet.inc';

CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU, 'tweet_onuploadmenu', $tweet_inc );
CCEvents::AddHandler(CC_EVENT_MAP_URLS, 'tweet_onmapurls', $tweet_inc );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  'tweet_config_fields', $tweet_inc );
CCEvents::AddHandler(CC_EVENT_FILTER_TOPICS,  'tweet_onfiltertopics', $tweet_inc );

?>