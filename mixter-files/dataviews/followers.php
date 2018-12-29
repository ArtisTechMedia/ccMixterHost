<?/*
[meta]
    type = profile
    desc = _('Followers user info')
    dataview = followers
    datasource = user
    require_args = user
[/meta]
*/

function followers_dataview()
{
    $sql =<<<EOF
        SELECT user_name
        FROM cc_tbl_user
        %where% 
EOF;

    return array( 'sql' => $sql,
                  'e' => array( CC_EVENT_FILTER_USER_INFO ) );

}
?>