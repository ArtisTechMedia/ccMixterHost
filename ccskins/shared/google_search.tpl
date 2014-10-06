<? $site = str_replace('http://','', cc_get_root_url() ); ?>
<div style="margin-bottom: 2em;">
<h2>%text(str_search_google)%</h2>
<form method="GET" action="http://www.google.com/search">
<input type="hidden" name="ie" value="utf-8" />
<input type="hidden" name="oe" value="utf-8" />
<table><tr>
<td>
    <a href="http://www.google.com/"><img src="http://www.google.com/logos/Logo_40wht.gif" alt="google"></a>
</td>
<td>
    <input type="text"   name="q" size="31" maxlength="255" value="" />
    <input type="submit" name="btng"        value="Google search" />
    <input type="hidden" name="domains"     value="%(#site)%" />
    <input type="hidden" name="sitesearch"  value="%(#site)%" />
</td></tr></table>
</form>
</div>