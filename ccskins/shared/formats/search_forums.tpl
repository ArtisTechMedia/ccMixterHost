<?/*
[meta]
    type     = search_results
    desc     = _('For forums search results (set type=forum)')
    example    = type=forum&t=search_forums&limit=30&search_type=any&s=charlie+rose
    datasource = topics
    dataview = search_forums
    embedded = 1
    required_args = type, search
[/meta]
[dataview]
function search_forums_dataview() 
{
    $cct = ccl('thread') . '/';

    $sql =<<<EOF
SELECT 
    user_real_name,
    CONCAT( forum_name, ' :: ', IF( LENGTH(topic_name) > 0, topic_name, forum_thread_name) ) as thread_name,
    CONCAT( '$cct', topic_thread, '#', topic_id) as topic_url,
    LOWER(CONCAT_WS(' ', topic_name, topic_text)) as qsearch
     %columns% 
FROM cc_tbl_topics
JOIN cc_tbl_forum_threads ON topic_thread=forum_thread_id
JOIN cc_tbl_forums ON forum_thread_forum=forum_id
JOIN cc_tbl_user ON topic_user=user_id
%joins%
%where%
%group%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_topics
JOIN cc_tbl_forum_threads ON topic_thread=forum_thread_id
JOIN cc_tbl_user ON topic_user=user_id
%where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( CC_EVENT_FILTER_SEARCH_RESULTS )
                );
}
[/dataview]
*/?>
<div  id="search_result_list">
%loop(records,R)%
   <div class="search_results_link" >
     <a href="%(#R/topic_url)%">%(#R/thread_name)% <!-- -->%text(str_by)%: %(#R/user_real_name)%</a>
   </div>
   <div class="search_results" >
    %(#R/qsearch)%
   </div>
%end_loop%
</div>
%call(prev_next_links)%
