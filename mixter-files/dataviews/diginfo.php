<?/*
[meta]
    type = dataview
    name = diginfo
    datasource = uploads
[/meta]
*/

function diginfo_dataview() 
{
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $avatar = cc_get_user_avatar_sql();

    $sql =<<<EOF
SELECT 
    upload_id, upload_name, upload_extra, upload_contest, user_name, upload_tags,
    upload_description as format_text_upload_description,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    license_url, license_name, license_tag,
    DATE_FORMAT( upload_date, '%a, %b %e, %Y @ %l:%i %p' ) as upload_date_format,
    {$avatar}
    %columns% 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
%joins%
%where%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*) 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                   'sql_count' => $sql_count,
                   'e'  => array(   CC_EVENT_FILTER_EXTRA,
                                    CC_EVENT_FILTER_FORMAT,
                                    CC_EVENT_FILTER_FILES )
                );
}

?>
