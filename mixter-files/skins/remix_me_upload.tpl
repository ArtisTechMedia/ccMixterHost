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
* $Id: remix_me_upload.tpl 13831 2009-12-25 12:35:42Z fourstones $
*
*/
if( !defined('IN_CC_HOST') )
  die('Welcome to ccHost!');
?>
 %%
[meta]
 type = format
 dataview = user_basic
 required_args = user
[/meta]
%%
%map(#R,records/0)%
<style>
#remix_me_doc p {
    font-size:14px; 
 }
</style>
<h1>Uploading Your %(#R/user_real_name)% Remix</h1>
<div id="remix_me_doc" style="padding-left:13%">
<p>First off, thanks for remixing %(#R/user_real_name)%!</p>
%if_not_null(logged_in_as)%
<p>Since you're already logged in, go straight to the <a href="<?= ccl('submit','remix') ?>">Submit A Remix</a> form...</p>
%else%
<p>In order to upload your remix you need to have an account with %(site-title)%.</p>
<p>If you already have one, great, <a href="<?= ccl('login') ?>">log in</a> and click on 'Submit Files' in the <b>Artists</b> menu.</p>
<p>If you don't have an account with us then by all means <a href="<?= ccl('register') ?>">create one now</a>. It's easy and free.</p>
<h2>Thanks! And welcome to the <a href="http://creativecommons.org">Creative Commons</a> Sample Pool!</h2>
%end_if%
</div>
