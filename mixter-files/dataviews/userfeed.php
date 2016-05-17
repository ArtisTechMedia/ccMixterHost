<?/*
[meta]
    type = dataview
    name = userfeed
    datasource = feed
[/meta]
*/

function userfeed_dataview() 
{
    global $CC_SQL_DATE;        

    $feed_date_format = "feed_date, DATE_FORMAT(feed_date, '$CC_SQL_DATE') as feed_date_format";

    $avatar = cc_get_user_avatar_sql();

    $sql =<<<EOF

    (select feed_id, user_real_name, user_name, upload_name as item_name, {$avatar}, feed_type, feed_seen, {$feed_date_format} 
        from cc_tbl_feed 
        join cc_tbl_uploads on feed_key=upload_id
        join cc_tbl_user on upload_user=user_id 
        %where% AND (feed_type = 'fup' OR feed_type = 'edp')
        order by feed_date desc)
      union
    (select feed_id, user_real_name, user_name, upload_name as item_name, {$avatar}, feed_type, feed_seen, {$feed_date_format}
        from cc_tbl_feed
        join cc_tbl_topics on feed_key=topic_id
        join cc_tbl_user on topic_user=user_id
        join cc_tbl_uploads on topic_upload=upload_id
        %where% AND feed_type = 'rev'
        order by feed_date desc)
      union
    (select feed_id, user_real_name, user_name, upload_name as item_name, {$avatar}, feed_type, feed_seen, {$feed_date_format}
        from cc_tbl_feed
        join cc_tbl_ratings on feed_key=ratings_id
        join cc_tbl_uploads on ratings_upload=upload_id
        join cc_tbl_user on ratings_user=user_id
        %where% AND feed_type = 'rec' 
        order by feed_date desc)      
        union
      (select feed_id, user_real_name, user_name, src.upload_name as item_name, {$avatar}, feed_type, feed_seen, {$feed_date_format}
        from cc_tbl_feed
        join cc_tbl_tree on feed_key=tree_id
        join cc_tbl_uploads as src on tree_parent=src.upload_id
        join cc_tbl_uploads as rmx on tree_child=rmx.upload_id
        join cc_tbl_user on rmx.upload_user=user_id
        %where% AND feed_type = 'rmx' 
        order by feed_date desc)
        union
      (select feed_id, user_real_name, user_name, topic_name as item_name, {$avatar}, feed_type, feed_seen, {$feed_date_format}
        from cc_tbl_feed
        join cc_tbl_topics on feed_key=topic_id
        join cc_tbl_user on topic_user=user_id
        %where% AND (feed_type = 'rpy' OR feed_type = 'adm')
        order by feed_date desc)
      
order by feed_date desc
%limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*) 
FROM cc_tbl_feed
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                   'sql_count' => $sql_count,
                   'e'  => array(  )
                );
}

?>
