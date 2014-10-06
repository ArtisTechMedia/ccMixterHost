%%
[meta]
    type     = format
    desc     = _('Named download links (good for Y! Music player)')
    dataview = links_by
[/meta]
%%

<div  id="cc_list_<?=md5($A['qstring'])?>">
%loop(records,R)%
   <div>
     <a class="cc_download_file_link" href="%(#R/download_url)%">%(#R/upload_name)%</a>
     %text(str_by)%
     <a class="cc_artist_page_link" href="%(#R/artist_page_url)%">%chop(#R/user_real_name,chop)%</a>
    [<a href="%(#R/file_page_url)%" class="cc_file_link">%text(str_detail)%</a>]
   </div>
%end_loop%
<i class="cc_tagline"><span>%call(format_sig)%</span></i>
</div>
