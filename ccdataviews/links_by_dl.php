<?/*
[meta]
    type = dataview
    name = links_by_dl
[/meta]
*/

function links_by_dl_dataview() 
{
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';

    $sql =<<<EOF
SELECT 
    upload_id, 
    IF( LENGTH(upload_name) > 23, CONCAT( SUBSTRING(upload_name,1,21), '...' ), upload_name ) as upload_name,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    IF( LENGTH(user_real_name) > 23, CONCAT( SUBSTRING(user_real_name,1,21), '...' ), user_real_name ) as user_real_name,
    user_name,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    upload_contest
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FILTER_FILES, CC_EVENT_FILTER_DOWNLOAD_URL )
                );
}

?>