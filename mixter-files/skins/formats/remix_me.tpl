<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: remix_me.tpl 11249 2008-11-16 23:43:16Z fourstones $
*
*/
if( !defined('IN_CC_HOST') )
  die('Welcome to ccHost!');
?>
 %%
[meta]
 type = format
 dataview = remix_me
 embedded = 1
 desc = _('Remix ME (use with Other Peoples Remixes...)')
 required_args = remixesof
[/meta]
  [dataview]
function remix_me_dataview()
{
    $furl = ccl('files') . '/';
    $stream_base = url_args( ccl('api','query','stream.m3u'), 'f=m3u&ids='  ) . '/';

    $sql =<<<EOF
        SELECT upload_id, upload_contest, upload_name, user_name, user_real_name,
               CONCAT( '{$furl}', user_name, '/', upload_id ) as file_page_url,
               license_url, license_name,
               CONCAT( '{$stream_base}', upload_id ) as stream_url
               %columns%
        FROM cc_tbl_uploads
        JOIN cc_tbl_user ON upload_user=user_id
        JOIN cc_tbl_licenses ON upload_license=license_id
        %joins%
        %where%
        %order%
        %limit%
EOF;

      $sql_count =<<<EOF
        SELECT COUNT(*)
        FROM cc_tbl_uploads
        JOIN cc_tbl_user ON upload_user=user_id
        %joins%
        %where%
        %order%
EOF;

        return array( 'sql' => $sql, 'sql_count' => $sql_count, 'e' => array( CC_EVENT_FILTER_DOWNLOAD_URL ) );
}
  [/dataview]
%%
<div id="cc_remix_me_outer">
<div style="background-color:#EEE;border:3px solid #444;width:375px;height:300px;overflow:scroll;padding:0px;">
<div id="cc_remix_me">
<div style="padding:2px;background-color:#FFF;">
<? /*
   There's no need to edit this file if all you want to do is put in your own logo or title!

   1. In your ccHost installation click on 'Manage Site', then 'Banners and Footers', then 'Banner Text, Footers, etc.'
   2. In the header of the form click on 'clicking here'
   3. Enter 'remix-me-title' (without the quotes), click on 'Submit'
   4. If you want a logo repeat steps 1 and 2 and enter 'remix-me-logo' (without the quotes) click on Submit
   5. Now repeat step 1, you should see two new fields for these values 

*/ ?>
%if_not_null(remix-me-logo)% <img style="float:left" src="%(remix-me-logo)%" />%end_if% 
    <div style="float:right;font-size:10px;font-family:arial">
    <a onclick="window.open( '%(query-url)%t=remix_me_download&user=%(#_GET/remixesof)%&f=html', 'cchostextrawin','status=1,toolbar=0,location=0,menubar=0,directories=0,resizable=1,scrollbars=1,height=600,width=450'); return 0;"
       href="javascript://">download my stems</a><br />
    <a href="%(query-url)%t=remix_me_upload&user=%(#_GET/remixesof)%">upload your remix</a>
    </div>
%if_not_null(remix-me-title)%<p style="text-align:center;margin:5px;font-weight:bold;font-family:arial;font-size: 14px;">%(remix-me-title)%</p>%end_if%
</div>
<div style="clear:left;background-color:#DDD;padding:2px;margin:2px;font-weight:bold;font-family:verdana;font-size:11px;">
Here are the most recent remixes other people have done with my stems. </div>
<table cellpadding="0" cellspacing="0" style="text-align:left;margin:2px;clear:left;font-weight:normal;font-family:verdana;font-size:11px;">
%loop(records,R)%
<tr>
  <td style="height:21px">%chop(#R/upload_name,23)%</td>
  <td>%text(str_by)%&nbsp;</td>
  <td>%chop(#R/user_real_name,12)%&nbsp;</td>
  <td><a href="%(#R/stream_url)%"><img style="border:0px" src="%url('images/player/hear-button-fg.gif')%" /></a></td>
  <td><a href="%(#R/download_url)%"><img style="border:0px" src="%url('images/menu-download.png')%" /></a></td>
  <td><a href="%(#R/file_page_url)%"><img style="border:0px" src="%url('images/i-fg.png')%" title="%(#R/upload_name)%" /></a>&nbsp;</td>
  <td><a href="%(#R/license_url)%" title="%(#R/license_name)%"><img style="border:0px" src="%url('images/cc-tiny.png')%" title="%(#R/license_name)%" /></a></td>
</tr>
%end_loop%
</table>
</div>
</div>
</div>