<?/*
[meta]
    type = dataview
    desc = _('Poccast Topics Info')
    datasource = topics
[/meta]
*/
require_once('ccdataviews/topics.php');

function topics_podinfo_dataview() 
{
    $urlp = ccl('people') . '/';
    $turl = url_args(ccl('podcast'), 'topic=');
    $avatar_sql = cc_get_user_avatar_sql();
    $slug = cc_get_topic_name_slug();
    
    

    $sql =<<<EOF
SELECT          
        topic_name, topic_type, topic_text,
        topic_id,
        user_real_name, $avatar_sql,
        CONCAT( '$turl', {$slug} ) as topic_url,
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
                   'e'  => array(CC_EVENT_FILTER_PODCAST_INFO)
                );
    
}

