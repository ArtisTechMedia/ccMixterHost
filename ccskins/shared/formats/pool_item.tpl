<?/*%%
[meta]
    type       = template_component
    desc       = _('Display Individual Pool Item')
    dataview   = pool_item_list
    datasource = pool_items
[/meta]
%%*/?>
<link rel="stylesheet" title="Default Style" type="text/css" href="%url(css/upload_page.css)%" />
<style type="text/css">
div#upload_wrapper{float:left;width:100%;}
div#upload_middle{margin: 0 30% 0 5%;padding-left:2.0em;}
div#upload_sidebar_box{float:left;width:30%;margin-left:-30%;}

#pool_info {
    text-align: center;
}
</style>

<!--[if IE]> 
<style type="text/css">
div#upload_wrapper{float:left;width:100%;}
div#upload_middle{margin: 0 35% 0 2%;padding:0px;}
div#upload_sidebar_box{float:left;width:30%;margin-left:-30%;}
</style>
<![endif]-->
%if_null(records/0)%
    <p>Can't find this pool item</p>
    %return%
%end_if%

%map(#R,records/0)%
<div id="upload_wrapper">
  <div id="upload_middle">
        <div class="box">
            <table cellspacing="0" cellpadding="0" id="credit_info">
                <tr><th></th>
                    <td><span class="cc_file_link upload_name" style="font-size:2em;">%(#R/pool_item_name)%</span></td></tr>
                <tr><th>%text(str_by)% </th>
                    <td><i>%(#R/pool_item_artist)%</i></td></tr>
                %if_not_null(#R/pool_item_extra/ttype)%
                    <? $tstr = $T->String('str_trackback_type_' . $R['pool_item_extra']['ttype']) ?>
                    <tr><th></th><td><?= $tstr ?><td></tr>
                %else%
                    <tr><th>%text(str_pool_from)% </th>
                        <td><a href="%(#R/pool_url)%">%(#R/pool_name)%</a></td></tr>
                %end_if%
                %if_not_null(#R/pool_item_description)%
                    <tr><th></th><td>%(#R/pool_item_description)%</td></tr>
                %end_if%
                %if_not_null(#R/pool_item_extra/embed)%
                    <tr><td colspan="2">%(#R/pool_item_extra/embed)%</td></tr>
                %end_if%
                %if_not_null(#R/pool_item_extra/posted)%
                    <tr><th>%text(str_pool_posted_by)%</th><td>%(#R/pool_item_extra/posted)%</td></tr>
                %end_if%
                <tr><th>%text(str_external_link)% </th>
                    <td><a class="cc_external_link" href="%(#R/pool_item_url)%"><span>%chop(#R/pool_item_url,44)%</span> 
                         <img src="%url(images/remote.gif)%" /></a></td></tr>
                %if_not_null(is_admin)%
                <tr><th><br />admin</th>
                    <td><br /><a class="small_button" href="%(query-url)%match=_web&datasource=pool_items&t=pool_item_admin&ids=%(#R/pool_item_id)%"><span>edit</span> </a></td></tr>
                %end_if%
            </table>
        </div>

        %if_not_null(#R/remix_children)%
            <div class="box" id="remix_info">
                <h2>%text(str_list_usedby)%</h2>
                <p style="position:relative;top:0px;left:0px;"><img src="%url('images/uploadicon.gif')%" /></p>
            %if_not_null(#R/children_overflow)%
                <div style="overflow: scroll;height:300px;">
            %end_if%
            %loop(#R/remix_children,P)%
                <div><a class="remix_links cc_file_link" href="%(#P/file_page_url)%">%(#P/upload_name)%</a> <span>%text(str_by)%</span>
                     <a class="cc_user_link" href="%(#P/artist_page_url)%">%(#P/user_real_name)%</a></div>
            %end_loop%
            %if_not_null(#R/children_overflow)%
                </div>
            %end_if%
            </div>
        %end_if%

        %if_not_null(#R/remix_parents)%
            <div class="box" id="remix_info">
                <h2>%text(str_list_uses)%</h2>
                <p style="position:relative;top:0px;left:0px;"><img src="%url('images/downloadicon.gif')%" /></p>
            %if_not_null(#R/parents_overflow)%
                <div style="overflow: scroll;height:300px;">
            %end_if%
            %loop(#R/remix_parents,P)%
                <div><a class="remix_links cc_file_link" href="%(#P/file_page_url)%">%(#P/upload_name)%</a> <span>%text(str_by)%</span>
                     <a class="cc_user_link" href="%(#P/artist_page_url)%">%(#P/user_real_name)%</a></div>
            %end_loop%
            %if_not_null(#R/parents_overflow)%
                </div>
            %end_if%
            </div>
        %end_if%

    </div>
</div>
<div id="upload_sidebar_box">

    <? if( $R['pool_short_name'] != '_web' ) { ?>
    <div class="box" id="license_info">
      <p><img src="%(#R/license_logo_url)%" />
        <div id="license_info_t" >
            %text(str_lic)%<br />
            Creative Commons<br />
            <a href="%(#R/license_url)%">%(#R/license_name)%</a><br />
        </div>
      </p>
    </div>
    <? } ?>

    <div class="box" id="pool_info">
        <h2>%text(str_pool_info_head)%</h2>
        <a class="pool_name" class="cc_external_link" href="%(#R/pool_site_url)%"><span>%(#R/pool_name)%</span> 
            <img src="%url(images/remote.gif)%" /></a>
        <p>"%(#R/pool_description)%"</p>
    </div>
</div>