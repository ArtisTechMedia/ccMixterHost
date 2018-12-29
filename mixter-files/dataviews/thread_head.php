<?/*
[meta]
    type = dataview
    desc = _('Forum Thread Head')
    datasource = forum_thread
[/meta]
*/

/*
+---------------------+-------------------+
| forum_thread_id     | int(11) unsigned  |
| forum_thread_forum  | int(6) unsigned   |
| forum_thread_user   | int(11) unsigned  |
| forum_thread_oldest | int(11) unsigned  |
| forum_thread_newest | int(11) unsigned  |
| forum_thread_date   | datetime          |
| forum_thread_extra  | mediumtext        |
| forum_thread_sticky | int(2) unsigned   |
| forum_thread_closed | int(2) unsigned   |
| forum_thread_name   | mediumtext   
+---------------------+-------------------+
+-------------------+-----------------+
| forum_id          | int(6) unsigned |
| forum_post_access | int(4) unsigned |
| forum_read_access | int(4) unsigned |
| forum_weight      | int(4) unsigned |
| forum_name        | varchar(255)    |
| forum_description | varchar(255)    |
| forum_group       | int(4)          |
+-------------------+-----------------+

*/
function thread_head_dataview() 
{
      $avatar_sql = cc_get_user_avatar_sql();

    $sql =<<<EOF
SELECT  forum_thread_id, user_name, user_real_name, 
        forum_thread_date, forum_thread_name,
        forum_name, forum_id

FROM cc_tbl_forum_threads 
JOIN cc_tbl_forums ON forum_thread_forum = forum_id
JOIN cc_tbl_user ON forum_thread_user = user_id
%where% 
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => '',
                   'e'  => array()
                );
}

