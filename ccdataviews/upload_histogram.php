<?/*
[meta]
    type = dataview
    desc = _('All the information needed for upload history')
    name = upload_histogram
[/meta]
*/

function upload_histogram_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $avatar_sql = cc_get_user_avatar_sql();
    $lic_logo = cc_get_license_logo_sql('small');

    $sql =<<<EOF
SELECT 
    user_id, upload_id, upload_name, upload_extra, 
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name, user_name, $avatar_sql,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    {$lic_logo},
    license_url, license_name, license_permits, license_required, license_prohibits,
    DATE_FORMAT( upload_date, '%Y' ) as year, 
    DATE_FORMAT( upload_date, '%a, %b %e, %Y @ %l:%i %p' ) as upload_date,
    upload_contest
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
%joins%
%where%
LIMIT 1
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FILTER_FILES,
                                  CC_EVENT_FILTER_REMIXES_FULL,
                                  CC_EVENT_FILTER_DOWNLOAD_URL,
                            )
                );
}

?>
