<?/*
[meta]
    type = dataview
    name = links_by_pool
    desc = _('Used by upload listing for Samples are From')
    datasource = pool_item
[/meta]
*/

function links_by_pool_dataview() 
{
    $fhome = ccl('pools/item') . '/';

    $sql =<<<EOF
SELECT IF( LENGTH(pool_item_name) > 20,   CONCAT( SUBSTRING(pool_item_name,1,18),   '...'), pool_item_name ) as upload_name,
       IF( LENGTH(pool_item_artist) > 20, CONCAT( SUBSTRING(pool_item_artist,1,18), '...'), pool_item_artist ) as user_real_name,
       CONCAT('$fhome', pool_item_id) as file_page_url,
       CONCAT('$fhome', pool_item_id) as artist_page_url,
       pool_item_id, pool_item_extra
    FROM cc_tbl_pool_item 
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                  'name' => 'links_by_pool',
                   'e'  => array( CC_EVENT_FILTER_POOL_ITEMS )
                );
}

?>