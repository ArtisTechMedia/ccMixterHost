<?/*
[meta]
    type = dataview
    desc = _('Generic Topics')
    datasource = topics
[/meta]
*/
function topics_dataview() 
{
    $urlp = ccl('people') . '/';
    $turl = ccl('thread') . '/';

    $sql =<<<EOF
SELECT          
        topic_name, topic_type,
        topic_text as format_html_topic_text, 
        topic_text as format_text_topic_text, 
        topic_text,
        topic_id,
        user_real_name, user_name, 
        CONCAT( '$turl', topic.topic_thread, '#', topic.topic_id ) as topic_url,
        CONCAT( '$urlp', user_name ) as artist_page_url,
        DATE_FORMAT( topic.topic_date, '%a, %b %e, %Y @ %l:%i %p' ) as topic_date_format,
        topic_date
FROM cc_tbl_topics AS topic
JOIN cc_tbl_user AS user ON (topic.topic_user = user_id) 
%where% 
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_topics AS topic
JOIN cc_tbl_user AS user ON (topic.topic_user = user_id) 
%where%
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array(
                                  CC_EVENT_FILTER_FORMAT, CC_EVENT_FILTER_TOPICS)
                );
}

