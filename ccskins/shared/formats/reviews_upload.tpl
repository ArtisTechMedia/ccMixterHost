<?/*
[meta]
    type          = template_component
    desc          = _('List of upload reviews (use match=upload_id)')
    datasource    = topics
    dataview      = reviews_upload
    embedded      = 1
    valid_args    = match
    required_args = match
[/meta]
[dataview]
function reviews_upload_dataview() 
{
    $urlp = ccl('people') . '/';
    $turl = ccl('reviews') . '/';
    $user_avatar_col = cc_get_user_avatar_sql();

    // sh*t this is a hack to hack, need to rethink/redo how topic_upload foreign keys work

    $sql =<<<EOF
SELECT  topic.topic_id, ((COUNT(parent.topic_id)-1) * 30) AS margin, topic.topic_left, topic.topic_right,
        IF( COUNT(parent.topic_id) > 1, 1, 0 ) as is_reply, topic.topic_deleted,
        topic.topic_text as format_html_topic_text, 
        user.user_real_name, user.user_name, user.user_num_reviews,
        CONCAT( '$urlp', user.user_name ) as artist_page_url,
        DATE_FORMAT( topic.topic_date, '%a, %b %e, %Y @ %l:%i %p' ) as topic_date_format,
        user.user_image as user_image,
        {$user_avatar_col}
FROM cc_tbl_topics AS topic, 
     cc_tbl_topics AS parent,
     cc_tbl_user AS user
%where% AND (topic.topic_upload = %match%)
        AND (topic.topic_type <> 'collab') 
        AND (topic.topic_user = user_id) 
        AND (topic.topic_left BETWEEN parent.topic_left AND parent.topic_right)
GROUP BY topic.topic_id
ORDER BY (topic.topic_left)  asc
%limit%
EOF;

    $sql_count =<<<EOF
SELECT  COUNT(*)
FROM cc_tbl_topics AS topic, 
     cc_tbl_topics AS parent,
     cc_tbl_user AS user
%where% AND (topic.topic_upload = %match%)
        AND (topic.topic_type <> 'collab') 
        AND (topic.topic_user = user_id) 
        AND (topic.topic_left BETWEEN parent.topic_left AND parent.topic_right)
GROUP BY topic.topic_id
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array(
                                  CC_EVENT_FILTER_FORMAT, CC_EVENT_FILTER_REVIEWS )
                );
}
[/dataview]
*/?>
<!-- template reviews_upload -->
<? 
    if( empty($A['topic_upload']) && !empty($_GET['match']) )
        $A['topic_upload'] = sprintf('%0d',$_GET['match']);
    cc_query_fmt('noexit=1&nomime=1&f=html&t=list_files&ids=' . $A['topic_upload']); 
    // sorry about this...
    $user_name = 
      CCDatabase::QueryItem('SELECT user_name FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$A['topic_upload']);
    $topic_url = ccl('reviews',$user_name,$A['topic_upload']);
    $thread_ids = array();
?>
<link rel="stylesheet" href="%url(css/topics.css)%" title="Default Style" type="text/css" />
<table class="cc_topic_thread" cellspacing="0" cellpadding="0">
%loop(records,R)%
<tr>
%if_not_null(#R/topic_deleted)%
    <td colspan="2"><div class="topic_deleted med_light_color">%text(str_topic_deleted)%</div></td>
%else%
    <? $thread_ids[] = $R['topic_id']; ?>
    %if_not_null(#R/is_reply)%
    <td>&nbsp;<a name="%(#R/topic_id)%"></a></td>
    <td class="cc_topic_reply" style="padding-left:%(#R/margin)%px">
        <div style="border-left:600px solid transparent;clear:both;font-size:2px;height:3px;">.</div>
        <div class="cc_topic_reply_body  light_bg">
            <div class="cc_topic_reply_head med_light_bg">
                <a class="topic_permalink" href="%(#topic_url)%#%(#R/topic_id)%">%text(str_permalink)%</a> 
                %if_not_null(flagging)%
                    <a class="flag topic_flag" title="%text(str_flag_this_topic)%" href="%(home-url)%flag/topic/%(#R/topic_id)%">&nbsp;</a>
                %end_if%
                <!-- %if(is_admin)% L: %(#R/topic_left)% / R: %(#R/topic_right)% - %end_if% -->
                <a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a> %(#R/topic_date_format)% 
            </div>
            <div class="cc_topic_reply_text">%(#R/topic_text_html)%</div>
            <div class="cc_topic_commands" id="commands_%(#R/topic_id)%"></div>
        </div>
    </td>
    %else%
    <td class="cc_topic_head">
        <a name="%(#R/topic_id)%"></a>
        <a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a>
        <div><a href="%(#R/artist_page_url)%/reviews"><?= $T->String(array('str_reviews_n',$R['user_num_reviews'])); ?></a></div>
        <a href="%(#R/artist_page_url)%"><img src="%(#R/user_avatar_url)%" /></a>
    </td>
    <td class="cc_topic_body">
        <div style="border-left:600px solid transparent;font-size:2px;height:3px;">.</div>
        <div class="cc_topic_date dark_bg light_color" >
            <a class="topic_permalink med_color" href="%(#topic_url)%#%(#R/topic_id)%">%text(str_permalink)%</a> 
            %if_not_null(flagging)%
                <a class="flag topic_flag" title="%text(str_flag_this_topic)%" href="%(home-url)%flag/topic/%(#R/topic_id)%">&nbsp;</a>
            %end_if%
            <!-- %if(is_admin)% L: %(#R/topic_left)% / R: %(#R/topic_right)% - %end_if% -->
            %(#R/topic_date_format)%
        </div>
        <div class="cc_topic_text med_light_bg">%(#R/topic_text_html)%</div>
        <div class="cc_topic_commands med_light_bg" id="commands_%(#R/topic_id)%"></div>
    </td>
    %end_if%
%end_if%
</tr>
%end_loop%
</table>
%if_not_null(#thread_ids)%
<script type="text/javascript">
if( user_name )
{
    new userHookup('topic_cmds','upload=<?= $A['topic_upload'] ?>&type=review&ids=<?= join(',',$thread_ids) ?>');
}
</script>
%end_if%
