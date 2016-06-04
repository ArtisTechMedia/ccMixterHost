<?/*
[meta]
    type = profile
    desc = _('Detail user info')
    dataview = user_info
    datasource = user
    require_args = user
[/meta]
*/

function user_info_dataview()
{
    $avatar_sql = cc_get_user_avatar_sql();

    $sql =<<<EOF
        SELECT $avatar_sql, user_id, user_name, user_real_name,
            user_homepage, user_description as format_html_user_description,
            DATE_FORMAT( user_registered, '%a, %b %e, %Y' ) as user_date_format
        FROM cc_tbl_user
        %where% 
EOF;

    return array( 'sql' => $sql,
                  'e' => array( CC_EVENT_FILTER_FORMAT,CC_EVENT_FILTER_USER_INFO ) );

}
?>