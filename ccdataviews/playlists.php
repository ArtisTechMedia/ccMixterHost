<?/*
[meta]
    desc = _('Playlist line info')
    type = dataview
    datasource = cart
[/meta]
*/
function playlists_dataview()
{
    $user_sql = cc_fancy_user_sql('user_real_name');

    $sql =<<<EOF
SELECT cart_id, cart_name, {$user_sql}, cart_dynamic, cart_num_items,
        DATE_FORMAT(cart_date, '%W, %M %e, %Y @ %l:%i %p') as cart_date_format,
        CONCAT(SUBSTRING(REPLACE(cart_tags, ',', ' '),1,120),'...') as cart_tags_munged,
        cart_tags
        %columns%
        FROM cc_tbl_cart 
        LEFT OUTER JOIN cc_tbl_user ON cc_tbl_cart.cart_user = user_id  
        %joins%
        %where% AND (cart_subtype <> "default") AND (cart_type = 'playlist') 
        %order%
        %limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*)
        FROM cc_tbl_cart
        LEFT OUTER JOIN cc_tbl_user ON cc_tbl_cart.cart_user = user_id  
        %joins%
        %where% AND 
           (cart_subtype <> 'default')
           AND 
           (cart_type = 'playlist') 
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array() );
}

?>