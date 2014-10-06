<?/*

[meta]
    desc     = _('Listing for recently updated')
    type     = query_browser_template
    example  = t=updated&sort=last_edit&ord=desc
    dataview = updated
    embedded = 1
[/meta]


[dataview]
function updated_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $stream_url = url_args( ccl('api','query','stream.m3u'), 'f=m3u&ids=' );
    $usersql = cc_fancy_user_sql('user_real_name');

    $sql =<<<EOF
SELECT 
    upload_id, 
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    $usersql, user_name, upload_name, upload_extra,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    IF( upload_tags LIKE '%,audio,%', CONCAT( '$stream_url', upload_id ) , '' ) as stream_url,
    DATE_FORMAT( upload_date, '%a, %b %e, %Y @ %l:%i %p' ) as upload_date_format,
    DATE_FORMAT( upload_last_edit, '%a, %b %e, %Y @ %l:%i %p' ) as upload_last_edit_format,
    upload_contest, upload_name
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where% AND upload_last_edit > '0000-00-00'
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where% AND upload_last_edit > '0000-00-00'
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'name' => 'list_wide',
                   'e'  => array( CC_EVENT_FILTER_FILES,
                                  CC_EVENT_FILTER_EXTRA,
                                  CC_EVENT_FILTER_PLAY_URL,
)
                );
}
[/dataview]

*/
function cc_op_to_str($op)
{
    switch( $op ) {
        case 'add':
            return 'str_updated_file_added';
        case 'replace':
            return 'str_updated_file_replaced';
        case 'del':
            return 'str_updated_file_deleted';
        default:
            return 'str_updated_unknown';
    }
}
?>
<link  rel="stylesheet" type="text/css" href="%url( 'css/playlist.css' )%" title="Default Style"></link>
<link  rel="stylesheet" type="text/css" href="%url( 'css/info.css' )%" title="Default Style"></link>
<style>
.cc_playlist_item {
    width: 600px;
}
td.last_op {
    font-weight: bold;
    color: white;
    padding: 2px 0px 2px 4px;
}
.last_op_add {
    background-color: #484;
}
.last_op_replace {
    background-color: #884;
}
.last_op_del {
    background-color: #844;
}

div.trr {
    margin-top: 1em;
}

a.cc_user_link, a.cc_user_link:visited, a.cc_user_link:link {
    font-weight: bold;
}
</style>
<div style="width:90%;margin: 0px auto;">
%loop(records,R)%
<div  class="trr">
    <div  class="tdc cc_playlist_item" id="_pli_%(#R/upload_id)%">
        <span>
            <a class="cc_playlist_pagelink" id="_plk_%(#R/upload_id)%" target="_parent" href="%(#R/file_page_url)%">%(#R/upload_name)%</a>
        </span>%text(str_by)%
        <a target="_parent" class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a>
    </div>
    <div class="tdc"><a class="info_button" id="_plinfo_%(#R/upload_id)%"></a></div>
    %if_not_empty(#R/fplay_url)%
    <div class="tdc cc_playlist_pcontainer"><a class="cc_player_button cc_player_hear" id="_ep_%(#R/upload_id)%" href="%(#R/fplay_url)%"></a></div>
    %end_if%
    <div  class="hrc"></div>
</div>
    <table>
        <tr><td>%text(str_list_date)%:</td><td>%(#R/upload_date_format)%</td></tr>
        <tr><td>%text(str_list_lastmod)%: </td><td>%(#R/upload_last_edit_format)%</td></tr>
        <tr><td></td><td class="last_op last_op_%(#R/upload_extra/last_op)%"><?= $T->String(cc_op_to_str($R['upload_extra']['last_op'])) ?></td></td>
        %loop(#R/files,F)%
            <tr><td></td><td>%(#F/file_nicname)% %(#F/file_filesize)%: %(#F/file_name)% </td></tr>
        %end_loop%
    </table>
%end_loop%
%call(prev_next_links)%
</div>

<script  src="<?= $T->URL('/js/info.js') ?>"></script>
<script>
var infos = new ccUploadInfo();
infos.hookInfos('.info_button',null);
</script>