<body>

%if_not_empty(site-disabled)%
    <div id="site_disabled_message" style="position:absolute">%text(str_site_disabled)%</div>
%end_if%
%if_null(#_GET/popup)%
    %if_not_empty(beta_message)%
        <div id="beta_message" style="position:absolute;">%(beta_message)%</div>
    %end_if%
%else%    
  <link rel="stylesheet"  type="text/css" title="Default Style" href="%url(css/short_page.css)%" />
%end_if%

<div class="hide">
  <a href="#content">%text(skip)%</a>
</div>

<div id="container" style="background-color:white;">

%if_null(#_GET/popup)%
<div id="header" class="light_color">

    <div id="login_info">
    %if_not_empty(logged_in_as)%<!-- logged in -->
        %text(str_loggedin)%: <span>%(logged_in_as)%</span> 
            <a class="light_color" href="%(home-url)%logout">%text(str_logout)%</a>
    %else%<!-- not logged in -->
        <span class="med_color">%text(str_logged_in_not)%</span>
        <a class="light_color" href="%(home-url)%login">%text(str_log_in)%</a>
        %if_not_empty(openid-type)%
            <a class="light_color" id="cc_openid_enabled" href="%(home-url)%login/openid"><div>%text(str_openid_enabled)%</div></a>
        %end_if%        
    %end_if%
    </div>

    <div id="header_search"><img id="header_search_img" height="50" width="70" src="%url(images/find.png)%" />
    <a class="light_color" id="search_site_link" href="%(home-url)%search"><h3>%text(str_find)%</h3>
    <span class="light_color">%text(str_findcontent)%</span></a></div>

    %if_not_empty(banner_message)%
        <div id="beta_message">%(show_beta_message)%</div>
    %end_if%

    <h1 id="site_title"><a href="%(root-url)%" title="%(site-title)%">
        %if_not_null(logo/src)% 
            <? $bimg = ccd($A['logo']['src']); ?><img src="%(#bimg)%" style="width:%(logo/w)%px;height:%(logo/h)%px"/> 
        %else% 
            <span class="light_color">%(site-title)%</span>
        %end_if%
    </a></h1>

    %if_not_empty(site-description)%
        <div id="site_description">%(site-description)%</div>
    %end_if%

    %if_not_empty(tab_pos/in_header)%
        %call('tabs.tpl/print_tabs')%
    %end_if%
</div><!-- header -->
%end_if%

<div style="display:none" id="debug"></div>

    <div id="wrapper">
<div id="content">

%if_null(#_GET/popup)%
%call(print_bread_crumbs)%

%if_not_empty(tab_pos/subclient)%
    %call('tabs.tpl/print_sub_tabs')%
%end_if%
%end_if%

%if_not_empty(page-title)%
    <h1 class="title">%text(page-title)%</h1>
%end_if%
<a name="content" ></a>
<div id="inner_content">
<?
    if( !empty($A['macro_names'] ) )
        while( $macro = array_shift($A['macro_names']) )
            $T->Call($macro);
?>
</div>
%loop(inc_names,inc_name)%   %call(#inc_name)% %end_loop%

</div> <!-- content -->
    </div> <!-- wrapper -->

%if_null(#_GET/popup)%

<div id="navigation">

    %if_not_empty(tab_pos/floating)%
        %call('tabs.tpl/print_tabs')%
    %end_if%

    %if_not_empty(tab_pos/nested)%
        %call('tabs.tpl/print_nested_tabs')%
    %end_if%

    %if_not_empty(menu_groups)%

        <div id="menu">

            %loop(menu_groups,group)%
              <div class="menu_group">
                <p>%text(#group/group_name)%</p>
                <ul>%loop(#group/menu_items,mi)%
                  <li><a href="%(#mi/action)%" %if_attr(#mi/id,id)%>%text(#mi/menu_text)%</a></li>
                %end_loop% </ul>
              </div>
            %end_loop%

        </div> <!-- end of menu -->

        %unmap(menu_groups)%

    %end_if%
</div>

<div id="extra">
  %if_null(edit_extra)%
    %settings(extras,custom_macros)%
    %loop(custom_macros/macros,mac)%
        <div class="menu_group">        
          %call_macro(#mac)%
        </div>
    %end_loop%
  %else%
    <!-- editing extras -->
    %call('extras_drop')%
  %end_if%
</div>

<div id="footer" class="med_light_bg">
  <div id="license"><p>%text(site-license)%</p></div>
  <p><?
  $__plug = str_replace('#rand#',rand(),$T->String($A['footer']));
  print $__plug;
?></p>
</div><!-- footer -->

%end_if% %% if_not_null_popup %%

</div> <!-- container -->


%loop(end_script_links,script_link)%
    <script type="text/javascript" src="%url(#script_link)%" ></script>
%end_loop%

%loop(end_script_blocks,block)%
    %call(#block)%
%end_loop%

<script type="text/javascript"> 
    new modalHook( [ 'search_site_link' ]);  
    $$('.selected_tab a').each( function(e) { e.style.cursor = 'default'; e.href = 'javascript:// disabled'; } );
%loop(end_script_text,tblock)%
    %(#tblock)%
%end_loop%
</script>

<!--[if lt IE 7.]>
<script defer type="text/javascript" src="%url(js/pngfix.js)%"></script>
<![endif]-->

</body>
