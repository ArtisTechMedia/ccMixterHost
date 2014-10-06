<?/*
[meta]
    type     = search_results
    desc     = _('For playlist search results')
    example    = limit=30&search_type=any&search=charlie+rose
    datasource = cart
    dataview = search_playlists
    embedded = 1
    required_args = search
[/meta]
[dataview]
function search_playlists_dataview() 
{
    $ccp = ccl('playlist','browse') . '/';
    $user_sql = cc_fancy_user_sql();

    $sql =<<<EOF
SELECT 
    cart_name, $user_sql,
    CONCAT( '$ccp', cart_id ) as playlist_url,
    LOWER(CONCAT_WS(' ', cart_name, cart_tags, cart_desc )) as qsearch
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
                   'e'  => array( CC_EVENT_FILTER_SEARCH_RESULTS )
                );
}
[/dataview]
*/?>
<div  id="search_result_list">
%loop(records,R)%
   <div class="search_results_link">
     <a class="cc_playlist_name" href="%(#R/playlist_url)%">%(#R/cart_name)%</a> by %(#R/fancy_user_name)%
   </div>
   <div class="search_results" >
    %(#R/qsearch)%
   </div>
%end_loop%
</div>
%call(prev_next_links)%
