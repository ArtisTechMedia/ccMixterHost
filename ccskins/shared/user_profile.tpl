<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/* %%
[meta]
    type = profile
    desc = _('User profile page (set match=user_name)')
    dataview = user_profile
    datasource = user
    embedded = 1
    require_args = user
[/meta]
[dataview]
function user_profile_dataview()
{
    $avatar_sql = cc_get_user_avatar_sql();
    $cce = ccl('people','contact') . '/';

    $sql =<<<EOF
        SELECT $avatar_sql, user_id, user_name, user_real_name, user_favorites, user_num_uploads,
            user_homepage, user_description as format_html_user_description, user_whatilike, user_whatido, user_lookinfor,
            user_num_reviewed, user_num_reviews, user_num_remixes, user_num_remixed,
            CONCAT( '$cce', user_name ) as user_emailurl, user_num_posts,
            DATE_FORMAT( user_registered, '%a, %b %e, %Y' ) as user_date_format
        FROM cc_tbl_user
        %where% 
EOF;

    return array( 'sql' => $sql,
                  'e' => array( CC_EVENT_FILTER_FORMAT, CC_EVENT_FILTER_USER_PROFILE ) );

}
[/dataview] %%
*/?>
<!-- template user_profile -->
%if_null(records/0)%
    %return%
%end_if%
%map(#U,records/0)%

<link rel="stylesheet" title="Default Style" href="%url(css/user_profile.css)%" />

<div id="user_profile">
    
    <?
        array_splice( $U['user_fields'], 1, 0, array(
             array( 'label'  => $T->String('str_contact'), 
                     'value' => "<a href=\"{$U['user_emailurl']}\">" . $T->String('str_email') . '</a>' ),
             array( 'label'  => $T->String('str_member_since'), 
                     'value' => $U['user_date_format'] ) ) );
    ?>

    <div id="user_fields">
        %loop(#U/user_fields,uf)%
            <div class="ufc" %if_attr(#uf/id,id)%><span class="ufc_label">%text(#uf/label)%</span> 
                <div class="ufc_value">%text(#uf/value)%</div></div>
        %end_loop%
    </div>
    <div class="user_tag_links">
        %loop(#U/user_tag_links,groups)%
            <span class="ufc_label">%text(#groups/label)%</span>
            <div class="ufc_value taglinks">
                    %loop(#groups/value,link)%
                        <a href="%(#link/tagurl)%">%(#link/tag)%</a>%if_not_last(#link)%, %end_if%
                    %end_loop%
            </div>
        %end_loop%
    </div>
</div>

<script type="text/javascript">
var user_desc = $('user_description_html');
var avatar_html = '<div id="avatar" style="float:right;width:94px;"><img src="%(#U/user_avatar_url)%" /></div>';
var breaker = '<div style="clear:both">&nbsp;</div>';
if( user_desc )
{
    new Insertion.Top(user_desc,avatar_html);
    new Insertion.Bottom(user_desc,breaker);

    if( window.round_box_enabled )
    {
        cc_round_box('user_description_html');
        CC$$('.ufc_label',user_desc).each( function( e ) { e.style.display = 'none'; } );
    }
    else
    {
        Element.addClassName(user_desc,'box');
    }
}
else
{
    new Insertion.Top($('user_profile'),avatar_html);
    new Insertion.Bottom($('user_profile'),breaker);
}
</script>