<?

/*
  $Id: mixup.inc 14109 2010-02-18 20:45:41Z fourstones $
*/

function mixup_helper_send_mail($msg,$user_id,$subject)
{
    $user_email = CCDatabase::QueryItem('SELECT user_email FROM cc_tbl_user WHERE user_id = ' . $user_id);
    require_once('cchost_lib/ccextras/cc-mail.inc');
    $mailer = new CCMailer();
    $mailer->Body($msg);
    $mailer->To($user_email);
    $mailer->Subject($subject);
    $mailer->Send();
}


function generator_mixup_mode_nav(&$form,$fieldname,$value,$class)
{
    $html     = $form->generator_select($fieldname,$value,$class);
    $mixup_id = $form->GetFormFieldItem($fieldname,'mixup');
    $urlb     = ccl('admin','mixup','massmail',$mixup_id) . '/';

    $js =<<<EOF
<script>
function do_nav() {
    var box = $('{$fieldname}');
    var mode = box.options[ box.selectedIndex ].value;
    window.location.href = '{$urlb}' + mode;
}
Event.observe( '{$fieldname}', 'change', do_nav );
</script>
EOF;
    return $html . $js;
}

function validator_mixup_mode_nav(&$form,$fieldname)
{
    return $form->validator_select($form,$fieldname);
}

function mixup_helper_admin_massmail($mixup_id,$mode='')
{
    mixup_helper_setup_page(_('Send Mixup Mass Mail'),$mixup_id);
    
    require_once('cchost_lib/cc-page.php');
    $page =& CCPage::GetPage();

    require_once('cchost_lib/cc-form.php');
    $form = new CCForm();

    $mod_opts = mixup_helper_get_mixup_options($mixup_id);

    if( empty($_POST) && empty($mode) )
        $mode = CCDatabase::QueryItem('SELECT mixup_mode FROM cc_tbl_mixups WHERE mixup_id = ' . $mixup_id);

    $fields = array(
            'mode_nav' =>
                array(
                    'label'      => 'Base message on mode:',
                    'form_tip'   => 'Select a mode to use as a template for the message',
                    'formatter'  => 'mixup_mode_nav',
                    'options'    => $mod_opts,
                    'value'      => $mode,
                    'mixup'      => $mixup_id,
                    'flags'      => CCFF_NONE,
                     ),
            'body_template' =>
                array(
                    'label'    => 'Message template',
                    'formatter' => 'textarea',
                    'flags'     => CCFF_REQUIRED
                )
    );
    $form->AddFormFields($fields);
    $form->SetSubmitText('Send Mail Now');
    if( empty($_POST)  || !$form->ValidateFields() ) {
        
        /* Show macro documentation */
        if( empty($_POST ) )
        {
            $msg = CCDatabase::QueryItem('SELECT mixup_mode_mail FROM cc_tbl_mixup_mode WHERE mixup_mode_id = '.$mode);
            $form->SetFormValue('body_template',$msg);
        }
        
        /* get the macro sub fields: */
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('f=php&dataview=mixup_mail&limit=1&mixup='.$mixup_id);
        $results = $query->Query($args);
        if( !empty($results[0][0]) ) {
            $help = mixup_helper_get_macro_help($results[0][0]);
            $form->SetFormHelp($help);
        }
        
        $page->AddForm( $form->GenerateForm() );
    }
    else {
        $form->GetFormValues($values);
        $query_str = 'f=php&dataview=mixup_mail&mixup='.$mixup_id .'&limit=' . mixup_helper_get_number_users($mixup_id);
        $org_text = $values['body_template'];
        if( mixup_helper_mail_merge($org_text,$query_str) )
        {
            $page->Prompt('OK, sent out all the mail');            
        }
        else
        {
            $page->Prompt('wups, nobody to send mail to!');            
        }
    }
}

function mixup_helper_get_macro_help(&$R)
{
    $html = '<table><tr><th>Macro</th><th>Example values</th>';
    foreach( $R as $K => $V )
    {
        if( $K == 'mixup_mode_mail ')
            continue;
        
        $html .= '<tr><td style="text-align:right"><span style="color:#AAA">%</span>' . $K . '<span style="color:#AAA">%</span></td>' .
                     '<td style="padding-left: 6px;color:#686;">  ' . $V . '</td></tr>';
    }
    $html .= '</table>';
    
    return $html;
}


