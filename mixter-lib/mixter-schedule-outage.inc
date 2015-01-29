<?

/*
  $Id: mixter-schedule-outage.inc 13835 2009-12-25 13:19:34Z fourstones $
*/


function schedule_outage_url_map()
{
    CCEvents::MapUrl( ccp('admin','scheduleoutage'), 'schedule_outage', CC_ADMIN_ONLY, ccs(__FILE__) );
}

function schedule_outage($clear='')
{
    require_once('cchost_lib/cc-form.php');
    require_once('cchost_lib/cc-admin.php');
    $admin = new CCAdmin();
    $page =& CCPage::GetPage();
    
    if( $clear == 'clear' )
    {
        $title = _('Clear Scheduled Outage');
        $admin->BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);
        
        $form = new CCForm();
        
        $sql = 'SELECT topic_id,topic_name FROM cc_tbl_forum_threads ' .
                    'LEFT OUTER JOIN cc_tbl_topics ON forum_thread_oldest = topic_id ' .
                    'WHERE forum_thread_forum = 1 ' .
                    'ORDER BY topic_date DESC ' .
                    'LIMIT 10'; // '1' is Anncouncements

        $rows = CCDatabase::QueryRows($sql);
        foreach( $rows as $R )
            $topics[$R['topic_id']] = $R['topic_name'];
        $fields = array(
            'reply_to' => array(
                'label' => 'Forum reply to:',
                'formatter' => 'select',
                'options' => $topics,
                'flags' => CCFF_NONE,
            ),
            'forum_reply' => array(
                    'label' => 'Forum reply',
                    'formatter' => 'textarea',
                    'want_formatting' => true,
                    'value' => 'OK, we are back up, sorry for any inconvenience!',
                    'flags' => CCFF_NONE,
                
            ),
            'tweet' => array(
                    'label' => 'Tweet',
                    'form_tip' => 'Leave blank for no tweet',
                    'formatter' => 'textedit',
                    'value' => 'OK, we are back up, sorry for any inconvenience!',
                    'flags' => CCFF_NONE,
                
            ),
            'setbanner' => array(
                    'label' => 'Clear banner',
                    'formatter' => 'checkbox',
                    'flags' => CCFF_NONE,
                    'value' => true,                
            ),
            
        );
        $form->AddFormFields($fields);
        if( empty($_POST) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            require_once('cchost_lib/ccextras/cc-topics.inc');
            $topicapi = new CCTopic();
            $v['topic_text'] = $values['forum_reply'];
            $v['topic_can_xlat'] = 0;
            list( $thread_id, $topic_id ) = $topicapi->_do_reply($values['reply_to'],$v,$record);
            if( $form->GetFormValue('setbanner') )
            {
                $configs =& CCConfigs::GetTable();
                $ttags = $configs->GetConfig('ttag');
                $ttags['beta_message'] = '';
                $configs->SaveConfig('ttag',$ttags);
            }
            if( !empty($values['tweet']) )
            {
                require_once('mixter-lib/mixter-tweet.inc');
                tweet_post_to_twitter($values['tweet']);
            }
            $url = ccl('thread',$thread_id);
            CCUtil::SendBrowserTo($url);
        }
        
        return;
    }
    
    $title = _('Announce Scheduled Outage');
    $admin->BreadCrumbs(true,array('url'=>'','text'=>$title));
    $page->SetTitle($title);
    $form = new CCForm();
    $clear_url = ccl('admin','scheduleoutage','clear');
    $help =<<<EOF
To send the "all clear" messages click <a href="{$clear_url}">here</a>.    
EOF;
    $form->SetHelpText($help);
    $me = CCUser::CurrentUserField('user_real_name');
    $deftext =<<<EOF
ccMixter will be going offline [when_goes_here] for site maintenance. Please make sure to finish up any uploads, reviews, forums posts, etc. well before that.

We're hoping to be back up and running in [duration_goes_here];

Thanks,
{$me}
EOF;
    $fields = array(
        'topictitle' => array(
                'label' => 'Forum topic title',
                'formatter' => 'textedit',
                'flags' => CCFF_REQUIRED,
                'value' => 'ccMixter is going offline [when_goes_here]',
        ),
        'forumpost' => array(
                'label' => 'Forum post',
                'formatter' => 'textarea',
                'value' => $deftext,
                'want_formatting' => true,
                'flags' => CCFF_NONE,
        ),
        'tweet' => array(
                'label' => 'Tweet',
                'formatter' => 'checkbox',
                'value' => true,
                'flags' => CCFF_NONE,
            
        ),
        'setbanner' => array(
                'label' => 'Update banner',
                'formatter' => 'checkbox',
                'flags' => CCFF_NONE,
                'value' => true,
            
        ),
        
    );
    $form->AddFormFields($fields);
    if( empty($_POST) || !$form->ValidateFields() )
    {
        $page->AddForm( $form->GenerateForm() );
    }
    else
    {
        $form->GetFormValues($values);
        $v['topic_name'] = $values['topictitle'];
        $v['topic_text'] = $values['forumpost'];
        $v['topic_can_xlat'] = 0;
        require_once('cchost_lib/ccextras/cc-forums.inc');
        $forums = new CCForumAPI();
        list( $thread_id, $topic_id ) = $forums->_post_new_topic($v,1); // '1' is Anncouncements forum
        $url = ccl('thread',$thread_id);
        if( $form->GetFormValue('setbanner') )
        {
            $msg = $v['topic_name'] . " <a href=\"{$url}\">Read more...</a>";
            $configs =& CCConfigs::GetTable();
            $ttags = $configs->GetConfig('ttag');
            $ttags['beta_message'] = $msg;
            $configs->SaveConfig('ttag',$ttags);
        }
        if( $form->GetFormValue('tweet') )
        {
            require_once('mixter-lib/mixter-tweet.inc');
            $status = $v['topic_name'] . ' ' . $url;
            tweet_post_to_twitter($status);
        }
        
        CCUtil::SendBrowserTo($url);
    }
}

/**
* Event handler for {@link CC_EVENT_ADMIN_MENU}
*
* @param array &$items Menu items go here
* @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
*/
function schedule_outage_admin_menu(&$items,$scope)
{
    if( $scope != CC_GLOBAL_SCOPE )
        return;

    $items += array( 
        'schedoutage'   => array('menu_text'   => _('Announce Scheduled Outage'),
                          'menu_group'  => 'configure',
                          'help'        => _('Tell the community about an upcoming outage'),
                          'weight'      => 350,
                          'action'      =>  ccl('admin','scheduleoutage'),
                          'access'      => CC_ADMIN_ONLY
                         ),
        );
}

?>