%%
[meta]
    type = ajax_component
    desc = _('Playlist head (set ids=playlist_id)')
    dataview = playlist_detail
    required_args = ids
[/meta]
%%
<!-- template playlist_2_head -->
%if_null(records)%
    %return%
%end_if%

%map(#R,records/0)%
<table  class="cc_pl_table">
<tr>
<td>
    %if_not_null(#R/menu)%
    <ul  class="cc_playlist_owner_menu light_bg dark_border">
    %loop(#R/menu,mi)%
        <li>
        <a target="_parent" href="%(#mi/url)%" id="%(#mi/id)%" class="%(#mi/class)%"><span>%text(#mi/text)%</span></a>
        </li>
    %end_loop%
    </ul>
    %end_if%
</td>
<td>
%if_null(skip_playlist_feed)%
<div class="cc_playlist_feed">
   <a target="_parent" class="pl_download" id="dlcart%(#R/cart_id)%" href="javascript://dl"><img src="%url('images/menu-download.png')%" /></a>
   <? if( !empty($GLOBALS['strings-profile']) && ($GLOBALS['strings-profile'] == 'audio') ) { ?>
   <a target="_parent" href="%(query-url)%playlist=%(#R/cart_id)%&f=xspf"><img src="%url('images/xspf.png')%" /></a>
   <? } ?>
   <a target="_parent" href="%(#R/feed_url)%"><img src="%url('images/feed-icon16x16.png')%" /></a>
   <a target="_parent" href="%(#R/share_url)%"><img src="%url('images/share-link.gif')%" /></a>
</div>
%end_if%
<a target="_parent" href="%(#R/permalink_url)%">
    <div class="cc_playlist_title">%(#R/cart_name)%</div>
</a>
<span class="cc_playlist_date">
  %text(str_pl_created_by)% <a target="_parent" class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a> 
  %(#R/cart_date_format)%
</span>
%if_not_null(#R/cart_desc_html)%
    <div  class="gd_description" id="pldesc_%(#R/cart_id)%">
        <div style="padding: 10px;">%(#R/cart_desc_html)%</div>
    </div>
%end_if%
%if_not_null(cart_msgs)%
    <div  class="gd_description" id="pldesc_%(#R/cart_id)%">
        <div  style="padding: 10px;">
            %loop(cart_msgs,cmsg)%
               %text(#cmsg)%<br /><br />
            %end_loop%
        </div>
    </div>
%end_if%
%if_not_null(#R/cart_tags)%
    <div class="taglinks" style="margin:4px" >
    %map(tag_urlbase,#R/browse_tag_url)%
    %map(tag_str,#R/cart_tags)%<!-- tags: -->
    %text(str_tags)%: %call('tags.php/taglinks_str')%
    </div>
%end_if%
 </td></tr>
<tr><td></td><td  style="height:22px;">
    <div  class="cc_playlist_pcontainer" id="plc_id"></div>
</td></tr>
</table>
