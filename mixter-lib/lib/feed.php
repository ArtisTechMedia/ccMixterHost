<?

require_once('mixter-lib/lib/feed-table.php');
require_once('mixter-lib/lib/feed-types.inc');
require_once('mixter-lib/lib/status.inc');


define('FEED_INVALID_ID', 'invalid feed id');

class CCLibFeed
{

  function AddEdPick($upload_id) {

    $uploads =& CCUploads::GetTable();
    $edpicks = $uploads->GetExtraField($upload_id, 'edpicks');
    foreach ($edpicks as $pick ) {
      $date = $pick['edited'];
      break;
    }

    $actions =& CCFeedActionTable::GetTable(); 
    $where = array(
        'action_actor' => ADMIN_ID,
        'action_verb'  => FEED_VERB_EDPICK,
        'action_object' => $upload_id,
        'action_object_type' => FEED_TYPE_UPLOAD,
        'action_date' => $date
      );    
    $action = $actions->AddAction($where);

    $uploader = $uploads->QUeryItemFromKey('upload_user',$upload_id);

    $feed =& CCFeedTable::GetTable();
    $where = array(
        'feed_action' => $action,
        'feed_user'   => $uploader,
        'feed_reason' => FEED_REASON_EDPICKED
      );
    $feed->AddItem($where);

    return _make_ok_status();
  }

  function AddRecommends($upload,$ratings,$date='') {

    $actions =& CCFeedActionTable::GetTable(); 
    $where = array(
        'action_actor' => $ratings['ratings_user'],
        'action_verb'  => FEED_VERB_RECOMMEND,
        'action_object' => $ratings['ratings_upload'],
        'action_object_type' => FEED_TYPE_UPLOAD,
        'action_date' => $date
      );    
    $action = $actions->AddAction($where);

    $uploader = $upload['upload_user'];
    $feed =& CCFeedTable::GetTable();
    $where = array(
        'feed_action' => $action,
        'feed_user'   => $uploader,
        'feed_reason' => FEED_REASON_RECOMMENDED
      );
    $feed->AddItem($where);

    return _make_ok_status();
  }

  function AddReview($upload,$topic) {
    $actions =& CCFeedActionTable::GetTable(); 
    $where = array(
        'action_actor' => $topic['topic_user'],
        'action_verb'  => FEED_VERB_REVIEW,
        'action_object' => $topic['topic_id'],
        'action_object_type' => FEED_TYPE_REVIEW,
        'action_date' => $topic['topic_date']
      );    
    $action = $actions->AddAction($where);

    $uploader = $upload['upload_user'];
    $feed =& CCFeedTable::GetTable();
    $where = array(
        'feed_action' => $action,
        'feed_user'   => $uploader,
        'feed_reason' => FEED_REASON_REVIEWED
      );
    $feed->AddItem($where);

    return _make_ok_status();        
  }

  function AddAdminMessage($topic) {
    $actions =& CCFeedActionTable::GetTable(); 
    $where = array(
        'action_actor' => ADMIN_ID,
        'action_verb'  => FEED_VERB_FORUM_POST,
        'action_object' => $topic['topic_id'],
        'action_object_type' => FEED_TYPE_FORUM_POST,
        'action_sticky' => 1,
        'action_date' => $topic['topic_date']
      );    
    $action = $actions->AddAction($where);
    return _make_ok_status();    
  }

  function AddFollowing($user,$following,$date='') {
    $actions =& CCFeedActionTable::GetTable(); 
    $user = CCUser::IDFromName($user);
    $following = CCUser::IDFromName($following);
    $where = array(
        'action_actor' => $user,
        'action_verb'  => FEED_VERB_FOLLOW,
        'action_object' => $following,
        'action_object_type' => FEED_TYPE_USER,
        'action_date' => $date
      );    
    $action = $actions->AddAction($where);

    $feed =& CCFeedTable::GetTable();
    $where = array(
        'feed_action' => $action,
        'feed_user'   => $following,
        'feed_reason' => FEED_REASON_FOLLOWED
      );
    $feed->AddItem($where);
    
    return _make_ok_status();    

  }
  function AddTopicReply($original,$topic) {

    require_once('cchost_lib/ccextras/cc-topics.inc');
    $posters = array();
    $top =& $original;
    $topics =& CCTopics::GetTable();
    while( $top )
    {
      if( ($top['topic_user'] != $topic['topic_user']) )
          $posters[] = $top['topic_user'];

      if( $top['topic_type'] != 'reply' )
          break;

      list( $parent_id ) = $topics->GetParentTopic($top['topic_id']);
      if( empty($parent_id) )
          break;

      $row = $topics->QueryKeyRow($parent_id);
      $top =& $row;
    }

    $actions =& CCFeedActionTable::GetTable(); 
    $where = array(
        'action_actor' => $topic['topic_user'],
        'action_verb'  => FEED_VERB_TOPIC_REPLY,
        'action_object' => $topic['topic_id'],
        'action_object_type' => $top['topic_type'] == 'review' ? FEED_TYPE_REVIEW : FEED_TYPE_FORUM_POST,
        'action_date' => $topic['topic_date']
      );    
    $action = $actions->AddAction($where);

    $feed =& CCFeedTable::GetTable();
    foreach ($posters as $poster) {
      $where = array(
          'feed_action' => $action,
          'feed_user'   => $poster,
          'feed_reason' => FEED_REASON_REPLIED
        );
      $feed->AddItem($where);      
    }

    return _make_ok_status(); 
  }

  function AddUpload($upload_id,$op,$parents) {

    if( $op !== CC_UF_NEW_UPLOAD ) {
      return;
    }

    $uploads =& CCUploads::GetTable();
    $upload_row = $uploads->QueryItemsFromKey('upload_id,upload_user,upload_date',$upload_id);

    // Get an action on demand
    $_action = null;
    $action = function() use(&$_action,$upload_row) {
      if( $_action ) {
        return $_action;
      }
      $actions =& CCFeedActionTable::GetTable();
      $where = array(
          'action_actor' => $upload_row['upload_user'],
          'action_verb'  => FEED_VERB_NEW_UPLOAD,
          'action_object' => $upload_row['upload_id'],
          'action_object_type' => FEED_TYPE_UPLOAD,
          'action_date' => $upload_row['upload_date']
        );    
      $_action = $actions->AddAction($where);
      return $_action;
    };

    $feed =& CCFeedTable::GetTable();

    if( empty($parents) ) {
      $action();
    } else {
      // Notify people who have been remixed
      $parent_owners = array_unique(array_map(function($u) { return $u['upload_user']; }, $parents));

      foreach ($parent_owners as $remixee ) {
          $where = array(
              'feed_action' => $action(),
              'feed_user'   => $remixee,
              'feed_reason' => FEED_REASON_REMIXED
            );
          $feed->AddItem($where);
      }
    }
        
    return _make_ok_status();
  }

  function PrePopulate() {
    require_once('mixter-lib/lib/feed-install.inc');
    UserFeedPrePopulate($this);
  }

}
?>