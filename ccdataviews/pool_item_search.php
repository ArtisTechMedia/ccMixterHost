<?/*
[meta]
    type = dataview
    name = pool_item_search
    datasource = pool_item
    required_args = match
[/meta]
*/

function pool_item_search_dataview() 
{
    $sql =<<<EOF
SELECT 
    pool_item_id, pool_item_name, pool_item_artist, pool_name
     %columns% 
FROM cc_tbl_pool_item
JOIN cc_tbl_pools ON pool_item_pool = pool_id
%joins%
%where% AND MATCH( `pool_item_description` , `pool_item_name` , `pool_item_artist` ) AGAINST( '%match%' IN BOOLEAN MODE )
%group%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>