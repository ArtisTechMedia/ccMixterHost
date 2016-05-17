<?/*
[meta]
    type = dataview
    name = userfeed
    datasource = feed
[/meta]
*/

function userfeed_dataview() 
{
    global $CC_SQL_DATE;        

    $feed_date_format = "DATE_FORMAT(feed_date, '$CC_SQL_DATE') as feed_date_format";

    $avatar = cc_get_user_avatar_sql( '',
                                      'user_avatar_url',
                                      "SUBSTRING_INDEX(SUBSTRING_INDEX(_user_info, ':', 3), ':', -1)",
                                      "SUBSTRING_INDEX(SUBSTRING_INDEX(_user_info, ':', 2), ':', -1)"
                                      );

    $sql =<<<EOF

SELECT feed_id, feed_type, feed_seen, feed_sticky, item_name,
   SUBSTRING_INDEX(SUBSTRING_INDEX(_user_info, ':', 1), ':', -1) AS user_real_name,
   SUBSTRING_INDEX(SUBSTRING_INDEX(_user_info, ':', 2), ':', -1) AS user_name,
   {$avatar}, {$feed_date_format}
FROM
  (SELECT *,
        IF(   feed_type = 'fup' OR feed_type = 'fol' OR feed_type = 'edp', 
                (
                    SELECT CONCAT(user_real_name,':',user_name,':',user_image)
                        FROM cc_tbl_uploads 
                        JOIN cc_tbl_user ON upload_user=user_id 
                        WHERE upload_id=feed_key
                ),
                IF( feed_type = 'rev' OR feed_type = 'rpy',
                        (
                            SELECT CONCAT(user_real_name,':',user_name,':',user_image)
                                FROM cc_tbl_topics
                                JOIN cc_tbl_user ON topic_user=user_id
                                WHERE topic_id = feed_key
                        ),
                        IF( feed_type = 'rec',
                            (
                                SELECT CONCAT(user_real_name,':',user_name,':',user_image)
                                    FROM cc_tbl_ratings
                                    JOIN cc_tbl_user ON ratings_user=user_id
                                    WHERE ratings_id = feed_key
                            ),
                            IF( feed_type = 'rmx', 
                                (
                                    SELECT CONCAT(user_real_name,':',user_name,':',user_image)
                                        FROM cc_tbl_tree
                                        JOIN cc_tbl_uploads ON tree_child=upload_id
                                        JOIN cc_tbl_user ON upload_user=user_id
                                        WHERE tree_id = feed_key
                                ),
                                ''
                            )
                        )
                    )
                
            ) AS _user_info,
        IF(feed_type = 'fup' OR feed_type = 'fol' OR feed_type = 'edp', 
                (
                    SELECT upload_name
                        FROM cc_tbl_uploads 
                        WHERE upload_id=feed_key
                ),
                IF(feed_type = 'rev',
                        (
                            SELECT upload_name
                                FROM cc_tbl_topics
                                JOIN cc_tbl_uploads ON topic_upload=upload_id
                                WHERE topic_id = feed_key
                        ),
                        IF( feed_type = 'rec',
                            (
                                SELECT upload_name 
                                    FROM cc_tbl_ratings
                                    JOIN cc_tbl_uploads ON ratings_upload=upload_id
                                    WHERE ratings_id = feed_key
                            ),
                            IF( feed_type = 'rmx', 
                                (
                                    SELECT upload_name
                                        FROM cc_tbl_tree
                                        JOIN cc_tbl_uploads ON tree_parent=upload_id
                                        WHERE tree_id = feed_key
                                ),
                                IF( feed_type = 'adm',
                                    (
                                        SELECT topic_name
                                            FROM cc_tbl_topics
                                            WHERE topic_id = feed_key
                                    ),
                                    ''
                                )
                            )
                        )
                    )
            ) AS item_name
        FROM cc_tbl_feed 
        %where%
        ORDER BY feed_date DESC
        %limit%
        ) AS _fun_fun;
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*) 
FROM cc_tbl_feed
%where%
EOF;

    return array( 'sql' => $sql,
                   'sql_count' => $sql_count,
                   'e'  => array(  )
                );
}

?>
