<?/*
[meta]
    type = topic_format
    desc = _('Content topic links (set type=topic_type')
    dataview = topic_dump
    embedded = 1
    datasource = topics
    required_args = type
[/meta]
[dataview]
function topic_dump_dataview()
{
    $sql =<<<EOF
        SELECT topic_name, topic_text as format_html_topic_text
        %columns%
        FROM cc_tbl_topics
        %joins%
        %where%
        %order%
        %limit%
EOF;
    $sql_count =<<<EOF
        SELECT COUNT(*)
        %columns%
        FROM cc_tbl_topics
        %joins%
        %where%
EOF;
        return array( 'sql' => $sql, 
                      'sql_count' => $sql_count,
                      'e' => array( CC_EVENT_FILTER_FORMAT, ) );
}
[/dataview]
*/?>

<div class="topic_dump">
%loop(records,R)%
<div><span class="topic_dump_name">%(#R/topic_name)%</span><span class="topic_dump_text">%(#R/topic_text_html)%</span></div>
%end_loop%
</div>
