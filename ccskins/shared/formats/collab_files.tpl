<?/*
[meta]
    type     = ajax_component
    desc     = _('List files in collaboration')
    dataview = collab_files
    embedded = 1
    valid_args = ids, collab
[/meta]
[dataview]
function collab_files_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $stream_url = url_args( ccl('api','query','stream.m3u'), 'f=m3u&ids=' );
    $me = CCUser::CurrentUser();
    $admin = CCUser::IsAdmin() ? 1 : 0;

    $sql =<<<EOF
SELECT 
    upload_id, upload_name, 
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name, user_name, 
    CONCAT( '$urlp', user_name ) as artist_page_url,
    upload_contest, upload_name, upload_published, upload_extra,
    IF( collab_user = $me OR $admin, 1, 0 ) as is_collab_owner
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_collab_uploads ON upload_id = collab_upload_upload
JOIN cc_tbl_collabs ON collab_upload_collab = collab_id
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'name' => 'list_wide',
                   'e'  => array( CC_EVENT_FILTER_EXTRA, 
                                 CC_EVENT_FILTER_FILES, ), 
                );
}
[/dataview]

*/

?>
<!-- template collab_files -->
%if_null(records)%
    &nbsp;%text(str_collab_no_files)%&nbsp;
%return%
%end_if%
%loop(records,R)%
<div class="file_line" id="_file_line_%(#R/upload_id)%">
   <div class="ccud light_border"><?=  join(', ',array_diff(split(',',$R['upload_extra']['ccud']),array('media'))) ?></div>
   <div class="file_info"><a class="fname cc_file_link" href="%(#R/file_page_url)%">%(#R/upload_name)%</a> 
      %text(str_by)% <a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a></div>

%if_not_empty(#R/is_collab_owner)%
    <div><a href="javascript://remove " id="_remove_%(#R/upload_id)%" class="file_cmd file_remove small_button">
        <span >%text(str_collab_remove2)%</span></a></div>
    <div><a href="javascript://publish" id="_publish_%(#R/upload_id)%" class="file_cmd file_publish small_button">
        <span id="_pubtext_%(#R/upload_id)%">
            %if_null(#R/upload_published)%<!-- -->%text(str_collab_publish)% %else%<!-- -->%text(str_collab_hide)% %end_if%
        </span></a>
    </div>
    <div><a href="javascript://tags" id="_tags_%(#R/upload_id)%" class="file_cmd file_tags small_button">%text(str_collab_tags)%</a></div>
%end_if%

    <div class="tags" id="_user_tags_%(#R/upload_id)%">%(#R/upload_extra/usertags)%</div>
    <br />
    <table  class="file_dl_table">
        %loop(#R/files,f)%
        <tr><td class="nic">%(#f/file_nicname)%</td>
            <td><a href="%(#f/download_url)%" class="down_button"></a></td>
            <td>%(#f/file_name)%</td>
        </tr>
        %end_loop%
    </table>
</div>
%end_loop%