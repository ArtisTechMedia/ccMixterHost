<?


/*
    signup link

    http://recaptcha.net/api/getkey?app=php
*/

function generator()
{
    require_once('recaptchalib.php');
    $publickey = "..."; // you got this from the signup page
    echo recaptcha_get_html($publickey);
}

function validator()
{
    require_once('recaptchalib.php');
    $privatekey = "...";
    $resp = recaptcha_check_answer ($privatekey,
                                    $_SERVER["REMOTE_ADDR"],
                                    $_POST["recaptcha_challenge_field"],
                                    $_POST["recaptcha_response_field"]);

    if (!$resp->is_valid) {
      die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
           "(reCAPTCHA said: " . $resp->error . ")");
    }
}
?>