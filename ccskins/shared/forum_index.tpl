<? if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?><!-- template forum_index -->
<link href="%url(css/forum.css)%" rel="stylesheet" title="Default Style" type="text/css" />

<div class="forum_index">
%loop(forums,group)%

    <div class="forum_group">
        <div class="forum_group_name">%text(#group/forum_group_name)%</div>
        %loop(#group/forums,forum)%
            <div class="forum"  style="position:relative;top:0px;left:0px;">
                <div class="forum_name" >
                    <a href="%(home-url)%forums/%(#forum/forum_id)%">%text(#forum/forum_name)%</a>
                </div>
                <div class="forum_description" >%text(#forum/forum_description)%</div>
                <div class="forum_stats">
                    <div>%text(str_forum_num_threads)%: %(#forum/num_threads)%</div>
                    <div>%text(str_forum_num_posts)%: %(#forum/num_posts)%</div>
                </div>
                <div class="forum_last_post">
                  %if_not_null(#forum/latest_post/user_real_name)%<!-- -->
                     %text(str_forum_latest_post)%: 
                      <a href="%(home-url)%thread/%(#forum/latest_post/forum_thread_id)%#%(#forum/latest_post/forum_thread_newest)%">
                          %date(#forum/latest_post/forum_thread_date,'j M')%<!-- --> %text(str_by)%: - 
                          %(#forum/latest_post/user_real_name)%</a>
                  %end_if%
                </div>
            </div>
        %end_loop%
    </div>

%end_loop%
</div>
