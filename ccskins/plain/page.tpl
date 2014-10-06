
%append( style_sheets, css/plain.css)%

%if(ajax)%
    %call('short_page.tpl')%
%else%
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 //EN">
<html>
    %call(head-type)%
    %if(show_body_header)%
        %call('body.tpl')%
    %else%
        %call('short_page.tpl')%
    %end_if%
</html>
%end_if%
