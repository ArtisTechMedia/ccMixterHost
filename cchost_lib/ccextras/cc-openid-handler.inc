<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-openid-handler.inc 12465 2009-04-29 05:06:54Z fourstones $
*
*/
/**
* @package cchost
* @subpackage user
*/

require_once('cchost_lib/ccextras/cc-openid.inc');
require_once('cchost_lib/ccextras/cc-openid-helpers.inc');


function cc_openid_handler($cmd,$arg1='')
{
    $api = new CCOpenID();
    
    switch( $cmd )
    {
        case 'login':
        {
            return cc_openid_onlogin($api,$arg1);
        }
        case 'auth':
        {
            cc_openid_auth($api);
            break;
            
        }
        case 'match':
        {
            $api->OnTryMatch();
            break;
        }

        case 'edit':
        {
            $api->EditIDs();
            break;
        }
                
        default:
        {
            ccoi_displayError( $api, "Unknown OpenID command: " . $cmd );
        }
        
    }
    
}

function ccoi_displayError($api,$msg)
{
    $api->OnIDFailed($msg);
}

function cc_openid_auth($api)
{
    session_start();
    $consumer = getConsumer();

    // Complete the authentication process using the server's
    // response.
    $return_to = getReturnTo();
    $response = $consumer->complete($return_to);

    // Check the response status.
    if ($response->status == Auth_OpenID_CANCEL) {
        // This means the authentication was cancelled.
        $msg = 'Verification cancelled.';
        ccoi_displayError($api,$msg);
    } else if ($response->status == Auth_OpenID_FAILURE) {
        // Authentication failed; display the error message.
        $msg = "OpenID authentication failed: " . $response->message;
        ccoi_displayError($api,$msg);
    } else if ($response->status == Auth_OpenID_SUCCESS) {
        // This means the authentication succeeded; extract the
        // identity URL and Simple Registration data (if it was
        // returned).
        $openid = $response->getDisplayIdentifier();
        $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
        $sreg = $sreg_resp->contents();
        $api->OnIDVerified( $openid, $response->endpoint->canonicalID, $sreg );
    }
    
}

function cc_openid_onlogin( $api, $openid )
{
    session_start();

    $ret = 1;
    
    $consumer = getConsumer();

    // Begin the OpenID authentication process.
    $auth_request = $consumer->begin($openid);

    // No auth request means we can't begin OpenID.
    if (!$auth_request) {
        ccoi_displayError($api,"Authentication error; not a valid OpenID.");
        return 0;
    }

    $sreg_request = Auth_OpenID_SRegRequest::build(
                                     // Required
                                     array('nickname'),
                                     // Optional
                                     array('fullname', 'email'));

    if ($sreg_request) {
        $auth_request->addExtension($sreg_request);
    }

    // Redirect the user to the OpenID server for authentication.
    // Store the token for this authentication so we can verify the
    // response.

    // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
    // form to send a POST request to the server.
    if ($auth_request->shouldSendRedirect()) {
        $redirect_url = $auth_request->redirectURL(getTrustRoot(),
                                                   getReturnTo());

        // If the redirect URL can't be built, display an error
        // message.
        if (Auth_OpenID::isFailure($redirect_url)) {
            displayError("Could not redirect to server: " . $redirect_url->message);
            $ret = 0;
        } else {
            // Send redirect.
            header("Location: ".$redirect_url);
        }
    } else {
        // Generate form markup and render it.
        $form_id = 'openid_message';
        $form_html = $auth_request->htmlMarkup(getTrustRoot(), getReturnTo(),
                                               false, array('id' => $form_id));

        // Display an error if the form markup couldn't be generated;
        // otherwise, render the HTML.
        if (Auth_OpenID::isFailure($form_html)) {
            ccoi_displayError("Could not redirect to server: " . $form_html->message);
            $ret = 0;
        } else {
            $form_html = str_replace('</html>','<h3>Contacting OpenID site...</h3></html>',$form_html);
            print $form_html;
            exit;
        }
    }
    
    return $ret;
}

?>
