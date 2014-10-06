<?/*
[meta]
    type = extras
    desc = _('Editorial Picks')
[/meta]
*/
?>
<p>%text(str_editors_picks)%</p>
<ul>
%query('tags=editorial_pick&t=links_menu&f=embed&chop=13&limit=5&noexit=1&nomime=1&cache=edpicks')%
<li><a href="%(home-url)%editorial/picks" class="cc_more_menu_link">%text(str_edpicks_more)%...</a></li>
</ul>
