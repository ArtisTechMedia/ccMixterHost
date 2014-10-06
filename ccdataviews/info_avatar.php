<?/*
[meta]
    type = dataview
    desc = _('Deep info (no remixes, user avatar)')
    name = info_avatar
[/meta]

*/

function info_avatar_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $avatar_sql = cc_get_user_avatar_sql();
    $lic_logo = cc_get_license_logo_sql('big');

    $sql =<<<EOF
SELECT 
    upload_banned, upload_tags, upload_published, 
    user_id, upload_user, upload_id, upload_name,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name,
    user_name,
    $avatar_sql,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    {$lic_logo},
    license_url, license_name,
    upload_contest
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
                   'e'  => array( CC_EVENT_FILTER_FILES,
                                  CC_EVENT_FILTER_DOWNLOAD_URL,
                                  CC_EVENT_FILTER_PLAY_URL,
                                  'hackavurl' ) // todo: remove this after beta!!
                );
}

?>
