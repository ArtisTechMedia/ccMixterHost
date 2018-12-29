<?/*
<?/*%%
[meta]
    type     = dataview
    desc     = _('Pool Item Listing')
    datasource = pool_item
[/meta]
*/
function pool_item_list_dataview()
{
    $urll = ccd('ccskins/shared/images/lics/'); 
    $urlls = ccd('ccskins/shared/images/lics/small-'); 
    $urlp = ccl('pools','pool') . '/';
    $urli = ccl('pools','item') . '/';

    $sql =<<<EOF
    SELECT 
        pool_item_id, pool_item_url, pool_item_name, pool_item_artist,
        pool_item_description,
        pool_name, pool_short_name, pool_description, pool_site_url,
        CONCAT( '$urli', pool_item_id) as pool_item_page,
        CONCAT( '$urlp', pool_id ) as pool_url,
        IF( pool_short_name = '_web', '', license_img_big  )  as license_logo_url, 
        IF( pool_short_name = '_web', '', license_img_small)  as license_logo_url_small,
        license_url, license_name, pool_item_extra,
        IF( pool_item_timestamp > 1202785684, 
            FROM_UNIXTIME( pool_item_timestamp, '%a, %b %e, %Y @ %l:%i %p' ), 
            0 ) as pool_item_date,
        pool_item_timestamp,
        LOWER(CONCAT_WS(' ', pool_item_name, pool_item_artist )) as qsearch
    FROM cc_tbl_pool_item
    JOIN cc_tbl_pools ON pool_item_pool = pool_id
    JOIN cc_tbl_licenses ON pool_item_license = license_id
%joins%
%where%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*)
    FROM cc_tbl_pool_item
    JOIN cc_tbl_pools ON pool_item_pool = pool_id
    JOIN cc_tbl_licenses ON pool_item_license = license_id
        %where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                  'e' => array(CC_EVENT_FILTER_REMIXES_FULL,CC_EVENT_FILTER_POOL_ITEMS) );
}
?>
