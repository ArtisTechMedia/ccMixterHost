<?
/*
  $Id: mixup_admin.inc 13835 2009-12-25 13:19:34Z fourstones $
*/

function mixup_admin_helper($cmd,$arg)
{
    require_once('cchost_lib/cc-page.php');
    $page =& CCPage::GetPage();
    
    switch( $cmd )
    {
        case 'create':
            {
                mixup_helper_setup_page(_('Create a New Mixup'));
                require_once('cchost_lib/cc-form.php');
                $form = new CCForm();
                $fields = mixup_helper_get_mixup_fields(0);
                $form->AddFormFields($fields);
                if( empty($_POST) || !$form->ValidateFields() ) {
                    $page->AddForm( $form->GenerateForm() );
                }
                else {
                    $form->GetFormValues($values);
                    $table = new CCTable('cc_tbl_mixups','mixup_id');
                    $values['mixup_id'] = $table->NextID();
                    $modes = mixup_helper_get_modes($values['mixup_id']);
                    $mode = 0;
                    foreach($modes as $M) {
                        if( $M['mixup_mode_type'] == CC_MIXUP_MODE_DISABLED ) {
                            $mode = $M['mixup_mode_id'];
                            break;
                        }
                    }
                    $values['mixup_mode'] = $mode;
                    $values['mixup_date'] = date( 'Y-m-d H:i:s');
                    $table->Insert($values);
                    $url = ccl('mixup', $values['mixup_name'] );
                    CCUtil::SendBrowserTo($url);
                }
            }
            break;
            
        case 'edit':
            {
                $mixup_id = $arg;
                mixup_helper_setup_page(_('Actions'), $mixup_id );
                $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_mixups WHERE mixup_id=' . $mixup_id);
                $html = '<div class="box" style="width:60%">' . mixup_helper_get_edit_form_help($row) . '</div>';
                $page =& CCPage::GetPage();
                $page->AddContent($html);
            }
            break;
        
        case 'properties':
            {
                $mixup_id = $arg;
                mixup_helper_setup_page(_('Mixup Properties'), $mixup_id );
                require_once('cchost_lib/cc-form.php');
                $form = new CCForm();
                $fields = mixup_helper_get_mixup_fields($mixup_id);
                $form->AddFormFields($fields);
                if( empty($_POST) )
                {
                    $populate = true;
                    $show = true;
                }
                else if( $form->ValidateFields() )
                {
                    $populate = false;
                    $show = false;
                    $form->GetFormValues($values);
                    $table = new CCTable('cc_tbl_mixups','mixup_id');
                    $values['mixup_playlist'] = (int)$values['mixup_playlist'];
                    $values['mixup_thread'] = (int)$values['mixup_thread'];
                    $values['mixup_id'] = $mixup_id;
                    $table->Update($values);
                    $url = ccl('mixup', $values['mixup_name'] );
                    CCUtil::SendBrowserTo($url);
                }
                else {
                    $populate = false;
                    $show = true;
                }
                
                if( $populate ) {
                    $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_mixups WHERE mixup_id=' . $mixup_id);
                    $form->PopulateValues($row);
                }
                
                if( $show ) {
                    $page->AddForm( $form->GenerateForm() );
                }
            }
            break;
            
        case 'editmodes':
            {
                $mixup_id = $arg;
                if( empty($mixup_id) ) {
                    mixup_helper_setup_page(_('Edit Default Mixup Modes'));
                }
                else {
                    mixup_helper_setup_page(_('Edit Mixup Modes'),$mixup_id);
                }
                $rows = mixup_helper_get_modes($mixup_id);
                $args = array();
                foreach( $rows as $mode_row )
                {
                    $args[] = array(
                             'actions' =>
                                array( 
                                    array(
                                        'action'    => ccl( 'admin', 'mixup', 'editmode', $mode_row['mixup_mode_id'] ),
                                        'menu_text' =>  _('Edit')
                                       ),
                                     ),
                               'help' => '"' . $mode_row['mixup_mode_name'] . '"',
                             );
                }
                // $args[] = array( 'action' => ccl('admin','mixup','newmode'), 'menu_text' => _('Create new mode'));
                $page->PageArg('use_buttons', 1 );
                $page->PageArg('client_menu',$args,'print_client_menu');                
            }
            break;
            
        case 'editmode':
            {
                $mode_id = $arg;
                $row = CCDatabase::QueryRow('SELECT mixup_mode_name, mixup_mode_mixup FROM cc_tbl_mixup_mode WHERE mixup_mode_id='.$mode_id);
                if( empty($row['mixup_mode_mixup']) ) {
                    mixup_helper_setup_page(_('Edit Default Mixup Mode: ') . $row['mixup_mode_name']);
                }
                else {
                    mixup_helper_setup_page(_('Edit Mixup Mode: ' . $row['mixup_mode_name']),$row['mixup_mode_mixup']);
                }
                require_once('cchost_lib/cc-form.php');
                $form = new CCForm();
                $fields = array(
                        'mixup_mode_name' =>
                            array( 'label'      => 'Name',
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_REQUIRED|CCFF_POPULATE,
                                 ),
                        'mixup_mode_date' =>
                            array( 'label' => 'Date',
                                'formatter' => 'date',
                                'form_tip' => _('A date that is significant to this mode.'),
                                'flags' => CCFF_POPULATE,
                                ),
                        'mixup_mode_desc' =>
                            array( 'label' => 'Description',
                                'formatter' => 'textarea',
                                'want_formatting' => 1,
                                'flags' => CCFF_POPULATE,
                                ),
                        'mixup_mode_mail' =>
                            array( 'label' => 'Mail Template',
                                'formatter' => 'textarea',
                                'form_tip' => _('Mail that might be relevant to this mode. (Leave blank for n/a)'),
                                'flags' => CCFF_POPULATE,
                                ),
                        );
                $form->AddFormFields($fields);
                $populate = false;
                $show = false;
                if( empty($_POST) ) {
                    $populate = true;
                    $show = true;
                }
                else
                {
                    if( $form->ValidateFields() ) {
                        $form->GetFormValues($values);
                        $values['mixup_mode_id'] = $mode_id;
                        $table = new CCTable('cc_tbl_mixup_mode','mixup_mode_id');
                        $table->Update($values);
                        $url = ccl('admin','mixup','editmodes',$row['mixup_mode_mixup']);
                        CCUtil::SendBrowserTo($url);
                    } else {
                        $populate = false;
                        $show = true;
                    }
                }
                
                if( $populate ) {
                    $row = CCDatabase::QueryRow("SELECT * FROM cc_tbl_mixup_mode WHERE mixup_mode_id = {$mode_id}");
                    $form->PopulateValues($row);
                }
                
                if( $show ) {
                    $page->AddForm( $form->GenerateForm() );                    
                }
                
                
            }
            break;
            
        case 'newmode':
            {
                
            }
            break;
        
        case 'assign':
            {
                mixup_helper_assignments($arg);
            }
            break;
        
        case 'status':
            {
                $mixup_id = $arg;
                mixup_helper_setup_page(_('Status'), $mixup_id );
                require_once('cchost_lib/cc-query.php');
                $query = new CCQuery();
                $args = $query->ProcessAdminArgs('t=mixup_user_status&sort=status&mixup='.$mixup_id);
                $query->Query($args);
            }
            break;
            
        default:
            {
                mixup_helper_setup_page('');
                $args = array( 
                    array(
                        'action'    => ccl( 'admin', 'mixup', 'create' ),
                        'menu_text' =>  _('Create a New Mixup')
                       ),
                    array(
                        'action'    => ccl( 'admin', 'mixup', 'editmodes' ),
                        'menu_text' =>  _('Edit Default Modes')
                       ),
                    );
                $page->PageArg('client_menu',$args,'print_client_menu');                
            }   
    }
}

