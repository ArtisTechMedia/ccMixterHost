<? /*
[meta]
    type     = dataview
    desc     = _('Multiple upload listing (wide)')
    datasource = uploads
[/meta]
*/
function upload_list_wide_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $urlr = ccl('reviews') . '/';
    $configs =& CCConfigs::GetTable();
    $chart = $configs->GetConfig('chart');
    $is_thumbs_up = empty($chart['thumbs_up']) ? '0' : '1';
    $ratings_on = empty( $chart['ratings'] ) ? '0' : '1';

    $user_avatar_col = cc_get_user_avatar_sql();
    $lic_logo = cc_get_license_logo_sql('small');

    $stream_url = url_args( ccl('api','query','stream.m3u'), 'f=m3u&ids=' );

    $sql =<<<EOF
SELECT 
    upload_id, 
    IF( LENGTH(upload_name) > 31, CONCAT( SUBSTRING(upload_name,1,29), '...'), upload_name ) as upload_name_chop,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    {$user_avatar_col},
    user_real_name, user_name, upload_score, upload_num_scores, upload_extra,
    $is_thumbs_up as thumbs_up, $ratings_on as ratings_enabled,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    {$lic_logo}, license_url, license_name,
    CONCAT( '$urlr', user_name, '/', upload_id ) as reviews_url,
    IF( upload_tags LIKE '%,audio,%', CONCAT( '$stream_url', upload_id ) , '' ) as stream_url,
    DATE_FORMAT( upload_date, '%a, %b %e, %Y @ %l:%i %p' ) as upload_date_format,
    upload_contest, upload_name,
    upload_num_remixes, upload_num_sources, upload_num_pool_sources, upload_num_pool_remixes
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
                   'name' => 'list_wide',
                   'e'  => array( CC_EVENT_FILTER_FILES,
                                  CC_EVENT_FILTER_UPLOAD_USER_TAGS, 
                                  CC_EVENT_FILTER_REMIXES_SHORT,
                                  CC_EVENT_FILTER_RATINGS_STARS,
                                  CC_EVENT_FILTER_DOWNLOAD_URL,
                                  CC_EVENT_FILTER_NUM_FILES,
                                  CC_EVENT_FILTER_PLAY_URL,
                                  CC_EVENT_FILTER_UPLOAD_LIST, )
                );
}