function mixup_helper_get_mode_type($mixup_id)
{
  $mode_type = CCDatabase::QueryItem('SELECT mixup_mode_type FROM cc_tbl_mixups JOIN cc_tbl_mixup_mode on mixup_mode = mixup_mode_id WHERE mixup_id = ' .$mixup_id);    
  return $mode_type;  
}

function mixup_helper_is_current_user_signed_up($mixup_id)
{
    return 
        CCDatabase::QueryItem('SELECT mixup_user_user FROM cc_tbl_mixup_user WHERE mixup_user_mixup ='
                          . $mixup_id .
                          ' AND mixup_user_user = ' . CCUser::CurrentUser() );
    
}

function mixup_helper_api($action,$mixup_id=0,$arg='')
{
    global $CC_GLOBALS;
    
    if( $action == 'adminremove' )
    {
        if( !CCUser::IsAdmin() )
            exit;
            
        $table  = new CCTable('cc_tbl_mixup_user','mixup_user_mixup');
        $args['mixup_user_mixup'] = $mixup_id;
        $args['mixup_user_user']  = $arg;
        $table->DeleteWhere($args);
        $url = mixup_helper_get_mixup_url($mixup_id);
        CCUtil::SendBrowserTo($url);
        exit;
    }
    
    if( $action == 'playlist' )
    {
        if( !CCUser::IsAdmin() )
            exit;
            
        $playlist = CCDatabase::QueryItem('SELECT mixup_playlist FROM cc_tbl_mixups WHERE mixup_id ='.$mixup_id);
        if( empty($playlist) ) {
            // we do this to ensure all the proper macro and bbCode substitutions in the desc field
            // (very heavy weight, done exactly ONCE in the lifetime of any given mixup)
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $args = $query->ProcessAdminArgs('f=php&dataview=mixups&mixup='.$mixup_id);
            list( list( $row ) ) = $query->Query($args);

            // create the playlist
            require_once('cchost_lib/ccextras/cc-playlist.inc');
            $playlist_api = new CCPlaylists();
            $prow = $playlist_api->_create_playlist('playlist',
                                                    $row['mixup_display'],
                                                    $row['mixup_desc_plain'],
                                                    0,  // current user
                                                    'tags=' . $row['mixup_tag']
                                                    );
            $playlist = $prow['cart_id'];
            
            $table = new CCTable('cc_tbl_mixups','mixup_id');
            $dargs['mixup_id'] = $mixup_id;
            $dargs['mixup_playlist'] = $playlist;
            $table->Update($dargs);
        }

        $url = ccl('playlist','browse',$playlist);
        CCUtil::SendBrowserTo($url);
        exit;
    }
    
    if( $action == 'faq' )
    {
        $topic_id = CCDatabase::QueryItem('SELECT topic_id FROM cc_tbl_topics WHERE topic_type = \'secret_faq\'');
        if( empty($topic_id) )
        {
            require_once('cchost_lib/cc-page.php');
            $page =& CCPage::GetPage();
            $page->Prompt('Hey, tell your admin to create a topic with the type "secret_faq"');
            return;
        }
        $url = ccl('topics','view',$topic_id);
        CCUtil::SendBrowserTo($url);
        exit;
    }

    if( $action == 'remove')
    {
        mixup_helper_do_remove($mixup_id);
        $action = 'status';
    }

    if( $action == 'confirm_signup' )
    {
        mixup_helper_confirm_signup($mixup_id,$arg);
        $action = 'status';
    }

    if( $action == 'confirm_remix' )
    {
        mixup_helper_confirm_remix($mixup_id,$arg);
        $action = 'status';
    }
    
    if( $action == 'signup' )
    {
        mixup_helper_signup_request($mixup_id);
        if( !empty($_GET['redir']) )
            CCUtil::SendBrowserTo( mixup_helper_get_mixup_url($mixup_id) );
        $action = 'status';
    }

    
    if( $action == 'status' )
    {        
        $mode_type = mixup_helper_get_mode_type($mixup_id);
 
        $args['show_who'] = true;
        $args['show_matches'] = false;
        $args['msg'] = '';
        $args['user_status'] = '';
        
        switch( $mode_type )
        {
            case CC_MIXUP_MODE_DISABLED:
                $args['show_who'] = false;
                break;
            case CC_MIXUP_MODE_SIGNUP:
            case CC_MIXUP_MODE_MIXING:
                break;
            case CC_MIXUP_MODE_UPLOADING:
            case CC_MIXUP_MODE_DONE:
                $args['show_who'] = false;
                $args['show_matches'] = true;
                break;
        }
        
        $args['user_status'] = mixup_helper_get_user_status($mixup_id,$mode_type);
        CCUtil::ReturnAjaxData($args);
        exit;
    }
    
    die('Unknown command: ' . $action . ' sent to mixup api' );
}

