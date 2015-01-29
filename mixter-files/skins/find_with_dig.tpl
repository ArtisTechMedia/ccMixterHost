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
* $Id$
*
*/

/*
[meta]
    type = template_component
    desc = _('Find with dig.ccmixter')
    dataview = passthru
[/meta]
*/?>
<style>
</style>
<div style="width:50%;margin: 0px auto;">
<div class="box">
    <p style="text-align:center;"><img src="/dig/images/logo-black.png" /></p>
    <p><?= $GLOBALS['str_dig_help'] ?></p>
</div>
<form action="http://dig.ccmixter.org/dig" method="get" style="text-align:center">
    <p><input type="text" style="width:350px" name="dig-query" /><input type="submit" value="dig" />
</form>
</div>