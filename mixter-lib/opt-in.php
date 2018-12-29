<?
/*
  $Id: opt-in.php 13835 2009-12-25 13:19:34Z fourstones $
*/

define('CC_MIXTER_OWNER', 'ArtisTech Media');
define('CC_OPT_IN_FLAG','opt-in-done');

CCEvents::AddHandler( CC_EVENT_MAP_URLS, 'opt_in_url_map' );
CCEvents::AddHandler( CC_EVENT_APP_INIT, 'opt_in_app_init' );

function opt_in_app_init()
{
    //return;

    global $CC_GLOBALS;
    
    // Don't bother if the user is not logged in, registered on ATM's server
    // or already opt-in'd
    
    if( empty($CC_GLOBALS['user_id']) ||
        ($CC_GLOBALS['user_registered'] > '2009-11-06') ||
        !empty($CC_GLOBALS['user_extra'][CC_OPT_IN_FLAG])  )
    {
        return;
    }

    $url = cc_current_url();

    // let these URLs through...
    
    if( preg_match('#/(opt-in|terms|logout|privacy|opt-in/deluser)/?$#',$url) ) {
        return;
    }
    
    CCUtil::SendBrowserTo(ccl('opt-in'));
}

function opt_in_url_map()
{
    CCEvents::MapUrl( 'opt-in', 'opt_in', CC_MUST_BE_LOGGED_IN );
}

function opt_in($cmd='')
{
    global $CC_GLOBALS;

    require_once('cchost_lib/cc-page.php');
    require_once('cchost_lib/cc-form.php');
    
    $page =& CCPage::GetPage();
    $page->SetTitle('ccMixter Opt-In');
    
    if( !empty($CC_GLOBALS['user_extra'][CC_OPT_IN_FLAG]) )
    {
        $page->Prompt('You have already set your opt-in options');
    }
    else
    {
        if( $cmd == 'deluser' )
        {
            opt_in_helper_del_user();
            return;
        }
    
        $NEW_OWNER = CC_MIXTER_OWNER;
        
        $form = new CCForm();
        $terms_url = url_args(ccl('terms'),'popup=1');
        $privacy_url = url_args(ccl('privacy'),'popup=1');

        $help =<<<EOF
<p>ccMixter is under new management.</p>
<p>Part of the agreement between Creative Commons and {$NEW_OWNER} is that current
ccMixter users have a choice how to move their account forward
or to remove the account completely.</p>
<p>First, it is important for you to know that the Terms of Use and Privacy Statement for the site have changed. Do not
take any action before you have read and understood them.</p>
<iframe style="width:95%;height:12em" src="{$terms_url}"></iframe><br /><br />
<iframe style="width:95%;height:12em" src="{$privacy_url}"></iframe>
EOF;

        $form->SetFormHelp($help);
        
        
        $fields = array(
            'opts' => array(
                    'label' => '&nbsp;',
                    'formatter' => 'opt_in_field',
                    'email_addr' => $CC_GLOBALS['user_email'],
                    'flags' => CCFF_REQUIRED
                    ),
                );
        
        $form->AddFormFields($fields);
        
        if( empty($_POST) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $user_id = CCUser::CurrentUser();
            
            $form->GetFormValues($values);
            if( $values['opts'] == 'in' )
            {
                $table = new CCTable('cc_tbl_user','user_id');
                $ua['user_id'] = $user_id;
                
                if( !empty($_POST['opt_in_email']) )
                {
                    $ua['user_email'] = $_POST['opt_in_email'];
                }

                $ex = CCDatabase::QueryItem('SELECT user_extra FROM cc_tbl_user WHERE user_id = ' . $user_id);
                $ex = unserialize($ex);
                $ex[CC_OPT_IN_FLAG] = true;
                $ua['user_extra'] = serialize($ex);
                $table->Update($ua);
                CCUtil::SendBrowserTo( ccl('people',$CC_GLOBALS['user_name']) );
                
            }
            else // delete account
            {
                CCUtil::SendBrowserTo( ccl('opt-in','deluser') );
            }
        }
    }
}

