<?

/*
  $Id: mixter-lost-password.php 13835 2009-12-25 13:19:34Z fourstones $
*/

CCEvents::AddAlias('lostpassword','atmlostpassword');
CCEvents::AddHandler( CC_EVENT_MAP_URLS, array('ATMLogin','OnMapUrls'), 'mixter-lib/mixter-lost-password.inc' );

?>