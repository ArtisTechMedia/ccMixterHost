<?/*
[meta]
    type = template_component
    desc = _('Admin Content')
    datasource = topics
    dataview = content_manage
    embedded = 1
[/meta]
[dataview]
function content_manage_dataview() 
{
    $sql =<<<EOF
SELECT  topic_id, topic_type, topic_name, topic_date
FROM cc_tbl_topics AS topic
%where% 
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_topics AS topic
%where%
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array()
                );
}
[/dataview]
*/
if( empty($_GET['topic_type']) )
    $_GET['topic_type'] = '';
?>
<!-- template content_manage -->
<style type="text/css">
.odd_row {
    background-color: #FDD;
}
table.cc_content_pages,
table.cc_content_topics {
    margin: 9px;
}

table.cc_content_pages  td,
table.cc_content_topics td {
    padding: 2px 15px 2px 3px;
}
#topic_picker {
    margin: 8px;
}
.butt {
    float: left;
}
</style>
<?
require_once('cchost_lib/cc-template.inc');
$A['content_pages'] = CCTemplateAdmin::GetContentPages();
$A['topic_types'] = CCDatabase::QueryItems("SELECT DISTINCT topic_type FROM cc_tbl_topics WHERE topic_type > '' ORDER BY topic_type");
?>
<h2><?= _('Content Pages') ?></h2>
<a href="%(home-url)%admin/content/page" class="butt cc_gen_button"><span><?= _('Create a new page')?></span></a>
<div style="clear:both">&nbsp;</div>
<table class="cc_content_pages">
%loop(content_pages,cp)%
<? $class = $i_cp & 1 ? 'odd_row' : 'even_row'; ?>
<tr class="%(#class)%" >
   <? $short_name = str_replace('.php','',basename($k_cp)); ?>
    <td><b>%(#cp)%</b> (/%(#short_name)%)</td>
    <td><a href="%(home-url)%admin/content/page/edit/%(q)%page=%(#k_cp)%" class="small_button"><span>edit</span></a></td>
    <td><a href="%(home-url)%admin/content/page/delete/%(q)%page=%(#k_cp)%" class="small_button"><span>delete</span></a></td>
    <? $page = ccl(preg_replace('/\.[^\.]+$/','',basename($k_cp))); ?>
    <td><a target="_blank" href="%(#page)%" class="small_button"><span>view</span></a></td>
</tr>
%end_loop%
</table>
<h2><?= _('Content Topics') ?></h2>
<a href="%(home-url)%admin/content/post" class="butt cc_gen_button"><span><?= _('Create new content')?></span></a>
<div style="clear:both">&nbsp;</div>
<div id="topic_picker"><?= _('Select topic type')?>: 
<select id="topic_types" 
  onchange="window.location.href = '<?= cc_current_url() . $A['q'] ?>' + 'topic_type=' + this.options[this.selectedIndex].value;">
%loop(topic_types,tt)%
<? $sel = $_GET['topic_type'] == $tt ? 'selected="selected"' : ''; ?>
<option value="%(#tt)%" <?= $sel ?>> %(#tt)%</option>
%end_loop%
</select></div>
<table class="cc_content_topics" cellspacing="0" cellspacing="0" >
%loop(records,R)%
<? $class = $i_R & 1 ? 'odd_row' : 'even_row'; ?>
<tr class="%(#class)%">
    <td>#%(#R/topic_id)%</td>
    <td>%(#R/topic_date)%</td>
    <td>%(#R/topic_name)%</td>
    <td>%(#R/topic_type)%</td>
    <td><a href="%(home-url)%admin/content/edit/%(#R/topic_id)%" class="small_button"><span>edit</span></a></td>
    <td><a href="%(home-url)%admin/content/delete/%(#R/topic_id)%" class="small_button"><span>delete</span></a></td>
</tr>
%end_loop%
</table>
%call(prev_next_links)%