function opt_in_helper_del_user()
{
    require_once('cchost_lib/cc-page.php');
    require_once('cchost_lib/cc-form.php');
    
    $page =& CCPage::GetPage();
    $page->SetTitle('ccMixter Opt-Out: Deleting Account');
    
    global $CC_GLOBALS;
    
    if( !empty($_POST) )
    {
        $user_name = $CC_GLOBALS['user_name'];
        $user_id = CCUser::CurrentUser();
        opt_in_helper_do_del_user($user_id);
        $page->Prompt("The account <b>{$user_name}</b> has been removed.");
        CCEvents::Invoke( CC_EVENT_LOGOUT, array( $CC_GLOBALS['user_name'] ) );
        //$page->Prompt("Account deletion code goes here...");
        return;
    }
    
    $form = new CCForm();
    $text =<<<EOF
<p>You have chosen to completely remove your <b>{$CC_GLOBALS['user_name']}</b> account from ccMixter. Of course, we are sorry to see you go!</p>
<p>Please understand two things about what you are about to do:</p>
<ul>
<li><b>This is permanent</b> Once you delete your account, we have no way of getting any of the information back,
including all the files you've ever uploaded to ccMixter. We will not be able to restore your account.
<i>There is NO UNDO</i><br /><br /></li>
<li><b>Your music will continue to under CC license</b> The music that people downloaded from ccMixter will continue to be under Creative Commons
license even after we delete them from our servers. That's because the CC license is "non-revokable."
(Read more about <a href="http://wiki.creativecommons.org/FAQ#What_if_I_change_my_mind.3F">what "non-revokable" means</a>.)</li>
</ul>
<br />
<br />
EOF;

    $form->SetHelpText($text);
    $page->String('str_uploading_msg'); // make sure to load string..
    // then change it...
    $GLOBALS['str_uploading_msg']  = _('This could take several minutes... please be patient...');    
    $form->EnableSubmitMessage(true);
    $form->SetSubmitText('DELETE MY ACCOUNT NOW');
    $page->AddForm( $form->GenerateForm() );
}

function opt_in_helper_do_del_user($user_id)
{
    // delete reviews, notifications, etc.
    $record = CCDatabase::QueryRow(
                'SELECT user_name, user_id FROM cc_tbl_user WHERE user_id='.$user_id);
    
    CCEvents::Invoke( CC_EVENT_USER_DELETED, array( $record['user_id'], &$record ) );
    
    // delete files
    
    $uploads =& CCUploads::GetTable();
    $where['upload_user'] = $user_id;
    $ids = $uploads->QueryKeys($where);
    require_once('cchost_lib/cc-uploadapi.php');
    foreach( $ids as $id )
        CCUploadAPI::DeleteUpload($id);
    
    
    // delete use record

    CCDatabase::Query('DELETE FROM cc_tbl_user WHERE user_id = ' . $user_id );    
    
}

function generator_opt_in_field(&$form,$varname,$value='',$class='')
{
    $email = $form->GetFormFieldItem($varname,'email_addr');
    $NEWOWNER = CC_MIXTER_OWNER;
    $email_edit = 'opt_in_email';
    $html =<<<EOF
<table cellspacing="4">
<tr>
    <td>
    <input type="radio" checked="checked" name="{$varname}" id="{$varname}_in" value="in" />
    <label for="{$varname}_in">Leave my account the way it is</label>.
    </td>
    <td>
        <p>This assumes that you have read and understood the
           <a href="/terms">Terms of Use</a> by {$NEWOWNER} and have agreed to them.</p>
        <p>You can choose to withold your email address from {$NEWOWNER}, just leave the field below blank.
        However, that means
        that you will not get any notifications including about remixes and trackbacks
        and no one will be able to contact you through the site.</p>
        Your current email: <input type="text" name="{$email_edit}" id="{$email_edit}" value="{$email}" size="40" />
    </td>
</tr>
<tr>
    <td>
        <input type="radio" name="{$varname}" id="{$varname}_out" value="out" />
        <label style="color:red" for="{$varname}_out">Remove my account</label>.
    </td>
    <td>
        This will destroy all traces of your ccMixter account, including remove any uploads (keeping
        in mind that CC licenses are <a href="http://wiki.creativecommons.org/FAQ#What_if_I_change_my_mind.3F">non-revokable</a>).
    </td>
</tr>
</table>
EOF;

    return $html;
}

function validator_opt_in_field(&$form,$fieldname)
{
    if( empty($_POST['opts']) || !in_array($_POST['opts'], array('in','out')) )
    {
        $form->SetFieldError($fieldname,'You must choose a option...');
        return false;
    }
    return true;
}
?>
