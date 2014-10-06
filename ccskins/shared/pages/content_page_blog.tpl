<?/*
[meta]
    type = content_page
    desc = _('Blog Page (Single column, no boxes)')
    dataview = content_page_blog
[/meta]
*/

if( empty($A['content_page_textformat']) )
    $A['content_page_textformat'] = 'format';

?>

<link rel="stylesheet" href="%url(css/blog.css)%" title="Default Style" type="text/css" />

<table class="cc_topic_thread blog_thread" cellspacing="0" cellspacing="0" >
%loop(records,R)%
<tr>
    <td class="cc_topic_body">
        <div class="cc_topic_name">%(#R/topic_name)%</div>
        <div class="cc_topic_date dark_bg light_color" >%(#R/topic_date_format)% </div>
        <div class="cc_topic_text med_light_bg">
            <? switch($A['content_page_textformat']) {
                case 'format': print $R['topic_text_html']; break;
                case 'text':   print $R['topic_text_plain']; break;
                case 'raw':    print $R['topic_text']; break;
            } ?>
        </div>
        <div class="cc_topic_credit"><a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a></div>
        %if_not_null(topic_url)%
            <div class="cc_topic_permalink"><a href="%(topic_url)%#%(#R/topic_id)%">premalink</a></div>
        %end_if%
    </td>
</tr>
%end_loop%
</table>
