<?/*
[meta]
    type = topic_format
    desc = _('New blurbs (set type=news')
    dataview = news_blurbs
    embedded = 1
    datasource = topics
    required_args = type
[/meta]
[dataview]
function news_blurbs_dataview()
{
    $author_url = ccl('people') . '/';
    
    $sql =<<<EOF
        SELECT topic_name, DATE_FORMAT(topic_date, '%b %e, %y') as date,
           topic_text as format_html_topic_text,
           CONCAT('$author_url',user_name) as author_url, user_real_name
        %columns%
        FROM cc_tbl_topics
        JOIN cc_tbl_user ON topic_user = user_id
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

<div class="news_topic_list">
%loop(records,R)%
<div class="news_div">
  <h4 class="news_title">%(#R/topic_name)%</h4>
  <div class="byline"><i>%text(str_news_posted_by)% <a href="%(#R/author_url)%">%(#R/user_real_name)%</a> %(#R/date)%</i></div>
  <span class="news_content">%(#R/topic_text_html)%</span>
  %if_not_last(#R)%<hr class="news_divider" />%end_if%
</div>
%end_loop%
</div>
