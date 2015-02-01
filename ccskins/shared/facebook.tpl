
%macro(fb_login_script)%
<script>
    function fb_on_login_response(response)
    {
        console.log('ccm login response');
        console.log(response);
        response = eval(response.response);
        if( response.num_users == 1 )
        {
            var url = home_url + '/people/' + response.user_name;
            document.location = url;
        }
        else if( response.num_users > 1 )
        {
            var url = home_url + '/fbattach/' + response.users[0].user_id;
            document.location = url;
        }
    }

    function fb_try_login(authResponse)
    {
        var url = home_url + 'api/fb/login';
        var params = { 'fbaccessid': authResponse.accessToken,
                        'fbuserid':  authResponse.userID };
        new Ajax.Request( url, { onComplete: fb_on_login_response,
                                 parameters: params } );    
    }

    function fb_on_status_change(response) {
        if (response.status === 'connected') {
            fb_try_login(response.authResponse);
        } else if (response.status === 'not_authorized') {
            // they probably refused some allowance
            console.log( "fb - logged into FB but not ccM's FB app");
        } else {
            console.log("fb - not sure about login state");
        }
    }

    function fb_check_login_state() {
        FB.getLoginStatus(fb_on_status_change);
    }
</script>
%end_macro%

%macro(facebook_login)%
%call('facebook.tpl/fb_login_script')%
<fb:login-button scope="public_profile,email" onlogin="fb_check_login_state();">
</fb:login-button>
%end_macro%

%macro(facebook_new_user)%

%end_macro%
