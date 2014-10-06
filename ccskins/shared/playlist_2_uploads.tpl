%%

So the deal with this is that it will use the whatever
playlist_list_lines macro is mapped to (e.g. playlist_2_audio for
audio sites, playlist_2_image for image gallery sites)

[meta]
    desc = _('List playlist lines (Headless playlist with style)')
    example = t=playlist_2_uploads&sort=num_playlists&ord=desc
    type = ajax_component
    dataview = playlist_line
[/meta]
%%
<!-- template playlist_2_uploads -->
<link  rel="stylesheet" type="text/css" href="%url('css/playlist.css')%" title="Default Style"></link>
<link  rel="stylesheet" type="text/css" href="%url('css/info.css')%"  title="Default Style"></link>
<script  src="%url('/js/info.js')%"></script>
<script  src="%url('js/playlist.js')%" ></script>
%call('flash_player')%
<div id="cc_pl_div" id="_pl_1">
%call('playlist_list_lines')%
</div>
<script type="text/javascript">
    cc_playlist_hook(1,new ccPagePlayer());
</script>
%call('prev_next_links')%