function mixup_helper_get_mixup_fields($mixup_id)
{
    $urlm = ccl('mixup') . '/';
    $fields = array(
            'mixup_name' =>
                array( 'label'      => 'URL',
                    'form_tip' => 'Last part of mixup URL',
                    'prefix' => $urlm,
                    'class' => 'form_input_short',
                   'formatter'  => 'textedit',
                   'flags'      => CCFF_REQUIRED|CCFF_POPULATE,
                     ),
            'mixup_display' =>
                array( 'label' => 'Display name',
                    'formatter' => 'textedit',
                    'flags' => CCFF_POPULATE,
                    ),
            'mixup_tag' =>
                array( 'label' => 'Upload Tag',
                        'formatter' => 'textedit',
                        'flags' => CCFF_POPULATE,
                        'form_tip' => 'Tells the system to watch for this tag on uploads'),
                
            'mixup_desc' =>
                array( 'label' => 'Description',
                    'formatter' => 'textarea',
                    'want_formatting' => 1,
                    'flags' => CCFF_POPULATE,
                    ),
            'mixup_admin' =>
                array( 'label' => 'Contact Person',
                    'formatter' => 'username',
                    'class' => 'form_input_short',
                    'value' => CCUser::CurrentUser(),
                    'flags' => CCFF_REQUIRED | CCFF_POPULATE | CCFF_POPULATE_WITH_DEFAULT,
                    ),
            'mixup_hidden' =>
                array( 'label' => 'Hide',
                    'formatter' => 'checkbox',
                    'flags' => CCFF_POPULATE,
                    ),
            );

    if( !empty($mixup_id) )
    {
        $mod_opts = mixup_helper_get_mixup_options($mixup_id);
        $fields['mixup_mode'] = 
                array( 'label' => 'Current Mode',
                        'formatter' => 'select',
                        'flags' => CCFF_POPULATE,
                        'options' => $mod_opts);
                

        $purl = ccl('admin','mixup','edit',$mixup_id);

        $fields['mixup_playlist'] = 
                array( 'label' => 'Playlist',
                        'formatter' => 'textedit',
                        'class' => 'form_input_short',
                        'form_tip' => 'This can be generated automatically <a href="'.$purl.'">here</a>',
                        'flags' => CCFF_POPULATE );
                
        
        $fields['mixup_thread'] = 
                array( 'label' => 'Forum Thread',
                        'formatter' => 'textedit',
                        'class' => 'form_input_short',
                        'form_tip' => '',
                        'flags' => CCFF_POPULATE );
    }
    return $fields;
}

