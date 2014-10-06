<?/*
[meta]
    type = ajax_component
    desc = _('Collab style topic thread (set match=collab_id)')
    example = t=collab_thread&f=html&type=collab&upload=123
    dataview = collab_thread
    datasource = topics
    valid_args = type,match
    required_args = match
    embedded = 1
[/meta]
[dataview]
function collab_thread_dataview() 
{
    $urlp = ccl('people') . '/';
    $turl = ccl('thread') . '/';
    $user_avatar_col = cc_get_user_avatar_sql();

    $sql =<<<EOF
SELECT  topic.topic_id, 
        topic.topic_text as format_html_topic_text, 
        user_real_name, user_name, user_num_posts,
        CONCAT( '$turl', topic.topic_thread, '#', topic.topic_id ) as topic_url,
        CONCAT( '$urlp', user_name ) as artist_page_url,
        DATE_FORMAT( topic.topic_date, '%a, %b %e, %Y @ %l:%i %p' ) as topic_date_format,
        {$user_avatar_col}
FROM cc_tbl_topics AS topic
JOIN cc_tbl_user AS user ON (topic.topic_user = user_id) 
%where% AND (topic.topic_upload = %match%) 
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
[/dataview]
*/?>

<link rel="stylesheet" href="%url(css/topics.css)%" title="Default Style" type="text/css" />

<table class="cc_topic_thread" cellspacing="0" cellspacing="0" >
%loop(records,R)%
<tr>
    <td  class="cc_topic_head">
        <a name="%(#R/topic_id)%"></a>
        <div><a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a></div>
        <a href="%(#R/artist_page_url)%"><img src="%(#R/user_avatar_url)%" /></a>
    </td>
    <td class="cc_topic_body">
        <div class="cc_topic_date dark_bg light_color" >%(#R/topic_date_format)% </div>
        <div class="cc_topic_text med_light_bg">%(#R/topic_text_html)%</div>
    </td>
</tr>
%end_loop%
</table>
