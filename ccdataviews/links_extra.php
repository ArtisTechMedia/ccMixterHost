<?/*
[meta]
    type = dataview
    name = links_extra
[/meta]
*/

function links_extra_dataview() 
{
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';

    $sql =<<<EOF
SELECT 
    upload_id, upload_name, upload_extra, upload_num_playlists,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name,
    CONCAT( '$urlp', user_name ) as artist_page_url
     %columns% 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array(   CC_EVENT_FILTER_EXTRA, CC_EVENT_FILTER_ED_PICK )
                );
}

?>