function mixup_helper_get_edit_form_help($mixup)
{
    $mixup_id = $mixup['mixup_id'];
    $mode_row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_mixup_mode WHERE mixup_mode_id = '.$mixup['mixup_mode']);
    $type = $mode_row['mixup_mode_type'];

    $prop_url    = ccl('admin','mixup', 'properties', $mixup_id ); 
    $mixup_url   = ccl('admin','mixup', 'assign',$mixup_id);
    $mail_url    = ccl('admin','mixup', 'massmail',$mixup_id);
    $mode_url    = ccl('admin','mixup', 'editmodes',$mixup_id);
    $makefrm_url = ccl('admin','submit'); // ccl('admin','mixup', 'makeform', $mixup_id);
    $status_url  = ccl('admin','mixup','status', $mixup_id );
    $playlist_url= ccl('api','mixup','playlist', $mixup_id);
    
    $txt =<<<EOF
    <p>Here's some stuff you can do with this mixup:</p>
    <ul>
        <li><a href="{$prop_url}">Edit Properties</a></li>
        <li><a href="{$mixup_url}">Generate/Edit Mixup Assignments</a></li>
        <li><a href="{$mail_url}">Send mail to everybody</a></li>
        <li><a href="{$status_url}">User confirmation report</a></li>
        <li><a href="{$playlist_url}">Create/Browse Dynamic Playlist</a></li>
        <li><a href="{$makefrm_url}">Edit submit forms</a></li>
        <li><a href="{$mode_url}">Edit mixup modes</a> </li>
    </ul>
EOF;

    return $txt;
}


