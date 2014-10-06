%if_null(#_GET/ajax)%
<!-- template short_page.tpl -->
<link rel="stylesheet"  type="text/css" title="Default Style" href="%url(css/short_page.css)%" />
%end_if%
%if(page_title)%
    <h1 class="title">%text(page-title)%</h1>
%end_if%
%loop(macro_names,macro)%    
    %call(#macro)%             
%end_loop%
%loop(inc_names,inc_name)%   
    %call(#inc_name)%
%end_loop%
