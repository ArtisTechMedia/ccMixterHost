<?/*
[meta]
    type = dataview
    name = pool_item_history_list
    desc = _('Used by upload histogram')
    datasource = pool_item
[/meta]
*/

function pool_item_history_list_dataview() 
{
    $fhome = ccl('pools/item') . '/';

    $sql =<<<EOF
    SELECT
        pool_item_name as upload_name, pool_item_url,
        pool_item_artist  as user_real_name,
        CONCAT('$fhome', pool_item_id) as file_page_url,
        CONCAT('$fhome', pool_item_id) as artist_page_url,
        pool_item_id, pool_item_extra,
        license_img_small as license_logo_url, 
        license_url, license_name, pool_item_extra,
        pool_name, pool_short_name, pool_site_url
    FROM cc_tbl_pool_item
    JOIN cc_tbl_pools ON pool_item_pool = pool_id
    JOIN cc_tbl_licenses ON pool_item_license = license_id    
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
