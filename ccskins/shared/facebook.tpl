
%macro(fb_login_script)%
    <script type="text/javascript" src="%url(js/facebook.js)%" ></script>
%end_macro%

%macro(facebook_login)%
<fb:login-button scope="public_profile,email" onlogin="fb_check_login_state();">
</fb:login-button>
%end_macro%

%macro(facebook_new_user)%

%end_macro%
