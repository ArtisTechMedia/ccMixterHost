<?

/*
SELECT user_id FROM cc_tbl_user where user_name IN (
select concat("'",replace(user_favorites,",","','" ),"'") as str 
  from cc_tbl_user 
  where user_id = 9);



*/
function UserFeedPrePopulate($lib) {


  CCDatabase::Query('LOCK TABLES cc_tbl_follow WRITE, cc_tbl_feed WRITE, cc_tbl_feed_action WRITE, cc_tbl_user READ, cc_tbl_uploads READ, cc_tbl_ratings READ, cc_tbl_tree READ, cc_tbl_topics READ');

  CCDatabase::Query('TRUNCATE TABLE cc_tbl_follow');
  CCDatabase::Query('TRUNCATE TABLE cc_tbl_feed');
  CCDatabase::Query('TRUNCATE TABLE cc_tbl_feed_action');


  $sql =<<<EOF
    SELECT user_id, CONCAT("'",replace(user_favorites,',',"','"),"'") as str FROM cc_tbl_user WHERE user_favorites > '';
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

  $sql =<<<EOF
    SELECT upload_id FROM cc_tbl_uploads ORDER BY upload_date DESC LIMIT 50;
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

  $sql =<<<EOF
    SELECT ratings_user,ratings_upload,upload_user 
      FROM cc_tbl_ratings 
      JOIN cc_tbl_uploads ON ratings_upload=upload_id
      ORDER BY ratings_id DESC LIMIT 200;
EOF;
  $ratings = CCDatabase::QueryRows($sql);
  foreach ($ratings as $rating) {
    $lib->AddRecommends($rating,$rating);
  }
  
  $sql =<<<EOF
    select topic_user,topic_id,upload_user 
      from cc_tbl_topics 
      join cc_tbl_uploads on topic_upload=upload_id
      where topic_upload > 0 and topic_name not like '%(Reply)%' order by topic_date desc limit 200;

EOF;
  $reviews = CCDatabase::QueryRows($sql);
  foreach ($reviews as $review) {
    $lib->AddReview($review,$review);
  }
  
  CCDatabase::Query('UNLOCK TABLES');
}


?>