<?

require_once('mixter-lib/lib/feed-table.php');
require_once('mixter-lib/lib/status.inc');


function _feed_preload($sql,$table,$userid) {
  $rows = CCDatabase::QueryRows($sql);
  foreach( $rows as $R ) {
    $table->AddItem($userid,$R['type'],$R['id'],$R['date']);
  }
}

class CCLibFeed
{
  function APIFeed($username='') {



  }

  function PrePopulate($username='') {

    require_once('cchost_lib/cc-tags.php');

    if( empty($username) ) {
      $username = CCUser::CurrentUserName();
    }

    $table =& CCFeedTable::GetTable();

    $username = strtolower($username);
    $sql = "SELECT user_id, user_favorites FROM cc_tbl_user WHERE LOWER(user_name) = '{$username}'";
    $x  =CCDatabase::QueryRow($sql);
    $userid = $x['user_id'];
    $current_favs = $x['user_favorites']; 

    // FAVORITES' UPLOADS
    if( !empty($current_favs) ) {
      $favs = CCTag::TagSplit($current_favs);
      $favs = array_map(function($f) { return "'{$f}'"; }, $favs);
      $favs = implode(',', $favs);
      $sql =<<<EOF
        SELECT upload_id as id, upload_date as date, 'fup' as type
          FROM cc_tbl_uploads
          JOIN cc_tbl_user ON upload_user=user_id
          WHERE user_name IN ({$favs})
          ORDER BY upload_date DESC
          LIMIT 10
EOF;
      _feed_preload($sql,$table,$userid);
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
      _feed_preload($sql,$table,$userid);


    $sql =<<<EOF
      SELECT ratings_id as id, DATE_ADD(upload_date,INTERVAL 1 DAY) as date, 'rec' as type
        FROM cc_tbl_ratings
        JOIN cc_tbl_uploads ON ratings_upload=upload_id
        WHERE upload_user = {$userid}
        ORDER BY ratings_id DESC
        LIMIT 10;
EOF;
      _feed_preload($sql,$table,$userid);

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
    _feed_preload($sql,$table,$userid);

    $sql =<<<EOF
      SELECT topics.topic_id as id, topics.topic_date as date, 'rpy' as type
          FROM (  SELECT topic_left, topic_right
                      FROM cc_tbl_topics
                      WHERE topic_right - topic_left > 1 AND
                            topic_user = 9
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

    _feed_preload($sql,$table,$userid);

      $x = 'feed should be populated for ';
      CCDebug::PrintV($x);
  }
}
?>