function mixup_helper_confirm_remix($mixup_id,$arg)
{
    $user_id = CCUser::CurrentUser();
    mixup_helper_update_confirm_remix($mixup_id,$user_id,$_GET['mixstatus']);
}

function mixup_helper_do_remove($mixup_id)
{
    $mixup_name = mixup_helper_get_mixup_name($mixup_id);
    $user_signed_up = mixup_helper_is_current_user_signed_up($mixup_id);
    if( $user_signed_up )
    {
        $table  = new CCTable('cc_tbl_mixup_user','mixup_user_mixup');
        $args['mixup_user_mixup'] = $mixup_id;
        $args['mixup_user_user']  = CCUser::CurrentUser();
        $table->DeleteWhere($args);
        $args['ret_msg'] = "You have been removed from \"{$mixup_name}\"";
        $args['updateStatus'] = true;
    }
    else
    {
        CCUtil::ReturnAjaxMessage("You are not signed up for \"{$mixup_name}\" (?!)");
    }    
}

function mixup_helper_confirm_signup($mixup_id,$confirm_code)
{
    $user = CCUser::CurrentUser();
    if( $user == -1 )
        die('invalid user');
    
    if( empty($confirm_code) )
    {
        if( empty($_GET['code']) )
        {
            CCUtil::ReturnAjaxMessage('missing confirm code',CC_AJAX_ERROR);
        }
        $confirm_code = CCUtil::StripText($_GET['code']);
        if( empty($confirm_code) )
        {
            CCUtil::ReturnAjaxMessage('invalid confirm code',CC_AJAX_ERROR);
        }
    }
    $table = new CCTable('cc_tbl_mixup_confirm','mixup_confirm_mixup');
    $q['mixup_confirm_mixup'] = $mixup_id;
    $q['mixup_confirm_user'] = $user;
    $q['mixup_confirm_code'] = $confirm_code;
    $count = $table->CountRows($q);
    $url = mixup_helper_get_mixup_url($mixup_id);
    if( $count > 0 )
    {
        $del['mixup_confirm_mixup'] = $mixup_id;
        $del['mixup_confirm_user'] = $user;
        $table->DeleteWhere($del);

        // insert the new user info...
        $table  = new CCTable('cc_tbl_mixup_user','mixup_user_mixup');
        $dargs['mixup_user_mixup'] = $mixup_id;
        $dargs['mixup_user_user']  = $user;
        $dargs['mixup_user_date'] = date( 'Y-m-d H:i:s');
        $table->Insert($dargs);

        // notify the new user...
        
        $org_text = CCDatabase::QueryItem('SELECT mixup_mode_mail FROM cc_tbl_mixups ' .
                                          'JOIN cc_tbl_mixup_mode on mixup_mode=mixup_mode_id ' .
                                          'WHERE mixup_id ='.$mixup_id);
        
        $query_str = 'f=php&dataview=mixup_mail&mixup='.$mixup_id .
                                          '&user=' . CCUser::CurrentUserName(); // not ID!!!
        
        mixup_helper_mail_merge( $org_text, $query_str );
            
    }
    else
    {
        CCUtil::ReturnAjaxMessage('Confirm code does not match(!?)',CC_AJAX_ERROR);
    }
}

function mixup_helper_signup_request($mixup_id)
{
    global $CC_GLOBALS;
    
    $user_signed_up = mixup_helper_is_current_user_signed_up($mixup_id);
    $mixup_name = mixup_helper_get_mixup_name($mixup_id);
    if( !$user_signed_up )
    {
        if( empty($CC_GLOBALS['user_email']) )
        {
            die('can not file user email address' );
        }
        else
        {
            $code = sprintf( '%04X', rand(0x1000,0xFFFF) );
            $table = new CCTable('cc_tbl_mixup_confirm','mixup_confirm_mixup');
            $targs = array();
            $targs['mixup_confirm_mixup'] = $mixup_id;
            $targs['mixup_confirm_user'] = CCUser::CurrentUser();
            $table->DeleteWhere($targs); // remove previous codes
            $targs['mixup_confirm_code'] = $code;
            $table->Insert($targs);
            $url = mixup_helper_get_mixup_url($mixup_id);
            
            $mail =<<<EOF
Hey {$CC_GLOBALS['user_real_name']},

Thanks for signing up for the {$mixup_name}!

Your confirmation code is: {$code}

Go to {$url} now and enter it.

Thanks,
The admins
EOF;
            mixup_helper_send_mail($mail,CCUser::CurrentUser(), $mixup_name . ' Confirmation');

        }
    }    
}

