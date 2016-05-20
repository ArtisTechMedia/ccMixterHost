<?/*
[meta]
    type = dataview
    name = userfeed
    datasource = feed
[/meta]
*/

/*

SELECT action_actor, action_verb, action_object_type, 
                action_sticky,  action_date,

                topic_id, topic_thread, topic_user,
                CASE feed_user
                    WHEN 9 THEN feed_reason
                    ELSE @FEED_REASON_FOLLOW
                END as reason,
                IF( action_object_type = @FEED_TYPE_FORUM_POST, topic_name, '') as topic_name,
                CASE action_object_type
                    WHEN @FEED_TYPE_REVIEW  THEN topic_upload
                    WHEN @FEED_TYPE_UPLOAD  THEN action_object
                    ELSE 0
                END as upload_id

            FROM cc_tbl_feed_action
            LEFT JOIN cc_tbl_feed    ON action_id=feed_action
            LEFT JOIN cc_tbl_topics  ON action_object=topic_id   AND 
                                         action_object_type IN (@FEED_TYPE_REVIEW,@FEED_TYPE_FORUM_POST)
            WHERE (action_sticky = 0) AND ((feed_user = '9'))             OR action_actor IN (SELECT follow_follows FROM cc_tbl_follow WHERE follow_user = 9)
            ORDER BY action_date DESC
            LIMIT 200 OFFSET 0;

            */

function userfeed_dataview($queryObj) 
{
    global $CC_SQL_DATE;        

    require_once('mixter-lib/lib/feed-types.inc');

    /*
    $actor_avatar = cc_get_user_avatar_sql('actor', 'actor_avatar_url');
    $artist_avatar = cc_get_user_avatar_sql('artist', 'artist_avatar_url');
    $poster_avatar = cc_get_user_avatar_sql('poster', 'poster_avatar_url');
    */

    $user_id = 0;
    if( !empty($queryObj->args['user']) ) {
        $user_id = CCUser::IDForName($queryObj->args['user']);
        $followers =<<<EOF
            OR action_actor IN (SELECT follow_follows FROM cc_tbl_follow WHERE follow_user = {$user_id})
EOF;
    }

    CCDatabase::Query('SELECT @FEED_REASON_FOLLOW := 5, @FEED_TYPE_UPLOAD := 1, @FEED_TYPE_REVIEW := 2, @FEED_TYPE_FORUM_POST := 3;');

    $sql =<<<EOF
    SELECT action_verb        as verb, 
            action_object_type as objtype, 
            action_sticky      as sticky,
            reason,
            CASE action_object_type
                WHEN @FEED_TYPE_FORUM_POST  THEN topic_name
                WHEN @FEED_TYPE_UPLOAD      THEN upl.upload_name
                WHEN @FEED_TYPE_REVIEW      THEN upl.upload_name
                ELSE ''
            END as name,
            topic_id, topic_thread, _d_.upload_id,
            DATE_FORMAT(action_date, '%W, %M %e, %Y @ %l:%i %p') as date_format,

            actor.user_name      as actor_user_name,
            actor.user_real_name as actor_real_name,

            artist.user_name      as user_name,
            artist.user_real_name as user_real_name

        FROM (

         SELECT action_actor, action_verb, action_object_type, 
                action_sticky,  action_date,

                topic_id, topic_thread, topic_user,
                CASE feed_user
                    WHEN {$user_id} THEN feed_reason
                    ELSE @FEED_REASON_FOLLOW
                END as reason,
                IF( action_object_type = @FEED_TYPE_FORUM_POST, topic_name, '') as topic_name,
                CASE action_object_type
                    WHEN @FEED_TYPE_REVIEW  THEN topic_upload
                    WHEN @FEED_TYPE_UPLOAD  THEN action_object
                    ELSE 0
                END as upload_id

            FROM cc_tbl_feed_action
            LEFT JOIN cc_tbl_feed    ON action_id=feed_action
            LEFT JOIN cc_tbl_topics  ON action_object=topic_id   AND 
                                         action_object_type IN (@FEED_TYPE_REVIEW,@FEED_TYPE_FORUM_POST)
            %where% ${followers}
            ORDER BY action_date DESC
            %limit%

        ) as _d_
             JOIN cc_tbl_user    actor    ON action_actor    = actor.user_id
        LEFT JOIN cc_tbl_uploads upl      ON _d_.upload_id   = upl.upload_id 
        LEFT JOIN cc_tbl_user    artist   ON upl.upload_user = artist.user_id
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*) 
        FROM cc_tbl_feed_action
            LEFT JOIN cc_tbl_feed ON action_id=feed_action
        %where% ${followers}
EOF;

    return array( 'sql' => $sql,
                   'sql_count' => $sql_count,
                   'e'  => array(  )
                );
}

?>
