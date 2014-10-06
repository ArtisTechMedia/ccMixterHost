<?/*
[meta]
    type       = ajax_component
    desc       = _('7 most recent reviewers (for menu)')
    datasource = topics
    dataview   = reviewers_recent
    embedded   = 1
[/meta]
[dataview]
function reviewers_recent_dataview() 
{
    $baseurl = ccl('reviews') . '/';

    $sql =<<<END
        SELECT CONCAT( '$baseurl', reviewee.user_name, '/', upload_id, '#', topic_id ) as revurl, 
            reviewer.user_real_name as reviewer_name, topic_type, topic_date, topic_left, topics.topic_user
        FROM cc_tbl_topics as topics
        JOIN cc_tbl_uploads ups      ON topics.topic_upload = ups.upload_id
        JOIN cc_tbl_user    reviewer ON topics.topic_user   = reviewer.user_id
        JOIN cc_tbl_user    reviewee ON ups.upload_user     = reviewee.user_id
        WHERE topic_type = 'review'
        ORDER BY topic_date DESC
        LIMIT 25
END;

    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FITLER_REVIEWERS_UNIQUE  )
                );
}
[/dataview]
*/?>

%loop(records,R)%
<li><a href="%(#R/revurl)%">%chop(#R/reviewer_name,13)%</a></li>
%end_loop%