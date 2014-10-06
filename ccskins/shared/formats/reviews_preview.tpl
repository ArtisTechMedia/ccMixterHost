<?/*
[meta]
    type = template_component
    desc = _('Preview of upload reviews')
    datasource = topic
    dataview = review_previews
    embedded = 1
[/meta]
[dataview]
function review_previews_dataview() 
{
    $turl = ccl('reviews') . '/';

    $sql =<<<EOF
SELECT topic_text              as format_text_topic_text, topic_upload,
       reviewee.user_real_name as reviewee_user_real_name,
       reviewee.user_name as reviewee_user_name,
       IF( LENGTH(reviewer.user_real_name) > 15, 
           CONCAT(SUBSTRING(reviewer.user_real_name,1,13),'...'),reviewer.user_real_name)  as reviewer_user_real_name,
       CONCAT( '$turl', reviewee.user_name, '/',  topic_upload, '#', topic_id ) as topic_url
        %columns% 
FROM cc_tbl_topics
JOIN cc_tbl_uploads as uploads  ON topic_upload = upload_id
JOIN cc_tbl_user    as reviewer ON topic_user   = reviewer.user_id
JOIN cc_tbl_user    as reviewee ON upload_user  = reviewee.user_id
%joins%
%where% AND (uploads.upload_id = %match%) AND (topic_type = 'review')
%order%
LIMIT 5
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
%text(str_recent_reviews)%
<ul>
%loop(records,R)%
<li>%(#R/reviewer_user_real_name)% <a href="%(#R/topic_url)%">%chop(#R/topic_text_plain,40)%</a></li>
%end_loop%
</ul>
%map(#upload_id,records/0/topic_upload)%
%map(#reviewee,records/0/reviewee_user_name)%
<div style="width:25%"><a class="cc_gen_button" href="%(home-url)%reviews/%(#reviewee)%/%(#upload_id)%"><span>%text(str_read_all)%</span></a></div>
