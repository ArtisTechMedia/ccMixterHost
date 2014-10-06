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
* $Id: cc-notify.inc 12611 2009-05-13 19:24:00Z fourstones $
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
define('CC_NTFY_IVE_BEEN_REVIEWED', 0x0001 );
define('CC_NTFY_THEY_BEEN_REMIXED', 0x0002 );
define('CC_NTFY_REVIEW_REPLY',      0x0004 );
define('CC_NTFY_ED_PICK',           0x0008 );
define('CC_NTFY_THEY_UPLOADED',     0x0010 );
define('CC_NTFY_IVE_BEEN_REMIXED',  0x0020 );
define('CC_NTFY_IVE_BEEN_RATED',    0x0040 );
define('CC_NTFY_TRACKBACK',         0x0080 );

define('CC_NTFY_MAX',    CC_NTFY_TRACKBACK );

class CCNotifications extends CCTable
{
    function CCNotifications()
    {
        $this->CCTable('cc_tbl_notifications','notify_id');
    }

    function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCNotifications();
        return $_table;
    }
}



/**
*
*
*/
class CCNotifyAPI
{

    function EditMyNotifications($other_user_name='')
    {
        require_once('cchost_lib/cc-page.php');

        CCPage::SetTitle('str_notify_edit');
        $bc[] = array( 'url' => ccl('preferences'), 'text' => 'str_pref_title' );
        CCUser::AddUserBreadCrumbs('str_notify_edit',$bc);

        // figure out what fields we are editing

        if( $other_user_name )
        {
            // user is at another user's profile
            // and wants to monitor their activity

            $users =& CCUsers::GetTable();
            $where['user_name'] = $other_user_name;
            $user_happy_name = $users->QueryItem('user_real_name',$where);
            $fields = array(
                array( 'label' => array('str_notify_has_been_remixed',$user_happy_name),
                       'flag' => CC_NTFY_THEY_BEEN_REMIXED, ),
                array( 'label' => array('str_notify_uploads_some',$user_happy_name),
                       'flag' => CC_NTFY_THEY_UPLOADED, ),
                );
        }
        else
        {
            // user is at their own profile and wants
            // to get notified when people affect her work

            $fields = array(
                array( 'label' => 'str_notify_i_been_remixed',
                       'flag' => CC_NTFY_IVE_BEEN_REMIXED, ),
                array( 'label' => 'str_notify_i_been_reviewed',
                       'flag' => CC_NTFY_IVE_BEEN_REVIEWED, ),
                array( 'label' => 'str_notify_i_been_replied',
                       'flag' => CC_NTFY_REVIEW_REPLY, ),
                array( 'label' => 'str_notify_i_been_rated',
                       'flag' => CC_NTFY_IVE_BEEN_RATED, ),
                array( 'label' => 'str_notify_i_been_tracked',
                       'flag' => CC_NTFY_TRACKBACK, ),
                );
        }

        // let's see if there's already a record
        // for this user in this context...

        $notifications =& CCNotifications::GetTable();
        $args['notify_user'] = CCUser::CurrentUser();
        if( $other_user_name )
        {
            $w2['user_name'] = $other_user_name;
            $args['notify_other_user'] = $users->QueryKey($w2);
        }
        else
        {
            $args['notify_other_user'] = 0;
        }
        $current_notifications = $notifications->QueryRow($args);

        require_once('cchost_lib/ccextras/cc-notify-forms.inc');

        $form = new CCEditUserNotificationForm($fields);

        if( !empty($_POST['editusernotification']) )
        {
            // user hit submit...

            $form->ValidateFields();
            $form->GetFormValues($values);
            $mask = 0;
            // $K is 'f_[n]' where [n] is the flag number
            foreach( $values as $K => $V )
            {
                if( $V )
                    $mask |= intval(substr($K,2));
            }
            $args['notify_mask'] = $mask;
            if( $current_notifications )
            {
                $args['notify_id'] = $current_notifications['notify_id'];
                $notifications->Update($args);
            }
            else
            {
                $notifications->Insert($args);
            }
            
            CCPage::Prompt('str_notify_updated');
        }
        else
        {
            // we're displaying the form...

            if( $current_notifications )
            {
                $mask = $current_notifications['notify_mask'];
                if( $mask )
                {
                    // we need to populate the current
                    // settings for this user in this
                    // context

                    $values = array();
                    $bit = 1;
                    for( $bit = 1; $bit <= CC_NTFY_MAX; $bit <<= 1 )
                    {
                        if( $mask & $bit )
                            $values['f_' . $bit] = 'on';
                    }
                    $form->PopulateValues($values);
                }
            }

            CCPage::AddForm( $form->GenerateForm() );
        }
    }

