%%
[meta]
    type     = template_component
    desc     = _('Links used by page menu (UL)')
    dataview = links
[/meta]
%%
%loop(records,R)%
   <li><a href="%(#R/file_page_url)%" class="cc_file_link">%chop(#R/upload_name,chop)%</a></li>
%end_loop%

