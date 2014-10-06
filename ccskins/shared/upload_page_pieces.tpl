<!-- template upload_page_pieces.tpl -->
%%
  Note, this template assumes that xmlns cc and dc
  are defined at a higher level (like at page.tpl)
%%
%macro(upload_page_tbl_layout)%
    <style>
        table#upload_page_table td {
            vertical-align: top;
        }
        td#upload_menu_box {
            width: 20%;
        }
        td#upload_sidebar_td {
            width: 30%;
        }
    </style>
    %call(upload_page_head)%
    <table id="upload_page_table">
        <tr>
            <td  id="upload_menu_box">
            </td>
            <td id="upload_middle_td">
                %call(upload_page_middle)%
            </td>
            <td id="upload_sidebar_td">
                %call(upload_page_sidebar)%
            </td>
        </tr>
    </table>
    %call(upload_page_foot)%
%end_macro%

%macro(upload_page_div_layout)%
    %call(upload_page_head)%
    <div id="upload_wrapper">
        <div id="upload_middle">
            %call(upload_page_middle)%
        </div>
    </div><!-- upload_middle/wrapper -->
    <div id="upload_sidebar_box">
        %call(upload_page_sidebar)%
    </div><!-- sidebar box -->
    <div id="upload_menu_box">
    </div><!-- upload_menu_box -->
    <div style="clear:both">&nbsp;</div>
    %call(upload_page_foot)%
%end_macro%

