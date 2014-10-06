<?/*
[meta]
    type = template_component
    desc = _('Browse topics left by a user')
    dataview = topics_user
    datasource = topics
    embedded = 1
    required_args = user
[/meta]
[dataview]
function topics_user_dataview() 
{
    $baseurl = ccl('thread') . '/';
    $baseus  = ccl('people') . '/';

    $sql =<<<END
        SELECT 
               CONCAT( '$baseurl', topic_thread, '#', topic_id ) as topic_url,
               CONCAT( '$baseus',  user_name ) as artist_page_url,
               topic_text as format_text_topic_text, topic_left,
               DATE_FORMAT( topic_date, '%a, %b %e %l:%i %p' ) as topic_date_format,
               forum_thread_name, forum_name
        FROM cc_tbl_topics
        JOIN cc_tbl_forum_threads  ON topic_thread = forum_thread_id
        JOIN cc_tbl_forums         ON forum_thread_forum = forum_id
        JOIN cc_tbl_user           ON topic_user = user_id
        %where% 
        %order%
        %limit%
END;

    $sql_count =<<<END
        SELECT COUNT(*)
        FROM cc_tbl_topics
        JOIN cc_tbl_user           ON topic_user = user_id
        %where% 
END;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( CC_EVENT_FILTER_FORMAT )
                );
}
[/dataview]
*/?>

<link rel="stylesheet" title="Default Style" type="text/css" href="%url(css/topics.css)%" />
<table class="cc_topic_user_table"  cellpadding="0" cellspacing="0">
  %loop(records,R)%
<tr>
    <td><span class="cc_topic_date">%(#R/topic_date_format)%</span></td>
    <td>
       <a class="topic_url" href="%(#R/topic_url)%">%(#R/forum_name)% :: %(#R/forum_thread_name)% </a>
    </td>
</tr>
<tr>
    <td></td>
    <td class="cc_topic_thumb"><a href="%(#R/topic_url)%"><span>%chop(#R/topic_text_plain,80)%</span></a></td>
<tr>
%end_loop%
</table>
%call(prev_next_links)%