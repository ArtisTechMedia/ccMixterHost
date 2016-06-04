<?

/*

  One time install of random events into feed

*/
function UserFeedPrePopulate($lib) {

  CCDatabase::Query('LOCK TABLES cc_tbl_follow WRITE, cc_tbl_feed WRITE, cc_tbl_feed_action WRITE, cc_tbl_user READ, cc_tbl_uploads READ, cc_tbl_ratings READ, cc_tbl_tree READ, cc_tbl_topics READ');

  CCDatabase::Query('TRUNCATE TABLE cc_tbl_follow');
  CCDatabase::Query('TRUNCATE TABLE cc_tbl_feed');
  CCDatabase::Query('TRUNCATE TABLE cc_tbl_feed_action');

  /*
    Convert user_favorites to cc_tbl_follow
  */
  $sql =<<<EOF
    SELECT user_id, CONCAT("'",REPLACE(user_favorites,',',"','"),"'") AS str FROM cc_tbl_user WHERE user_favorites > '';
EOF;
  $rows = CCDatabase::QueryRows($sql);
  foreach ($rows as $row) {
    $sql =<<<EOF
      INSERT INTO cc_tbl_follow (follow_user,follow_follows) 
        SELECT {$row['user_id']}, user_id 
          FROM cc_tbl_user 
          WHERE user_name IN ({$row['str']})
EOF;
    CCDatabase::Query($sql);
  }

  /*
    Add 'user follows other-user'
  */
  $sql = "SELECT DISTINCT follow_user FROM cc_tbl_follow LIMIT 30";
  $follows = implode(',', CCDatabase::QueryItems($sql) );
  $sql = "SELECT follow_user,follow_follows FROM cc_tbl_follow WHERE follow_user IN ({$follows}) GROUP BY follow_user";
  $follows = CCDatabase::QueryRows($sql);
  $count = 3;
  foreach ($follows as $F) {
    $d = strtotime("-${count} days");
    $date = date( 'Y-m-d H:i:s', $d );
    $lib->AddFollowing($F['follow_user'], $F['follow_follows'],$date);
    ++$count;
  }

  /*
    add topic replies
  */
  $sql = "SELECT * FROM cc_tbl_topics WHERE topic_type = 'reply' ORDER BY topic_date DESC LIMIT 100";
  $topics = CCDatabase::QueryRows($sql);
  require_once('cchost_lib/ccextras/cc-topics.inc');
  $topix =& CCTopics::GetTable();
  foreach ($topics as $topic) {
    list( $parent_id ) = $topix->GetParentTopic($topic['topic_id']);
    $parent = $topix->QueryKeyRow($parent_id);
    $lib->AddTopicReply($parent,$topic);
  }

  /*
    editorial picks
  */
  $sql = "SELECT upload_id FROM cc_tbl_uploads WHERE upload_tags LIKE '%,editorial_pick,%' ORDER BY upload_date DESC LIMIT 10";
  $upload_ids = CCDatabase::QueryItems($sql);
  foreach ($upload_ids as $upload_id) {
    $lib->AddEdPick($upload_id);
  }

  /*
    admin announcements
  */
  $sql =<<<EOF
    SELECT * FROM cc_tbl_topics 
      WHERE topic_forum = 1 AND topic_name NOT LIKE '%(Reply)%'  
      ORDER BY topic_date DESC 
      LIMIT 4;
EOF;
  $topics = CCDatabase::QueryRows($sql);
  foreach ($topics as $topic) {
    $lib->AddAdminMessage($topic);
  }

  /*
    uploads (including remixes)
  */
  $d = strtotime("-60 days");
  $upload_date = date( 'Y-m-d H:i:s', $d );
  $sql =<<<EOF
    SELECT upload_id 
      FROM cc_tbl_uploads 
      WHERE upload_published > 0 AND upload_banned < 1 AND upload_date > '{$upload_date}'
      ORDER BY upload_date DESC 
EOF;
  $upload_ids = CCDatabase::QueryItems($sql);
  foreach ($upload_ids as $upload_id) {
    $sql =<<<EOF
      SELECT upload_user
        FROM cc_tbl_tree
        JOIN cc_tbl_uploads ON tree_parent=upload_id
        WHERE tree_child = {$upload_id};
EOF;
    $parents = CCDatabase::QueryRows($sql);
    $lib->AddUpload($upload_id,CC_UF_NEW_UPLOAD,$parents);
  }

  /*
    ratings
  */
  $sql =<<<EOF
    SELECT ratings_user,ratings_upload,upload_user,upload_date 
      FROM cc_tbl_ratings 
      JOIN cc_tbl_uploads ON ratings_upload=upload_id
      WHERE upload_date > '{$upload_date}'
      ORDER BY ratings_id DESC 

EOF;
  $ratings = CCDatabase::QueryRows($sql);
  $count = 1;
  foreach ($ratings as $rating) {
    $d = strtotime("-${count} days");
    $upload = strtotime($rating['upload_date']);
    $date = date( 'Y-m-d H:i:s', max($d,$upload) );
    $lib->AddRecommends($rating,$rating,$date);
    $count++;
  }
  
  /*
    reviews
  */
  $sql =<<<EOF
    SELECT topic_user,topic_id,topic_date,upload_user 
      FROM cc_tbl_topics 
      JOIN cc_tbl_uploads on topic_upload=upload_id
      WHERE topic_upload > 0 and topic_name NOT LIKE '%(Reply)%' AND upload_date > '{$upload_date}'
      ORDER BY topic_date desc 
EOF;
  $reviews = CCDatabase::QueryRows($sql);
  foreach ($reviews as $review) {
    $lib->AddReview($review,$review);
  }
  
  CCDatabase::Query('UNLOCK TABLES');
}


?>