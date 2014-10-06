<?/*
[meta]
    type     = search_results
    desc     = _('For upload search results')
    example    = t=search_uploads&limit=30&search_type=any&s=charlie+rose
    dataview = search_uploads
    embedded = 1
    valid_args = search, search_type
    required_args = search
[/meta]
[dataview]
function search_uploads_dataview() 
{
    $ccp = ccl('people') . '/';
    $ccu = ccl('files') . '/';

    $sql =<<<EOF
SELECT 
    upload_id, upload_name, user_real_name,
    CONCAT( '$ccp', user_name ) as artist_page_url,
    CONCAT( '$ccu', user_name, '/', upload_id ) as file_page_url,
    LOWER(CONCAT_WS(' ', upload_name, upload_description, upload_tags)) as qsearch
     %columns% 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%group%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
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
     <a href="%(#R/file_page_url)%" class="cc_file_link">%(#R/upload_name)%</a> %text(str_by)%
     <a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a>
   </div>
   <div class="search_results" >
    %(#R/qsearch)%
   </div>
%end_loop%
</div>
%if(prev_next_links)%<!-- -->
  %call(prev_next_links)%<!-- -->
%end_if%  
