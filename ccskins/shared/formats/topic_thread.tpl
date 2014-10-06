<?/*
[meta]
    type = template_component
    desc = _('Forum topic thread (set match=thread_id)')
    datasource = topics
    dataview = topic_thread
    embedded = 1
    required_args = match
[/meta]
[dataview]
function topic_thread_dataview() 
{
    $urlp = ccl('people') . '/';
    $turl = ccl('thread') . '/';
    $user_avatar_col = cc_get_user_avatar_sql();

    $sql =<<<EOF
SELECT  topic.topic_id, IF( COUNT(parent.topic_id) > 2, (COUNT(parent.topic_id) - 1) * 30, 0 ) AS margin,
        topic.topic_left, topic.topic_right, topic.topic_deleted,
        IF( COUNT(parent.topic_id) > 2, 1, 0 ) as is_reply, 
        topic.topic_text as format_html_topic_text, 
        user_real_name, user_name, user_num_posts,
        CONCAT( '$turl', topic.topic_thread, '#', topic.topic_id ) as topic_url,
        CONCAT( '$urlp', user_name ) as artist_page_url,
        DATE_FORMAT( topic.topic_date, '%a, %b %e, %Y @ %l:%i %p' ) as topic_date_format,
        {$user_avatar_col}
FROM cc_tbl_topics AS topic, 
     cc_tbl_topics AS parent,
     cc_tbl_user AS user
%where% AND (topic.topic_thread = %match%)
        AND (topic.topic_user = user_id) 
        AND (topic.topic_left BETWEEN parent.topic_left AND parent.topic_right)
GROUP BY topic.topic_id
ORDER BY (topic.topic_left)  asc
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_topics AS topic, 
     cc_tbl_topics AS parent,
     cc_tbl_user AS user
%where% AND (topic.topic_thread = %match%)
        AND (topic.topic_user = user_id) 
        AND (topic.topic_left BETWEEN parent.topic_left AND parent.topic_right)
GROUP BY topic.topic_id
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array(
                                  CC_EVENT_FILTER_FORMAT, CC_EVENT_FILTER_TOPICS)
                );
}
[/dataview]
*/
?>
<!-- template topic_thread (tpl)-->

%if_null(records)%
    <div>There's no topics here!</div>
    %return%
%end_if%

<script type="text/javascript">
var cc_show_xlat = function( orig_id, xlat_id, is_native)
{
    var url = query_url + 't=topic&f=html&ids=' + xlat_id;
    new Ajax.Updater( $('topic_text_' + orig_id), url, { method: 'get' } );
}

</script>
<div class="forum_cmds">
%loop(thread_commands,TC)%
    <a class="cc_gen_button" href="%(#TC/url)%"><span>%text(#TC/text)%</span></a>
%end_loop%
</div>
%call('topic_list.tpl')%
<script type="text/javascript">
if( window.user_name && userHookup )
{
    new userHookup('topic_cmds','ids=<?= join(',',$A['thread_ids']) ?>&thread=%(topic_thread_id)%');
}
</script>
