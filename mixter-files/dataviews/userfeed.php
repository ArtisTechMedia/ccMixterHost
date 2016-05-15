<?/*
[meta]
    type = dataview
    name = userfeed
    datasource = userfeed
[/meta]
*/

function userfeed_dataview() 
{
    /*
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    */
    $avatar = cc_get_user_avatar_sql();

    $sql =<<<EOF

    (select user_real_name, upload_name as name, user_image, feed_type, feed_date 
        from cc_tbl_feed 
        join cc_tbl_uploads on feed_key=upload_id
        join cc_tbl_user on upload_user=user_id 
        where feed_type = 'fup' 
        order by feed_date desc)
      union
    (select user_real_name, upload_name as name, user_image, feed_type, feed_date
        from cc_tbl_feed
        join cc_tbl_topics on feed_key=topic_id
        join cc_tbl_user on topic_user=user_id
        join cc_tbl_uploads on topic_upload=upload_id
        where feed_type = 'rev' 
        order by feed_date desc)
      union
    (select user_real_name, upload_name as name, user_image, feed_type, feed_date
        from cc_tbl_feed
        join cc_tbl_ratings on feed_key=ratings_id
        join cc_tbl_uploads on ratings_upload=upload_id
        join cc_tbl_user on ratings_user=user_id
        where feed_type = 'rec' 
        order by feed_date desc)      
        union
      (select user_real_name, src.upload_name as name, user_image, feed_type, feed_date
        from cc_tbl_feed
        join cc_tbl_tree on feed_key=tree_id
        join cc_tbl_uploads as src on tree_parent=src.upload_id
        join cc_tbl_uploads as rmx on tree_child=rmx.upload_id
        join cc_tbl_user on rmx.upload_user=user_id
        where feed_type = 'rmx' 
        order by feed_date desc)
        union
      (select user_real_name, topic_name as name, user_image, feed_type, feed_date
        from cc_tbl_feed
        join cc_tbl_topics on feed_key=topic_id
        join cc_tbl_user on topic_user=user_id
        where feed_type = 'rpy' 
        order by feed_date desc)
      order by feed_date desc;


%joins%
%where%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*) 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                   'sql_count' => $sql_count,
                   'e'  => array(   CC_EVENT_FILTER_EXTRA,
                                    CC_EVENT_FILTER_FORMAT,
                                    CC_EVENT_FILTER_FILES )
                );
}

?>
