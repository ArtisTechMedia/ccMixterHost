%%
[meta]
   type = template_component
   desc = _('List topics marked for translation')
   dataview = topic_xlat_links
   embedded = 1
   datasource = topics
[/meta]
[dataview]
function topic_xlat_links_dataview()
{
    $turl = ccl('thread') . '/';
    $xurl = ccl('topics','translate') . '/';

    $sql =<<<EOF
SELECT          
        topic_name, topic_type,
        topic_text as format_html_topic_text, 
        topic_id,
        CONCAT( '$turl', topic.topic_thread, '#', topic.topic_id ) as topic_url,
        CONCAT( '$xurl', topic.topic_id ) as xlat_url
FROM cc_tbl_topics AS topic
%where% AND topic_can_xlat = 1
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
                   'e'  => array(
                                  CC_EVENT_FILTER_FORMAT )
                );
}
[/dataview]
%%

<style>
.xlinks {
    margin: 2em;
    width: 80%;
    border: 1px solid;
    padding: 0.3em;
}
.xlats {
    padding: 0.4em;
    margin: 0.5em;
}
</style>
%loop(records,R)%
<div class="xlinks dark_border" >
 <a href="%(#R/topic_url)%">%(#R/topic_name)%</a>
 <div class="xlats med_dark_bg">
	<a style="display:block; float:left;" class="cc_topic_xlat_link light_bg" href="%(#R/xlat_url)%" >&nbsp;%text(str_translate)%&nbsp;</a>
    %map(topic_id,#R/topic_id)%
    %call('topic_xlat_head.tpl/print_xlat_head')%
    &nbsp;
 </div>
</div>
%end_loop%
