%%
[meta]
    type = ajax_component
    desc = _('Playlist head alt (set ids=playlist_id)')
    dataview = playlist_detail
    required_args = ids
[/meta]
%%
<!-- template playlist_2_info -->
%if_null(records)%
    %return%
%end_if%

%map(#R,records/0)%
<h2 class="playlist_info_h2" style="margin:0px;"><a target="_parent" href="%(#R/permalink_url)%">%(#R/cart_name)%</a></h2>
<div>
  %text(str_pl_created_by)% <a target="_parent" class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a> 
</div>
%if_not_null(#R/cart_desc_html)%
    <div  class="gd_description" id="pldesc_%(#R/cart_id)%" style="padding: 10px;">
        %(#R/cart_desc_html)%
    </div>
%end_if%
<a href="%(#R/share_url)%"><img src="%url('images/share-link.gif')%" /></a>

