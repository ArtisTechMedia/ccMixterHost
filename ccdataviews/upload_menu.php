<?/*
[meta]
    type = dataview
    name = upload_menu
[/meta]
*/
function upload_menu_dataview() 
{
    $configs =& CCConfigs::GetTable();
    $chart = $configs->GetConfig('chart');
    $is_thumbs_up = empty($chart['thumbs_up']) ? '0' : '1';
    $ratings_on = empty( $chart['ratings'] ) ? '0' : '1';
    
    $sql =<<<EOF
SELECT upload_id, upload_banned, upload_tags, upload_published, upload_contest,
       user_id, user_name, upload_user, upload_name, {$is_thumbs_up} as is_thumbs_up,
       {$ratings_on} as ratings_on, upload_num_scores, upload_extra
    FROM cc_tbl_uploads
    JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%order%
LIMIT 1
EOF;
    return array( 'sql' => $sql,
                   'e'  => array(
                                 CC_EVENT_FILTER_EXTRA,
                                 CC_EVENT_FILTER_FILES,
                                 CC_EVENT_FILTER_DOWNLOAD_URL,
                                 CC_EVENT_FILTER_UPLOAD_MENU)
                );
}