function mixup_helper_get_user_status($mixup_id,$mode_type)
{
    // coming out of this function answer the question:
    // "What should I be doing?"
    
    if( CCUser::IsLoggedIn() )
    {
        $logged_in = CCUser::CurrentUser();
        $user_signed_up = mixup_helper_is_current_user_signed_up($mixup_id);
    }
    else
    {
        $logged_in = false;
        $user_signed_up = null;
    }
    
    $text = null;

    $mixup_name = mixup_helper_get_mixup_name($mixup_id);
    
    switch( $mode_type )
    {
        case CC_MIXUP_MODE_SIGNUP:
            $text = mixup_helper_get_signup_text($mixup_id,$mode_type,$logged_in,$user_signed_up,$mixup_name);
            break;
        case CC_MIXUP_MODE_MIXING:
            if( $user_signed_up )
            {
                $text = mixup_helper_get_mixing_status_text($mixup_id,$mixup_name);
            }
            else
            {
                $text = "Sorry, signups are closed.";
            }
            break;
        case CC_MIXUP_MODE_REMINDER:
            if( $user_signed_up )
            {
                $text = mixup_helper_get_remix_confirm_text($mixup_id,$mixup_name,$logged_in);
            }
            else
            {
                $text = "Sorry, signups are closed.";
            }
            break;
        case CC_MIXUP_MODE_DISABLED:
        case CC_MIXUP_MODE_UPLOADING:
        case CC_MIXUP_MODE_DONE:
            break;
    }
    
    return $text;
}

function mixup_helper_get_number_users($mixup_id)
{
    $sql = "SELECT COUNT(mixup_user_user) FROM cc_tbl_mixup_user WHERE mixup_user_mixup = {$mixup_id}"; 
    return CCDatabase::QueryItem($sql);
}

function mixup_helper_get_remix_confirm_text($mixup_id,$mixup_name,$logged_in)
{
    $confirm_url = ccl('api','mixup','confirm_remix',$mixup_id);
    $link_text = 'Update your status';
    $opts = mixup_helper_get_mixup_confirm_opts();
    $op = CCDatabase::QueryItem( "SELECT mixup_user_confirmed FROM cc_tbl_mixup_user WHERE mixup_user_mixup = {$mixup_id} AND mixup_user_user = {$logged_in}" );
    
    $options = '';
    foreach( $opts as $K => $V )
    {
        $options .= "<option value=\"{$K}\" ";
        if( $K == $op )
            $options .= 'selected="selected" ';
        $options .= ">{$V}</options>\n";
    }
    global $CC_GLOBALS;
    $text = <<<EOF

Hello {$CC_GLOBALS['user_real_name']}! Your assignment has been mailed to <b>{$CC_GLOBALS['user_email']}</b><br /><br />

Your current mixing status is:
<select id="param" name="mixstatus" onchange="$('mixup_dyn_button').style.display = '';">{$options}</select>
<a href="{$confirm_url}" id="mixup_dyn_button" class="small_button" style="display:none;font-color:red;font-weight:bold">{$link_text}</a>

<br />

EOF;
    
    return $text;
    
}


function mixup_helper_get_mixing_status_text($mixup_id,$mixup_name)
{
    /*
    $user = CCUser::CurrentUser();
    $row = CCDatabase::QueryRow('SELECT user_name, user_real_name FROM cc_tbl_mixup_user JOIN cc_tbl_user ON '
                                 . ' mixup_user_other=user_id WHERE mixup_user_user = ' . $user 
                                 . ' AND mixup_user_mixup = ' . $mixup_id );
    
    $html =<<<EOF

<div id="mixup_name" style="display:none;text-align:center;">
    Your assignement is 
</div>
EOF;
    */
    global $CC_GLOBALS;
    return "Hello {$CC_GLOBALS['user_real_name']}! Your assignment has been mailed to <b>{$CC_GLOBALS['user_email']}</b><br /><br />" .
        "If you " .
            "don't see it, check in your junk/spam folder. If you still don't see it in 2 hours, contact the admins using " .
            "the link at the bottom of this page."; 
}

