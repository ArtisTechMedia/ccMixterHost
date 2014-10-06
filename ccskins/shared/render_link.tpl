<!-- template render_play_link -->
%macro(render_play_link)%
    %map(#R,render_record)%
    %if_not_empty(#R/fplay_url)%
        <div class="playerdiv"><span class="playerlabel">%text(str_play)%</span><a class="cc_player_button cc_player_hear" id="_ep_%(#R/upload_id)%">
            <span>%(#R/upload_name)%</span></a></div>
        <script type="text/javascript"> $('_ep_%(#R/upload_id)%').href = '%(#R/fplay_url)%' </script>
    %end_if%
    %if_not_empty(#R/flash_id)%
        <div class="flash_link_div">
            <a class="small_button" id="%(#R/flash_id)%" href="javascript://flash play">%text(str_play)%</a>
        </div>
    %end_if%
    %if_not_empty(#R/image_id)%
        <div class="image_link_div">
        %if_not_empty(#R/thumbnail)%
            <a class="thumbnail_link" id="%(#R/image_id)%" href="javascript://image"><img id="thumbnail_%(#R/upload_id)%" class="upload_thumbnail" src="%(#R/thumbnail)%" /></a>
        %else%
            <a class="small_button" id="%(#R/image_id)%" href="javascript://image">%text(str_show)%</a>
        %end_if%
        </div>
    %end_if%
%end_macro%
