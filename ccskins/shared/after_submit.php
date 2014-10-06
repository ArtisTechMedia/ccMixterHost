<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

/*
[meta]
    type     = template_component
    desc     = _('Page shown after successful submission')
    dataview = after_submit
    embedded = 1
    required_args = ids
[/meta]
[dataview]
function after_submit_dataview()
{
    $ccf = ccl('files') . '/';

    $sql =<<<EOF
        SELECT upload_id, upload_name, upload_contest, user_name,
            CONCAT( '$ccf', user_name, '/', upload_id ) as file_page_url,
            contest_friendly_name
        FROM cc_tbl_uploads
        JOIN cc_tbl_user ON upload_user=user_id
        LEFT OUTER JOIN cc_tbl_contests ON upload_contest = contest_id
        %where%
EOF;
    
    return array( 'sql' => $sql,
                  'e' => array( CC_EVENT_FILTER_FILES, CC_EVENT_FILTER_DOWNLOAD_URL )
                  );
}
[/dataview]
*/

if( empty($A['records'][0]) )
{
    return;
}

?>
<!-- template after_submit -->
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/after_submit.css') ?>" /><?

$R =& $A['records'][0];

print '<div id="after_submit"><h2>' . $T->String('str_submit_after') . '</h2>'
    . '<p>' . $T->String( array('str_submit_succeeded',$R['upload_name'] ) ) . '</p>'
    . '<p>' . $T->String( array('str_submit_link', "<a href=\"{$R['download_url']}\">${R['download_url']}</a>" ) ) . '</p>';

/*
if( empty($R['contest_id']) )
{
    print '<p>' . $T->String( array( 'str_submit_no_contest', $R['upload_name'] ) ) . '</p>';
}
else
{
    print '<p>' . $T->String( array( 'str_submit_contest', $R['upload_name'], $R['contest_friendly_name'] ) ) . '</p>';
}
*/

print '<a id="add_block" href="' . ccl('file','add',$R['upload_id']) . '">'
    . $T->String( array( 'str_submit_add_files',$R['upload_name'] ) ) 
    . '</a>';

print '</div>';

?>
