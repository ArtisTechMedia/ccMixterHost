<?/*
[meta]
    type = dataview
    desc = _('Simple Topic Info')
    datasource = topics
[/meta]
*/
function topic_info_dataview() 
{

    $sql =<<<EOF
SELECT          
        topic_name, topic_type,
        topic_id,
        user_real_name, user_name, 
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
                   'e'  => array()
                );
}

