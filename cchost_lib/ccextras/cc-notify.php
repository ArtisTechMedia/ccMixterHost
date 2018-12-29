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
* $Id: cc-notify.php 11443 2008-12-31 22:28:29Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCNotify',  'OnMapUrls'));
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCNotify' , 'OnGetConfigFields') );
CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE,array( 'CCNotify' , 'OnFilterUserProfile') );
CCEvents::AddHandler(CC_EVENT_USER_DELETED,       array( 'CCNotify' , 'OnUserDelete') );


require_once('cchost_lib/ccextras/cc-extras-events.php'); // for EVENT_TOPIC stuff

CCEvents::AddHandler(CC_EVENT_REVIEW,         array( 'CCNotify',  'OnReview'));
CCEvents::AddHandler(CC_EVENT_TOPIC_REPLY,    array( 'CCNotify',  'OnReply'));
CCEvents::AddHandler(CC_EVENT_ED_PICK,        array( 'CCNotify',  'OnEdPick'));
CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,    array( 'CCNotify',  'OnUploadDone')); 
CCEvents::AddHandler(CC_EVENT_RATED,          array( 'CCNotify',  'OnRated')); 
CCEvents::AddHandler(CC_EVENT_TRACKBACKS_APPROVED, array( 'CCNotify', 'OnTrackbacksApproved' ));
/**
*
*
*/
class CCNotify
{

    function EditMyNotifications($other_user_name='')
    {
        if( !$this->_is_notify_on() )
            return;
        require_once('cchost_lib/ccextras/cc-notify.inc');
        $notify_api = new CCNotifyAPI();
        $notify_api->EditMyNotifications($other_user_name);
    }

    function OnRated($rating_rec,$rating,&$record)
    {
        if( !$this->_is_notify_on() )
            return;
        require_once('cchost_lib/ccextras/cc-notify.inc');
        $notify_api = new CCNotifyAPI();
        $notify_api->OnRated($rating_rec,$rating,$record);
    }

    function OnReview(&$review,&$upload)
    {
        if( !$this->_is_notify_on() )
            return;
        require_once('cchost_lib/ccextras/cc-notify.inc');
        $notify_api = new CCNotifyAPI();
        $notify_api->OnReview($review);
    }

    function OnReply(&$reply, &$original)
    {
        if( !$this->_is_notify_on() )
            return;

        require_once('cchost_lib/ccextras/cc-notify.inc');
        $notify_api = new CCNotifyAPI();
        $notify_api->OnReply($reply, $original);
    }

    function OnEdPick($upload_id)
    {
        if( !$this->_is_notify_on() )
            return;

        require_once('cchost_lib/ccextras/cc-notify.inc');
        $notify_api = new CCNotifyAPI();
        $notify_api->OnEdPick($upload_id);
    }

    function OnTrackbacksApproved(&$tb_info) 
    {
        if( !$this->_is_notify_on() )
            return;

        require_once('cchost_lib/ccextras/cc-notify.inc');
        $notify_api = new CCNotifyAPI();
        $notify_api->OnTrackbacksApproved($tb_info);
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_DONE}
    * 
    * @param integer $upload_id ID of upload row
    * @param string $op One of {@link CC_UF_NEW_UPLOAD}, {@link CC_UF_FILE_REPLACE}, {@link CC_UF_FILE_ADD}, {@link CC_UF_PROPERTIES_EDIT'} 
    * @param array &$parents Array of remix sources
    */
    function OnUploadDone($upload_id,$op,$parents=array())
    {
        if( $op != CC_UF_NEW_UPLOAD || !$this->_is_notify_on() )
            return;

        require_once('cchost_lib/ccextras/cc-notify.inc');
        $notify_api = new CCNotifyAPI();
        $notify_api->OnUploadDone($upload_id,$op,$parents);
    }


    /**
    * Event handler for {@link CC_EVENT_FILTER_USER_PROFILE}
    *
    * Add extra data to a user row before display
    *
    * @param array &$record User record to massage
    */
    function OnFilterUserProfile(&$rows)
    {
        $row =& $rows[0];
        if( $this->_is_notify_on() && CCUser::IsLoggedIn())
        {
            if( CCUser::CurrentUser() != $row['user_id'] )
            {
                $url = ccl('people','notify','edit',$row['user_name']);
                $row['user_fields'][] = array( 'label' => 'str_user_notifications', 
                                               'value' => array('str_notify_get_notified',"<a href=\"$url\">",$row['user_real_name'],'</a>')
                                              );
            }

        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('people', 'notify', 'edit'), array('CCNotify','EditMyNotifications'), 
                CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '[username]', 
                _('Display notify options form. Optional parameter is other user to get notified about.'), CC_AG_USER );
            
        CCEvents::MapUrl( ccp('admin', 'notify', 'cleanup'), array('CCNotify','CleanUp'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Remove notifications to dead user accounts.'), CC_AG_USER );
    }

    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields['notify'] =
               array(  'label'      => _('Allow email notifications'),
                       'form_tip'   => _('Is it ok to allow users to get notified on activity on their accounts and others?'),
                       'value'      => 0,
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE );
        }
    }

    function OnUserDelete($user_id)
    {
        require_once('cchost_lib/ccextras/cc-notify.inc');
        $table =& CCNotifications::GetTable();
        $w['notify_user'] = $user_id;
        $table->DeleteWhere($w);
    }
    
    function CleanUp()
    {
        $sql = "select notify_id from cc_tbl_notifications left outer join cc_tbl_user on notify_user = user_id where isnull(user_id);";
        $notify_ids = CCDatabase::QueryItems($sql);
        
        if( empty($notify_ids) )
        {
            $count = '0';
        }
        else
        {
            $count = count($notify_ids);
            $notify_str = join(',',$notify_ids);
            $sql2 = "delete from cc_tbl_notifications where notify_id in ({$notify_str})";
            CCDatabase::Query($sql2);
        }
        
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Notifications Cleanup'));
        $page->Prompt("Notifiations cleaned up {$count} records");
    }

    function _is_notify_on()
    {
        global $CC_GLOBALS;

        return !empty($CC_GLOBALS['notify']) ;
    }

}



?>
