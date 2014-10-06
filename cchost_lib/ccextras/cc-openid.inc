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
* $Id: cc-openid.inc 12559 2009-05-06 19:54:43Z fourstones $
*
*/

/**
* @package cchost
* @subpackage user
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* API for handling OpenID logins
*
*/
class CCOpenID
{
    /**
     * Main OpenID login screen /openid/login
     *
     */
    function Login()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/ccextras/cc-openid-forms.inc');
        $page =& CCPage::GetPage();
        $this->_build_bread_crumb_trail1($page);
        $title = 'str_openid_login_title';
        $page->SetTitle($title);
        $form = new CCOpenIDLoginForm();
        if( empty($_POST['openidlogin']) || !$form->ValidateFields() )
        {
            $page->AddForm($form->GenerateForm());
        }
        else
        {
            $form->GetFormValues($values);
            require_once('cchost_lib/ccextras/cc-openid-handler.inc');
            cc_openid_handler('login',$values['openid_url']);
        }
    }

    /**
     * Event handler for {@link CC_EVENT_FORM_INIT}
     *
     * For contact mailer form (/people/contact) and user notification
     * (/people/notify/edit) we make sure the user in question actually
     * has an email address before showing the default screens. Otherwise
     * we throw up contingency screens.
     * 
     * @param object &$form CCForm object
     * @param object &$fields Current array of form fields
     */
    function OnFormInit(&$form)
    {
        $class = strtolower( get_class($form) );
        switch( $class )
        {
            case 'cccontactmailerform':
            {
                // hmm, well, hack the URL to get the username
                preg_match('#/[a-z0-9_]+$#i',$_GET['ccm'],$m);
                $m = ltrim($m[0],'/');
                if( empty($m) )
                {
                    return; //this should never happen
                }
                $user_email = CCDatabase::QueryItem('SELECT user_email FROM cc_tbl_user WHERE user_name=\''.$m.'\'');
                if( !empty($user_email) )
                    return; // we're cool, let the system handle it.
                $url = ccl('openid','contact',$m);
                CCUtil::SendBrowserTo($url);
                break;
            }
            case 'cceditusernotificationform':
            {
                $user_name = CCUser::CurrentUserName();
                $user_email = CCDatabase::QueryItem(
                    'SELECT user_email FROM cc_tbl_user WHERE user_name=\''.$user_name.'\'');
                if( !empty($user_email) )
                    return; // we're cool, let the system handle it.
                $url = ccl('openid','notify',$user_name);
                CCUtil::SendBrowserTo($url);
                break;
            }
        }

    }

    /**
     * Users are redirected here when contact target does not have an email addr.
     *
     * @param string $user_name 
     */
    function Contact($user_name)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_openid_contact');
        $user_id = CCUser::IDFromName($user_name);
        $openids = $this->_get_openids($user_id);
        $page->SetArg('contact_info',$openids,'openid_contact');
    }

    /**
     * Users are redirected here when notify target does not have an email addr.
     *
     * @param string $user_name 
     */
    function Notify($user_name)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_openid_notify');
        $page->SetArg('user_name',$user_name,'openid_notify');
    }


    function _build_bread_crumb_trail1(&$page)
    {
        $trail[] = array( 'url' => ccl(), 
                          'text' => 'str_home');
        
        $trail[] = array( 'url' => ccl('login'), 
                          'text' => 'str_log_in' );
                          
        $trail[] = array( 'url' => '',
                          'text' => 'str_openid_login_title' );

        $page->AddBreadCrumbs($trail);
    }
    
    
    function _build_bread_crumb_trail2(&$page)
    {
        global $CC_GLOBALS;

        $trail[] = array( 'url' => ccl(), 
                          'text' => 'str_home');
        
        $trail[] = array( 'url' => ccl('people'), 
                          'text' => 'str_people' );
        
        $trail[] = array( 'url' => ccl('people',$CC_GLOBALS['user_name']), 
                          'text' => $CC_GLOBALS['user_real_name'] );

        $trail[] = array( 'url' => ccl('people','profile'), 
                          'text' => 'str_prof_edit_your_settings' );

        $trail[] = array( 'url' => '', 'text' => 'str_openid_manage' );

        $page->AddBreadCrumbs($trail);
    }
    
    /**
     * Form for managing a user's OpenIDs (/openid/edit)
     *
     */
    function EditIDs()
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['openid-type']) )
            return;

        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/ccextras/cc-openid-forms.inc');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_openid_manage');
        $this->_build_bread_crumb_trail2($page);
        
        $user_id = CCUser::CurrentUser();
        $user_name = CCUser::CurrentUserName();
        $recs = $this->_get_openids($user_id,'*');
        $form = new CCManageOpenIDsForm($recs);
        
        if( empty($_POST['manageopenids']) || !$form->ValidateFields($form) )            
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $table = new CCTable('cc_tbl_openids','openid');
            if( !empty($_POST['S']) )
            {
                foreach( $_POST['S'] as $oid )
                {
                    $uarg = array();
                    switch( $oid['act'] )
                    {
                        case 'show':
                        {
                            $uarg['openid_show'] = 1;
                            $uarg['openid'] = $oid['openid'];
                            $table->Update($uarg);
                            break;
                        }

                        case 'hide':
                        {
                            $uarg['openid_show'] = 0;
                            $uarg['openid'] = $oid['openid'];
                            $table->Update($uarg);
                            break;
                        }

                        case 'delete':
                        {
                            $table->DeleteKey($oid['openid']);
                            break;
                        }
                    }
                }
            }

            if( empty($_POST['new']['openid']) )
            {
                CCUtil::SendBrowserTo( ccl('people',$user_name,'profile') );
            }
            else
            {
                cc_openid_handler('login',$_POST['new']['openid']);
            }
        }
    }

    function _get_openids($user_id,$fields='openid')
    {
        if( $fields == 'openid' )
            return CCDatabase::QueryItems('SELECT openid FROM cc_tbl_openids WHERE openid_user='.$user_id);
        return CCDatabase::QueryRows('SELECT '.$fields.' FROM cc_tbl_openids WHERE openid_user='.$user_id);        
    }
    
    /**
     * Event handler for {@link CC_EVENT_FILTER_USER_PROFILE}
     *
     * We add the user's OpenIDs (assuming they have agreed to
     * display them)
     * 
     * @param object &$records CCUser record (at offset 0)
     */
    function OnFilterUserProfile(&$records)
    {
        $user_rec =& $records[0];
        $openids = $this->_get_openids($user_rec['user_id'],'*');
        if( empty($openids) )
            return;
        $text = array();
        foreach( $openids as $OID )
        {
            if( empty($OID['openid_show']) )
                continue;
            $openid = $OID['openid'];
            $text[] = "<a href=\"{$openid}\" class=\"cc_openid_link\">{$openid}</a>";
        }
        if( empty($text) )
            return;
        $text = join('<br />',$text);
        $arr = array( 'label' => 'str_openids', 'value' => $text, 'id' => 'openids' );
        if( count($user_rec['user_fields']) > 0 )
        {
            array_splice( $user_rec['user_fields'], 1, 0, array( $arr ) );
        }
        else
        {
            $row['user_fields'][] = $arr;
        }
    }
    
    /**
     * Event handler for {@link CC_EVENT_FORM_FIELDS}
     *
     * @param object &$form CCForm object
     * @param object &$fields Current array of form fields
     */
    function OnFormFields( &$form, &$fields )
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['openid-type']) )
            return;

        $class = strtolower( get_class($form) );
        
        switch( $class )
        {
            case 'ccnewuserform':
            case 'ccuserloginform':
            {
                
                $url = ccl('login','openid');
                $link1 = "<a href=\"{$url}\" id=\"cc_openid_enabled\">";
                $link2 = '</a><p></p>';
                $form->SetHelpText(array('str_openid_login_help',$link1,$link2));
                break;
            }
            
            case 'ccuserprofileform':
            {
                $fields['user_password']['form_tip'] = 'str_openid_pw_tip';

                $field['openids_edit'] = array (
                 'label'      => '',
                 'url'        => ccl('openid','edit'),
                 'formatter'  => 'button',
                 'class'      => 'cc_openid_link',
                 'value'      => 'str_openid_manage',
                 'flags'      => CCFF_NOUPDATE );
                 
                $form->InsertFormFields( $field, 'after', 'user_real_name');                   
            }
        }
    }
    
    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields['openid-type'] =
               array(  'label'      => _('OpenID'),
                       'form_tip'   => _('Enable OpenID log in and registration.'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE  ); 
        }
    }

    /**
     * This is called when an OpenID auth has failed
     *
     * @param string $msg
     */
    function OnIDFailed($msg)
    {
	    require_once('cchost_lib/cc-page.php');
	    CCPage::Prompt( $msg );
    }
        
    /**
     * This is called when an OpenID auth has been OK's without a known match
     *
     * This called the first time we see an OpenID and we're trying to
     * match it up with some account, or create a new one.
     *
     * This is the submit form handler (/openid/match) for CCOpenIDHookUpForm
     */
    function OnTryMatch()
    {
        if( empty($_POST['openidinfo']) )
            CCUtil::Send404();

        $args = unserialize( urldecode($_POST['openidinfo']) );

        require_once('cchost_lib/ccextras/cc-openid-forms.inc');
        $form = new CCOpenIDHookUpForm($args);
        if( !$form->ValidateFields() )
        {
            require_once('cchost_lib/cc-page.php');
            CCPage::SetTitle('str_openid_login_title');
            CCPage::AddForm($form->GenerateForm());
        }
        else
        {
            if( !empty($form->_match_user_id) )
            {
                $this->_do_assoc($form->_args['openid'],$form->_match_user_id);
                $this->_do_login($form->_match_user_id);
            }
            elseif( !empty($form->_new_user_info) )
            {
                require_once('cchost_lib/cc-login.php');
                $login = new CCLogin();
                $new_password = $login->_make_new_password();
                $fields['user_password'] = md5($new_password);
                $fields['user_registered'] = date( 'Y-m-d H:i:s' );
                $fields['user_name'] = $form->_new_user_info[0];
                $fields['user_real_name'] = empty($form->_new_user_info[1]) ? $fields['user_name'] : 
                                                    $form->_new_user_info[1];
                $users =& CCUsers::GetTable();
                $fields['user_id'] = $users->NextID();
                $users->Insert($fields);
                $this->_do_assoc($form->_args['openid'],$fields['user_id']);
                $this->_do_login($fields['user_id']);
            }
            else
            {
                // this should never happen
            }
        }
    }

    function _make_canon($openid)
    {
        if( strpos($openid,'http://') != 0 )
            $openid = 'http://' . $openid;

        return rtrim($openid,'/');
    }
    
    function _do_assoc($openid,$user_id,$show=0)
    {
        $rec['openid'] = $this->_make_canon($openid);
        $rec['openid_user'] = $user_id;
        $rec['openid_show'] = $show;
        $openids = new CCTable('cc_tbl_openids','openid');
        if( $openids->QueryKeyRow($rec['openid']) )
            $openids->Update($rec);
        else
            $openids->Insert($rec);
    }
        
    function _do_login($user_id)
    {
        require_once('cchost_lib/cc-login.php');
        list( $user, $pw ) = CCDatabase::QueryRow(
           'SELECT user_name,user_password FROM cc_tbl_user WHERE user_id='.$user_id,false);
        $login = new CCLogin();
        $login->_create_login_cookie(1,$user,$pw);
        CCEvents::Invoke(CC_EVENT_LOGIN, array( $user_id ) );
        $login->_do_login_redirect(ccl('people',$user));
    }
    
    /**
     * This is called when an OpenID auth has been OK's without a known match
     *
     * This called the first time we see an OpenID and we're trying to
     * match it up with some account, or create a new one.
     *
     * This is the submit form handler (/openid/match) for CCOpenIDHookUpForm
     * 
     * @param string $openid OpenID returned by authority
     * @param string $canonicalID Should be cononical openID (but it's mainly blank)
     * @param array  $sreg Array of potential stuff authority gave us (nickname, email, fullname)
     */
	function OnIDVerified( $openid,$canonicalID,$sreg)
	{
	    $user_id = CCUser::CurrentUser();

	    if( $user_id != -1 )
	    {
	        // user is already logged in, we're just attaching.
	        $this->_do_assoc($openid,$user_id,true);
	        $user_name = CCUser::CurrentUserName();
	        CCUtil::SendBrowserTo( ccl('people',$user_name,'profile') );
        }
	    /*
            [fullname] => victor stone
            [nickname] => victorstone
            [email] => victor.stone@gmail.com
	     */
        if( empty($canonicalID) )
            $canonicalID = $this->_make_canon($openid);
        $args['connonical'] = $canonicalID;
        $row = CCDatabase::QueryRow("SELECT * FROM cc_tbl_openids WHERE openid = '{$canonicalID}'");
        if( empty($row) )
        {
            require_once('cchost_lib/cc-page.php');
            CCPage::SetTitle('str_openid_login_title');
            $args = $sreg;
            $args['openid'] = $openid;
            $user_name = cc_fancy_user_sql('display_name');
            $sql = "SELECT user_id, user_email, {$user_name} FROM cc_tbl_user WHERE (user_homepage LIKE '{$canonicalID}%') ";
            if( !empty($args['nickname']) )
            {
                $sql .= "OR (user_name = '{$args['nickname']}') ";
                $nick_n = $args['nickname'];
                $taken = CCUser::IDFromName($nick_n);
                $cnt = 0;
                while( !empty($taken) )
                {
                    $nick_n = sprintf($args['nickname'] . '%0d',++$cnt);
                    $taken = CCUser::IDFromName($nick_n);
                }
                $args['free_user_name'] = $nick_n;
            }
            if( !empty($args['email']) )
            {
                $sql .= "OR (user_email = '{$args['email']}') ";
            }
            if( !empty($args['fullname']) )
            {
                $sql .= "OR (user_real_name = '{$args['fullname']}') ";
            }
            $args['matches'] = CCDatabase::QueryRows($sql);
              
            require_once('cchost_lib/ccextras/cc-openid-forms.inc');
            $form = new CCOpenIDHookUpForm($args);
            CCPage::AddForm($form->GenerateForm());
        }
        else
        {
            $this->_do_login($row['openid_user']);
        }
    }
    
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('login','openid'), array( 'CCOpenID', 'Login' ), CC_ONLY_NOT_LOGGED_IN, ccs(__FILE__), 
            '', _('Show OpenID Login form'), CC_AG_USER  );
        CCEvents::MapUrl( ccp('openid'), 'cc_openid_handler', CC_DONT_CARE_LOGGED_IN,
            dirname(__FILE__) . '/cc-openid-handler.inc', '', _('Internal handler for openid comm.'), CC_AG_USER ); 
        CCEvents::MapUrl( ccp('openid','contact'), array( 'CCOpenID', 'Contact' ), CC_DONT_CARE_LOGGED_IN,
            ccs(__FILE__), '', _('Contact form hook to handle openid'), CC_AG_USER  );
        CCEvents::MapUrl( ccp('openid','notify'), array( 'CCOpenID', 'Notify' ), CC_MUST_BE_LOGGED_IN,
            ccs(__FILE__), '', _('Notify form hook to handle openid'), CC_AG_USER  );
    }
}


?>
