<?/*
[meta]
    type = template_component
    desc = _('Upload reviews for insta-review popup')
    datasource = topics
    dataview = review_summary
    embedded = 1
[/meta]
[dataview]
function review_summary_dataview() 
{
    $sql =<<<EOF
SELECT topic_text as format_html_topic_text,  user_real_name, 
        DATE_FORMAT( topic_date, '%a, %b %e %Y %l:%i %p' ) as topic_date_format
        %columns% 
FROM cc_tbl_topics
JOIN cc_tbl_user ON topic_user = user_id
JOIN cc_tbl_uploads ON topic_upload=upload_id
%joins%
%where% AND (topic_type = 'review')
%order%
%limit%
EOF;

    return array( 'sql' => $sql,
                   'e'  => array(CC_EVENT_FILTER_FORMAT)
                );
}
[/dataview]
*/?>

%if_null(records)%
    %return%
%end_if%<!-- -->
<div class="review_pre">
%loop(records,R)%
<div class="review_pre_user">%(#R/user_real_name)%</div>
<div class="review_pre_date">%(#R/topic_date_format)%</div>
<div class="review_pre_text">%(#R/topic_text_html)%</div>
%end_loop%
</div>
