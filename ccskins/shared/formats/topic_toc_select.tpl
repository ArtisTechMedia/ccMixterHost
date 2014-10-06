<?/*
[meta]
    type = page_content_toc
    desc = _('TOC select drop-down')
    dataview = topic_page_links
    required_args = page
[/meta]
*/
$tocsel = empty($_GET['topic']) ? '' : $_GET['topic'];
?>
<select class="page_topic_toc" id="page_toc" onchange="document.location = this.options[this.selectedIndex].value;">
%loop(records,R)%
<? if( $R['topic_slug'] == $tocsel ) { ?>
<option selected="selected" value="%(#R/topic_url)%">%(#R/topic_name)%</option>
%else%
<option value="%(#R/topic_url)%">%(#R/topic_name)%</option>
%end_if%
%end_loop%
</select>