function mixup_helper_get_signup_text($mixup_id,$mode_type,$logged_in,$user_signed_up,$mixup_name)
{
    global $CC_GLOBALS;
    $text = '';
    
    if( $logged_in )
    {
        $email = mixup_helper_get_email_text();
        
        if( $user_signed_up )
        {
            switch( $mode_type )
            {
                case CC_MIXUP_MODE_SIGNUP:
                    {
                        
                        $remove_url = ccl('api','mixup','remove',$mixup_id);
                        $button = mixup_helper_make_button($remove_url,'Remove me!');
                        
                        $text =<<<EOF
Hey {$CC_GLOBALS['user_real_name']}!<br ><br >
You are currently signed up for <b>{$mixup_name}</b>!<br /><br />
{$email}
<p>If you want to back out, now is the time! Just click on the button below. (If you change your mind again,
you can always sign up again.)<p>
{$button}
EOF;
                    }
                    break;
                case CC_MIXUP_MODE_MIXING:
                case CC_MIXUP_MODE_UPLOADING:
                    $text = 'You must have an account and log in to participate in <b>'.$mixup_name.'</b>';
                    break;
                case CC_MIXUP_MODE_DISABLED:
                case CC_MIXUP_MODE_DONE:
                    break;
            }            
        }
        else // user not signed up
        {
            $confirm_code = CCDatabase::QueryItem("SELECT mixup_confirm_code FROM cc_tbl_mixup_confirm WHERE ".
                                  " mixup_confirm_mixup={$mixup_id} ".
                                  " AND mixup_confirm_user={$logged_in}");
            
            if( $confirm_code )
            {
                $signup_url = url_args(ccl('api','mixup','signup', $mixup_id ),'redir=1');
                
                $profile_url = ccl('people','profile');                
                $confirm_url = ccl('api','mixup','confirm_signup',$mixup_id);
                $button = mixup_helper_make_button( $confirm_url, "Sign me up!"  );
                
                // this means we sent the code, they just haven't confirmed yet
                $text =<<<EOF
Hey {$CC_GLOBALS['user_real_name']} - thanks for signing up!<br /><br />
We've sent a user confirmation code to <b style="color:green">{$CC_GLOBALS['user_email']}</b>. 
(If that is not your correct email address, make sure to
<a style="text-decoration:underline" href="{$profile_url}">update your profile</a> and then come back here.)<br /><br />
If you don't see the confirmation code email in your Inbox, make sure to check your 'spam' folder in
case it got dropped there.<br /><br />
<input type="text" id="param" name="code" value="" /> {$button}<br /><br />
If you've waited a long time (about an hour) and you still don't see it, <a href="{$signup_url}">sigh... OK, try another confirmation code</a>
EOF;
            }
            else
            {
                // Check eligabity
                $no_email = empty($CC_GLOBALS['user_email']);
                $rules = mixup_helper_get_rules();
                $count_ok = false;
                foreach( $rules as $R )
                {
                    $count = mixup_helper_count_uploads($R['tag']);
                    if( $count >= $R['min'] )
                    {
                        $count_ok = true;
                    }
                }

                if( !$no_email && $count_ok )
                {
                    $signup_url = ccl('api','mixup','signup', $mixup_id );
                    $button = mixup_helper_make_button( $signup_url, 'my e-mail is correct, sign me up!' );
                    
                    $text =<<<EOF
Hi {$CC_GLOBALS['user_real_name']}, <br />
<br />
You're all set to sign up for the mixup!<br />
<br />
{$email}
{$button}
EOF;
                }
                else
                {
                    $text = "Hi {$CC_GLOBALS['user_real_name']}, <br /><br />{$email}";
                                        
                    if( !$count_ok )
                    {
                        $also = $no_email ? "Also, u" : "U";
                        
                        $text .= "{$also}nfortunately, you don't have enough of the right kind of uploads. You need at least: ";
                        $OR = '';
                        foreach( $rules as $R )
                        {
                            $s = $R['min'] == '1' ? '' : 's';
                            $text .= "{$OR}<b>{$R['min']}</b> upload{$s} of the type <b>{$R['tag']}</b>";
                            $OR = ', <i>OR</i>, ';
                        }
                        
                        $text .= ' in order to participate.';
                        
                    }
                }
            }
        }
        
    }
    else // user not logged in
    {
        switch( $mode_type )
        {
            case CC_MIXUP_MODE_SIGNUP:
            case CC_MIXUP_MODE_MIXING:
            case CC_MIXUP_MODE_UPLOADING:
                $text = 'You must have an account and log in to participate in <b>'.$mixup_name.'</b>';
                break;
            case CC_MIXUP_MODE_DISABLED:
            case CC_MIXUP_MODE_DONE:
                break;
        }
    }
    return $text;
}

