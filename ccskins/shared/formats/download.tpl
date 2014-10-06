<?/*
[meta]
    type = download_template
    desc = _('Download files [direct links]')
    dataview = files
[/meta]
*
* Don't call this template directly, it is picked
* by admin/downloads
* 
*/?>
<div  id="cc_download">
<div id="download_help">
    <div>%text(str_list_IEtip)%</div>
    <div>%text(str_list_Mactip)%</div>
</div>
%loop(records,R)%
<? $rkey = array_keys($R['files']);
   $fname = $R['files'][$rkey[0]]['file_name'];
   $tname = preg_replace( '/(.*)(_[0-9]+)?\.[^.]+$/', '\1.txt', $fname );
   //t=files_info.tpl&f=text&returnas=(#tname)&ids=(#R/upload_id)
?>
<p class="upload_name">%(#R/upload_name)% <span style="font-style:italic">%if_not_null(#R/upload_extra/bpm)% (BPM: %(#R/upload_extra/bpm)%) %end_if%</span></p>
<ol>
    %loop(#R/files,F)%
         <li>
            %(#F/file_nicname)% %(#F/file_filesize)%: <a href="%(#F/download_url)%">%(#F/file_name)%</a> 
         </li>
    %end_loop%
</ol>
%end_loop%
</div>
