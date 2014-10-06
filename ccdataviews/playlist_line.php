<?/*
[meta]
    type = dataview
    name = playlist_line
[/meta]
*/

function playlist_line_dataview() 
{
    $urlf             = ccl('files') . '/';
    $user_sql         = cc_fancy_user_sql('user_real_name','user');
    $ccp              = ccl('people') . '/';
    $browse_ref_url   = url_args( ccl('playlist','browse'), 'upload=' );

    $lic_logo = cc_get_license_logo_sql('small');

    $sql =<<<EOF
SELECT 
    upload_id, upload_name, {$user_sql}, user.user_name, 
    CONCAT( '$urlf', user.user_name, '/', upload_id ) as file_page_url,
    upload_num_playlists,
    CONCAT( '{$ccp}', user.user_name ) as artist_page_url,
    CONCAT('{$browse_ref_url}', upload_id ) as playlist_browse_url,
    {$lic_logo}, license_url, license_name,
    upload_contest
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user user ON upload_user = user.user_id
JOIN cc_tbl_licenses ON upload_license=license_id
%joins%
%where%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT count(*)
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license=license_id
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                   'sql_count' => $sql_count, 
                   'e'  => array( CC_EVENT_FILTER_FILES,
                                  CC_EVENT_FILTER_DOWNLOAD_URL,
                                  CC_EVENT_FILTER_PLAY_URL,
                                )
                );
}
?>