function mixup_helper_make_button($url,$text)
{
    $text =<<<EOF
<p>
<a href="{$url}" id="mixup_dyn_button" class="small_button" style="font-color:red;font-weight:bold">{$text}</a>
</p>
EOF;

    return $text;    
}

function mixup_helper_get_email_text()
{
    global $CC_GLOBALS;
    $profile_url = ccl('people','profile');

    if( empty($CC_GLOBALS['user_email']) )
    {
        $text =<<<EOF
You can't sign up unless we have a valid email address for you. Please fix it in <a
href="{$profile_url}">your profile</a>.<br /><br />
EOF;
        
    }
    else
    {
        $text =<<<EOF
We have your current email as <b style="color:green">{$CC_GLOBALS['user_email']}</b> If that is not correct it, please fix it in <a
href="{$profile_url}">your profile</a>.<br /><br />
EOF;
    }
    return $text;

}


function mixup_helper_count_uploads($tag)
{
    require_once('cchost_lib/cc-query.php');
    $qry = new CCQuery();
    $name = CCUser::CurrentUserName();
    $args = $qry->ProcessAdminArgs('f=count&tags='.$tag.'&user='.$name);
    $result = $qry->Query($args);
    return  (integer) trim($result[0],'[]');
}

function mixup_helper_get_rules()
{
    return array(
                array( 'tag' => 'sample',    'min' => 2 ),
                array( 'tag' => 'acappella', 'min' => 1 ),
    );
    
}


function mixup_helper_mail_merge($org_text,$query_str)
{
    require_once('cchost_lib/cc-query.php');
    $query = new CCQuery();
    $args = $query->ProcessAdminArgs($query_str);
    $results = $query->Query($args);
    
    if( empty($results[0][0]) ) {
        return false;
    }
    $merge_with = $results[0][0];
    $replace_pats = array_map('mixup_helper_addpatt',array_keys($merge_with));
    
    foreach( $results[0] as $R ) {
        $text = str_replace( $replace_pats, $R, $org_text );
        mixup_helper_send_mail($text,$R['mixer_user_id'],$R['mixup_display']);
    }

    return $merge_with;
}

function mixup_helper_do_shuffle( $first, $org_second, $rejects  = array() )
{
    $second = $org_second;
    $num = count($first);
    shuffle($second);
    $pairs = array();

    for( $i = 0; $i < $num; $i++ )
    {
        $f = $first[$i];
        $s = $second[$i];
        if( $f == $s ) // || ( in_array($f,$rejects) && in_array($s, $rejects) ) )
        {
            //dlog("\nFORCING reshuffle on {$f}/{$s}\n");
            return mixup_helper_do_shuffle($first, $org_second);
        }
        
        $pairs[] = array( $first[$i], $second[$i] );
    }
    
    return $pairs;
}

function mixup_helper_gen_assignments($mixup_id)
{
    $users = CCDatabase::QueryItems('SELECT mixup_user_user FROM cc_tbl_mixup_user WHERE mixup_user_mixup =' . $mixup_id);
    
    require_once('cchost_lib/cc-dataview.php');
    $rules = mixup_helper_get_rules();
    $filters = array();
    foreach( $rules as $R )
    {
        $dv = new CCDataView();
        $filters[] = $dv->MakeTagFilter($R['tag']);
    }
    $filter = join( ' OR ', $filters);
    $cutoff_file = dirname(__FILE__) . '/mixup_cutoff';
    $cutoff = file_exists( $cutoff_file ) ? file_get_contents( $cutoff_file ) : 24;
    $lo = array();
    $hi = array();
    foreach( $users as $K => $U )
    {
        $count = CCDatabase::QueryItem("SELECT COUNT(*) FROM cc_tbl_uploads WHERE ({$filter}) AND upload_user={$U}");
        $N = CCUser::GetUserName($U);
        if( $count <= $cutoff )
        {
            $lo[] = $U;
        }
        else
        {
            $hi[] = $U;
        }
    }

    if( count($hi) & 1 ) // odd number?
    {
        $odd = array_shift($hi);
        $lo[] = $odd;
    }
    
    $pairs_lo = mixup_helper_do_shuffle_outer($mixup_id,$lo);
    $pairs_hi = mixup_helper_do_shuffle_outer($mixup_id,$hi);

    $pairs = array_merge($pairs_hi,$pairs_lo);
    $table = new CCTable('cc_tbl_mixup_user','mixup_user_id');
    foreach( $pairs as $P )
    {
        $a['mixup_user_other'] = $P[1];
        $w['mixup_user_mixup'] = $mixup_id;
        $w['mixup_user_user']  = $P[0];
        $table->UpdateWhere($a,$w);
    }
}

