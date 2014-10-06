<?/*
[meta]
    type     = format
    desc     = _('Links to upload page with attribution and stream links')
    dataview = links_stream
    embedded = 1
[/meta]
[dataview]
function links_stream_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $stream_url = url_args( ccl('api','query','stream.m3u'), 'f=m3u&ids=' );

    $sql =<<<EOF
SELECT 
    upload_id, upload_name, CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    user_real_name, user_name, 
    CONCAT( '$urlp', user_name ) as artist_page_url,
    IF( upload_tags LIKE '%,audio,%', CONCAT( '$stream_url', upload_id ) , '' ) as stream_url,
    upload_contest, upload_name
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
JOIN cc_tbl_licenses ON upload_license = license_id
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'name' => 'list_wide',
                   'e'  => array( CC_EVENT_FILTER_FILES, ), 
                );
}
[/dataview]
*/?>
<div  id="cc_list">
%loop(records,R)%
    <div>
    %if_not_null(#R/stream_url)%
        <div><a href="%(#R/stream_url)%" class="cc_streamlink" type="audio/x-mpegurl">&nbsp;</a></div>
    %end_if%
     <a href="%(#R/file_page_url)%" class="cc_file_link">%chop(#R/upload_name,chop)%</a> %text(str_by)%
     <a href="%(#R/artist_page_url)%">%chop(#R/user_real_name,chop)%</a>
   </div>
   <div style="clear:left"> </div>
%end_loop%
<i class="cc_tagline"><span>%call(format_sig)%</span></i>
</div>
%call('util.tpl/patch_stream_links')%
