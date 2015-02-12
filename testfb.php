<html>
<head>
<script type="text/javascript" src="ccskins/shared/js/prototype.js" ></script>
<script>
    window.fbAsyncInit = function() {
    // facebook
    FB.init({
            appId      : '1550961735160190',
            xfbml      : true,
            version    : 'v2.2'
            });
        };

    (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>
<script>
    function post(path, params, method) {
        method = method || "post"; // Set method to post by default if not specified.

        // The rest of this code assumes you are not using a library.
        // It can be made less wordy if you use one.
        var form = document.createElement("form");
        form.setAttribute("method", method);
        form.setAttribute("action", path);

        for(var key in params) {
            if(params.hasOwnProperty(key)) {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", key);
                hiddenField.setAttribute("value", params[key]);

                form.appendChild(hiddenField);
             }
        }

        document.body.appendChild(form);
        form.submit();
    }

    function fb_on_status_check_for_create(response)
    {
        var authResponse = response.authResponse;
        var url = home_url + 'fbcreate';
        var params = { 'fbaccessid': authResponse.accessToken,
                        'fbuserid':  authResponse.userID };
        post( url, params );
    }
    
    function fb_on_login_response(response)
    {
        console.log('ccm login response');
        console.log(response);
        response = eval(response.response);
        if( response.num_users == 0 )
        {
            FB.getLoginStatus(fb_on_status_check_for_create);
        }
        else if( response.num_users == 1 )
        {
            var url = home_url + 'people/' + response.user_name;
            document.location = url;
        }
        else if( response.num_users > 1 )
        {
            var url = home_url + 'fbattach/' + response.users[0].user_id;
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
</head>
<body>

<fb:login-button scope="public_profile,email" onlogin="fb_check_login_state();">
</fb:login-button>
</body>
</html>