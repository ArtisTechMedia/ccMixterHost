<?/*
[meta]
    type = content_page
    desc = _('Generic Page')
    dataview = content_page
    datasource = topics
    embedded = 1
[/meta]
[dataview]
function content_page_dataview() 
{
    $sql =<<<EOF
SELECT  topic_text as format_html_topic_text, 
        topic_text as format_text_topic_text, 
        topic_text,
        topic_id,
        topic_name,
        topic_format
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
                   'e'  => array(
                                  CC_EVENT_FILTER_FORMAT)
                );
}
[/dataview]
*/
if( empty($A['content_page_textformat']) )
    $A['content_page_textformat'] = 'format';

if( !empty($A['content_page_width']) ) {
?>
<style type="text/css">
#inner_content {
    width: <?= $A['content_page_width'] ?>;
    margin: 0px auto;
}
</style>
<?
}
?>
<table class="cc_content_page" cellspacing="0" cellspacing="0" style="width:100%">
<?  $num_cols = empty($A['content_page_columns']) ? 2 : $A['content_page_columns'];
    $wid = intval(100/$num_cols);
    $rows = array_chunk($A['records'],$num_cols); ?>
%loop(#rows,row)%
<tr>
    %loop(#row,R)%
        <td style="vertical-align:top;width:<?=$wid?>%">%if_not_null(content_page_box)%<div class="box">%end_if%
           <?   
             $tname = preg_replace( '/[^\w]/','',$R['topic_name']);
             if( empty($tname) ) 
                { print '<br class="topic_box_head_spacer" />'; }
             else
                { print "<h2>".$R['topic_name']."</h2>"; }
             switch($A['content_page_textformat']) {
                    case 'format': print $R['topic_text_html']; break;
                    case 'text':   print $R['topic_text_plain']; break;
                    case 'raw':    print $R['topic_text']; break;
                } ?>
        %if_not_null(content_page_box)%</div>%end_if%</td>
    %end_loop%
</tr>
%end_loop%
</table>
%if_not_null(content_page_paging)%
    <!-- -->%call(prev_next_links)%
%end_if%
