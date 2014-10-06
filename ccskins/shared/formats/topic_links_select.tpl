<?/*
[meta]
    type = page_conent_toc
    desc = _('TOC select drop-down')
    dataview = topic_page_links
    required_args = page
[/meta]
*/?>
<select onchange="document.location = this.options[this.selectedIndex].value;">
%loop(records,R)%
<option value="%(#R/topic_url)%">%(#R/topic_name)%</option>
%end_loop%
</select>

