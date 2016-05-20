<?

require_once('mixter-lib/lib/feed-table.php');
require_once('mixter-lib/lib/feed-types.inc');
require_once('mixter-lib/lib/status.inc');


define('FEED_INVALID_ID', 'invalid feed id');

class CCLibFeed
{

  function AddEdPick($upload_id) {
    $actions =& CCFeedActionTable::GetTable(); 
    $where = array(
        'action_actor' => ADMIN_ID,
        'action_verb'  => FEED_VERB_EDPICK,
        'action_object' => $upload_id,
        'action_object_type' => FEED_TYPE_UPLOAD,
      );    
    $action = $actions->AddAction($where);

    $uploads =& CCUploads::GetTable();
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

  function AddRecommends($upload,$ratings) {

    $actions =& CCFeedActionTable::GetTable(); 
    $where = array(
        'action_actor' => $ratings['ratings_user'],
        'action_verb'  => FEED_VERB_RECOMMEND,
        'action_object' => $ratings['ratings_upload'],
        'action_object_type' => FEED_TYPE_UPLOAD,
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

  function AddAdminMessage($topic_id) {
    $table =& CCFeedTable::GetTable(); 
    $table->AddItem(ADMIN_ID,FEED_TYPE_ADMIN_MSG,$topic_id,true);
    return _make_ok_status();    
  }

  function AddTopicReply($user_id,$topic_id) {
    require_once('cchost_lib/ccextras/cc-topics.inc');
    $topics =& CCTopics::GetTable();
    $reply = $topics->QueryKeyRow($topic_id);
    $type = $reply['topic_thread'] ? FEED_TYPE_REPLY : FEED_TYPE_REPLY_REV;
    $table =& CCFeedTable::GetTable(); 
    $table->AddItem($user_id,$type,$topic_id,true);
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

    // Notify people who have been remixed
    $parent_owners = empty($parents)
                    ? array()
                    : array_unique(array_map(function($u) { return $u['upload_user']; }, $parents));

    foreach ($parent_owners as $remixee ) {
        $where = array(
            'feed_action' => $action(),
            'feed_user'   => $remixee,
            'feed_reason' => FEED_REASON_REMIXED
          );
        $feed->AddItem($where);
    }
    
    return _make_ok_status();
  }

  function _get_followers($user_id) {
    // Get followers of this uploaders
    require_once('mixter-lib/lib/user.php');
    $userlib    = new CCLibUser();
    $status     = $userlib->Followers($user_id);
    if( !$status->ok() ) {
      return array();
    }
    return array_map( function($i) { return CCUser::IDFromName($i); }, $status->data );
  }

  function PrePopulate() {
    require_once('mixter-lib/lib/feed-install.inc');
    UserFeedPrePopulate($this);
  }

}
?>