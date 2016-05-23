<?

require_once('mixter-lib/lib/feed.php');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,        array( 'CCEventsFeed', 'OnMapUrls'));
CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP, array( 'CCEventsFeed', 'OnApiQuerySetup'));
CCEvents::AddHandler(CC_EVENT_ED_PICK,         array( 'CCEventsFeed', 'OnEdPick'));
CCEvents::AddHandler(CC_EVENT_RATED,           array( 'CCEventsFeed', 'OnRated'));
CCEvents::AddHandler(CC_EVENT_REVIEW,          array( 'CCEventsFeed', 'OnReview'));
CCEvents::AddHandler(CC_EVENT_FORUM_POST,      array( 'CCEventsFeed', 'OnForumPost'));
CCEvents::AddHandler(CC_EVENT_TOPIC_REPLY,     array( 'CCEventsFeed', 'OnTopicReply'));

define('USER_FIELD_FEED_SEEN','feedseen');

class CCEventsFeed
{
    function OnMapUrls()
    {
        /*
        CCEvents::MapUrl( ccp('api','user','feed','markseen'),
            array( 'CCAPIFeed', 'APIFeedLastSeen'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '', _('Mark a feed item as seen'), CC_AG_USER );
        */
        CCEvents::MapUrl( ccp('api','feed','lastseen'),
            array( 'CCAPIFeed', 'APIFeedLastSeen'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '', _('Mark a feed as seen'), CC_AG_USER );

        CCEvents::MapUrl( ccp('api','feed','unsee'),
            array( 'CCAPIFeed', 'APIFeedUnSee'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '', _('for debugging'), CC_AG_USER );
    }

    function OnApiQuerySetup( &$args, &$queryObj, $requiresValidation )
    {
        if( !empty($args['datasource']) && $args['datasource'] === 'feed')
        {
            if( 0 ) {
                $lib = new CCLibFeed();
                $lib->PrePopulate();
                $x = array(
                    CCDatabase::QueryRows('select * from cc_tbl_feed_action'),
                    CCDatabase::QueryRows('select * from cc_tbl_feed'),
                    );
                CCDebug::PrintV($x);
            }

            $sticky = empty($args['sticky']) ? 0 : 1;
            $queryObj->where[] = "action_sticky = {$sticky}";

            if( !empty($args['user']) ) {
                $user_id = CCUser::IDForName($args['user']);
                if( !$user_id ) {
                    $queryObj->dead = true;
                    return;
                }
                if( !empty($args['sinced']) && $args['sinced'] == 'lastseen' ) {
                    $users =& CCUsers::GetTable();
                    $lastseen = $users->GetExtraField($user_id,USER_FIELD_FEED_SEEN);
                    $args['sinced'] = empty($lastseen) ? '10 years ago' : $lastseen;
                }

                if( !empty($args['following']) ) {
                    $queryObj->where[] = "action_actor IN (SELECT follow_follows FROM cc_tbl_follow WHERE follow_user = {$user_id})";
                    unset($args['user']);
                }

            }
        }
    }

    function OnEdPick($upload_id)
    {
        $lib = new CCLibFeed();
        $lib->AddEdPick($upload_id);        
    }

    function OnRated($ratingRec, $score, &$uploadRecord )
    {
        $lib = new CCLibFeed();
        $lib->AddRecommend($uploadRecord,$ratingRec);
    }

    function OnReview(&$topic,&$upload_rec)
    {
        $lib = new CCLibFeed();
        $lib->AddReview($upload_rec,$topic);
    }

    function OnForumPost(&$topic)
    {
        if( !empty($topic['topic_forum']) &&  $topic['topic_forum'] == ADMIN_FORUM ) {
            $lib = new CCLibFeed();
            $lib->AddAdminMessage($topic);
        }
    }

    function OnTopicReply(&$replyTopic,&$originalTopic)
    {
        $lib = new CCLibFeed();
        $lib->AddTopicReply($originalTopic,$replyTopic);
    }

    function OnUploadDone($upload_id,$op,$parents)
    {
        $lib = new CCLibFeed();
        $lib->AddUpload($upload_id,$op,$parents);
    }
}

class CCAPIFeed
{
    function APIFeedLastSeen($username) {

        if( $username != CCUser::CurrentUserName() ) {
            $status = _make_err_status(USER_UNKNOWN_USER);
        } else {
            $users =& CCUsers::GetTable();
            $users->SetExtraField( CCUser::CurrentUser(),USER_FIELD_FEED_SEEN, date( 'Y-m-d H:i:s' ) );
            $status = _make_ok_status();
        }
        CCUtil::ReturnAjaxObj($status);
    }

    function APIFeedUnSee($username) {

        if( $username != CCUser::CurrentUserName() ) {
            $status = _make_err_status(USER_UNKNOWN_USER);
        } else {
            $users =& CCUsers::GetTable();
            $users->SetExtraField( CCUser::CurrentUser(),USER_FIELD_FEED_SEEN, '' );
            $status = _make_ok_status();
        }
        CCUtil::ReturnAjaxObj($status);
    }
}
?>