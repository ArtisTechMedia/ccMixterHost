<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_component
    desc = _('People like me page')
    dataview = user_match
    datasource = user
    embedded = 1
[/meta]
[dataview]
function user_match_dataview()
{
    $avatar_sql = cc_get_user_avatar_sql();
    $ccp = ccl('people') . '/';
    $cce = ccl('people','contact') . '/';

    $sql =<<<EOF
        SELECT $avatar_sql,  user_real_name, user_name, 
            user_whatido, user_whatilike,
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
<!-- template user_match -->
<link rel="stylesheet" href="%url(css/user_list.css)%" title="Default Style" />
<style>
.whatido, .whatilike {
    margin: 3px;
}
</style>
<?
$bw = ccl('search','people','whatilike') . '/';
$bl = ccl('search','people','whatido') . '/';
function _um_helper($tags,$url)
{
    $tags = split(',',$tags); 
    $comma = '';
    foreach( $tags as $tag )
    {
        print $comma . '<a href="' . $url. $tag .'">'.$tag.'</a>';
        $comma = ', ';
    }
}
?>
<div id="user_listing">
%loop(records,u)%
    <div class="user_record">
        <div class="avatar" style="margin:4px;"><a href="%(#u/artist_page_url)%" class="cc_user_link user_link"><img src="%(#u/user_avatar_url)%" /></a></div>
        <a href="%(#u/artist_page_url)%" class="cc_user_link user_link">%(#u/user_real_name)% <span>(%(#u/user_name)%)</span></a>
        <a href="%(#u/user_emailurl)%" class="contact_link">%text(str_contact_artist)%</a>
        <div class="member_since">%text(member_since)%: %(#u/user_date_format)%</div>
        %if_not_null(#u/user_whatido)%
            <div class="whatido">%text(str_prof_what_i_pound_on)%: <? _um_helper($u['user_whatido'],$bl); ?></div>
        %end_if%
        %if_not_null(#u/user_whatilike)%
            <div class="whatilike">%text(str_prof_what_i_like)%: <? _um_helper($u['user_whatilike'],$bw); ?></div>
        %end_if%
        <div style="clear:both">&nbsp;</div>
    </div>
%end_loop%
</div>

%call(prev_next_links)%