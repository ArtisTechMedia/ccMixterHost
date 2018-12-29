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
* $Id: remix_me_download.tpl 11242 2008-11-15 22:01:08Z fourstones $
*
*/

/*
[meta]
    type = ajax_component
    desc = _('Remix Me Download')
    dataview = user_basic
    require_args = user
[/meta]
*/?>
<style>
* {
    font-family: Verdana;
    font-size: 11px;
}
body {
    background-color:#EEE;
    border:3px solid #444;
    padding:0px;
    margin:0px;
}
#download {
    margin:2px;
    font-weight:normal;
    font-family:verdana;
    font-size:11px;
}
#download_help {
    border: 1px solid #444;
    padding: 4px;
    margin: 4px;
    width: 280px;
}

.upload_name {
}

ol li {
}
ol li a {
    font-weight: bold;
    text-decoration: none;
}
ol li a:hover {
    text-decoration: underline;
}
</style>
%map(#R,records/0)%
<div style="padding:2px;background-color:#FFF;">
%if_not_null(remix-me-logo)% <img style="float:left" src="%(remix-me-logo)%" />%end_if% 
<p style="text-align:center;margin:5px;font-weight:bold;font-size: 14px;">
%if_not_null(remix-me-title)%<!-- title -->%(remix-me-title)%<!-- /title -->
%else%
Remix %(#R/user_real_name)%
%end_if%
</p>
</div>
<div style="clear:left;background-color:#DDD;padding:2px;margin:2px;font-weight:bold;font-family:verdana;font-size:11px;">
These are all licensed under a <a target="_blank" href="http://creativecommons.org">Creative Commons</a> license. For details check
the <a target="_blank" href="%(home-url)%/people/%(#R/user_name)%sample">%(#R/user_real_name)% profile page</a>.</div>
<?= cc_query_fmt('t=download&user='.$R['user_name'] .'&tags=sample&f=embed'); ?>
