
%macro(print_sub_tabs)%
%if_not_empty(sub_nav_tabs)%
    %if_not_empty(sub_tab_prefix)%
       <span id="sub_tab_prefix">%(sub_tab_prefix)%</span>
    %end_if%
    <ul id="sub_tabs">
    %loop(sub_nav_tabs/tabs,tab)%
        <li %if_class(#tab/selected,selected_tab)%><a href="%(#tab/url)%" title="%text(#tab/help)%"><span>%text(#tab/text)%</span></a></li>
    %end_loop%
    %unmap(sub_nav_tabs)%
    </ul>
    <div class="post_sub_tab_breaker"></div>
%end_if%
%end_macro%

%macro(print_tabs)%
%if_not_empty(tab_info)%
    <ul id="%(tabs_id)%">
    %loop(tab_info/tabs,tab)%
        <li %if_class(#tab/selected,selected_tab)%><a href="%(#tab/url)%" title="%text(#tab/help)%"><span>%text(#tab/text)%</span></a></li>
    %end_loop%
    %unmap(tab_info)%
    </ul>
    <div class="post_tab_breaker"></div>
%end_if%
%end_macro%

%macro(print_nested_tabs)%
%if_not_empty(tab_info)%
    <ul id="%(tabs_id)%">
    %loop(tab_info/tabs,tab)%
        <li %if_class(#tab/selected,selected_tab)%><a href="%(#tab/url)%" title="%text(#tab/help)%"><span>%text(#tab/text)%</span></a></li>
        %if_not_null(#tab/selected)%
          <li>
            %if_not_empty(sub_nav_tabs)%
                <ul id="sub_tabs">
                %loop(sub_nav_tabs/tabs,tab)%
                    <li %if_class(#tab/selected,selected_tab)%><a href="%(#tab/url)%" title="%text(#tab/help)%"><span>%text(#tab/text)%</span></a></li>
                %end_loop%
                %unmap(sub_nav_tabs)%
                </ul>
                <div class="post_sub_tab_breaker"></div>
            %end_if%
           </li>
        %end_if%                    
    %end_loop%
    %unmap(tab_info)%
    </ul>
    <div class="post_tab_breaker"></div>
%end_if%
%end_macro%
