<?/*
[meta]
    type = dataview
    name = links_by_ratings
[/meta]
*/

function links_by_ratings_dataview() 
{
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';

    $sql =<<<EOF
SELECT 
    upload_id,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    upload_name,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    user_real_name, upload_contest, user_name, upload_date,
    upload_num_scores
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( CC_EVENT_FILTER_DOWNLOAD_URL )
                );
}

?>
