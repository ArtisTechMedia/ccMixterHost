<? %%
[meta]
    type     = format
    desc     = _('Medium verbose (license, attribution, download link, tags, description)')
    dataview = default
[/meta]
%%?>
<div  id="cc_list">
<table >
%loop(records,R)%   
<tr ><td class="cc_list_fileinfo">
    <a href="%(#R/license_url)%" title="%(#R/license_name)%" class="cc_liclogo">
        <img  src="%(#R/license_logo_url)%" />
    </a>
    <a href="%(#R/file_page_url)%" class="cc_file_link">%chop(#R/upload_name,chop)%</a> <?= $T->String('str_by')?>
    <a href="%(#R/artist_page_url)%">%chop(#R/user_real_name,chop)%</a>
    <span class="cc_upload_date">%(#R/upload_date_format)%</span>
    %if_not_null(#R/files/0/download_url)%
        <a class="cc_download_url" href="%(#R/files/0/download_url)%">%(#R/files/0/file_nicname)%</a>
    %end_if%
    <div  class="taglinks"><?= trim($R['upload_tags'],',') ?></div>
    %if_not_null(#R/upload_description_html)%
        <div  class="cc_description">%(#R/upload_description_html)%</div>
    %end_if%
    </td>
</tr>
%end_loop%
</table>
<i class="cc_tagline"><span>%call(format_sig)%</span></i>
</div>