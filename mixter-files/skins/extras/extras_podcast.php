<? /*%%
[meta]
    type = extras
    desc = _('Podcasts')
    allow_user = 1
[/meta]
*/?>

<p>Podcasts</p>
<ul>
<? cc_query_fmt('f=embed&type=podcast&t=topic_page_links&page=podcast&limit=5&chop=19&offset=1'); ?>
</ul>

