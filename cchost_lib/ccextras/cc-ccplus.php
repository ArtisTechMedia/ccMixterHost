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
* $Id: cc-bpm.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage audio
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCCCPlus', 'OnGetConfigFields' ), 'cchost_lib/ccextras/cc-ccplus.inc' );

CCEvents::AddHandler(CC_EVENT_FORM_FIELDS,        array( 'CCCCPlus', 'OnFormFields'), 'cchost_lib/ccextras/cc-ccplus.inc' );
CCEvents::AddHandler(CC_EVENT_FORM_POPULATE,      array( 'CCCCPlus', 'OnFormPopulate'), 'cchost_lib/ccextras/cc-ccplus.inc'  );
CCEvents::AddHandler(CC_EVENT_FORM_VERIFY,        array( 'CCCCPlus', 'OnFormVerify'), 'cchost_lib/ccextras/cc-ccplus.inc'  );

CCEvents::AddHandler(CC_EVENT_SOURCES_CHANGED,    array( 'CCCCPlus', 'OnSourcesChanged'), 'cchost_lib/ccextras/cc-ccplus.inc'  );

CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,        array( 'CCCCPlus', 'OnUploadDone'), 'cchost_lib/ccextras/cc-ccplus.inc'  );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCCCPlus', 'OnMapUrls'), 'cchost_lib/ccextras/cc-ccplus.inc'  );
CCEvents::AddHandler(CC_EVENT_USER_ADMIN_MENU,    array( 'CCCCPlus', 'OnUserAdminMenu'), 'cchost_lib/ccextras/cc-ccplus.inc'  );


?>
