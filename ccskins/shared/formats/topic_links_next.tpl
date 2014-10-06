<?/*
[meta]
    type = topic_format
    desc = _('Next-prev Content topic links(set page=content_page_name,topic=topic_name)')
    dataview = topic_page_links
    required_args = page
[/meta]
*/?>
%if_not_null(records/1)%
<a href="%(#R/topic_url)%">%(#R/topic_name)%</option>
%end_loop%
</select>

