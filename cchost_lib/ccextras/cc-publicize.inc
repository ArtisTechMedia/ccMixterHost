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
* $Id: cc-publicize.inc 12683 2009-05-31 19:36:44Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
*/
class CCPublicize
{
    function Publicize($user='')
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        if( empty($user) )
        {
            if( !($user = CCUser::CurrentUserName()) )
            {
                $page->Prompt('str_share_no_user');
                return;
            }
        }

        $itsme  = $user == CCUser::CurrentUserName();
        if( !$this->_pub_wizard_allowd($itsme) )
        {
            $page->Prompt('str_share_no_feature');
            return;
        }

        $this->_share('user',$user,$itsme);
    }

    function Share($id)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $uploads =& CCUploads::GetTable();
        if( !$uploads->KeyExists($id) )
        {
            $page->Prompt('str_share_no_upload');
            return;
        }
        $record = CCDatabase::QueryRow('SELECT upload_id, user_name, upload_name, user_real_name FROM cc_tbl_uploads JOIN ' .
                                       'cc_tbl_user ON upload_user=user_id WHERE upload_id=' . $id );
        $record['file_page_url'] = ccl('files',$record['user_name'],$id);
        $this->_share('upload',$record);
    }

    function SharePlaylist($id)
    {
        $this->_share('playlist',$id);
    }

    function _share($type,$arg1,$arg2='')
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        $args['intro'] = '';
        $args['step1'] = 'str_share_step1';
        $args['step2'] = 'str_share_step2';
        $args['hiddens'] = array();

        $do_share_popup = true;

        $combos = array();

        if( $type == 'user' )
        {
            $sql = 'SELECT user_name, user_real_name FROM cc_tbl_user WHERE user_name=\'' . $arg1 . '\'';
            $record = CCDatabase::QueryRow($sql);
            if( empty($record) )
            {
                $page->Prompt('str_share_no_user');
                return;
            }
            $record['artist_page_url'] = ccl('people',$record['user_name']);
            $args['bookmark_url'] = $record['artist_page_url'];
            $configs =& CCConfigs::GetTable();
            $template_tags = $configs->GetConfig('ttag');
            $args['bookmark_title'] = $record['user_real_name'] . ' @ ' . $template_tags['site-title'];

            $args['email_url'] = ccl( 'email', 'user', $arg1 );

            $args = array_merge( $args, $record );
            
            if( $arg2 ) // $itsme 
            {
                $args['intro'] = 'str_share_intro1';
                $yourremixes = 'str_share_your_remixes';
                $othersremixes = 'str_share_other_remixes';
                $allyourups = 'str_share_your_uploads';
                $title = 'str_share_pub_yourself';
            }
            else
            {
                $args['intro'] = $page->String(array('str_share_intro2a', '<b>' . $record['user_real_name'] . '</b>' ))  . ' ' .

                    $page->String(array('str_share_intro2b', '<b>' . $record['user_real_name'] . '</b>' ));
                $yourremixes = $page->String(array( 'str_share_your_remixes_s', $record['user_real_name'] ));
                $othersremixes = $page->String(array( 'str_share_other_remixes_s',
                                             $record['user_real_name'] ));
                $allyourups = $page->String(array( 'str_share_all_uploads_s', $record['user_real_name'] ));
                $title = $page->String(array( 'str_share_publicize_s', $record['user_real_name'] ));
            }
            $opts['title'] = 'str_share_type_links';
            $opts['name']  = '';
            $opts['class']  =
            $opts['id']    = 'usertypechanger';
            $opts['help']  = '';
            $opts['opts'] = array( 
                    array( 'value'    => 'remix',
                           'selected' => true,
                           'text'     => $yourremixes ),
                    array( 'value'    => $record['user_name'],
                           'selected' => false,
                           'text'     => $othersremixes ),
                    array( 'value'    => 'all',
                           'selected' => false,
                           'text'     => $allyourups )
                    );
            $combos[] = $opts;

            $do_num_links = true;
            $do_chop = true;
        }
        else
        {
            if( $type == 'upload' )
            {
                $record =& $arg1;
                
                $args['intro'] = 'str_share_intro3';
                $args['bookmark_url'] = $record['file_page_url'];
                $args['bookmark_title'] = $record['upload_name'];
                $args['email_url'] = ccl( 'email', 'upload', $record['upload_id'] );

                $args = array_merge( $args, $record );
                $title = $page->String(array( 'str_share_share_s', $record['upload_name'] ));
                $args['hiddens'][] = array( 'value' => $record['upload_id'], 'name' => 'ids' );
            }
            elseif ($type == 'playlist')
            {
                list( $playlist_name, $query, $subtype ) = CCDatabase::QueryRow(
                        'SELECT cart_name, cart_dynamic, cart_subtype FROM cc_tbl_cart WHERE cart_id='.$arg1,false);
                
                if( !empty($subtype) && ($subtype == 'default') )
                {
                    require_once('cchost_lib/cc-query.php');
                    $query = new CCQuery();
                    $query= $query->ProcessUriArgs();
                    $do_share_popup = false;
                }

                if( !empty($query) )
                {
                    if( is_string($query) )
                        parse_str($query,$query);
                    foreach( $query as $name => $value )
                        $args['hiddens'][] = array( 'name' => $name, 'value' => $value );
                }
                else
                {
                    $args['hiddens'][] = array( 'value' => $arg1, 'name' => 'playlist' );
                }
                $args['bookmark_url'] = ccl('playlist','browse',$arg1);
                $args['bookmark_title'] = $playlist_name;
                $args['email_url'] = ccl( 'email', 'playlist', $arg1 );
                $title = $page->String(array( 'str_share_share_s', $playlist_name ));

                if( $do_share_popup )
                {
                    $args['intro'] = 'str_share_intro4a';
                }
                else
                {
                    $args['intro'] = 'str_share_intro4b';
                }

                $sql =<<<EOF
                    SELECT user_name,user_real_name
                    FROM cc_tbl_user 
                    JOIN cc_tbl_cart ON cart_user = user_id
                    WHERE cart_id = {$arg1}
EOF;
                list( $user_name, $user_real_name ) = CCDatabase::QueryRow($sql,false);
                $this->_build_bread_crumb_trail_pl($user_name, $user_real_name, $arg1, $playlist_name );

            }

            $do_num_links = false;
            $do_chop = false;
            $args['user_name'] = '';
        }

        if( $do_num_links )
        {
            $opts = array();
            $opts['title'] = 'str_share_num_links';
            $opts['name']  =
            $opts['id']    = 'limit';
            $opts['class'] = 'queryparam';
            $opts['help']  = '';
            $opts['opts']  = array(
                    array( 'value'     => '1',
                           'selected'  => false,
                           'text'      => 'str_share_num_links1' ),
                    array( 'value'     => '5',
                           'selected'  => true,
                           'text'      => 'str_share_num_links5' ),
                    array( 'value'     => '10',
                           'selected'  => false,
                           'text'      => 'str_share_num_links10'),
                    array( 'value'     => '50',
                           'selected'  => false,
                           'text'      => 'str_share_num_links50')
                );
            $combos[] = $opts;

            $s = 's';
        }
        else
        {
            $s = '';
        }

        /*
            Formats
        */
        $opts = array();
        $opts['title'] = 'str_share_format';
        $opts['help']  = '';
        $opts['class'] = 'queryparam';
        $opts['name']  =
        $opts['id']    = 'template';
        require_once('cchost_lib/cc-template.inc');
        $embeds = CCTemplateAdmin::GetFormats('embed');
        $embedded_templates = array();
        foreach( $embeds as $K => $F )
        {
            $embedded_templates[] = $K;
            $opts['opts'][] = array( 'value' => $K, 'text' => $F, 'selected' => false );
        }
        $fmts = CCTemplateAdmin::GetFormats('format');
        foreach( $fmts as $K => $F )
        {
            $opts['opts'][] = array( 'value' => $K, 'text' => $F, 'selected' => false );
        }
        $combos[] = $opts;

        /*
            Chop
        */
        if( $do_chop )
        {
            $opts = array();
            $opts['title'] = 'str_share_chop';
            $opts['class'] = 'queryparam';
            $opts['name']  =
            $opts['id']    = 'chop';
            $opts['help']  = 'str_share_cut_off';
            $opts['opts']  = array(
                    array( 'value'  => '10',
                           'selected'  => true,
                           'text'   => 'str_share_cut_off_10'),
                    array( 'value'  => '20',
                           'selected'  => false,
                           'text'   => 'str_share_cut_off_20'),
                    array( 'value'  => '25',
                           'selected'  => false,
                           'text'   => 'str_share_cut_off_25'),
                    array( 'value'  => '0',
                           'selected'  => false,
                           'text'   => 'str_share_cut_off_off'),
                );
            $combos[] = $opts;
        }

        $args['combos'] =& $combos;
        $args['embedded_templates'] = $embedded_templates;

        $args['seehtml']       = 'str_share_raw_html';
        $args['showformatted'] = 'str_share_formatted';
        
        $args['preview'] = 'str_share_preview';
        $args['previewwarn'] = 'str_share_preview_warn';
        $args['htmlwarn'] = 'str_share_html_warn';

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle( $title );
        $page->PageArg('publicize', 'publicize.php/publicize');
        $page->PageArg('PUB', $args );
        if( $do_share_popup )
        {
            $page->PageArg('share', 'share.php/share_popup' );
            $page->AddMacro('share');
        }
        $page->AddMacro('publicize');

        if( !empty($record) )
        {
            $this->_build_bread_crumb_trail($record['user_name'], empty($record['upload_id']) ? '' : $record['upload_id']);
        }
    }

    /**
    * @access private
    */
    function _build_bread_crumb_trail_pl($username,$user_real_name,$playlist_id,$playlist_name)
    {
        $trail[] = array( 'url' => ccl(), 'text' => _('Home') );
        
        $trail[] = array( 'url' => ccl('people'), 
                          'text' => 'str_people' );

        $trail[] = array( 'url' => ccl('people',$username), 
                                   'text' => $user_real_name );

        $trail[] = array( 'url' => ccl('people',$username,'playlists'), 
                          'text' => 'str_playlists' );

        $trail[] = array( 'url' => ccl('playlist','browse',$playlist_id ),
                          'text' => $playlist_name );

        $trail[] = array( 'url' => '', 'text' => 'str_share_text' );

        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    /**
    * @access private
    */
    function _build_bread_crumb_trail($username,$upload_id)
    {
        $trail[] = array( 'url' => ccl(), 'text' => _('Home') );
        
        $trail[] = array( 'url' => ccl('people'), 
                          'text' => 'str_people' );
        $users =& CCUsers::GetTable();
        $user_real_name = $users->QueryItem('user_real_name',
                                            "user_name = '$username'");
        if( !empty($user_real_name) )
        {
            $trail[] = array( 'url' => ccl('people',$username), 
                                       'text' => $user_real_name );
            if( !empty($upload_id) )
            {
                $uploads =& CCUploads::GetTable();
                $upload_name = $uploads->QueryItemFromKey('upload_name',
                                                          $upload_id);
                if( !empty($upload_name) )
                {
                    $upload_name = '"' . $upload_name . '"';
                    $trail[] = array( 'url' => ccl('files',$username,
                                                   $upload_id), 
                                       'text' => $upload_name );
                }
            }
        }

        $trail[] = array( 'url' => '', 'text' => 'str_share_text' );

        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    function _find_fmt_template($name)
    {
        $trythese = array( $name, 
                           'formats/' . $name . '.xml',
                           $name . '.xml',
                           'formats/' . $name );

        foreach( $trythese as $trythis )
            if( ($file = CCTemplate::GetTemplate($trythis)) )
                return $file;

        return false;
    }


    function Email($type='',$arg1='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Share This Link'));

        $type = CCUtil::Strip($type);
        $arg1 = CCUtil::Strip($arg1);

        switch( $type )
        {
            case 'user':
                if( !empty($arg1) )
                {
                    $ccp = ccl('people') . '/';
                    $record = CCDatabase::QueryRow("SELECT CONCAT('$ccp',user_name, '/profile') as artist_page_url," . 
                                 "user_name,user_real_name from cc_tbl_user WHERE user_name = '$arg1'" );
                }
                if( !empty($record) )
                {
                    $regarding = $arg1;
                    $this->_build_bread_crumb_trail($record['user_name'], '');
                }
                $ok = !empty($regarding);
                break;
            case 'upload':
                if( !empty($arg1) )
                {
                    $ccf = ccl('files') . '/';
                    $record = CCDatabase::QueryRow("SELECT CONCAT('$ccf',user_name,upload_id) as file_page_url,' . 
                                 'upload_id,upload_name,user_name,user_real_name from cc_tbl_uploads JOIN " .
                                 'cc_tbl_user ON upload_user=user_id WHERE upload_id='.$arg1);
                }
                $ok = !empty($record);
                if( $ok )
                {
                    $regarding = $record['upload_id'] . '/' . $record['upload_name'];
                    $this->_build_bread_crumb_trail($record['user_name'], $record['upload_id']);
                }
                break;
            case 'playlist':
                if( !empty($arg1) )
                {
                    $ccp = ccl('playlist','browse',$arg1);
                    require_once('cchost_lib/ccextras/cc-playlist.inc');
                    $plobj = new CCPlaylists();
                    $plobj->_build_bread_crumb_trail($arg1,'Share');

                    $record = CCDatabase::QueryRow(
                                'SELECT user_name, user_real_name, cart_name, cart_id FROM cc_tbl_cart ' .
                                '   JOIN cc_tbl_user ON cart_user = user_id ' .
                                ' WHERE cart_id='.$arg1);

                }
                if( !empty($record) )
                    $regarding = $record['cart_name'];
                $ok = !empty($regarding);
                break;

            default:
                $ok = false;
        }
        if( !$ok )
            CCUtil::Send404(true);

        require_once('cchost_lib/ccextras/cc-publicize-forms.inc');
        $form = new CCShareLinkForm($type,$record);

        if( empty($_POST['sharelink']) || !$form->ValidateFields())
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($fields);
            require_once('cchost_lib/ccextras/cc-mail.inc');
            global $CC_MAILER;

            if( empty($CC_MAILER) )
                $mailer = new CCMailer();
            else
                $mailer = $CC_MAILER;

            if( empty($fields['mail_from']) )
            {
                $users =& CCUsers::GetTable();
                $row = $users->QueryKeyRow($fields['mail_from_id']);
                $fields['mail_from'] = $row['user_email'];
            }
            $body = urldecode($fields['mail_stock_body']) . "\n\n" . $fields['mail_body'];
            $mailer->From( $fields['mail_from'] );
            $mailer->To( $fields['mail_to'] );
            $mailer->Subject( $fields['mail_subject'] );
            $mailer->Body( $body );
            @$mailer->Send();

            CCDebug::Enable(true);
            CCDebug::Log("Email share from {$fields['mail_from']} to {$fields['mail_to']} re: {$regarding}");

            $page->Prompt( _('Mail sent') );
        }
    }

    function _pub_wizard_allowd($itsme)
    {
        global $CC_GLOBALS;

        return !empty($CC_GLOBALS['pubwiz']) &&
               (
                    $CC_GLOBALS['pubwiz'] == CC_DONT_CARE_LOGGED_IN ||
                    (
                        ($CC_GLOBALS['pubwiz'] == CC_MUST_BE_LOGGED_IN) && $itsme
                    )
               );
    }


    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('publicize'),  array( 'CCPublicize', 'Publicize'), 
                CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{user_name}', _('Displays users publicize page'), CC_AG_USER  );

        CCEvents::MapUrl( ccp('share'), array( 'CCPublicize', 'Share' ),  
                CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{id}', _('Displays Share/Embed PopupWindow'), CC_AG_UPLOADS  );

        CCEvents::MapUrl( ccp('share','playlist'), array( 'CCPublicize', 'SharePlaylist' ),  
                CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{id}', _('Displays share playlist forms'), CC_AG_UPLOADS  );

        CCEvents::MapUrl( ccp('email'), array( 'CCPublicize', 'Email' ),  
                CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{type},{id}', _('Email link to a friend'), CC_AG_UPLOADS  );

    }

}


?>
