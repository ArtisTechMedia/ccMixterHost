<?/*
[meta]
    type     = search_results
    desc     = _('For user search results')
    example    = t=search_users&limit=30&search_type=any&search=charlie+rose
    datasource = users
    dataview = search_users
    embedded = 1
    required_args = search
[/meta]
[dataview]
function search_users_dataview() 
{
    $ccp = ccl('people') . '/';

    $sql =<<<EOF
SELECT 
    user_name, user_real_name,
    CONCAT( '$ccp', user_name, '/', 'profile' ) as artist_page_url,
    LOWER(CONCAT_WS(' ', user_name, user_real_name, user_description)) as qsearch
     %columns% 
FROM cc_tbl_user
%joins%
%where%
%group%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_user
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( CC_EVENT_FILTER_SEARCH_RESULTS )
                );
}
[/dataview]
*/?>
<div  id="search_result_list">
%loop(records,R)%
   <div class="search_results_link">
     <a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a>
   </div>
   <div class="search_results" >
    %(#R/qsearch)%
   </div>
%end_loop%
</div>
%call(prev_next_links)%