function mixup_helper_get_default_modes($mixup_id)
{    
    if( empty($mixup_id) )
        $mixup_id = '0'; // make sure it's text
        
    $sql = 'SELECT * FROM cc_tbl_mixup_mode WHERE mixup_mode_mixup = ' . $mixup_id;
    $rows = CCDatabase::QueryRows($sql);
    if( !empty($rows) )
        return $rows;
    
    $baseurl = ccl('people') . '/';
    
    $fields = array(
         array( CC_MIXUP_MODE_DISABLED,             // mode_type
                $mixup_id,                          // mixup_id
                'Not active yet',                   // name
                'This mixup is not active yet.',    // desc
                '',                                 // e-mail
                'now'                              // date offset
                ),                            
         
         array( CC_MIXUP_MODE_SIGNUP,
                $mixup_id,                          // mixup_id
                'Signing Up',
                'This mixup is currently signing up participants. '.
                    'E-mail notifications will go out on %mixup_mode_date%',
                "DO NOT REPLY TO THIS EMAIL - IT WAS GENERATED AUTOMATICALLY\n\n" .
                    "Hello %mixer_full_name%,\n\n" .
                    "Thanks for signing up for %mixup_display%!\n\n" .
                    "The next step: On %mixup_mode_date% you will recieve an e-mail with " .
                    "the name of person you will remix.\n\n" .
                    "Don't do anything until you get that mail!\n\n" .
                    "If you have questions or problems: %mixup_admin_contact%\n\n" .
                    "More information: %mixup_url%\n\n" .
                    "Thanks,\n" .
                    "%mixup_name% Admin",
                'now + 2 weeks'
                ),
         
         array( CC_MIXUP_MODE_MIXING,
                $mixup_id,                          // mixup_id
                'Mixing',
                'The sign up period for this mixup is closed. ' .
                    'Participants are currently mixing it up. They will upload their remixes on: %mixup_mode_date%',
                "DO NOT REPLY TO THIS EMAIL - IT WAS GENERATED AUTOMATICALLY\n\n" .
                    "Hello %mixer_full_name%,\n\n" .
                    "Thanks for signing up for %mixup_display%!\n\n" .
                    "Your mixup assignment is: %mixee_full_name%\n\n" .
                    "Their home page: %mixee_page_url%\n\n" .
                    "(Remember this is a secret - don't tell anybody who were assigned!)\n\n" .
                    "The next step: Download their sources and get to remixing. " .
                    "Then, upload your remix on %mixup_mode_date% (NOT BEFORE!)\n\n" .
                    "If you have questions or problems: %mixup_admin_contact%\n\n" .
                    "More information: %mixup_url%\n\n" .
                    "Thanks,\n" .
                    "%mixup_name% Admin",
                'now + 4 weeks'
                ),
         
         array( CC_MIXUP_MODE_REMINDER,
                $mixup_id,                          // mixup_id
                'Reminder',
                'The sign up period for this mixup is closed. ' .
                    'Participants are currently mixing it up. They will upload their remixes on: %mixup_mode_date%',
                "DO NOT REPLY TO THIS EMAIL - IT WAS GENERATED AUTOMATICALLY\n\n" .
                    "Hello %mixer_full_name%,\n\n" .
                    "The time is getting close to remix %mixee_full_name% \n\n" .
                    "Here's that URL again: %mixee_page_url%\n\n" .
                    "The next step: Upload your remix on %mixup_mode_date% (NOT BEFORE!)\n\n" .
                    "If you have questions or problems: %mixup_admin_contact%\n\n" .
                    "More information: %mixup_url%\n\n" .
                    "Thanks,\n" .
                    "%mixup_name% Admin",
                'now + 4 weeks'
                ),
         
         array( CC_MIXUP_MODE_UPLOADING,
                $mixup_id,                          // mixup_id
                'Uploading',
                'The sign up period for this mixup is closed. ' .
                    'The mixup particpants should be uploading right now(!)',
                "DO NOT REPLY TO THIS EMAIL - IT WAS GENERATED AUTOMATICALLY\n\n" .
                    "Hello %mixer_full_name%,\n\n" .
                    "The remix period is over! (You're done with your remix of %mixee_full_name%, right?)\n\n" .
                    "The next step: Upload your remix as soon as you can!\n\n" .
                    "If you have questions or problems: %mixup_admin_contact%\n\n" .
                    "More information: %mixup_url%\n\n" .
                    "Thanks,\n" .
                    "%mixup_name% Admin",
                'now + 4 weeks'
                ),
         
         array( CC_MIXUP_MODE_DONE,
                $mixup_id,                          // mixup_id
               'Done',
                'This mixup is ancient history',
                '',
                'now'
                ),                            
         );
      
    $table = new CCTable('cc_tbl_mixup_mode', 'mixup_mode_id');
    $cols = array( 'mixup_mode_type', 'mixup_mode_mixup', 'mixup_mode_name', 'mixup_mode_desc',
                  'mixup_mode_mail', 'mixup_mode_date_offset' );
    $table->InsertBatch($cols, $fields);
    $rows = CCDatabase::QueryRows($sql);
    return $rows;
}


function mixup_helper_get_modes($mixup_id='')
{
    if( empty($mixup_id) )
      $mixup_id = "0";
      
    $sql = 'SELECT * FROM cc_tbl_mixup_mode WHERE mixup_mode_mixup = ' . $mixup_id;
    $rows = CCDatabase::QueryRows($sql);
    if( empty($rows) ) {
        $recs = mixup_helper_get_default_modes(0);
        if( empty($mixup_id) )
            return $recs;
        $keys = array_keys($recs);
        $c = count($keys);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $recs[ $keys[$i]];
            $R['mixup_mode_mixup'] = $mixup_id;
            $R['mixup_mode_date'] = date( 'Y-m-d H:i:s', strtotime( $R['mixup_mode_date_offset'] ));
            unset($R['mixup_mode_id']);
        }        
        $cols = array_keys( $recs[0] );
        $table = new CCTable('cc_tbl_mixup_mode', 'mixup_mode_id');
        $table->InsertBatch($cols, $recs );
        $rows = CCDatabase::QueryRows($sql);
    }
    
    return $rows;
}

function mixup_helper_get_mixup_options($mixup_id)
{
    $modes = mixup_helper_get_modes($mixup_id);
    $mod_opts = array();
    foreach( $modes as $M ) {
        $mod_opts[ $M['mixup_mode_id'] ] = $M['mixup_mode_name'];
    }
    return $mod_opts;
}


?>