<?/*
[meta]
    type = extras
    desc = _('Links to virtual roots')
[/meta]
*/?>

<p>%text(str_mini_sites)%</p>
<ul>
<? $roots = cc_get_config_roots(); ?>
%loop(#roots,item)%
  <li><a href="%(#item/url)%">%(#item/scope_name)%</a></li>
%end_loop%
</ul>