function mixup_helper_do_shuffle_outer($mixup_id,$pairs)
{
    for( $i = 0; $i < 3; $i++ )
    {
        $pairs_back = mixup_helper_do_shuffle($pairs,$pairs);
        $swap = mixup_helper_check_prev_assignments($mixup_id,$pairs_back);
        if( empty($swap) )
        {
            break;
        }
    }    
    
    if( 0 && $swap )
    {
        d($swap);
    }
    
    return $pairs_back;
}

function mixup_helper_check_prev_assignments($mixup_id,$pairs)
{
    $swap_out = array();

    foreach( $pairs as $P )
    {
        $sql = "SELECT COUNT(*) FROM cc_tbl_mixup_user WHERE (mixup_user_user = {$P[0]} AND mixup_user_other = {$P[1]}) AND "
               . "mixup_user_mixup <> {$mixup_id}";
        $count = CCDatabase::QueryItem($sql);
        if( $count )
        {
            $sql2 = "SELECT user_id,user_name FROM cc_tbl_user WHERE user_id IN ({$P[0]},{$P[1]})";
            $ids = CCDatabase::QueryRows($sql2);
            $swap_out[] = array( $ids, $P, $sql );
        }
    }
    
    return $swap_out;
}

function mixup_helper_assignments($mixup_id)
{
    require_once('cchost_lib/cc-form.php');
    require_once('cchost_lib/cc-page.php');
    
    $page =& CCPage::GetPage();
    mixup_helper_setup_page('Edit Assignments',$mixup_id);
    
    if( empty($_POST) && !empty($_GET['gen_assignments']) ) 
        mixup_helper_gen_assignments($mixup_id);
        
    $form = new CCGridForm();
    $cols = array( 'Remixer', 'Assignment', 'Upload' );
    $form->SetColumnHeader($cols);
    $rows = CCDatabase::QueryRows('SELECT cc_tbl_mixup_user.* FROM cc_tbl_mixup_user JOIN cc_tbl_user on ' .
                                  'mixup_user_user=user_id WHERE mixup_user_mixup = ' . $mixup_id . ' ORDER by user_name');
    $count = 0;
    $stat_options = array(
                        CC_MIXUP_STATUS_DONE     => "Done",
                        CC_MIXUP_STATUS_NOT_SURE => "Not sure",
                        CC_MIXUP_STATUS_CANT     => "Bailed",
                        CC_MIXUP_STATUS_FLAKED   => "Flaked"
                    );
    
    foreach( $rows as $R )
    {
        $S = 'S[' . ++$count . ']';
        $a = array(
              array(
                'element_name'  => $S . '[mixup_user_user]',
                'value'      => $R['mixup_user_user'],
                'formatter'  => 'username',
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => $S . '[mixup_user_other]',
                'value'      => $R['mixup_user_other'],
                'formatter'  => 'username',
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => $S . '[mixup_user_upload]',
                'value'      => $R['mixup_user_upload'],
                'formatter'  => 'textedit',
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => $S . '[mixup_user_confirmed]',
                'value'      => $R['mixup_user_confirmed'],
                'formatter'  => 'select',
                'options'    => $stat_options,
                'flags'      => CCFF_POPULATE ),
            );

        $form->AddGridRow( $count, $a );
        $form->SetHiddenField( $S . '[mixup_user_id]', $R['mixup_user_id'] );
        
    }
    
    $S = 'new[%i%]';
    $a = array(
          array(
            'element_name'  => $S . "[mixup_user_user]",
            'value'      => '',
            'formatter'  => 'username',
            'flags'      => CCFF_NONE ),
          array(
            'element_name'  => $S . "[mixup_user_other]",
            'value'      => '',
            'formatter'  => 'username',
            'flags'      => CCFF_NONE ),
          array(
            'element_name'  => $S . "[mixup_user_upload]",
            'value'      => '',
            'formatter'  => 'textedit',
            'class'      => 'cc_form_input_short',
            'flags'      => CCFF_NONE ),
          array(
            'element_name'  => $S . '[mixup_user_confirmed]',
            'value'      => CC_MIXUP_STATUS_NOT_SURE,
            'formatter'  => 'raw_select',
            'options'    => $stat_options,
            'flags'      => CCFF_NONE ),
        );

    $form->AddMetaRow($a,_('Add Asssignment'));
    
    
    if( empty($_POST) || !$form->ValidateFields() )
    {
        $url = url_args( ccl('admin','mixup','assign',$mixup_id), 'gen_assignments=1');
        $help =<<<EOF
        <a class="small_button" href="{$url}">Auto-generate Assignment</a>
        <p><b>NOTE</b>: Auto-generating will re-assign all values below.</p>
        <p><b>NOTE</b>: Auto-generating assignments will <b>not</b> send assignment emails.
EOF;
        $form->SetFormHelp($help);
        $page->AddForm( $form->GenerateForm() );
    }
    else
    {
        // sigh.
        // The structure of POST is perfect, but the values
        // are only translated to user_ids in the $form obj.
        $form->GetFormValues($values);
        $s = $_POST['S'];
        $c = count($s);
        $table = new CCTable('cc_tbl_mixup_user','mixup_user_id');
        for( $i = 0; $i < $c; $i++ )
        {
            $k = 'S[' . ($i+1) . ']';
            $da['mixup_user_id']        = $values[ $k . '[mixup_user_id]' ];
            $da['mixup_user_user']      = $values[ $k . '[mixup_user_user]' ];
            $da['mixup_user_other']     = $values[ $k . '[mixup_user_other]' ];
            $da['mixup_user_upload']    = $values[ $k . '[mixup_user_upload]' ];
            $da['mixup_user_confirmed'] = $values[ $k . '[mixup_user_confirmed]' ];
            $table->Update($da);
        }

        if( !empty($_POST['new']) )
        {
            $n = $_POST['new'];
            $usernames = array();
            foreach( $n as $newrec ) {
                $usernames[] = $newrec['mixup_user_user'];
                $usernames[] = $newrec['mixup_user_other'];
            }
            $usernames = "'" . join( "','", array_unique($usernames)) . "'";
            $sql = 'SELECT user_name,user_id from cc_tbl_user WHERE user_name IN (' . $usernames . ')';
            $rows = CCDatabase::QueryRows($sql);

            $usernames = array();
            foreach( $rows as $R )
                $usernames[ $R['user_name']] = $R['user_id'];
            foreach( $n as $args )
            {
                $args['mixup_user_mixup'] = $mixup_id;
                $args['mixup_user_user']  = empty($args['mixup_user_user'])  ? 0 : $usernames[ $args['mixup_user_user'] ];
                $args['mixup_user_other'] = empty($args['mixup_user_other']) ? 0 : $usernames[ $args['mixup_user_other'] ];
                $table->Insert($args);
            }
        }
        
        $url = ccl('admin', 'mixup', 'edit', $mixup_id );
        CCUtil::SendBrowserTo($url);
        
    }

}


