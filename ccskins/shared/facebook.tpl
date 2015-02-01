
%macro(facebook_login)%
<fb:login-button scope="public_profile,email" onlogin="checkLoginState();">
</fb:login-button>

<div id="status">
</div>

<div id="fb-email"></div>
<div id="fb-name"></div>
<div id="fb-image"></div>
%end_macro%

%macro(facebook_script)%
%end_macro%
