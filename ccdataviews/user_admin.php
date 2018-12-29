<?/*
[meta]
    type = dataview
    desc = _('Basic user info for data mining')
    name = user_basic
    datasource = user
[/meta]
*/
function user_admin_dataview() 
{
    $sql =<<<EOF
SELECT user_id, user_name, user_real_name,
       user_registered, user_homepage,
       user_num_uploads, user_num_reviews,
       user_extra, user_num_posts,
       user_last_known_ip, 
       SUBSTRING(user_description,LOCATE('http',user_description),30) as descurl,
       SUBSTRING(user_description,0,30) as shortdesc,
       substring(user_email,LOCATE('@',user_email)+1) as edomain
    FROM cc_tbl_user 
%joins%
%where%
%order%
%limit%
EOF;
    $sql_count =<<<EOF
SELECT COUNT(*)
    FROM cc_tbl_user 
%joins%
%where%
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array()
                );
}

