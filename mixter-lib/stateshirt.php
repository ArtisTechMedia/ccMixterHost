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
* $Id: stateshirt.php 11152 2008-11-11 22:28:19Z fourstones $
*
*/

/**
* @package cchost
* @subpackage audio
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'StateShirt' , 'OnGetConfigFields' ), dirname(__FILE__) . '/stateshirt.inc' );

CCEvents::AddHandler(CC_EVENT_FORM_FIELDS,    array( 'StateShirt', 'OnFormFields'), dirname(__FILE__) . '/stateshirt.inc' );
CCEvents::AddHandler(CC_EVENT_FORM_POPULATE,  array( 'StateShirt', 'OnFormPopulate'), dirname(__FILE__) . '/stateshirt.inc'  );
CCEvents::AddHandler(CC_EVENT_FORM_VERIFY,    array( 'StateShirt', 'OnFormVerify'), dirname(__FILE__) . '/stateshirt.inc'  );

CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,    array( 'StateShirt', 'OnUploadDone'), dirname(__FILE__) . '/stateshirt.inc'  );


?>
