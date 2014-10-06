%%
[meta]
    type     = format
    desc     = _('Links to upload page with download links')
    dataview = links_dl
[/meta]
%%
<div  id="cc_list">
%loop(records,R)%
   <div>
     <a href="%(#R/file_page_url)%" class="cc_file_link">%chop(#R/upload_name,chop)%</a> 
     <a href="%(#R/download_url)%">%(#R/files/0/file_nicname)%</a>
   </div>
%end_loop%
<i class="cc_tagline"><span>%call(format_sig)%</span></i>
</div>