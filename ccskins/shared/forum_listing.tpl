<? if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?><!-- template forum_listing -->
<link href="%url(css/forum.css)%" rel="stylesheet" title="Default Style" type="text/css" />
<div class="forum_cmds">
%loop(forum_cmds,FC)%
    <a href="%(#FC/url)%" class="cc_gen_button"><span>%text(#FC/text)%</span></a>
%end_loop%
</div>
<br style="clear:both" />
<table class="forum_listing">
<tr>
<th class="med_border">%text(str_forum_topic)%</th>
<th class="med_border">%text(str_forum_author)%</th>
<th class="med_border">%text(str_forum_replies)%</th>
<th class="med_border">%text(str_forum_latest)%</th>
</tr>
%loop(threads,thread)%
 <tr>
   <td>
        %if_not_null(#thread/forum_thread_sticky)% <div class="forum_sticky_thread"> </div> %end_if%
        <a href="%(#thread/thread_url)%">%(#thread/oldest_topic_name)%</a>
   </td>
   <td>
        <a class="cc_user_link" href="%(#thread/author_url)%">%(#thread/author_real_name)%</a>
   </td>
   <td class="forum_replies">
        %(#thread/num_topics)%
   </td>
   <td>
      %if_not_null(#thread/newest_real_name)%
          <a href="%(#thread/newest_topic_url)%">%(#thread/newest_topic_date)%</a>
          <div>%text(str_by)%: %(#thread/newest_real_name)%</div>
       %end_if%
   </td>
</tr>
%end_loop%
</table>
%call(prev_next_links)%
