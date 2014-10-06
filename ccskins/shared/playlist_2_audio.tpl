%%
[meta]
    desc           = _('Playlist style lines for audio tracks (no style info)')
    type           = query_browser_template
    dataview       = playlist_line
[/meta]
%%
<!-- template playlist_2_audio -->
%loop(records,R)%
<div class="trr">
  <div class="tdc cc_playlist_item" id="_pli_%(#R/upload_id)%">
    <span>
      <a class="cc_playlist_pagelink cc_file_link" id="_plk_%(#R/upload_id)%" target="_parent" 
            href="%(#R/file_page_url)%">%chop(#R/upload_name,30)%</a>
     </span>%text(str_by)% <a target="_parent" class="cc_user_link" href="%(#R/artist_page_url)%">%chop(#R/user_real_name,30)%</a>
  </div>
  %if_null(skip_info_button)%
  <div class="tdc"><a class="info_button" title="[ info ]" id="_plinfo_%(#R/upload_id)%"></a></div>
  %end_if%
  %if_null(skip_action_button)%
  <div class="tdc pl_action_button"><a class="menuup_hook" id="_plaction_%(#R/upload_id)%" title="[ action ]">&nbsp;</a></div>
  %end_if%
  %if_null(skip_found_in)%<!-- -->
    %if_not_null(#R/playlist_browse_url)%
    <div class="tdc" style="padding-left:5px"><a href="%(#R/playlist_browse_url)%" class="pl_found_in" title="%text(str_pl_found_in)%: (%(#R/upload_num_playlists)%) %text(str_pl_playlists)% "> (%(#R/upload_num_playlists)%)</a>
    </div>
    %end_if%
  %end_if%
  <div class="tdc">
    <a href="%(#R/license_url)%" title="%(#R/license_name)%"><img src="%(#R/license_logo_url)%" title="%(#R/license_name)%"/></a>
  </div>
  %if_not_null(#R/fplay_url)%
    <div class="tdc cc_playlist_pcontainer">
      <a class="cc_player_button cc_player_hear" id="_ep_%(#R/upload_id)%" href="%(#R/fplay_url)%"> </a>
    </div>
  %end_if%<div class="hrc"> </div>
</div>
%end_loop%
