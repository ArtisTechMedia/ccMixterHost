<?

CCEvents::AddHandler(CC_EVENT_MAP_URLS, array( 'CCEventsFeed', 'OnMapUrls'));

class CCEventsFeed
{
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','user', 'feed'),
            array( 'CCAPIFeed', 'APIFeed'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '', _('Return the current event feed'), CC_AG_USER );

        // TEMP
        CCEvents::MapUrl( ccp('api','user', 'pp'),
            array( 'CCLibFeed', 'PrePopulate'),   CC_MUST_BE_LOGGED_IN,   
            'mixter-lib/lib/feed.php',
            '', _('testing'), CC_AG_USER );
    }
}

/*
  CREATE TABLE cc_tbl_feed (
    feed_id         int(11) NOT NULL auto_increment,
    feed_user       int(11) NOT NULL default '0',
    feed_type       varchar(3) NOT NULL default '',
    feed_date       datetime default NULL,
    feed_key        int(11) NOT NULL default '0',
    feed_seen       int(1) NOT NULL default '0',
    PRIMARY KEY  (feed_id)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

*/

class CCAPIFeed
{
  function APIFeed($username='') {



  }
}
?>