%%/*
[meta]
    type = extras
    desc = _('dig Search Badge')
[/meta]
*/
%%
%if_null(logged_in_as)%
<br /><br />
<a href="http://dig.ccmixter.org"><img src="http://dig.ccmixter.org/images/dig.ccmixter-white-on-black-100x22.jpg" /></a>
<ul><li>
Search our archives for<br />
music for your video,<br />
podcast or school project<br />
at <a href="http://dig.ccmixter.org" style="font-weight:bold;text-decoration:underline;color:blue;">dig.ccMixter</a></li></ul>

%end_if%