function mixup_helper_setup_page($text,$mixup_id=0)
{        
    require_once('cchost_lib/cc-admin.php');
    $admin = new CCAdmin();
    $title = _('Admin Mixups');
    
    if( $mixup_id )
    {
        $name = CCDatabase::QueryItem('SELECT mixup_display FROM cc_tbl_mixups WHERE mixup_id = '.$mixup_id);
        $trail1 = array( 'url' => ccl('admin','mixup'), 'text' => $title );
        $trail2 = array( 'url' => ccl('admin','mixup','edit', $mixup_id), 'text' => $name );
        $trail3 = array( 'url' => '', 'text' => $text );
        $admin->BreadCrumbs(true,$trail1,$trail2,$trail3);
        $text .= " \"{$name}\" ";
        
    }
    else if( $text )
    {
        $trail1 = array( 'url' => ccl('admin','mixup'), 'text' => $title );
        $trail2 = array( 'url' => '', 'text' => $text );
        $admin->BreadCrumbs(true,$trail1,$trail2);
    }
    else
    {
        $text = $title;
        $trail2 = array( 'url' => '', 'text' => $title );
        $admin->BreadCrumbs(true,$trail2);
    }
    
    require_once('cchost_lib/cc-page.php');
    $page =& CCPage::GetPage();
    $page->SetTitle($text);
}    

function mixup_helper_get_mixup_name($mixup_id)
{
    return CCDatabase::QueryItem('SELECT mixup_display from cc_tbl_mixups WHERE mixup_id = ' . $mixup_id);
    
}

function mixup_helper_get_mixup_url($mixup_id)
{
    $name = CCDatabase::QueryItem('SELECT mixup_name from cc_tbl_mixups WHERE mixup_id = ' . $mixup_id);
    
    return ccl( 'mixup', $name );
}
?>