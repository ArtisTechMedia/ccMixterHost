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
* $Id: cc-collab.php 11151 2008-11-11 22:26:59Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCCollab',  'OnMapUrls')         , 'cchost_lib/ccextras/cc-collab.inc' );
CCEvents::AddHandler(CC_EVENT_FORM_FIELDS,        array( 'CCCollab', 'OnFormFields')      , 'cchost_lib/ccextras/cc-collab.inc' );
CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,        array( 'CCCollab', 'OnUploadDone')      , 'cchost_lib/ccextras/cc-collab.inc' );
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,      array( 'CCCollab',  'OnUploadDelete')    , 'cchost_lib/ccextras/cc-collab.inc' );
CCEvents::AddHandler(CC_EVENT_FILTER_COLLAB_CREDIT, array( 'CCCollabHV',  'OnFilterCollabCredit') );
CCEvents::AddHandler(CC_EVENT_USER_PROFILE_TABS,  array( 'CCCollabHV',  'OnUserProfileTabs') );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCCollab' , 'OnGetConfigFields' ), 'cchost_lib/ccextras/cc-collab.inc' );


class CCCollabHV 
{
    function OnUserProfileTabs( &$tabs, &$record )
    {
        global $CC_GLOBALS;
        
        if( empty($CC_GLOBALS['collab_enabled']) )
            return;

        if( empty($record['user_id']) )
        {
            $tabs['collabs'] = 'str_collaborations';
            return;
        }

        $c = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_collab_users WHERE collab_user_user='.$record['user_id']);

        if( !$c  )
            return;

        $tabs['collabs'] = array(
                    'text' => 'str_collaborations',
                    'help' => 'str_collaborations',
                    'tags' => 'collabs',
                    'access' => 4,
                    'function' => 'url',
                    'user_cb' => array( 'CCCollab', 'UserTab' ),
                    'user_cb_mod' => 'cchost_lib/ccextras/cc-collab.inc',
            );
    }

    function OnFilterCollabCredit(&$records)
    {
        $c = count($records);
        $k = array_keys($records);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[ $k[$i] ];
            if( empty($R['collab_id']) )
                continue;

            $collab_id = $R['collab_id'];
            require_once('cchost_lib/ccextras/cc-collab.inc');
            if( empty($R['collab_name']) )
            {
                $collab_info = CCDatabase::QueryRow('SELECT * FROM cc_tbl_collabs WHERE collab_id='.$collab_id);
                foreach( $collab_info as $K => $V )
                    $R[$K] = $V;
            }
            $api = new CCCollab();
            $R['collab_users'] = $api->_get_collab_users($collab_id);
        }
    }

}

?>
