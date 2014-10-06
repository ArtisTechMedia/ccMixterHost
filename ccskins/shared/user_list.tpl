<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_component
    desc = _('People page')
    datasource = users
    dataview = user_list
    embedded = 1
[/meta]
[dataview]
function user_list_dataview()
{
    $avatar_sql = cc_get_user_avatar_sql();
    $ccp = ccl('people') . '/';
    $cce = ccl('people','contact') . '/';

    $sql =<<<EOF
        SELECT $avatar_sql,  user_real_name, user_name, 
            CONCAT( '$ccp', user_name ) as artist_page_url,
            CONCAT( '$cce', user_name ) as user_emailurl,
            DATE_FORMAT( user_registered, '%a, %b %e, %Y' ) as user_date_format
            %columns%
        FROM cc_tbl_user
        %joins%
        %where%
        %order%
        %limit%
EOF;

    $sql_count =<<<EOF
        SELECT COUNT(*)
        FROM cc_tbl_user
        %where%
EOF;
    
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                  'e' => array(
                             CC_EVENT_FILTER_USER_LIST
                             )
                 );
}
[/dataview]
*/?>
<!-- template user_list -->
<link rel="stylesheet" href="%url(css/user_list.css)%" title="Default Style" />
<style>
#inner_content {
    width: 85%;
    margin: 0px auto;
}
</style>

<h1>%text(people)%</h1>

<div id="user_index" class="light_bg dark_border">
    %loop(user_index,ui)%
    <a href="%(#ui/url)%" title="%(#ui/text)%">%(#ui/text)%</a> 
    %end_loop%
        <div class="user_breaker"></div>
</div>

<div id="user_listing">
%loop(records,u)%
    <div class="user_record">
        <div class="avatar"><a href="%(#u/artist_page_url)%" class="cc_user_link user_link"><img src="%(#u/user_avatar_url)%" /></a></div>
        <a href="%(#u/artist_page_url)%" class="cc_user_link user_link">%(#u/user_real_name)% <span>(%(#u/user_name)%)</span></a>
        <a href="%(#u/user_emailurl)%" class="contact_link">%text(str_contact_artist)%</a>
        <div class="member_since">%text(member_since)%: %(#u/user_date_format)%</div>
        <div class="user_breaker"></div>
    </div>
%end_loop%
</div>

%call(prev_next_links)%