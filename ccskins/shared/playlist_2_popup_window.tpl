%%
[meta]
    type = ajax_component
    desc = _('Playlist in a popop window')
    dataview = passthru
    required_args = playlist
[/meta]
%%
<!-- template playlist_2_show_one -->
<style type="text/css">
  div#wrapper {
      float: none;
      width: 100%;
      margin: 0px;
}
div#content {
    margin: 0px;
}
  div.cc_playlist_popup_window,
  div#wrapper,
  #cc_wrapper1, #cc_wrapper2
  #cc_content, #cc_centercontent {
     width: auto;
  }
  #plc_id {
    width: 250px;
  }
  body {
    margin: 5px;
  }
  h1 { display: none; } /* don't ask */
</style>
%map(playlist_id,#_GET['playlist'])%
<link  rel="stylesheet" type="text/css" href="%url('css/playlist.css')%" title="Default Style"></link>
<script  src="%url('js/playlist.js')%" ></script>
%map(player_options,'autoHook: false,showVolume: false,showProgress: false,plcc_id: \'plc_id\'')%
%call('flash_player')%
%map(skip_playlist_feed,true)%
%map(skip_info_button,true)%
%map(skip_action_button,true)%
<div class="cc_playlist_popup_window">
%call('playlist_2_nostyle')%
</div>

<script type="text/javascript">
cc_playlist_hook( %(playlist_id)%, new ccPagePlayer(), { play_buttons: true } );
new ccParentRedirector(); 
</script>
