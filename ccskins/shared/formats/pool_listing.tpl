<?/*%%
[meta]
    type     = template_component
    desc     = _('Display Pool Items')
    dataview = pool_item_list
    datasource = pool_items
[/meta]
%%*/?>

<link rel="stylesheet" type="text/css" title="Default Style" href="%url('css/pool_listing.css')%" />

<div id="upload_wrapper">
  <div id="upload_middle">
    <div id="upload_listing">&nbsp;
%loop(records,R)%
  <div class="upload" >
        <div class="pool_item_info"
          %if_not_null(#R/license_logo_url_small)%
              <a class="lic_link" href="%(#R/license_url)%" about="%(#R/pool_item_url)%"
                      rel="license" title="%(#R/license_name)%" ><img src="%(#R/license_logo_url_small)%" /></a> 
          %end_if%
          %if_not_null(#R/pool_item_extra/ttype)%
                    <? $tstr = $T->String('str_trackback_type_' . $R['pool_item_extra']['ttype']) ?>
                    <span class="pool_item_type"><?= $tstr ?></span>: 
          %end_if%
            <a class="cc_file_link upload_name" href="%(#R/pool_item_page)%">%(#R/pool_item_name)%</a>
            <br />%text(str_by)% <a class="cc_user_link" href="%(#R/pool_item_url)%">%(#R/pool_item_artist)%</a>
          %if_not_null(#R/pool_item_date)%
             <div class="pool_item_date"><b>%text(date_added)%</b> %(#R/pool_item_date)%</div>
          %end_if%
            <br /><a class="cc_external_link" href="%(#R/pool_item_url)%"><span>%text(str_external_link)%</span> 
                         <img src="%url(images/remote.gif)%" /></a>

         </div>
    %if_not_null(#R/remix_children)%
        <div id="remix_info"><h2>%text(str_list_usedby)%</h2>
        %loop(#R/remix_children,P)%
            <div>
                <a class="remix_links cc_file_link" href="%(#P/file_page_url)%">%chop(#P/upload_name,15)%</a> %text(str_by)%
                 <a class="cc_user_link" href="%(#P/artist_page_url)%">%chop(#P/user_real_name,17)%</a></div>
        %end_loop%
        %if_not_null(#R/more_children_link)%
            <a class="remix_more_link" href="%(#R/more_children_link)%">%text(str_more)%...</a>
        %end_if%
        </div>
    %end_if%

    %if_not_null(#R/remix_parents)%
        <div id="remix_info"><h2>%text(str_list_uses)%</h2>
        %loop(#R/remix_parents,P)%
            <div>
                <a class="remix_links cc_file_link" href="%(#P/file_page_url)%">%chop(#P/upload_name,15)%</a> %text(str_by)%
                 <a class="cc_user_link" href="%(#P/artist_page_url)%">%chop(#P/user_real_name,17)%</a></div>
        %end_loop%
        %if_not_null(#R/more_parents_link)%
            <a class="remix_more_link" href="%(#R/more_parents_link)%">%text(str_more)%...</a>
        %end_if%
        </div>
    %end_if%

    <div style="clear:both">&nbsp;</div>
  </div><!--  end upload  -->
%end_loop%
    </div> <!-- upload listing -->
  </div>
</div> <!-- upload middle/wrapper -->

<br style="clear:both" />
%call(prev_next_links)%
