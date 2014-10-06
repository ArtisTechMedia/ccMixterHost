<?/*
[meta]
    type = extras
    desc = _('Latest Uploads')
    allow_user = 1
[/meta]
*/?>
<p>%text(str_new_uploads)%</p>
<ul>
%query('f=embed&t=links_menu&chop=13&limit=5&cache=latest')%
<li><a href="%(home-url)%files" class="cc_more_menu_link">%text(str_more_newuploads)%</a></li>
</ul>