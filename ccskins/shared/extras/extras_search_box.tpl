<?/*
[meta]
    type = extras
    desc = _('Search Box')
    allow_user = 1
[/meta]
*/?>
<p>%text(str_search)%</p>
<form action="%(home-url)%search/results" method="get">
<div>
<input class="cc_search_edit" name="search_text" value="search text"></input>
<input type="hidden" name="search_in" value="all"></input>
<input type="submit" value="Search"></input>
</div>
</form>