    function OnRated($rating_rec,$rating,&$record)
    {  
              
        require_once('cchost_lib/cc-config.php');        
        require_once('cchost_lib/cc-page.php');
        $configs =& CCConfigs::GetTable();
        $C       = $configs->GetConfig('chart',CC_GLOBAL_SCOPE);
        $page   =& CCPage::GetPage();
                
        $uploader = $record['upload_user'];
        $ibr = CC_NTFY_IVE_BEEN_RATED;
        $where = "(notify_user = $uploader) AND (notify_mask & $ibr)";
        $notifications =& CCNotifications::GetTable();
        $notify_row = $notifications->QueryRow($where);
        if( $notify_row )
        {
            $body = "\n\n" . $page->String('str_notify_dont_respond') . "\n\n";
            
            if(  empty($C['thumbs_up']) )
            {
                $body .= $page->String('str_notify_got_rating') . "\n\n%s\n";

                $body = sprintf($body, $record['upload_name'],
                                       $rating,
                                       $record['file_page_url'] );

                $subject = $page->String('str_notify_rating_subject');
            }
            else
            {
                $rater = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_id='.
                                             $rating_rec['ratings_user'] );
                                             
                $body .= $page->String('str_notify_got_recc') . "\n\n%s\n";

                $body = sprintf($body, $record['upload_name'],
                                       $rater,
                                       $record['file_page_url'] );

                $subject = $page->String('str_notify_recc_subject');
            }

            $nrarray = array( $notify_row );
            $this->_do_notify($subject, $body, $nrarray  );
        }
    }


    function OnReview(&$review)
    {
        require_once('cchost_lib/cc-page.php');
        $page   =& CCPage::GetPage();
        $uploads =& CCUploads::GetTable();
        $uploader = $uploads->QueryItemFromKey('upload_user',$review['topic_upload']);
        if( $uploader )
        {
            $ibr = CC_NTFY_IVE_BEEN_REVIEWED;
            $where = "(notify_user = $uploader) AND (notify_mask & $ibr)";
            $notifications =& CCNotifications::GetTable();
            $notify_row = $notifications->QueryRow($where);
            if( $notify_row )
            {
                $body = "\n\n" . $page->String('str_notify_dont_respond')  . 
                "\n\n" . 
                $page->String('str_notify_reviewed_by') .
                "\n\n%s\n";
                $upload_name = $uploads->QueryItemFromKey('upload_name',$review['topic_upload']);
                $body = sprintf($body,$upload_name,
                                      $review['user_real_name'],
                                      $review['topic_permalink']);

                $subject = $page->String('str_notify_review');
                $nrarray = array( $notify_row );
                $this->_do_notify($subject, $body, $nrarray  );
            }
        }
    }

    function OnTrackbacksApproved(&$trackback_info)
    {
        require_once('cchost_lib/cc-page.php');
        $page   =& CCPage::GetPage();
        
        $upload_id = $trackback_info['pool_tree_parent'];
        $pool_item_id = $trackback_info['pool_tree_pool_child'];

        list( $uploader, $upload_name ) = CCDatabase::QueryRow(
                    'SELECT upload_user, upload_name FROM cc_tbl_uploads WHERE upload_id='.$upload_id,false);
        $ibr = CC_NTFY_TRACKBACK;
        $where = "(notify_user = $uploader) AND (notify_mask & $ibr)";
        $notifications =& CCNotifications::GetTable();
        $notify_row = $notifications->QueryRow($where);
        if( $notify_row )
        {
            $item_url = ccl('pools','item',$pool_item_id);
            
            $body = "\n\n" . $page->String('str_notify_dont_respond')  . 
            "\n\n" .
            $page->String('str_notify_track_used') .
            "\n\n%s\n";
            $body = sprintf($body,$upload_name,$item_url);

            $subject = $page->String('str_notify_trackback');
            $nrarray = array( $notify_row );
            $this->_do_notify($subject, $body, $nrarray  );
        }
    }
    
    function OnReply(&$reply, &$original)
    {
        // figure out everybody who's in
        // on this thread:

        $this_poster = $reply['topic_user'];
        $posters = array();
        $C =& $original;
        $topics =& CCTopics::GetTable();
        while( $C )
        {
            if( ($C['topic_user'] != $this_poster) )
                $posters[] = $C['topic_user'];

            if( $C['topic_type'] != 'reply' )
                break;

            list( $parent_id ) = $topics->GetParentTopic($C['topic_id']);
            if( empty($parent_id) )
                break;

            $row = $topics->QueryKeyRow($parent_id);
            $C =& $row;
        }

        // this shouldn't happen in today's code
        // but there's no reason to break tomorrows...

        if( empty($C) )
            return;

        if( !empty($C['topic_upload']) )
        {
            // $C should be pointing to a post at this point
            // let's add the person that uploaded the thing

            $uploads =& CCUploads::GetTable();
            $uploader = $uploads->QueryItemFromKey('upload_user', $C['topic_upload']);
            if( $uploader != $this_poster )
                $posters[] = $uploader;
        }

        // Do any of the participants even want to
        // get notified about replies?
        //
        $posters = array_unique($posters);

        if( empty($posters) )
            return;

        $wq = array();
        foreach( $posters as $poster )
        {
            $wq[] = "(notify_user = $poster)";
        }
        $wq = join( ' OR ', $wq );
        $nrr = CC_NTFY_REVIEW_REPLY;
        $where = "(notify_mask & $nrr) AND ($wq)";

        $notifications =& CCNotifications::GetTable();
        $notify_rows = $notifications->QueryRows($where);

        if( empty($notify_rows) )
            return;

        // Do the notifications
        //
        if( $C['topic_type'] == 'review' )
        {
            $sql =<<<EOF
                SELECT DISTINCT user_name 
                FROM cc_tbl_user 
                JOIN cc_tbl_uploads on user_id=upload_user
                JOIN cc_tbl_topics on upload_id=topic_upload
                WHERE topic_upload = {$C['topic_upload']}
EOF;
            $user_name = CCDatabase::QueryItem($sql);
            $url = ccl('reviews',$user_name,$C['topic_upload']) . '#' . $reply['topic_id'];
        }
        else
        {
            $url = ccl('thread',$C['topic_thread']) . '#' . $reply['topic_id'];
        }

        require_once('cchost_lib/cc-page.php');
        $page   =& CCPage::GetPage();
        $subject = $page->String('str_notify_topic');
        $body = "\n\n" . $page->String('str_notify_dont_respond')  . "\n\n" . 
        $page->String('str_notify_replied') . 
        "\n\n%s\n";
        $body = sprintf( $body, $C['topic_name'], $url );

        $this->_do_notify($subject,$body,$notify_rows);
    }

    function OnEdPick($upload_id)
    {
        /*
        $notifications =& CCNotifications::GetTable();
        $notify_rows = $notifications->QueryRows($where);
        */
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_DONE}
    * 
    * @param integer $upload_id ID of upload row
    * @param string $op One of {@link CC_UF_NEW_UPLOAD}, {@link CC_UF_FILE_REPLACE}, {@link CC_UF_FILE_ADD}, {@link CC_UF_PROPERTIES_EDIT'} 
    * @param array &$parents Array of remix sources
    */
    function OnUploadDone($upload_id,$op,&$parents)
    {
        $urlf = ccl('files') . '/';

        $sql =<<<EOF
        SELECT upload_user, user_real_name, CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url, upload_name,
               upload_published
            FROM cc_tbl_uploads 
            JOIN cc_tbl_user ON upload_user = user_id
            WHERE upload_id = {$upload_id}
EOF;
        list( $uploader, $user_real_name, $file_page_url, $upload_name, $published ) = CCDatabase::QueryRow($sql,false);

        if( empty($published) )
            return;

        // people who want get notified when this user
        // has uploaded anything:

        $notify_rows = array();
        $where = "((notify_mask & " . CC_NTFY_THEY_UPLOADED . ") AND (notify_other_user = $uploader)) ";

        if( $parents )
        {
            // upload has remixes...

            $count = count($parents);
            $sampled_users = array();
            for( $i = 0; $i < $count; $i++ )
            {
                $sampled_users[] = $parents[$i]['upload_user'];
            }

            if( $sampled_users )
            {
                $sampled_users = array_unique($sampled_users);
                $they_remixed = array();
                $me_remixed = array();
                foreach( $sampled_users as $sampled_user )
                {
                    $they_remixed[] = "(notify_other_user = $sampled_user)";
                    $me_remixed[] = "(notify_user = $sampled_user)";
                }

                // people who want to get notified when some one ELSE
                // is remixed:

                $tbr = CC_NTFY_THEY_BEEN_REMIXED;
                $they_remixed = join( ' OR ', $they_remixed );
                $where .= "OR ((notify_mask & $tbr) AND ($they_remixed)) ";

                // people who want to know when they themselves are
                // remixed...
                
                $ibr = CC_NTFY_IVE_BEEN_REMIXED;
                $me_remixed = join( ' OR ', $me_remixed );
                $where .= "OR ((notify_mask & $ibr) AND ($me_remixed))";
            }
        }

        $notifications =& CCNotifications::GetTable();
        $notify_rows = $notifications->QueryRows($where);

        $configs =& CCConfigs::GetTable();
        $ttags = $configs->GetConfig('ttag');
        $site_name = $ttags['site-title'];
        require_once('cchost_lib/cc-page.php');
        $page   =& CCPage::GetPage();
        
        $subject = $page->String(array('str_notify_upload_by',$user_real_name));
        $body = "\n\n" . $page->String('str_notify_dont_respond')  . "\n\n" . 
        $page->String('str_notify_uploaded')
        . "\n\n\"%s\"\n\n%s\n";
        $body = sprintf($body, $user_real_name,
                               $ttags['site-title'],
                               $upload_name,
                               $file_page_url);

        if( $parents )
        {
            $body .= "\n" . $page->String('str_notify_uses') . "\n\n";
            $count = count($parents);
            for( $i = 0; $i < $count; $i++ )
            {
                $R =& $parents[$i];
                $body .= "\n" . $page->String(
                    array('str_notify_uses_by', "\"{$R['upload_name']}\"", $R['user_real_name']))
                    . "\n";
            }
        }

        $body .= "\n\n" . $page->String(array('str_notify_upload_thanks', "\n\n" . $ttags['site-title']));

        $this->_do_notify($subject, $body, $notify_rows);
    }


    function _do_notify($subject,$body,&$notifies)
    {
        global $CC_GLOBALS;

        $count = count($notifies);
        $user_ids = array();
        for( $i = 0; $i < $count; $i++ )
        {
            $user_ids[] = $notifies[$i]['notify_user'];
        }
        require_once('cchost_lib/ccextras/cc-mail.inc');

        $user_ids = array_unique($user_ids);

        $mailer = new CCMailer();
        $mailer->Subject( $subject );
        $users =& CCUsers::GetTable();
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $sendtext = $page->String('str_notify_sent_to');
        foreach( $user_ids as $user_id )
        {
            $wx['user_id'] = $user_id;
            $urow = $users->QueryRow($wx,'user_email,user_name,user_real_name');
            $mailer->To($urow['user_email']);
            $u_body = $body . "\n\n {$sendtext}: {$urow['user_real_name']} ({$urow['user_name']})\n";
            $mailer->Body($u_body);
            $ok = @$mailer->Send();
            if( !$ok )
            {
                CCDebug::Enable(true);
                CCDebug::Log("Message '$subject' failed to send: \n{$u_body}");
            }
        }
    }

}



?>
