%%
[meta]
    type     = format
    desc     = _('Links to upload page with attribution and download links (UL)')
    dataview = links_by
[/meta]
%%
<div  id="cc_list">
<ul>
%loop(records,R)%
   <li>
     <a href="%(#R/file_page_url)%" class="cc_file_link">%chop(#R/upload_name,chop)%</a>%text(str_by)%
     <a href="%(#R/artist_page_url)%">%chop(#R/user_real_name,chop)%</a>
     <a href="%(#R/download_url)%">%(#R/files/0/file_nicname)%</a>
   </li>
%end_loop%
</ul>
<i class="cc_tagline"><span>%call(format_sig)%</span></i>
</div>
