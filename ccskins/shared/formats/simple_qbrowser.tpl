<?/*
[meta]
    desc     = _('Upload, info, playlist style')
    type     = query_browser_template
    dataview = playlist_line
[/meta]
*/
?>
%loop(records,R)%
<div  class="trr">
    <div  class="tdc cc_playlist_item" id="_pli_%(#R/upload_id)%">
        <span>
            <a class="cc_playlist_pagelink" id="_plk_%(#R/upload_id)%" target="_parent" href="%(#R/file_page_url)%">%(#R/upload_name)%</a>
        </span>
    </div>
    <div class="tdc"><a class="info_button" id="_plinfo_%(#R/upload_id)%"></a></div>
    %if_not_empty(#R/fplay_url)%
    <div class="tdc cc_playlist_pcontainer"><a class="cc_player_button cc_player_hear" id="_ep_%(#R/upload_id)%" href="%(#R/fplay_url)%"></a></div>
    %end_if%
    <div  class="hrc"></div>
</div>
%end_loop%