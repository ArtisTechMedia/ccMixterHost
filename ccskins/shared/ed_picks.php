<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type     = template_component
    desc     = _('for displaying edpicks')
    dataview = edpick_detail
    embedded = 1
[/meta]
[dataview]
function edpick_detail_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $avatar_sql = cc_get_user_avatar_sql();
    $lic_logo = cc_get_license_logo_sql('small');

    $sql =<<<EOF
SELECT 
    user_id, upload_user, upload_id, upload_name, upload_extra, 
    upload_description as format_html_upload_description,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name,
    user_name,
    $avatar_sql,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    {$lic_logo},
    license_url,
    collab_upload_collab as collab_id, upload_contest
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
LEFT OUTER JOIN cc_tbl_collab_uploads ON upload_id = collab_upload_upload
%joins%
%where%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
LEFT OUTER JOIN cc_tbl_collab_uploads ON upload_id = collab_upload_upload
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( CC_EVENT_FILTER_FILES,
                                  CC_EVENT_FILTER_ED_PICK_DETAIL,
                                  CC_EVENT_FILTER_COLLAB_CREDIT,
                                  CC_EVENT_FILTER_FORMAT,
                                  CC_EVENT_FILTER_DOWNLOAD_URL,
                                  CC_EVENT_FILTER_PLAY_URL,
                                  CC_EVENT_FILTER_UPLOAD_LIST,
                                  )
                );
}
[/dataview]
*/
?>
<!-- template ed_picks -->
<style type="text/css">
#edpicks td {
    vertical-align: top;
    width: 32.5%;
}
#edpicks td img {
    float: left;
    margin: 5px;
}
.pick_text {
    margin: 4px;
}
.reviewer {
    margin: 3px;
    text-align: right;
    font-style: italic;
}
.playlabel {
    float: left;
    margin-right: 5px;
}
.info_div {
    float: right;
    width: 23px;
}
</style>
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/info.css') ?>" title="Default Style"></link>

<?
$rows = array_chunk($A['records'], 3);
$row_count = count($rows);
$row_keys = array_keys($rows);
?><table id="edpicks" cellspacing="0" cellpadding="0" ><?
for( $i = 0; $i < $row_count; $i++ )
{
    ?><tr><?
    $row =& $rows[ $row_keys[$i] ];
    $col_count = count($row);
    $col_keys = array_keys($row);
    for( $n = 0; $n < $col_count; $n++ )
    {
        $R =& $row[ $col_keys[$n] ];
        ?><td>
        <div class="box">
            <h2><a class="cc_file_link" href="<?= $R['file_page_url'] ?>"><?= $R['upload_name'] ?></a></h2>
            <div class="info_div"><a class="info_button" id="_plinfo_<?= $R['upload_id'] ?>"></a></div>
            <div><?= $T->String('str_by')?>: <a class="cc_user_link" href="<?= $R['artist_page_url'] ?>"><?= $R['user_real_name'] ?></a></div>
            <a class="cc_user_link" href="<?= $R['artist_page_url'] ?>"><img src="<?= $R['user_avatar_url'] ?>" /></a>
            <div class="pick_text">
                <?= $R['edpick']['review'] ?>
            </div>
            <div class="reviewer">
                <?= $R['edpick']['reviewer'] ?>
            </div>
            <div style="clear:both" />            
            <?
              $A['render_record'] = $R;
              $T->Call('render_link');              
            ?>
            <div style="clear:both" />            
        </div>
        </td><?
    }
    ?></tr><?
}
?></table>
<script  src="<?= $T->URL('js/info.js') ?>"></script>
<script type="text/javascript">
var uinfo = new ccUploadInfo();
uinfo.hookInfos('.info_button',$('edpicks'));
</script>
<?
$T->Call('prev_next_links');
$T->Call('flash_player');
?>
<script type="text/javascript">
if( window.ccEPlayer )
    ccEPlayer.hookElements($('edpicks'));
</script>
