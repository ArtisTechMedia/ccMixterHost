<body>
<div id="container">
    %if_not_empty(site-disabled)%
      <div id="site_disabled_message" style="position:absolute">%text(str_site_disabled)%</div>
    %end_if%
    %if_not_empty(beta_message)%
      <div id="beta_message" style="position:absolute;">%(beta_message)%</div>
    %end_if%

    <div class="hide">
      <a href="#content">%text(skip)%</a>
    </div>

  <div id="globalWrapper">
      <div id="headerWrapper">
        <div id="headerLogo">
            <h1><a href="%(root-url)%" title="%(site-title)%" >
            %if_not_null(logo/src)% 
            <? $bimg = ccd($A['logo']['src']); ?><img src="%(#bimg)%" style="width:%(logo/w)%px;height:%(logo/h)%px"/> 
            %else% 
            <span class="light_color">%(site-title)%</span>
            %end_if%
            </a></h1>
        </div><!-- #headerLogo -->
            %if_not_null(site-description)%
              <div class="light_color" id="site_description">%(site-description)%</div>
            %end_if%
 
        <div id="headerNav">
            %if_not_empty(tab_pos/in_header)%
                %call('tabs.tpl/print_tabs')%
            %end_if%
        </div><!-- #headerNav -->
    </div><!-- #headerWrapper -->
    <div id="wrapper"><div id="content">
        <div id="tools">
            <div class="sideitem">
                <form method="get" id="searchform" action="%(home-url)%search/results">
                    <div>
                        <input title="Search" accesskey="f"
                            value="" name="search_text" id="search_text" class="inactive" type="text">
                        <input id="searchsubmit" value="Go" type="submit">
                        <input type="hidden" name="search_in" value="all"></input>
                    </div>
                </form>
                %if_null(logged_in_as)%
                     <span><a href="%(home-url)%login">%text(str_log_in_create)%</a></span>
                     %if_not_empty(openid-type)%
                      <span>(<a href="%(home-url)%login/openid">%text(str_openid)%</a>)</span>
                     %end_if%
                %else%
                     <span>%text(str_loggedin)%: <b><span>%(logged_in_as)%</span></b> 
                        <span><a class="small_button" href="%(home-url)%logout">%text(logout)%</a></span>
                %end_if%
            </div><!-- .sideitem -->
            <div style="margin-right:200px">
            %call(print_bread_crumbs)%

            <div id="pageNav">
                %call('tabs.tpl/print_sub_tabs')% <!-- -->
            </div><!-- #pageNav -->
            </div>
        </div><!-- tools -->

        <div id="page_title">
            %if_not_empty(page-title)%
                <h1 class="page_title">%text(page-title)%</h1>
            %end_if%
            <br class="page_title_breaker" />
        </div><!-- #page_title -->
<!-- page content -->
 
        <a name="content" ></a>    
        <div id="inner_content">
<?
            if( !empty($A['macro_names'] ) )
                while( $macro = array_shift($A['macro_names']) )
                    $T->Call($macro);
?>
        </div><!-- inner_content -->
        %loop(inc_names,inc_name)%   %call(#inc_name)% %end_loop%
    </div></div><!-- #content / wrapper -->
<!-- end content -->

    <div id="navigation">
        %loop(menu_groups,group)%
            <div class="menu_group">
                <p>%text(#group/group_name)%</p>
                <ul>%loop(#group/menu_items,mi)%
                    <li><a href="%(#mi/action)%" %if_attr(#mi/id,id)%>%text(#mi/menu_text)%</a></li>
                %end_loop% </ul>
            </div>
        %end_loop%
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
            <br style="clear:both" />
            <br />
            
            <!-- editing extras -->
            %call('extras_drop')%
        %end_if%
    </div>
   
   <!-- footer -->
    <div id="footer" class="ccfooter">
        <div id="footerWrapper">
            <div id="footerContent" class="ccbox">%text(footer)%</div>
            <div id="footerLicense">
                <p class="ccbox">
                 %text(site-license)%
                </p>
            </div>
        </div>
    </div>
   
  </div><!-- global wrapper -->

%loop(end_script_links,script_link)%
  <script type="text/javascript" src="%url(#script_link)%" ></script>
%end_loop%

%loop(end_script_blocks,block)%
  %call(#block)%
%end_loop%

<script type="text/javascript"> 
    new modalHook( [ 'search_site_link', 'mi_login', 'mi_register']);  
    $$('.selected_tab a').each( function(e) { e.style.cursor = 'default'; e.href = 'javascript:// disabled'; } );
%loop(end_script_text,tblock)%
    %(#tblock)%
%end_loop%
</script>
</div><!-- #container -->
</body>
