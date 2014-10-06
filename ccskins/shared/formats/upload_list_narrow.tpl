%%
[meta]
    type     = list
    desc     = _('Multiple upload listing (narrow)')
    dataview = list_narrow
[/meta]
%%
<link rel="stylesheet" href="<?= $T->URL('css/upload_list_narrow.css'); ?>"  title="Default Style" type="text/css" />
<div  id="cc_narrow_list">
<ul>
<table cellspacing="0" cellpadding="0"  >
%map(dochop,'1')%
%loop(records,R)%

   <tr><td>
          <div class="box">
            <div><a href="javascript://download" class="download_hook" id="_ed__%(#R/upload_id)%">%text(str_list_download)%</a></div>
            <div><a class="cc_file_link" href="%(#R/file_page_url)%" >%text(str_detail)%</a></div>
            <div><a href="javascript://action" class="menuup_hook" id="_emup_%(#R/upload_id)%" >%text(str_action)%</a></div>
          </div>
       </td>
       <td><a href="%(#R/file_page_url)%" class="cc_file_link upload_name"><span %if_attr(#R/upload_name_cls,class)%>%chop(#R/upload_name,60)%</span></a>
        <div>%chop(#R/upload_description_plain,75)% <a href="%(#R/file_page_url)%">(%text(str_more)%)</a></div>
     </td></tr>
   %if_not_null(#R/fplay_url)%
   <tr><th>%text(str_play)%</th><td><a class="cc_player_button cc_player_hear" id="_ep_%(#R/upload_id)%"> </a><script type="text/javascript">
    $('_ep_%(#R/upload_id)%').href = '%(#R/fplay_url)%'</script></tr>
   %end_if%
    %if_not_empty(#R/flash_id)%
        <tr><th></th><td>
            <a class="small_button flash_game_link" id="%(#R/flash_id)%" href="javascript://flash play">%text(str_play)%</a>
        </td></tr>
    %end_if%
   
   <tr><th>%text(str_by)%</th><td><a href="%(#R/artist_page_url)%" class="cc_user_link artist_name">%chop(#R/user_real_name,chop)%</a> 
                              <span class="upload_date">%(#R/upload_date_format)%</span></td></tr>
   <tr><th>%text(str_license)%</th>
     <td><a href="%(#R/license_url)%"><img src="%(#R/license_logo_url)%" /></a></td>
   </tr>
    <tr><td /><td id="_%(#R/upload_id)%" class="rate_head"></td></tr>
   <tr><td colspan="2">
       %if_not_null(#R/remix_parents)%
        <div id="remix_info"><h2>%text(str_list_uses)%</h2>
        %loop(#R/remix_parents,P)%
            <div><a class="remix_links cc_file_link" href="%(#P/file_page_url)%">%(#P/upload_name)%</a> %text(str_by)%
                 <a class="cc_user_link" href="%(#P/artist_page_url)%">%(#P/user_real_name)%</a></div>
        %end_loop%
        %if_not_null(#R/more_parents_link)%
            <a class="remix_more_link" href="%(#R/more_parents_link)%">%text(str_more)%...</a>
        %end_if%
        </div>
    %end_if%
    %if_not_null(#R/remix_children)%
        <div id="remix_info"><h2>%text(str_list_usedby)%</h2>
        %loop(#R/remix_children,P)%
            <div>
            %if_not_null(#P/pool_item_extra/ttype)%
                <? $tstr = $T->String('str_trackback_type_' . $P['pool_item_extra']['ttype']) ?>
                <span class="pool_item_type">%(#tstr)%</span>: 
            %end_if%
            <a class="remix_links cc_file_link" href="%(#P/file_page_url)%">%(#P/upload_name)%</a> %text(str_by)%
                 <a class="cc_user_link" href="%(#P/artist_page_url)%">%(#P/user_real_name)%</a></div>
        %end_loop%
        %if_not_null(#R/more_children_link)%
            <a class="remix_more_link" href="%(#R/more_children_link)%">%text(str_more)%...</a>
        %end_if%
        </div>
    %end_if%
  <td></tr>
  <tr><td class="rec_end" colspan="2" class="dark_border"></td></tr>

%end_loop%
</table>
%call(prev_next_links)%
</div>

%call('flash_player')%
<script type="text/javascript">
if( window.ccEPlayer )
    ccEPlayer.hookElements($('cc_narrow_list'));
</script>
<script type="text/javascript">
var dl_hook = new queryPopup("download_hook","download",str_download); 
    dl_hook.height = '550';
    dl_hook.width  = '700';
dl_hook.hookLinks(); 
var menu_hook = new queryPopup("menuup_hook","ajax_menu",str_action_menu);
menu_hook.hookLinks();
</script>
