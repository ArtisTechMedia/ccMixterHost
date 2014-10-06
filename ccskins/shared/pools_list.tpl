<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

/*%%
[meta]
    type     = ajax_component
    desc     = _('Display remix searchable pools')
    dataview = pools
    datasource = pools
    embedded = 1
[/meta]
[dataview]
function pools_dataview()
{
    $urlp = ccl('pools','pool') . '/';

    $sql =<<<EOF
    SELECT 
        pool_name, pool_short_name, pool_description, pool_site_url,
        CONCAT( '$urlp', pool_id ) as pool_url
    FROM cc_tbl_pools
%joins%
%where% AND (pool_search = 1) AND (pool_banned =0)
%order%
%limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*)
    FROM cc_tbl_pools
    %where% AND (pool_search = 1) AND (pool_banned =0)
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                  'e' => array() );
}
[/dataview]
%%*/?>
<!-- template pools_list -->
<link rel="stylesheet" title="Default Style" type="text/css" href="%url(css/pool_listing.css)%" />
%loop(records,R)%
<div id="pools_list">
    <div class="box" id="pool_info">
        <a class="pool_name" class="cc_external_link" href="%(#R/pool_site_url)%"><span>%(#R/pool_name)%</span> 
            <img src="%url(images/remote.gif)%" /></a>
        <p>"%(#R/pool_description)%"</p>
        <p><a href="%(#R/pool_url)%">%text(str_pool_details_link)%</a></p>
    </div>
</div>
%end_loop%
%call(prev_next_links)%