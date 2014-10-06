%%
[meta]
    type = ajax_component
    desc = _('Playlist head and listing (no styles)')
    dataview = passthru
    required_args = playlist
[/meta]
%%
<!-- template playlist_2_nostyle -->
%map(playlist_id,#_GET/playlist)%
<? cc_query_fmt('t=playlist_2_head&f=embed&ids='.$A['playlist_id']); ?>

<div  class="cc_pl_div" id="cartlines%(playlist_id)%">
<? cc_query_fmt('t=playlist_list_lines&f=embed&playlist='.$A['playlist_id']); ?>
</div>
