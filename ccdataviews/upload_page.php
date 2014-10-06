<?/*
[meta]
    type = dataview
    desc = _('All the information needed for uploads page')
    name = upload_page
[/meta]
*/

function upload_page_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $avatar_sql = cc_get_user_avatar_sql();
    $configs =& CCConfigs::GetTable();
    $chart = $configs->GetConfig('chart');
    $is_thumbs_up = empty($chart['thumbs_up']) ? '0' : '1';
    $ratings_on = empty( $chart['ratings'] ) ? '0' : '1';
    $lic_logo = cc_get_license_logo_sql('big');
    

    $sql =<<<EOF
SELECT 
    upload_banned, upload_tags, upload_published, 
    user_id, upload_user, upload_id, upload_name, upload_extra, 
    upload_description as format_html_upload_description,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    $is_thumbs_up as thumbs_up, $ratings_on as ratings_enabled,
    user_real_name,user_name, $avatar_sql, upload_num_scores, upload_score,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    {$lic_logo},
    IF( license_id IN ('cczero','publicdomain'), 1, 0 ) as is_waiver,
    license_url, license_name, license_permits, license_required, license_prohibits,
    DATE_FORMAT( upload_date, '%Y' ) as year, 
    DATE_FORMAT( upload_date, '%a, %b %e, %Y @ %l:%i %p' ) as upload_date,
    IF( upload_last_edit > '', DATE_FORMAT( upload_last_edit, '%a, %b %e, %Y @ %l:%i %p' ), 0 ) as upload_last_edit,
    upload_contest,
    collab_upload_collab as collab_id
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
                                  CC_EVENT_FILTER_ED_PICK_DETAIL,
                                  CC_EVENT_FILTER_COLLAB_CREDIT,
                                  CC_EVENT_FILTER_RATINGS_STARS,
                                  CC_EVENT_FILTER_FORMAT,
                                  CC_EVENT_FILTER_REMIXES_FULL,
                                  CC_EVENT_FILTER_DOWNLOAD_URL,
                                  CC_EVENT_FILTER_MACROS, 
                                  CC_EVENT_FILTER_PLAY_URL,
                                  CC_EVENT_FILTER_UPLOAD_PAGE,
                            )
                );
}

?>
