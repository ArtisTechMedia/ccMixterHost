<?/*
[meta]
    desc     = _('For seeing who recommends a given upload (match=upload_id)')
    type     = template_component
    required_args = match
    datasource = ratings
    dataview = recc
    embedded = 1
[/meta]
[dataview]
function recc_dataview()
{
    $ccp = ccl('people') . '/';

    $sql =<<<EOF
    SELECT 
        IF( rater.user_name = REPLACE(rater.user_real_name,' ','_'), 
            rater.user_real_name, 
            CONCAT( rater.user_real_name, ' (', rater.user_name, ')' ) ) as fancy_user_name,
        CONCAT( '{$ccp}', rater.user_name, '/recommends' ) as rater_page_url
    %columns%
FROM cc_tbl_ratings 
JOIN cc_tbl_user as rater ON ratings_user = rater.user_id
JOIN cc_tbl_uploads ON ratings_upload = upload_id
JOIN cc_tbl_user as uploader ON upload_user = uploader.user_id
%joins%
%where% AND (ratings_upload = %match%) AND (uploader.user_id <> rater.user_id)
%order%
%limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*)
FROM cc_tbl_ratings 
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array()
                );
}
[/dataview]
*/
?>

%if_null(records)%
    %return%
%end_if%<!-- -->
%text(str_recommended_by)%: 
%loop(records,R)%
<a href="%(#R/rater_page_url)%">%(#R/fancy_user_name)%</a>%if_not_last(#R)%, %end_if%
%end_loop%
</ul>