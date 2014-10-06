%%
[meta]
    type     = ajax_component
    desc     = _('used by pool remix search')
    datasource = pool_items
    dataview_param = ok
[/meta]
%%
%loop(records,R)%
   <div class="remix_check_line" id="rl_%(#R/pool_item_id)%" >
     <input class="remix_checks" type='checkbox' name='pool_sources[%(#R/pool_item_id)%]' id='src_%(#R/pool_item_id)%'  /> <span id="rc_%(#R/pool_item_id)%">
     <span class="upload_name">%chop(#R/pool_item_name,30)%</span></span> %text(str_by)%
     <span class="artist_name">%chop(#R/pool_item_artist,23)% (%(#R/pool_name)%)</span>
   </div>
%end_loop%
