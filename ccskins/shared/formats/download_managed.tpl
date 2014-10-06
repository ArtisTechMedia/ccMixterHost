<?/*
[meta]
    type = download_template
    desc = _('Download files [managed]')
    dataview = files
[/meta]
*
* Don't call this template directly, it is picked
* by admin/downloads
* 
*/?>

<? // NOTE: Enable counting, checksum, RDF license at admin/download ?>
    
<div  id="cc_download">
%loop(records,R)%
<p class="upload_name">%(#R/upload_name)% <span style="font-style:italic">%if_not_null(#R/upload_extra/bpm)% (BPM: %(#R/upload_extra/bpm)%) %end_if%</span></p>
<ol>
    %loop(#R/files,F)%
         <li>
            <? $url = ccl('download',$R['user_name'],$F['file_id']); ?>
            %(#F/file_nicname)% %(#F/file_filesize)%: <a href="%(#url)%">%(#F/file_name)%</a> 
         </li>
    %end_loop%
</ol>
%end_loop%
</div>

