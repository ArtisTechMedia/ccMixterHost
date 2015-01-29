<?php

/*
  $Id: mixter-dump-all.php 13835 2009-12-25 13:19:34Z fourstones $
*/

CCEvents::AddHandler( CC_EVENT_MAP_URLS, 'cc_mixter_dump_map_urls');

function cc_mixter_dump_map_urls()
{
    CCEvents::MapUrl( ccp( 'api', 'dump','all') ,  'cc_mixter_dump_all', CC_ADMIN_ONLY );
    CCEvents::MapUrl( ccp( 'api', 'dump' ) , 'cc_mixter_api_dump', CC_DONT_CARE_LOGGED_IN );
}


function cc_mixter_dump_all()
{
    require_once('mixter-lib/mixter-dump-all2.inc');
    
}

?>