<?

require_once( 'mixter-lib/lib/status.inc' );
require_once( 'mixter-lib/lib/user.php' );
require_once( 'mixter-lib/lib/follow-table.php' );

define('USER_INVALID_CAPTCHA', 'invalid captcha');
define('INVALID_FOLLOW_TYPE',  'invalid follow type');
define('UNKNOWN_USER',         'unknown user');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,            array( 'CCEventsUser',  'OnMapUrls'));
CCEvents::AddHandler(CC_EVENT_FILTER_USER_INFO,    array( 'CCEventsUser',  'OnFilterUserInfo'));

class CCEventsUser
{
    /**
    * Event handler for mapping urls to methods
    *
    * @see CCEvents::MapUrl
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('api','user','current'), array('CCAPIUser','CurrentUser'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Ajax callback for currently logged in user'), CC_AG_USER);
        CCEvents::MapUrl( ccp('api','user','login'), array('CCAPIUser','Login'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Perform login'), CC_AG_USER);
        CCEvents::MapUrl( ccp('api','user','logout'), array('CCAPIUser','Logout'),
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Perform logout'), CC_AG_USER);
        CCEvents::MapUrl( ccp('api','user','follow'), array('CCAPIUser','Follow'),
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '', _('Perform logout'), CC_AG_USER);
        CCEvents::MapUrl( ccp('api','user','thumbnail'), array('CCAPIUser','Thumbnail'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('get a user thumbnail'), CC_AG_USER);
    }

    function OnFilterUserInfo(&$rows)
    {
        $row =& $rows[0];
        $follow =& CCFollowTable::GetTable();
        $username = $row['user_name'];
        $row['following'] = $follow->Following($username);
        $row['followers'] = $follow->Followers($username);
        $row['is_admin'] = CCUser::IsAdmin($username);
        $row['is_super'] = CCUser::IsSuper($username);
    }
}

class CCAPIUser
{
    function CurrentUser()
    {
        $lib = new CCLibUser();
        $status = $lib->CurrentUser();
        CCUtil::ReturnAjaxObj($status);
    }

    function Login() {
        CCUtil::Strip($_REQUEST);
        /*
        if( empty($_REQUEST['captcha']) || !$this->_pass_captcha($_REQUEST['captcha']) ) {
            $status = _make_err_status(USER_INVALID_CAPTCHA)
        }
        */
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $remember = $_REQUEST['remember'];
        $lib = new CCLibUser();
        $status = $lib->Login($username,$password,$remember);
        CCUtil::ReturnAjaxObj($status);        
    }

    function Logout() {
        $lib = new CCLibUser();
        $status = $lib->Logout();
        CCUtil::ReturnAjaxObj($status);
    }

    function _pass_captcha($value) {
        $key = file_get_contents('cchost_lib/captcha.txt');
        $snoopy = CCUtil::HTTPClient();                
        $link = 'https://www.google.com/recaptcha/api/siteverify';

        @$snoopy->submit($link, array( 'secret' => $key,
                                       'response' => $value,
                                       'remoteip' => $_SERVER['REMOTE_ADDR']));

        return !empty($snoopy->results) && (strstr($snoopy->results,'"success": true') !== FALSE);
    }

    function Follow($type,$follower,$followee) {
        $follower = CCUtil::Strip($follower);
        $followee = CCUtil::Strip($followee);
        $users =& CCUsers::GetTable();
        if( $type !== 'follow' && $type !== 'unfollow' ) {
            $status = _make_err_status(INVALID_FOLLOW_TYPE);
        } else if( empty($follower) || empty($followee) ) {
            $status = _make_err_status(UNKNOWN_USER);
        } else if( (int)$users->CountRows("user_name IN ('{$follower}','{$followee}')") !== 2 ) {
            $status = _make_err_status(UNKNOWN_USER);
        } else {
            $lib = new CCLibUser();
            $status = $lib->Follow($type === 'follow', $follower, $followee);
        }
        CCUtil::ReturnAjaxObj($status);        
    }

    function Thumbnail($username) {
        $checkid = CCUser::IDFromName($username);
        if( !empty($checkid) ) {
            $lib = new CCLibUser();
            $name = $lib->Thumbnail($username);
            $info = pathinfo($name);
            if( file_exists($name) ) {
                $fp = @fopen($name, 'rb');
                if( $fp !== FALSE ) {
                    header("Content-Type: image/" . $info['extension']);
                    header("Content-Length: " . filesize($name));
                    fpassthru($fp);
                    exit;
                }
            }
        }
        CCUtil::Send404();
    }
}
?>
