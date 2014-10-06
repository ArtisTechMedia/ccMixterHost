<?/*
[meta]
    type = extras
    desc = _('Recently Updated')
    allow_user = 1
[/meta]
*/?>

<p>%text(str_updated_recent)%</p>
<ul>
%query('sort=last_edit&t=links_menu&f=html&chop=13&limit=5&noexit=1&nomime=1&cache=remixes')%
<li><a href="%(home-url)%view/media/remix/updated" class="cc_more_menu_link">%text(str_updated_more)%</a></li>
</ul>