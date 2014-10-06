<? %%
[meta]
    type     = format
    desc     = _('Links with license, attribution)')
    dataview = default
[/meta]
%%?>
<ul>
%loop(records,R)%
<li style="white-space:nowrap"><a href="%(#R/file_page_url)%" class="cc_file_link">%chop(#R/upload_name,chop)%</a> %text(str_by)%: <a href="%(#R/artist_page_url)%">%chop(#R/user_real_name,chop)%</a> -- <a href="%(#R/license_url)%" title="%(#R/license_name)%" style="font-size:80%;font-style:italic;" >(cc) %(#R/license_name)%</a></li>
%end_loop%
</ul>
<i class="cc_tagline"><span>%call(format_sig)%</span></i>
