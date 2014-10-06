<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = bogus_component
    dataview = playlist_recent_links
    embedded = 1
[/meta]
[dataview]
function playlist_recent_links_dataview($qry)
{
    // this is just so wrong...
    // breaks every rule of the 'architecture' was intended for
    // and yet... here it is...

    $sql =<<<EOF
SELECT DISTINCT cart_id
FROM `cc_tbl_cart_items`
JOIN cc_tbl_uploads ON upload_id = cart_item_upload
JOIN cc_tbl_cart ON cart_id = cart_item_cart
WHERE (cart_user != upload_user) AND (cart_num_items > 3)
ORDER BY cart_date DESC
LIMIT 5 
EOF;

    $cart_ids = CCDatabase::QueryItems($sql);
    
    if( empty($cart_ids) )
    {
        $where = '0';
    }
    else
    {
        $where = 'cart_id IN (' . join(',',$cart_ids) . ')';
    }

    $ccp = ccl('playlist','browse') . '/';

    $sql =<<<EOF
        SELECT cart_name, CONCAT('{$ccp}',cart_id) as playlist_url
        FROM cc_tbl_cart
        WHERE {$where}
EOF;

    return array( 'sql' => $sql,
                   'e'  => array() );
}
[/dataview]
*/?>
%loop(records,PL)%
  <li><a href="%(#PL/playlist_url)%">%chop(#PL/cart_name,12)%</a></li>
%end_loop%

