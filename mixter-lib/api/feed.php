<?

require_once('mixter-lib/lib/feed.php');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,        array( 'CCEventsFeed', 'OnMapUrls'));
CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP, array( 'CCEventsFeed', 'OnApiQuerySetup'));
CCEvents::AddHandler(CC_EVENT_ED_PICK,         array( 'CCEventsFeed', 'OnEdPick'));
CCEvents::AddHandler(CC_EVENT_RATED,           array( 'CCEventsFeed', 'OnRated'));
CCEvents::AddHandler(CC_EVENT_REVIEW,          array( 'CCEventsFeed', 'OnReview'));
CCEvents::AddHandler(CC_EVENT_FORUM_POST,      array( 'CCEventsFeed', 'OnForumPost'));
CCEvents::AddHandler(CC_EVENT_TOPIC_REPLY,     array( 'CCEventsFeed', 'OnTopicReply'));

class CCEventsFeed
{
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','user','feed','markseen'),
            array( 'CCAPIFeed', 'APIMarkSeen'),   CC_MUST_BE_LOGGED_IN,   ccs(__FILE__),
            '', _('Mark a feed item as seen'), CC_AG_USER );
    }

    function OnApiQuerySetup( &$args, &$queryObj, $requiresValidation )
    {
        if( !empty($args['datasource']) && $args['datasource'] === 'feed')
        {
            $lib = new CCLibFeed();
            $lib->PrePopulate(empty($args['user']) ? null : $args['user']);
            $sticky = empty($args['sticky']) ? 0 : 1;
            $queryObj->where[] = "feed_sticky = {$sticky}";
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
        $lib->AddRecommend($uploadRecord['upload_user'],$ratingRec['ratings_id']);
    }

    function OnReview(&$topic,&$upload_rec)
    {
        $lib = new CCLibFeed();
        $lib->AddReview($upload_rec['upload_user'],$topic['topic_id']);
    }

    function OnForumPost(&$topic)
    {
        if( !empty($topic['topic_forum']) &&  $topic['topic_forum'] == ADMIN_FORUM ) {
            $lib = new CCLibFeed();
            $lib->AddAdminMessage($topic['topic_id']);
        }
    }

    function OnTopicReply(&$replyTopic,&$originalTopic)
    {
        $lib = new CCLibFeed();
        $lib->AddTopicReply($originalTopic['topic_user'],$replyTopic['topic_id']);
    }

    function OnUploadDone($upload_id,$op,$parents)
    {
        $lib = new CCLibFeed();
        $lib->AddItemsBasedOnUpload($upload_id,$op,$parents);
    }
}

class CCAPIFeed
{
    function APIMarkSeen($feed_item_id) {
        $feed_item_id = CCUtil::CleanNumber($feed_item_id);
        $lib = new CCLibFeed();
        $status = $lib->MarkItemAsSeen($feed_item_id);
        CCUtil::ReturnAjaxObj($status);
    }
}
?>