<?/*
[meta]
    name = topic_flat_list
    desc = _('Topic flat list')
    datasource = topics
[/meta]
*/
function topic_flat_list_dataview() 
{
    $urlp = ccl('people') . '/';
    $turl = ccl('thread') . '/';
    $user_avatar_col = cc_get_user_avatar_sql();

    $sql =<<<EOF
SELECT  topic.topic_name, topic.topic_id, 0 AS margin,
        topic.topic_left, topic.topic_right, topic.topic_deleted,
        0 as is_reply, 
        topic.topic_text as format_html_topic_text, 
        user_real_name, user_name, user_num_posts,
        CONCAT( '$turl', topic.topic_thread, '#', topic.topic_id ) as topic_url,
        CONCAT( '$urlp', user_name ) as artist_page_url,
        DATE_FORMAT( topic.topic_date, '%a, %b %e, %Y @ %l:%i %p' ) as topic_date_format,
        {$user_avatar_col}
FROM cc_tbl_topics AS topic, 
     cc_tbl_user AS user
%where% AND (topic.topic_user = user_id) 
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_topics AS topic
%where% 
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array(
                                  CC_EVENT_FILTER_FORMAT, CC_EVENT_FILTER_TOPICS)
                );
}