%macro(upload_page_head)%
    <link rel="stylesheet" type="text/css" title="Default Style" href="%url('css/upload_page.css')%" />
    %map(record,records/0)% 
    %map(#R,record)%
    <script type="text/javascript">
    var ratings_enabled = '%(#R/ratings_enabled)%';
    </script>

    %if_not_null(flagging)%
        <a class="flag upload_flag" title="%text(str_flag_this_upload)%" href="%(home-url)%flag/upload/%(#R/upload_id)%">&nbsp;</a>
    %end_if%
    <div id="date_box">
        %text(str_list_date)%: %(#R/upload_date)%<!-- -->
        %if_not_empty(#R/upload_last_edit)%
        <span id="modified_date">%text(str_list_lastmod)%: %(#R/upload_last_edit)%&nbsp;%if_not_null(#R/upload_extra/last_op)% (%text(#R/upload_extra/last_op)%) %end_if%</span>
        %end_if%
    </div>
%end_macro%

%macro(upload_page_middle)%
    %map(#R,record)%
    <div class="box">
        <img src="%(#R/user_avatar_url)%" style="float:right" />
        <table cellspacing="0" cellpadding="0" id="credit_info">
        %if_null(#R/collab_id)%
            <tr><th>%text(str_by)%</th><td><a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a></td></tr>
        %else%
             <tr><th>%text(str_collab_project)%:</th><td><a href="%(home-url)%collab/%(#R/collab_id)%">%(#R/collab_name)%</a></td></tr> 
                  <tr><th>%text(str_collab_credit)%:</th><td>
            %loop(#R/collab_users,U)%
                <a href="%(home-url)%people/%(#U/user_name)%">%(#U/user_real_name)%</a> %(#U/collab_user_credit)% %if_not_last(#U)% <br /> %end_if%
            %end_loop%
            </td></tr>
        %end_if%

        %if_not_null(#R/upload_extra/featuring)%
            <tr><th>%text(str_featuring)%</th><td>%(#R/upload_extra/featuring)%</td></tr>
        %end_if%

        %if_not_null(#R/files/0/file_format_info/ps)%
            <tr><th>%text(str_list_length)%</th><td>%(#R/files/0/file_format_info/ps)%</td></tr>
        %end_if%

        %if_not_null(#R/upload_extra/bpm)%
            <tr><th>%text(str_bpm)%</th><td>%(#R/upload_extra/bpm)%</td></tr>
        %end_if%

        %if_not_null(#R/ratings_enabled)%
            %if_empty(#R/thumbs_up)%
            <tr><th id="rate_label_%(#R/upload_id)%">%if_not_null(#R/ratings)%<!-- -->%text(str_ratings)% %end_if%</th>
                <td><span id="rate_block_%(#R/upload_id)%">%call('util.tpl/ratings_stars_user')% </span></td></tr>
            %else%
                <tr><th>%text(str_recommends)%</th>
                <td>%call('util.php/recommends')%</td></tr>
            %end_if%
        %end_if%

        </table>

        %if_not_null(#R/upload_description_html)%
            <?  $scroll = (strlen($R['upload_description_html']) > 400) || (preg_match_all('/<br/',$R['upload_description_html'],$brs) > 17); ?>
            %if_not_null(#scroll)%
                <div style="overflow:scroll;height:19em;border:1px solid #BBB;padding:4px;">
            %end_if%
            %(#R/upload_description_html)%
            %if_not_null(#scroll)%
                </div>
            %end_if%
        %end_if%

        <div class="taglinks">
        %loop(#R/upload_taglinks,tag)%
            <a href="%(#tag/tagurl)%">%(#tag/tag)%</a>%if_not_last(#tag)%, %end_if%
        %end_loop%
        </div>

        %map(render_record,#R)%
        %if_not_empty(#R/render_link_macro)%
            %call(#R/render_link_macro)%
        %else%
            %call(render_link)%
        %end_if%

        <div class="info_box_clear">&nbsp;</div>
    </div><!-- info box -->

    %if_not_null(#R/file_macros)%
        <div class="box">
        %loop(#R/file_macros,M)%
            %call(#M)%
        %end_loop%
        </div>
    %end_if%
%end_macro%

%macro(upload_page_sidebar)%
    %map(#R,record)%
    
    <div class="box" id="license_info"
        %if_not_null(#R/files/0/file_extra/sha1)% about="urn:sha1:%(#R/files/0/file_extra/sha1)%" %end_if% >
      <p>
        <div id="license_info_t">
            "<a href="%(#R/file_page_url)%" rel="cc:attributionURL"><span %if_attr(#R/dcmi,href)% property="dc:title" %if_attr(#R/dcmirel,rel)%>%(#R/upload_name)%</span></a>" <br />
            %text(str_by)%
            <span property="cc:attributionName"> %(#R/user_real_name)%</span><br /><br />
            %if_null(#R/is_waiver)%
              %(#R/year)% - %text(str_lic)%
            %end_if%<br />
            Creative Commons<br />
            <a rel="license" href="%(#R/license_url)%"
                   title="%(#R/license_name)%">%(#R/license_name)%</a><br /><br />
            <a rel="license" href="%(#R/license_url)%"
                  title="%(#R/license_name)%"><img title="%(#R/license_name)%"
                  src="%(#R/license_logo_url)%" /></a><br /><br />
            <p id="license_more_info">
                %if_null(#R/is_waiver)%
                    <!-- license -->
                    <?= $T->String(array('str_lic_click',"<a href=\"{$R['license_url']}\">","</a>")); ?>
                %else%<!-- waiver -->
                    <?= $T->String(array('str_lic_waiver',"<a href=\"{$R['license_url']}\">","</a>")); ?>                
                %end_if%
            </p>
        </div>
      </p>
    </div>

    %if_not_null(#R/edpick)%
        <div class="box" id="pick_box">
            <img src="%url('images/big-red-star.gif')%" />
            <h2>%text(str_edpick)%</h2>
                <p>%(#R/edpick/review)%</p>
                <div class="pick_reviewer">%(#R/edpick/reviewer)%</div>
        </div>
    %end_if%

    %if_not_null(#R/remix_parents)%
        <div class="box" id="remix_info">
            <img src="%url('images/downloadicon.gif')%" />
            <h2>%text(str_list_uses)%</h2>
        %if_not_null(#R/parents_overflow)%
            <div style="overflow: scroll;height:300px;">
        %end_if%
        %loop(#R/remix_parents,P)%
            <div>
              <a class="remix_links cc_file_link" rel="dc:source" href="%(#P/file_page_url)%">%(#P/upload_name)%</a>
              %text(str_by)%
              <a href="%(#P/artist_page_url)%" class="cc_user_link user_name">%(#P/user_real_name)%</a>
            </div>
        %end_loop%
        %if_not_null(#R/parents_overflow)%
            </div>
        %end_if%
        <div id="histogram_link">
          <p>
            <a href="%(query-url)%t=upload_histogram&ids=%(#R/upload_id)%">%text(str_remix_history)%</a>
          </p>
        </div>
        </div>
    %end_if%

    %if_not_null(#R/remix_children)%
        <div class="box" id="remix_info">
            <img src="%url('images/uploadicon.gif')%" />
            <h2>%text(str_list_usedby)%</h2>
        %if_not_null(#R/children_overflow)%
            <div style="overflow: scroll;height:300px;">
        %end_if%
        %loop(#R/remix_children,P)%
            <div>
            %if_not_null(#P/pool_item_extra/ttype)%
                <? $tstr = $T->String('str_trackback_type_' . $P['pool_item_extra']['ttype']) ?>
                <span class="pool_item_type">%(#tstr)%</span>: 
            %end_if%
                <a class="remix_links cc_file_link" href="%(#P/file_page_url)%">%(#P/upload_name)%</a> <span>%text(str_by)%</span>
                 <a class="cc_user_link" href="%(#P/artist_page_url)%">%(#P/user_real_name)%</a>
            </div>
        %end_loop%
        %if_not_null(#R/children_overflow)%
            </div>
        %end_if%
        </div>
    %end_if%
%end_macro%

%macro(upload_page_foot)%
    %map(#R,record)%
    %call('flash_player')%
    <script type="text/javascript">
    if( window.ccEPlayer )
        ccEPlayer.hookElements($('upload_middle'));
    </script>
    %if_not_null(enable_playlists)%
        %if_not_null(logged_in_as)%
            %call('playlist.xml/playlist_menu')%
        %end_if%
    %end_if%

    <script type="text/javascript">
    function menu_cb(resp) {
        $('upload_menu_box').innerHTML = resp.responseText;
        var dl_hook = new queryPopup("download_hook","download",str_download); 
        dl_hook.height = '550';
        dl_hook.width = '700';
        dl_hook.hookLinks();
        if( window.round_box_enabled )
            cc_round_boxes();
        if( user_name && ratings_enabled )
        {
            null_star = '%url('images/stars/star-empty.gif')%';
            full_star = '%url('images/stars/star-red.gif')%';
            rate_return_t = 'ratings_stars_user';
            recommend_return_t = 'recommends';
            new userHookup('upload_list', 'ids=%(#R/upload_id)%');
            %if_not_null(enable_playlists)%
                playlist_hook_menu();
            %end_if%
        }
    }
    var menu_url = query_url + 't=upload_menu&f=html&ids=%(#R/upload_id)%';
    new Ajax.Request( menu_url, { method: 'get', onComplete: menu_cb } );
    </script>
%end_macro%
