<?

/*
  $Id: mixter-schedule-outage.php 13835 2009-12-25 13:19:34Z fourstones $
*/

$sched_inc = dirname(__FILE__) . '/mixter-schedule-outage.inc';

CCEvents::AddHandler( CC_EVENT_MAP_URLS,           'schedule_outage_url_map', $sched_inc );
CCEvents::AddHandler( CC_EVENT_ADMIN_MENU, 'schedule_outage_admin_menu', $sched_inc );
//CCEvents::AddHandler( CC_EVENT_APP_INIT, 'opt_in_app_init' );


?>