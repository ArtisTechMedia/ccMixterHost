%%
[meta]
    type = extras
    desc = _('Recent Playlists')
    allow_user = 1
[/meta]
%%

<p>%text(str_recent_playlists)%</p>
<ul>
%query('t=playlist_recent_links')%
<a href="%(home-url)%view/media/playlists" class="cc_more_menu_link">%text(str_more_playlists)%...</a>
