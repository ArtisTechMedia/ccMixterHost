<?

require_once('mixter-lib/lib/feed-table.php');
require_once('mixter-lib/lib/status.inc');


define('ADMIN_ID', 1);
define('ADMIN_FORUM', 1);

define('FEED_INVALID_ID', 'invalid feed id');

class CCLibFeed
{
  function CCLibFeed() {
  }

  function MarkItemAsSeen($feed_id) {
    $table =& CCFeedTable::GetTable(); 
    if( empty($feed_id) || !$table->KeyExists($feed_id) ) {
      return _make_err_status(FEED_INVALID_ID);
    } 
    $user = $table->QueryItemFromKey('feed_user',$feed_id);
    if( $user != CCUser::CurrentUser() ) {
      return _make_err_status(USER_UNKNOWN_USER);
    }
    $table->MarkAsSeen($feed_id);
    return _make_ok_status();    
  }

  function AddEdPick($upload_id) {
    $table =& CCFeedTable::GetTable(); 
    $table->AddItem(ADMIN_ID,FEED_TYPE_EDPICK,$upload_id,true);
    return _make_ok_status();
  }

  function AddRecommends($user_id, $ratings_id) {
    $table =& CCFeedTable::GetTable(); 
    $table->AddItem($user_id,FEED_TYPE_RECOMMEND,$ratings_id);
    return _make_ok_status();    
  }

  function AddReview($user_id,$review_topic_id) {
    $table =& CCFeedTable::GetTable(); 
    $table->AddItem($user_id,FEED_TYPE_REVIEW,$review_topic_id);
    return _make_ok_status();        
  }

  function AddAdminMessage($topic_id) {
    $table =& CCFeedTable::GetTable(); 
    $table->AddItem(ADMIN_ID,FEED_TYPE_ADMIN_MSG,$topic_id,true);
    return _make_ok_status();    
  }

  function AddItemsBasedOnUpload($upload_id,$op,$parents) {
    require_once('mixter-lib/lib/user.php');

    $user_id    = CCDatabase::QueryItem("SELECT upload_user FROM cc_tbl_uploads WHERE upload_id={$upload_id}");
    $userlib    = new CCLibUser();
    $status     = $userlib->Followers($user_id);

    if( !$status->ok() ) {
      return $status;
    }

    $followers  = array_map( CCUser::IDForName, $status->data );
    $parent_owners = empty($parents)
                    ? array()
                    : array_unique(array_map(function($u) { return $u['upload_user']; }, $parents));

    $table =& CCFeedTable::GetTable(); 

    foreach ($followers as $user_id) { 
        if( in_array($user_id, $parent_owners) ) {
            continue;
        }
        $type = '';
        if( $op === CC_UF_NEW_UPLOAD ) {
            $type = FEED_TYPE_FOLLOWER_UPLOAD;
        } else if( $op === CC_UF_FILE_ADD || $op === CC_UF_FILE_REPLACE ) {
            $type = FEED_TYPE_FOLLOWER_UPDATE;
        }
        if( $type ) {
          $table->AddItem($user_id,$type,$upload_id);
        }

    }
    foreach ($parent_owners as $remixee ) {
        $table->AddItem($remixee,FEED_TYPE_REMIXED);
    }
    return _make_ok_status();
  }

  function _feed_preload ($sql,$table,$userid,$sticky=false) {
    $rows = CCDatabase::QueryRows($sql);
    foreach( $rows as $R ) {
      if( !$table->HasItem($userid,$R['type'],$R['id']) ) {
        $id = $table->AddItem($userid,$R['type'],$R['id'],$R['date']);
        if( $sticky ) {
          $table->MarkAsSticky($id,$sticky);
        }
      }
    }
  }

