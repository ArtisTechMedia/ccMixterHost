<?/*
[meta]
    type = dataview
    name = links_by_dl
[/meta]
*/

function links_dl_dataview() 
{
    $urlf = ccl('files') . '/';

    $sql =<<<EOF
SELECT 
    upload_id, upload_name,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_name, upload_contest
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FILTER_FILES,CC_EVENT_FILTER_DOWNLOAD_URL )
                );
}

?>