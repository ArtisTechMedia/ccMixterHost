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
* $Id: remix_me_embed.tpl 11247 2008-11-16 11:27:48Z fourstones $
*
*/
if( !defined('IN_CC_HOST') )
  die('Welcome to ccHost!');
/*
[meta]
    type = template_component
    desc = _('Remix Me Embed')
    dataview = user_basic
    required_args = user
[/meta]
*/?>
%map(#R,records/0)%

<h1>Embed a "Remix Me" Widget In Your Web Page</h1>

<div style="width:450px;text-align:center;margin:14px auto;">
<p>Now you can encourage folks to remix your stems and a cappellas by embedding this widget into your web site!
For other embed goodies, go to your <a href="<?= ccl('publicize', $R['user_name']); ?>">Publicize page</a>.
</p>

<div style="margin:0px auto;width:530px">
<script type="text/javascript" src="%(query-url)%limit=50&template=mixter-files%2Fskins%2Fformats%2Fremix_me.tpl&chop=10&remixesof=%(#R/user_name)%&format=docwrite" ></script>
</div>

<p>Copy the following code into your web page:</p>

<textarea style="width:35em;height:10em;">
<script type="text/javascript" src="%(query-url)%limit=50&template=mixter-files%2Fskins%2Fformats%2Fremix_me.tpl&chop=10&remixesof=%(#R/user_name)%&format=docwrite" ></script>
</textarea>

</div>