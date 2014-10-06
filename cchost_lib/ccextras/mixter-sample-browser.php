<?


if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,     array( 'CCSampleBrowser' , 'OnMapUrls'), dirname(__FILE__) . '/mixter-sample-browser.inc' );

?>