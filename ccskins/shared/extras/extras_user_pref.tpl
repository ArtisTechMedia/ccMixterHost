<?/*
[meta]
    type = extras
    desc = _('User Preferred Extras')
[/meta]
*/

?>
%if_not_null(logged_in_as)%
    %loop(user_extra/prefs/extras,uextra)%
        %call(#uextra)%
    %end_loop%
%end_if%

