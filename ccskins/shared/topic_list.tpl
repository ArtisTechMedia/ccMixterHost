<link rel="stylesheet" href="%url(css/topics.css)%" title="Default Style" type="text/css" />
<? $A['thread_ids'] = array(); ?>
<table class="cc_topic_thread" cellspacing="0" cellpadding="0">
%loop(records,R)%
<tr>
%if_not_null(#R/topic_deleted)%
    <td colspan="2"><div class="topic_deleted med_light_color">%text(str_topic_deleted)%</div></td>
%else%
    <? $A['thread_ids'][] = $R['topic_id']; ?>
    %if_not_null(#R/is_reply)%
    <td>&nbsp;<a name="%(#R/topic_id)%"></a></td>
    <td class="cc_topic_reply" style="padding-left:%(#R/margin)%px">
        <div style="border-left:600px solid transparent;font-size:2px;height:3px;">.</div>
        <div class="cc_topic_reply_body  light_bg">
            <div class="cc_topic_reply_head med_light_bg">
                <a class="topic_permalink light_color" href="%(#R/topic_url)%">%text(str_permalink)%</a> 
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
        <div><a href="%(#R/artist_page_url)%/topics"><?= $T->String(array('str_forum_posts_n',$R['user_num_posts'])); ?></a></div>
        <a href="%(#R/artist_page_url)%"><img src="%(#R/user_avatar_url)%" /></a>
        <? $role = cc_get_user_role($R['user_name']); ?>
        %if_not_null(#role)%
        <div class="user_role">%(#role)%</div>
        %end_if%

    </td>
    <td class="cc_topic_body">
        <div style="border-left:600px solid transparent;font-size:2px;height:3px;">.</div>
        %if_not_null(show_topic_name)%
            <div class="cc_topic_date dark_border"  style="border:1px solid" ><h3>%(#R/topic_name)%</h3></div>
        %end_if%
        <div class="cc_topic_date dark_bg light_color" >
            <a class="topic_permalink light_color" href="%(#R/topic_url)%">%text(str_permalink)%</a> 
            %if_not_null(flagging)%
                <a class="flag topic_flag" title="%text(str_flag_this_topic)%" href="%(home-url)%flag/topic/%(#R/topic_id)%">&nbsp;</a>
            %end_if%
            <!-- %if(is_admin)% L: %(#R/topic_left)% / R: %(#R/topic_right)% - %end_if% -->
            %(#R/topic_date_format)% 
        </div>
        %map(topic_id,#R/topic_id)%
        %call('topic_xlat_head.tpl/print_xlat_head')%
        <div id="topic_text_%(#R/topic_id)%" class="cc_topic_text med_light_bg">%(#R/topic_text_html)%</div>
        <div class="cc_topic_commands med_light_bg" id="commands_%(#R/topic_id)%"></div>
    </td>
    %end_if%
%end_if%
</tr>
%end_loop%
</table>
%call(prev_next_links)%
