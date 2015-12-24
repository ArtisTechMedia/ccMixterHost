<?/*
[meta]
    desc = _('Playlist head details')
    type = dataview
    datasource = cart
[/meta]
*/
function playlist_head_dataview()
{
    $avatar_sql = cc_get_user_avatar_sql();

    $sql =<<<EOF
SELECT  cart_id, cart_name, cart_user, cart_num_items, cart_subtype, cart_tags, cart_dynamic,
        user_real_name, user_name, {$avatar_sql},
        DATE_FORMAT(cart_date, '%W, %M %e, %Y @ %l:%i %p') as cart_date_format,
        SUBSTRING(REPLACE(cart_tags, ',', ' '),1,120) as cart_tags_munged,
        cart_desc as format_html_cart_desc,
        cart_desc as cart_description
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
                                  CC_EVENT_FILTER_CART_NSFW ) 
                 );
}

?>
