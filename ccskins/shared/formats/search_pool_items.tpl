<?/*
[meta]
    type     = search_results
    desc     = _('For pool items search results (set type=pools)')
    example    = type=pools&t=search_pool_items&limit=30&search_type=any&s=rooster
    dataview = pool_item_list
    datasource = pool_items
    required_args = type, search
[/meta]
*/?>
<div  id="search_result_list">
%loop(records,R)%
   <div class="search_results_link" >
     <a href="%(#R/pool_item_page)%">%(#R/pool_item_artist)% - %(#R/pool_item_name)%</a> (%(#R/pool_name)%)
   </div>
   <div class="search_results" >
    %(#R/qsearch)%
   </div>
%end_loop%
</div>
%call(prev_next_links)%
