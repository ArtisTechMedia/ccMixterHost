<?/*
[meta]
    type = page_content_toc
    desc = _('TOC link list (UL)')
    dataview = topic_page_links
    required_args = page
[/meta]
*/
$tocsel = empty($_GET['topic']) ? '' : $_GET['topic'];
?>
<ul class="page_topic_toc light_bg" id="page_toc" >
%loop(records,R)%
<? if( empty($tocsel) || $R['topic_slug'] == $tocsel ) { $tocsel = -1; ?>
<li>%(#R/topic_name)%</li>
%else%
<li><a href="%(#R/topic_url)%">%(#R/topic_name)%</a></li>
%end_if%
%end_loop%
</ul>

