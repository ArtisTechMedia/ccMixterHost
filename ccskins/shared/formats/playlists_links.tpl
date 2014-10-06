<?/*
[meta]
    type     = ajax_component
    desc     = _('Playlist links')
    datasource = cart
    dataview = playlists_links
    embedded = 1
[/meta]
[dataview]
function playlists_links_dataview() 
{
    $ccpl = ccl('playlist','browse') . '/';
    $ccp  = ccl('people') . '/';
    $user_sql = cc_fancy_user_sql();

    $sql =<<<EOF
SELECT 
    cart_name, $user_sql,
    CONCAT( '$ccpl', cart_id ) as playlist_url,
    CONCAT( '$ccp', user_name) as artist_page_url
     %columns% 
FROM cc_tbl_cart
JOIN cc_tbl_user ON cart_user=user_id
%joins%
%where% AND (cart_type = 'playlist') AND (cart_dynamic > '' OR (cart_num_items > 0))
%group%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_cart
%joins%
%where% AND (cart_type = 'playlist') AND (cart_dynamic > '' OR (cart_num_items > 0))
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( )
                );
}
[/dataview]
*/?>
<div  id="search_result_list">
%loop(records,R)%
   <div class="search_results_link">
     <a class="cc_playlist_name" href="%(#R/playlist_url)%">%(#R/cart_name)%</a> %text(str_by)% 
       <a class="cc_user_name" href="%(#R/artist_page_url)%">%(#R/fancy_user_name)%</a>
   </div>
%end_loop%
</div>
%call(prev_next_links)%
