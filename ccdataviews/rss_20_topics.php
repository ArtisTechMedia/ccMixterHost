<?/*
[meta]
    type = dataview
    name = rss_20_topics
    datasource = topics
    desc = _('For RSS 2.0 Topic Feed')
[/meta]
*/

function rss_20_topics_dataview($queryObj)
{
    global $CC_GLOBALS;
    
    $TZ = ' ' . CCUtil::GetTimeZone();


    $ccr = ccl('reviews') . '/';
    $cct = ccl('thread') . '/';
    $cctopic = ccl('topics','view') . '/';

    // NOTE add to this list when you get to blogs, etc.
    if( empty($queryObj->args['page']) )
    {
        if( !empty($queryObj->args['thread']) )
        {
            $ccp_sql = "CONCAT('{$cct}',topic_thread, '#', topic_id)";
        }
        else
        {
            // this is bogus.. but we may be stuck in the case of replies to reviews (?)
            $ccp_sql = "CONCAT('{$cctopic}',topic_id)";
        }
    }
    else
    {
        $slug = cc_get_topic_name_slug();
        $ccp = url_args(ccl($queryObj->args['page']),'topic=');
        $ccp_sql = "CONCAT('{$ccp}', {$slug} )";
    }

    // Thu, 27 Dec 2007 09:28:38 PST
    // %a,  %d %b %Y    %T

    $Y = date('Y') + 1;

    // There is a moment between SVN update and ?update=1 where the feed for the 
    // home page will break because the 'topic_format/nsfw' fields aren't actually
    // part of the table yet. This is prevented with this line:
    
    $topic_format = empty($CC_GLOBALS['v_5_1_topic_format']) ? '' : 'topic_format,';
    $topic_nsfw   = empty($CC_GLOBALS['v_5_1_topic_nsfw'])   ? '' : 'topic_nsfw,';
    
    $sql =<<<EOF
        SELECT topic_date, author.user_real_name, {$topic_format} {$topic_nsfw}
            topic_text as format_text_topic_text, 
            topic_text as format_html_topic_text,
            
        CONCAT( DATE_FORMAT(topic_date,'%a, %d %b %Y %T'), '$TZ' ) as rss_pubdate,

        IF( LENGTH(forum_name) > 0,
            CONCAT( forum_name, ' :: ', IF( LENGTH(topic_name) > 0, topic_name, forum_thread_name) ),
            topic_name ) as topic_name,
        IF( topic_type = 'review', 
            CONCAT('{$ccr}',reviewee.user_name,'/',topic_upload,'#',topic_id),
            IF( topic_type = 'forum',
              CONCAT('{$cct}',topic_thread, '#', topic_id),
              {$ccp_sql}              
              )
          ) as topic_permalink
        FROM cc_tbl_topics
        JOIN cc_tbl_user author ON topic_user=author.user_id
        LEFT OUTER JOIN cc_tbl_forum_threads ON topic_thread=forum_thread_id
        LEFT OUTER JOIN cc_tbl_forums ON forum_thread_forum=forum_id
        LEFT OUTER JOIN cc_tbl_uploads ups ON topic_upload=upload_id
        LEFT OUTER JOIN cc_tbl_user reviewee ON ups.upload_user=reviewee.user_id
        %joins%
        %where% and (topic_date < '${Y}') AND (topic_deleted = 0)
        %order%
        %limit%
EOF;

    return array(   'sql' => $sql,
                    'e' => array(
                            CC_EVENT_FILTER_FORMAT,
                            ) );
}
