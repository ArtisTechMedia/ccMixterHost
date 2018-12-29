<?/*
[meta]
    type = dataview
    name = userfeed
    datasource = feed
[/meta]
*/

function userfeed_dataview($queryObj) 
{
    global $CC_SQL_DATE;        

    // see 'mixter-lib/lib/feed-types.inc'

    $vars = "SELECT @FEED_TYPE_UPLOAD := 1, @FEED_TYPE_REVIEW := 2, @FEED_TYPE_FORUM_POST := 3, @FEED_TYPE_USER := 4";
    CCDatabase::Query($vars);

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
            action_date,

            feeder.user_name      as feeder_user_name,
            feeder.user_real_name as feeder_real_name,

            actor.user_name      as actor_user_name,
            actor.user_real_name as actor_real_name,

            artist.user_name      as user_name,
            artist.user_real_name as user_real_name

        FROM (

         SELECT action_actor, action_verb, action_object_type, 
                action_sticky,  action_date, action_object,

                topic_id, topic_thread, topic_user,
                feed_reason as reason,
                IF( action_object_type = @FEED_TYPE_FORUM_POST, topic_name, '') as topic_name,
                CASE action_object_type
                    WHEN @FEED_TYPE_REVIEW  THEN topic_upload
                    WHEN @FEED_TYPE_UPLOAD  THEN action_object
                    ELSE 0
                END as upload_id,

                feed_user

            FROM cc_tbl_feed_action
            LEFT OUTER JOIN cc_tbl_feed   ON action_id=feed_action
            LEFT       JOIN cc_tbl_topics ON action_object=topic_id   AND 
                                               action_object_type IN (@FEED_TYPE_REVIEW,@FEED_TYPE_FORUM_POST)
            %joins%                                         
            %where%
            ORDER BY action_date DESC
            %limit%

        ) as _d_
                   JOIN cc_tbl_user    actor    ON action_actor    = actor.user_id
        LEFT OUTER JOIN cc_tbl_user    feeder   ON _d_.feed_user   = feeder.user_id
        LEFT OUTER JOIN cc_tbl_uploads upl      ON _d_.upload_id   = upl.upload_id 
        LEFT OUTER JOIN cc_tbl_user    artist   ON artist.user_id = IF( action_object_type = @FEED_TYPE_USER, _d_.action_object, upl.upload_user)
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*) 
        FROM cc_tbl_feed_action
            LEFT JOIN cc_tbl_feed ON action_id=feed_action
            %joins%
        %where%
EOF;

    return array( 'sql' => $sql,
                   'sql_count' => $sql_count,
                   'e'  => array(  )
                );
}

?>