  function _admin_feed_preload($table) {

    if( empty(CCDatabase::QueryItem("SELECT COUNT(*) FROM cc_tbl_feed WHERE feed_sticky = 1")) ) {
      $sql =<<<EOF
        SELECT upload_id as id, upload_date as date, 'edp' as type
          FROM cc_tbl_uploads
          WHERE upload_tags LIKE '%,editorial_pick,%'
          ORDER BY upload_date DESC
          LIMIT 10
EOF;
      $this->_feed_preload($sql,$table,ADMIN_ID,true);

      $forum = ADMIN_FORUM;
      $sql =<<<EOF
        SELECT topic_id as id, topic_date as date, 'adm' as type
            FROM cc_tbl_topics
            JOIN cc_tbl_forums ON topic_forum=forum_id
            WHERE topic_forum = {$forum} AND 
                  topic_name NOT LIKE '%(Reply)%'
            ORDER BY topic_date DESC
            LIMIT 4
EOF;
      $this->_feed_preload($sql,$table,ADMIN_ID,true);

    }

  }

  function PrePopulate($username='') {

    require_once('cchost_lib/cc-tags.php');

    if( empty($username) ) {
      $username = CCUser::CurrentUserName();
    }

    $username = strtolower($username);
    $sql = "SELECT user_id, user_favorites FROM cc_tbl_user WHERE LOWER(user_name) = LOWER('{$username}')";
    $x  =CCDatabase::QueryRow($sql);

    $userid = $x['user_id'];

    $table =& CCFeedTable::GetTable();

    $this->_admin_feed_preload($table);

    $crows = (int)$table->CountRows(array('feed_user' => $userid));
    if( $crows > 5 ) {
      return;
    }


    $current_favs = $x['user_favorites']; 


    // FAVORITES' UPLOADS
    if( !empty($current_favs) ) {
      $favs = CCTag::TagSplit($current_favs);
      $favs = array_map(function($f) { return "'{$f}'"; }, $favs);
      $favs = implode(',', $favs);
      $sql =<<<EOF
        SELECT upload_id as id, upload_date as date, 'fol' as type
          FROM cc_tbl_uploads
          JOIN cc_tbl_user ON upload_user=user_id
          WHERE user_name IN ({$favs})
          ORDER BY upload_date DESC
          LIMIT 10
EOF;
      $this->_feed_preload($sql,$table,$userid);
    }

    // reviews 
    $sql =<<<EOF
      SELECT topic_id as id, topic_date as date, 'rev' as type
        FROM cc_tbl_topics
        JOIN cc_tbl_uploads ON topic_upload=upload_id
        WHERE topic_type = 'review' AND
              upload_user = {$userid}
        ORDER BY topic_date DESC
        LIMIT 10
EOF;
      $this->_feed_preload($sql,$table,$userid);


    $sql =<<<EOF
      SELECT ratings_id as id, DATE_ADD(upload_date,INTERVAL 1 DAY) as date, 'rec' as type
        FROM cc_tbl_ratings
        JOIN cc_tbl_uploads ON ratings_upload=upload_id
        WHERE upload_user = {$userid}
        ORDER BY ratings_id DESC
        LIMIT 10;
EOF;
      $this->_feed_preload($sql,$table,$userid);

        $x =<<<EOF

EOF;

    $sql =<<<EOF
      SELECT DISTINCT tree_id as id, rmx.upload_date as date, 'rmx' as type
        FROM cc_tbl_tree 
        JOIN cc_tbl_uploads as src ON tree_parent=src.upload_id 
        JOIN cc_tbl_uploads as rmx ON tree_child=rmx.upload_id
        WHERE src.upload_user = {$userid} 
        ORDER BY rmx.upload_date DESC 
        LIMIT 10;

EOF;
    $this->_feed_preload($sql,$table,$userid);

    $sql =<<<EOF
      SELECT topics.topic_id as id, topics.topic_date as date, 'rpy' as type
          FROM (  SELECT topic_left, topic_right
                      FROM cc_tbl_topics
                      WHERE topic_right - topic_left > 1 AND
                            topic_user = {$userid}
                      ORDER BY topic_date DESC
                      LIMIT 3
                      ) 
                utopics
          JOIN cc_tbl_topics topics
            ON utopics.topic_left = topics.topic_left-1
          JOIN cc_tbl_user 
            ON topics.topic_user = user_id
          LIMIT 10;
EOF;

    $this->_feed_preload($sql,$table,$userid);

  }
}
?>