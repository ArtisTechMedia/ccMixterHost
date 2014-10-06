<?/*
[meta]
    type = topic_format
    desc = _('Content topic links (set page=content_page_name)')
    dataview = topic_page_links
    required_args = page
[/meta]
*/?>
%loop(records,R)%
<li><a href="%(#R/topic_url)%" class="topic_link">%chop(#R/topic_name,chop)%</a></li>
%end_loop%

