%%
[meta]
    type     = ajax_component
    desc     = _('used by remix search')
    dataview_param = ok
[/meta]
%%
%loop(records,R)%
   <div class="remix_check_line" id="rl_%(#R/upload_id)%" style="clear:left;margin-bottom:10px;">
     <div style="float:left; margin-right:4px;"><input title="%(#R/upload_name)%" class="remix_checks" type='checkbox' name='remix_sources[%(#R/upload_id)%]' id='src_%(#R/upload_id)%'  /></div> 
     <span style="float:left;display:block;margin-right:4px;" id="rc_%(#R/upload_id)%">
        <span class="upload_name" title="%(#R/upload_name)%">%chop(#R/upload_name,30)%</span>
     </span>
         %text(str_by)%
         <span class="artist_name">%chop(#R/user_real_name,33)% </span>
   </div>
%end_loop%