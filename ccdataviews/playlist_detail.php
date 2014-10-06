<?/*
[meta]
    desc = _('Playlist details')
    type = dataview
    datasource = cart
[/meta]
*/
function playlist_detail_dataview()
{
    $user_sql   = cc_fancy_user_sql('user_real_name');
    $share_url  = ccl('share','playlist') . '/';
    $feed_url   = url_args( ccl('api','query'), 'f=rss&playlist=' );
    $dl_url     = url_args( ccl('api','query'), 't=download&playlist=' );

    $browse_tag_url = url_args( ccl('playlist','browse'), 'tags=' );
    $permalink      = ccl('playlist','browse') . '/';
    $user_sql_url   = "CONCAT( '".ccl('people')."/', user_name )";
    
    $sql =<<<EOF
SELECT  cart_id, cart_name, cart_user, cart_num_items, cart_subtype, cart_tags, cart_dynamic,
        {$user_sql}, user_name,
        DATE_FORMAT(cart_date, '%W, %M %e, %Y @ %l:%i %p') as cart_date_format,
        SUBSTRING(REPLACE(cart_tags, ',', ' '),1,120) as cart_tags_munged,
        {$user_sql_url}  as artist_page_url,
        cart_desc as format_html_cart_desc,
        CONCAT( '{$dl_url}', cart_id ) as dl_url,
        CONCAT( '{$share_url}', cart_id ) as share_url,
        CONCAT( '{$feed_url}', cart_id ) as feed_url,
        CONCAT( '{$permalink}', cart_id ) as permalink_url,
        '{$browse_tag_url}'  as browse_tag_url
        %columns%
        FROM cc_tbl_cart 
        LEFT OUTER JOIN cc_tbl_user ON cc_tbl_cart.cart_user = user_id  
        %where% AND (cart_subtype <> "default") AND (cart_type = 'playlist') 
        LIMIT 1
EOF;

    $sql_count =<<<EOF
    SELECT 1
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( CC_EVENT_FILTER_FORMAT,
                                  CC_EVENT_FILTER_CART_MENU,
                                  CC_EVENT_FILTER_CART_NSFW ) 
                 );
}

?>
