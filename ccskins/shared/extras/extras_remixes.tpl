<?/*
[meta]
    type = extras
    desc = _('Latest Remixes')
    allow_user = 1
[/meta]
*/?>

<p>%text(str_new_remixes)%</p>
<ul>
%query('tags=remix&t=links_menu&f=html&chop=13&limit=5&noexit=1&nomime=1&cache=remixes')%
<li><a href="%(home-url)%view/media/remix/latest" class="cc_more_menu_link">%text(str_more_remixes)%</a></li>
</ul>
