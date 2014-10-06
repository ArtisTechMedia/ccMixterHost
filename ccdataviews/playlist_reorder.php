<?/*
[meta]
    type = dataview
    name = playlist_reorder
[/meta]
*/

function playlist_reorder_dataview()
{
    $username = cc_fancy_user_sql('username');
    
    $sql =<<<EOF
        SELECT upload_id, upload_name, {$username}, cart_item_id,cart_item_order
        FROM cc_tbl_cart_items
        JOIN  cc_tbl_uploads ON cart_item_upload=upload_id
        JOIN cc_tbl_user ON upload_user=user_id
        %where%
        ORDER BY cart_item_order ASC
EOF;

    $sql_count =<<<EOF
        SELECT COUNT(*)
        FROM cc_tbl_uploads 
        JOIN cc_tbl_cart_items ON cart_item_upload=upload_id
        %where%
EOF;

     return array( 'sql' => $sql,
                   'sql_count' => $sql_count,
                   'e'   => array() );
}                  
?>
