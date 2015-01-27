<?/*
[meta]
    type = dataview
    desc = _('Deep info for upload details')
    name = info
[/meta]

*/

function info_dataview() 
{
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $lic_logo = cc_get_license_logo_sql('big');

    $sql =<<<EOF
SELECT 
    user_id, upload_user, upload_id, upload_name, upload_extra, 
    upload_description as format_text_upload_description, upload_tags,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    {$lic_logo},
    license_url, license_name,
    DATE_FORMAT( upload_date, '%a, %b %e, %Y @ %l:%i %p' ) as upload_date,
    collab_upload_collab as collab_id, upload_contest, user_name
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
LEFT OUTER JOIN cc_tbl_collab_uploads ON upload_id = collab_upload_upload
%joins%
%where% 
LIMIT 1
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FILTER_FILES,
                                  CC_EVENT_FILTER_UPLOAD_TAGS,
                                  CC_EVENT_FILTER_COLLAB_CREDIT,
                                  CC_EVENT_FILTER_EXTRA,
                                  CC_EVENT_FILTER_FORMAT,
                                  CC_EVENT_FILTER_REMIXES_FULL)
                );
}

?>
