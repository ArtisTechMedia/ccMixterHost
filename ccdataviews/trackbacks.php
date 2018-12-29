<?/*
[meta]
    type = dataview
    name = links_by_pool
    desc = _('Used by upload listing for Samples are From')
    datasource = pool_item
[/meta]
*/

function trackbacks_dataview() 
{
    $fhome = ccl('pools/item') . '/';

    $sql =<<<EOF
       SELECT pool_item_id, pool_item_name, pool_item_artist, pool_item_url, pool_item_extra
    FROM cc_tbl_pool_item 
%joins%
%where% AND pool_item_approved > 0
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                  'name' => 'trackbacks',
                   'e'  => array( CC_EVENT_FILTER_POOL_ITEMS )
                );
}

?>