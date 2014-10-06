%%
[meta]
    type = ajax_component
    desc = _('Listing with Play All/in Window')
    dataview = playlist_line
[/meta]
%%
<!-- template playable_list -->

%if_not_null(get/popup)%
<style>
div#wrapper {
      float: none;
      width: 100%;
      margin: 0px;
}
div#content {
    margin: 0px;
}
div.cc_playlist_popup_window
{
     width: auto;
}
body {
    margin: 5px;
}
h1 { display: none; }
</style>
%end_if%
<div class="cc_playlist_popup_window">
<table  class="cc_pl_table">
<tr><td>
  <ul  class="cc_playlist_owner_menu light_bg dark_border">
    <li><a target="_parent" href="javascript://" id="_pla_1" class="cc_playlist_playlink"><span>%text(str_pl_play_all_tracks)%</span></a></li>
    %if_null(#_GET/popup)%
    <li><a target="_parent" href="javascript://" id="_plw_1" class="cc_playlist_playwindow"><span>%text(str_pl_play_in_window)%</span></a></li>
    %end_if%
</ul>
 </td>
 <td><h2 id="twohead"></h2>
    <table>
      <tr>
        <td style="height:22px;">
          <div style="width:250px" class="cc_playlist_pcontainer" id="plc_id"></div>
        </td>
      </tr>
    </table>
 </td>
 </tr>
</table>

<link  rel="stylesheet" type="text/css" href="%url('css/playlist.css')%" title="Default Style"></link>
<link  rel="stylesheet" type="text/css" href="%url('css/info.css')%"  title="Default Style"></link>
<script  src="%url('/js/info.js')%"></script>
<script  src="%url('js/playlist.js')%" ></script>
%map(player_options,'autoHook: false,showVolume: false,showProgress: false,plcc_id: \'plc_id\'')%
%call('flash_player')%
%if_not_null(get/popup)%
   %map(skip_action_button,true)%
   %map(skip_into_button,true)%
   %map(skip_found_in,true)%
%end_if%
<div  class="cc_pl_div" id="cartlines1">
%call(playlist_list_lines)%
</div>
</div>
<script type="text/javascript">
cc_playlist_hook( 1, new ccPagePlayer(),{ play_buttons: true, action_buttons: true, info_buttons: true } );
CC$$('H1').each( function(h1) { $('twohead').innerHTML = h1.innerHTML; } );
</script>
%if_not_null(get/popup)%
%call(popup_background)%
%end_if%
