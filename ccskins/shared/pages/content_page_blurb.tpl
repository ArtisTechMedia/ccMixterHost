<?/*
[meta]
    type     = content_page
    desc     = _('Blurbs for the Extras Sidebar')
    dataview = content_page_blurb
    datasource = topics
    embedded = 1
[/meta]
[dataview]
function content_page_blurb_dataview()
{
    $sql =<<<EOF
SELECT  topic_text as format_html_topic_text, 
        topic_text as format_text_topic_text, 
        topic_text,
        topic_name
FROM cc_tbl_topics AS topic
%where% 
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*) FROM cc_tbl_topics 
%where%
EOF;
    return array( 'sql'       => $sql,
                  'sql_count' => $sql_count,
                  'datasource'=> 'topics',
                  'e'         => array(
                                  CC_EVENT_FILTER_FORMAT
                                )
                );
}
[/dataview]
*/

if( empty($A['content_page_textformat']) )
    $A['content_page_textformat'] = 'format';
?>

%loop(records,R)%
<li class="blurb_item"><span class="blurb_name">%(#R/topic_name)%</span> <span class="blurb_text">
<? switch($A['content_page_textformat']) {
    case 'format': print $R['topic_text_html']; break;
    case 'text':   print $R['topic_text_plain']; break;
    case 'raw':    print $R['topic_text']; break;
} ?>
</span></li>
%end_loop%
