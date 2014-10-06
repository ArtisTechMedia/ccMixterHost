
%append( style_sheets, css/cc5.css)%

%if(ajax)%
    %call('short_page.tpl')%
%else%
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
   xmlns:cc="http://creativecommons.org/ns#"   
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xml:lang="en" lang="en">
    %call(head-type)%
    %if(show_body_header)%
        %if_empty(get/popup)%
            %call('body.tpl')%
        %else%
            %call('short_page.tpl')%
        %end_if%
    %else%
        %call('short_page.tpl')%
    %end_if%
</html>
%end_if%
