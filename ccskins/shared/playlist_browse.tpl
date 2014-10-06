<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_component
[/meta]
[dataview]
// NOT USED
function playlists_browse_dataview()
{
    $sql =<<<EOF 
    SELECT cart_id, cart_name, user_real_name, cart_dynamic,
        DATE_FORMAT(cart_date, '%W, %M %e, %Y @ %l:%i %p') as cart_date_format,
        REPLACE(SUBSTR(cart_tags,1,110), ',', ' ', cart_tags) as cart_tags_munged
        FROM cc_tbl_cart 
        LEFT OUTER JOIN cc_tbl_user ON cc_tbl_cart.cart_user = user_id  
        %where% 
        %order%
        %limit%
EOF;

    $sql_count = <<<EOF
    SELECT COUNT(*)
        %where% AND (cart_subtype <> "default") AND (cart_num_items > 0) AND (cart_type = 'playlist') 
EOF;

    return array( 'sql' => $sql,
                   'e'  => array() );
}
*/?>
<!-- template playlist_browse -->
%loop(records,PL)%  
  <div  class="cc_playlist_line med_bg" id="_pl_%(#PL/cart_id)%">%(#PL/cart_name)% 
        <span class="cc_playlist_dyn_user">%text(str_pl_created_by)% <!-- -->%(#PL/user_real_name)%</span>
    %if_not_null(#PL/cart_dynamic)%
       <span class="cc_playlist_dyn_label">(%text(str_pl_dynamic)%)</span>
    %end_if%
    <span>%(#PL/cart_tags_munged)%</span>
    %if_null(#PL/cart_dynamic)%
       <span> %text(str_pl_items)%: %(#PL/cart_num_items)%</span>
    %end_if%
    </div>
%end_loop%

%call(prev_next_links)%

