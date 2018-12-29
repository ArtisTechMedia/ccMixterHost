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
* $Id: cc-collab.inc 12626 2009-05-19 17:24:09Z fourstones $
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
require_once('cchost_lib/ccextras/cc-collab-tables.inc');
require_once('cchost_lib/ccextras/cc-topics.inc');

class CCCollabTopics extends CCTopics
{
    function CCCollabTopics()
    {
        $this->CCTopics();
        $this->LimitType('collab');
        $this->SetOrder('topic_date','ASC');
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        static $table;
        if( !isset($table) )
            $table = new CCCollabTopics();
        return $table;
    }
}


/**
* Event listener for collaboration projects
*/
class CCCollab
{
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
            $fields['collab_enabled'] =
               array(  'label'      => _('Enable Collaborations Feature'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);
        }
    }

    function UserTab($user_name)
    {
        $user_real_name = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_name=\''.$user_name.'\'');
        $title = sprintf("%s's Collaborations",$user_real_name);

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=collab_user&datasource=collab_user&user='.$user_name.'&title='.$title);
        $query->Query($args);
    }

    function OnUploadDelete($record)
    {
        if( !empty($record['upload_extra']['collab']) )
        {
            $this->_remove_upload($record['upload_extra']['collab'],$record['upload_id']);
        }
    }

    /**
    * Event handler for {@link CC_EVENT_FORM_FIELDS}
    *
    * Called when the form is building. Add and modify fields here
    *
    * @param object &$form CCForm object
    * @param object &$fields Current array of form fields
    */
    function OnFormFields(&$form,&$fields)
    {
        /************************************************************
           N.B. This can be called more than once for the same form
        ***************************************************************/

        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['collab_enabled']) )
            return;

        if( is_subclass_of($form,'CCUploadMediaForm') ||
                    is_subclass_of($form,'ccuploadmediaform') )
        {
            $collab_users = new CCCollabUsers();
            $collab_users->AddJoin( new CCCollabs(), 'collab_user_collab' );
            $w['collab_user_user'] = CCUser::CurrentUser();
            $collabs = $collab_users->QueryRows($w);
            $upload_id = $form->GetFormValue('upload_id');
            if( $upload_id )
            {
                $uploads = new CCUploads();
                $old_collab = $uploads->GetExtraField( $upload_id, 'collab' );
            }
            else
            {
                $old_collab = 0;
            }
            
            if( !empty($collabs) )
            {
                $F = array();

                $options = array( 0 => _('(none)') );
                foreach( $collabs as $collab )
                    $options[ $collab['collab_id'] ] = $collab['collab_name'];

                $F['collab'] = 
                    array( 'label' => 'str_collaboration',
                           'formatter' => 'select',
                           'form_tip'  => 'str_collab_assign_this_file',
                           'options'   => $options,
                           'value'     => $old_collab,
                           'flags'     => CCFF_NOUPDATE,
                            );

                $form->InsertFormFields( $F, 'before', 'upload_tags' );
            }
        }
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_DONE}
    *
    * Upload is complete and otherwise successful. Mark the upload with tags and 
    * extra fields here.
    *
    * @param integer $upload_id ID of upload row
    * @param string $op One of {@link CC_UF_NEW_UPLOAD}, {@link CC_UF_FILE_REPLACE}, {@link CC_UF_FILE_ADD}, {@link CC_UF_PROPERTIES_EDIT'}
    * @param array &$parents Array of remix sources
    */
    function OnUploadDone($upload_id, $op)
    {

        if( ($op == CC_UF_NEW_UPLOAD || $op == CC_UF_PROPERTIES_EDIT) &&
            array_key_exists('collab',$_POST)
          )
        {
            $uploads = new CCUploads();
            $old_collab = $uploads->GetExtraField( $upload_id, 'collab' );

            if( empty($_POST['collab']) )
            {
                if( !empty($old_collab) )
                {
                    $this->_remove_upload($old_collab,$upload_id);
                }
            }
            else
            {
                $collab_id = $_POST['collab'];
                if( $old_collab != $collab_id )
                    $this->_remove_upload($old_collab,$upload_id);

                // ugh, this should be compared to submit form types...
                $ccud = $uploads->GetExtraField( $upload_id, 'ccud' );
                preg_match('/(remix|sample|original|acappella)/',$ccud,$m);
                $this->_add_upload($collab_id,$upload_id,$m[1]);
            }
        }
    }

    function _add_upload($collab_id,$upload_id,$ccud)
    {
        $collab_uploads = new CCCollabUploads();
        $w['collab_upload_upload'] = $upload_id;
        $w['collab_upload_collab'] = $collab_id;
        $rows = $collab_uploads->QueryRows($w);
        if( empty($rows) )
        {
            $w['collab_upload_type'] = $ccud;
            $collab_uploads->Insert($w);
            $url = ccl( 'collab', $collab_id );
            $text = _('A new file has been posted to your collaboration project') . "\n\n$url\n\n" . _('Thanks');
            $this->_mail_out( $collab_id, _('New Upload'), $text); 
        }
        $uploads = new CCUploads();
        $uploads->SetExtraField( $upload_id, 'collab', $collab_id );
        $this->Sync($collab_id);
    }

    function Create()
    {
        $page =& CCPage::GetPage();
        require_once('cchost_lib/ccextras/cc-collab-forms.inc');
        $form =  new CCCollabForm();
        if( empty($_POST['collab']) || !$form->ValidateFields() )
        {
            $page->SetTitle('str_collab_create');
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $collabs = new CCCollabs();
            $form->GetFormValues($values);
            $values['collab_date'] = date('Y-m-d H:i:s',time());
            $values['collab_user'] = CCUser::CurrentUser();
            $values['collab_id'] = $collabs->NextID();
            $collabs->Insert($values);

            $collab_users = new CCCollabUsers();
            $uvalues['collab_user_collab'] = $values['collab_id'];
            $uvalues['collab_user_user'] = CCUser::CurrentUser();
            $uvalues['collab_user_role'] = 'owner';
            $uvalues['collab_user_confirmed'] = 1;
            $collab_users->Insert($uvalues);

            $url = ccl('collab',$values['collab_id']);
            CCUtil::SendBrowserTo($url);
        }
    }

    function _delete_confirmed($collab_id)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $topics =& CCCollabTopics::GetTable();
        $w['topic_upload'] = $collab_id;
        $w['topic_type'] = 'collab';
        $topics->DeleteWhere($w);
        $cuploads =& CCCollabUploads::GetTable();
        $w2['collab_upload_collab'] = $collab_id;
        $rows = $cuploads->QueryRows($w2);
        $uploads =& CCUploads::GetTable();
        foreach( $rows as $row )
            $uploads->UnsetExtraField($row['collab_upload_upload'],'collab');
        $cuploads->DeleteWhere($w2);
        $users =& CCCollabUsers::GetTable();
        $users->DeleteKey($collab_id);
        $collabs =& CCCollabs::GetTable();
        $collabs->DeleteKey($collab_id);
        $page->SetTitle('str_collab_remove');
        $page->Prompt('str_collab_removed');
    }

    function Edit($collab_id='')
    {
        if( empty($collab_id) )
            CCUtil::Send404();

        if( !empty($_POST['confirmdelete']) )
        {
            $this->_delete_confirmed($collab_id);
            return;
        }
        
        $page =& CCPage::GetPage();
        require_once('cchost_lib/ccextras/cc-collab-forms.inc');
        $form =  new CCCollabForm();
        $fields = array( 
                    'collab_delete' =>
                       array( 'label'       => _('Remove (delete) this project.'),
                               'formatter'  => 'checkbox',
                               'form_tip'   => _('This will not delete the files.'),
                               'flags'      => CCFF_NONE),
                    );
                          
        $form->AddFormFields($fields);

        $is_post = !empty($_POST['collab']);
        $collabs = new CCCollabs();
        if( !$is_post || !$form->ValidateFields() )
        {
            $collab_row = $collabs->QueryKeyRow($collab_id);
            $page->SetTitle( 'str_collab_project_s', $collab_row['collab_name'] );
            if( !$is_post )
                $form->PopulateValues($collab_row);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            if( !empty($values['collab_delete']) )
            {
                $page->SetTitle('str_collab_remove');
                $row = $this->_collab_row($collab_id);
                require_once('cchost_lib/cc-upload-forms.php');
                $name = $row['collab_name'];
                $form = new CCConfirmDeleteForm($name);
                $help = sprintf(_('This will remove (delete) the "%s" collaboration project. This will not remove or delete
                           the files in the project, just the project itself.'),$name);
                $form->SetFormHelp($help);
                $help = sprintf(_('%sThere is no UNDO%s'),'<span style="color:red">', '</span>');
                $form->SetFormHelp($help);
                $page->AddForm( $form->GenerateForm() );
            }
            else
            {
                unset($values['collab_delete']);
                $values['collab_id'] = $collab_id;
                $collabs->Update($values);
                $url = ccl('collab',$values['collab_id']);
                CCUtil::SendBrowserTo($url);
            }
        }
    }

    function _collab_row($collab_id)
    {
        if( empty($collab_id) )
            CCUtil::Send404();

        $collabs = new CCCollabs();
        $collabs->AddJoin( new CCUsers(), 'collab_user' );
        $collab_row = $collabs->QueryKeyRow($collab_id);

        if( empty($collab_row) )
            CCUtil::Send404();

        return $collab_row;
    }

    function ViewAll()
    {
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=collab_main&datasource=collab&title=str_collab&limit=14');
        $query->Query($args);
    }

    function View($collab_id='')
    {
        $collab_id = sprintf("%0d",$collab_id);
        if( empty($collab_id) )
        {
            $this->ViewAll();
            return;
        }

        $collab_row = $this->_collab_row($collab_id);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle( 'str_collab_project_s', $collab_row['collab_name'] );

        require_once('cchost_lib/ccextras/cc-format.inc');
        $collab_row['collab_desc'] = _cc_format_format($collab_row['collab_desc']);

        $collab_users = $this->_get_collab_users($collab_id);
        list( $is_owner, $is_member, $is_confirmed ) = $this->_get_user_acccess($collab_row,$collab_users);
        $lics     = $this->_get_licenses();

        $args = array(
                'is_owner'  => $is_owner,
                'is_member' => $is_member,
                'is_confirmed' => $is_confirmed,
                'collab'    => $collab_row,
                'lics'      => $lics,
                );
        $page->PageArg( 'collab', $args, 'collab_view' );
        $this->_build_bread_crumb_trail($collab_row['collab_name'] );
    }

    function _get_licenses()
    {
        require_once('cchost_lib/cc-submit.php');
        $sapi = new CCSubmit();
        return $sapi->GetSubmitLicenses();
    }
    
    /**
    * @access private
    */
    function _build_bread_crumb_trail($collab_name)
    {
        $trail[] = array( 'url' => ccl(), 
                          'text' => _('Home') );
        
        if( !empty($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'],'remix/collab') )
        {
            $trail[] = array( 'url' => ccl('view','media','remix'), 
                              'text' => _('Remixes') );

            $trail[] = array( 'url' => ccl('view','media','remix','collab'), 
                              'text' => _('Collaborations') );
        }
        else
        {
            $trail[] = array( 'url' => ccl('collab'), 
                              'text' => _('Collaborations') );
        }

        $trail[] = array( 'url' => '', 'text' => $collab_name );

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    function _get_collab_users($collab_id)
    {
        $ccp = ccl('people') . '/';
        $sql =<<<EOF
            SELECT CONCAT('$ccp',user_name) as artist_page_url, user_real_name, user_name, user_email, user_id,
                       cc_tbl_collab_users.*
            FROM cc_tbl_user
            JOIN cc_tbl_collab_users on collab_user_user=user_id
            WHERE collab_user_collab = {$collab_id}
EOF;

        return CCDatabase::QueryRows($sql);
    }

    function _get_user_acccess($collab_row,$collab_users=array())
    {
        $curr_user = CCUser::CurrentUser();
        $is_member = false;
        $is_confirmed = false;

        if( CCUser::IsAdmin() )
        {
            $is_owner = true;
            $is_member = true;
            $is_confirmed = true;
        }
        else
        {
            $is_owner = $curr_user == $collab_row['collab_user'];

            if( $is_owner )
            {
                $is_member = true;
                $is_confirmed = true;
            }
            else
            {
                if( empty($collab_users) )
                    $collab_users = $this->_get_collab_users($collab_row['collab_id']);

                foreach( $collab_users as $CU )
                {
                    if( $is_member = ($curr_user == $CU['user_id']) )
                    {
                        $is_confirmed = $CU['collab_user_confirmed'];
                        break;
                    }
                }
            }
        }

        return array( $is_owner, $is_member, $is_confirmed );
    }

    function GetUploads( $collab_id='' )
    {
        $collab_id = sprintf('%d',$collab_id);
        if( empty($collab_id) )
            CCUtil::Send404();
            
        $collab_row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_collabs WHERE collab_id='.$collab_id);
        list( $is_owner, $is_member, $is_confirmed ) = $this->_get_user_acccess($collab_row);

        $sql =<<<EOF
            SELECT collab_upload_upload FROM cc_tbl_collab_uploads 
            JOIN cc_tbl_uploads ON collab_upload_upload = upload_id
            WHERE collab_upload_collab = {$collab_id} AND upload_banned = 0
EOF;
        $ids = CCDatabase::QueryItems($sql);
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $query->_ignore_visible = $is_confirmed;
        $args = $query->ProcessAdminArgs('t=collab_files&f=html&ids=' . join(',',$ids));
        $sqlargs['where'] = 'collab_id = ' . $collab_id;
        $query->QuerySQL($args,$sqlargs);
    }

    function GetUsers( $collab_id='' )
    {
        $collab_id = sprintf('%d',$collab_id);
        if( empty($collab_id) )
            CCUtil::Send404();
            
        global $CC_GLOBALS;
        $collab_row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_collabs WHERE collab_id='.$collab_id);
        list( $CC_GLOBALS['is_collab_owner'], 
              $CC_GLOBALS['is_collab_member'], 
              $CC_GLOBALS['is_collab_confirmed'] ) = $this->_get_user_acccess($collab_row);

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=collab_users&f=html&collab='.$collab_id);
        $query->Query($args);
    }

    function Upload( $collab_id='', $upload_id='', $cmd='' )
    {
        $ok = !empty($collab_id) && !empty($upload_id) && !empty($cmd);
        if( $ok )
        {
            $collab_id = CCUtil::Strip($collab_id);
            $upload_id = CCUtil::Strip($upload_id);
            $cmd = CCUtil::Strip($cmd);
            $ok = !empty($collab_id) && !empty($upload_id) && !empty($cmd);
        }


        if( !$ok )
        {
            $args[CC_AJAX_ERROR] = _('Invalid arguments');
        }

        if( $ok )
        {
            $args['upload_id'] = $upload_id;
            switch($cmd)
            {
                case 'remove':
                    $this->_remove_upload($collab_id,$upload_id);
                    $args[CC_AJAX_WARNING] = _('File removed from project');
                    break;

                case 'publish':
                    $uploads = new CCUploads();
                    if( !$uploads->KeyExists($upload_id) )
                    {
                        $args[CC_AJAX_ERROR] = _('Can not find that file');
                    }
                    else
                    {
                        $pubrow = $uploads->QueryKeyRow($upload_id);
                        $value = $pubrow['upload_published'] ? 0 : 1;
                        $w2['upload_published'] = $value;
                        $w2['upload_id'] = $upload_id;
                        $uploads->Update($w2);
                        if( $value )
                        {
                            $args['published'] = 1;
                            $args[CC_AJAX_MESSAGE] = _('Upload is now visible to everybody');
                        }
                        else
                        {
                            $args['published'] = 0;
                            $args[CC_AJAX_WARNING] = _('Upload is now only visible to members of the project');
                        }
                        
                        CCEvents::Invoke( CC_EVENT_UPLOAD_DONE, array( $upload_id, CC_UF_PROPERTIES_EDIT, array(&$pubrow) ) );
                    }
                    break;

                default:
                    $args[CC_AJAX_ERROR] = _('Unknown command: ' . $cmd);
                    break;
            }
        }

        $this->_output($args);
    }

    function User($collab_id='',$username='',$cmd='')
    {
        global $CC_GLOBALS;

        $ok = !empty($collab_id) && !empty($username) && !empty($cmd);
        if( $ok )
        {
            $collab_id = CCUtil::Strip($collab_id);
            $username = CCUtil::Strip($username);
            $cmd = CCUtil::Strip($cmd);
            $ok = !empty($collab_id) && !empty($username) && !empty($cmd);
        }

        if( !$ok )
        {
            $args[CC_AJAX_ERROR] = _('Invalid arguments');
        }

        $args = array();

        if( $ok )
        {
            $user_id = CCUser::IDFromName($username);

            switch( $cmd )
            {
                case 'add':
                    $user_real_name = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_id='.$user_id);
                    $users = new CCCollabUsers();
                    $w1['collab_user_user'] = $user_id;
                    $w1['collab_user_collab'] = $collab_id;
                    $collab_user = $users->QueryRow($w1);
                    if( !empty($collab_user) )
                    {
                        $args[CC_AJAX_ERROR] = sprintf(_('%s is already a member'),$user_real_name);
                    }
                    else
                    {
                        $w1['collab_user_role'] = 'member';
                        $users->Insert($w1);
                        $args['user_name'] = $username;
                        $args['user_real_name'] = $user_real_name;
                        $args[CC_AJAX_MESSAGE] = sprintf(_('%s added to project'),$user_real_name);
                    }
                    break;

                case 'remove':
                    $user_real_name = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_id='.$user_id);
                    $users = new CCCollabUsers();
                    $w1['collab_user_user'] = $user_id;
                    $w1['collab_user_collab'] = $collab_id;
                    $collab_user = $users->QueryRow($w1);
                    if( empty($collab_user) )
                    {
                        $args[CC_AJAX_ERROR] = sprintf(_('%s is not member of this project'),$user_real_name);
                    }
                    else
                    {
                        $w1['collab_user_role'] = 'member';
                        $users->DeleteWhere($w1);
                        $args['user_name'] = $username;
                        $args[CC_AJAX_MESSAGE] = sprintf(_('%s removed from project'),$user_real_name);
                        $c = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_collab_users WHERE collab_user_confirmed =1 AND collab_user_collab='.$collab_id);
                        if( $c < 2 )
                        {
                            $args[CC_AJAX_MESSAGE] .= ' (NO CONFIRMED MEMBERS: This project is no longer listed)';
                        }
                        $confirmed = ($c < 2) ? 0 : 1;
                        $sql = "update cc_tbl_collabs set collab_confirmed = $confirmed WHERE collab_id = $collab_id";
                        CCDatabase::Query($sql);
                    }
                    break;

                case 'credit':
                    if( ($ok = !empty($_GET['credit'])) == true )
                    {
                        $credit = CCUtil::Strip($_GET['credit']);
                        $ok = !empty($credit);
                    }

                    if( !$ok )
                    {
                        $args[CC_AJAX_ERROR] = _('Missing argument');
                    }
                    else
                    {
                        $users = new CCCollabUsers();
                        $w1['collab_user_user'] = $user_id;
                        $w1['collab_user_collab'] = $collab_id;
                        $w2['collab_user_credit'] = $credit;
                        $users->UpdateWhere($w2,$w1);
                        $args['user_name'] = $username;
                        $args['credit'] = $credit;
                        $args[CC_AJAX_MESSAGE] = _('User credit updated');
                    }
                    break;

                case 'contact':
                    if( ($ok = !empty($_POST['text'])) == true )
                    {
                        $text = CCUtil::Strip($_POST['text']);
                        $ok = !empty($text);
                    }
                    if( !$ok )
                    {
                        $args[CC_AJAX_ERROR] = _('Missing argument');
                    }
                    else
                    {
                        $user_email = CCDatabase::QueryItem('SELECT user_email FROM cc_tbl_user WHERE user_id='.$user_id);
                        $url = ccl('collab',$collab_id);
                        $collab_name = CCDatabase::QueryItem('SELECT collab_name FROM cc_tbl_collabs WHERE collab_id='.$collab_id);
                        $text .= "\n\nProject: \"$collab_name\"\n$url\n";
                        $this->_mail_out( $collab_id, _('Private message'), $text, $user_email );
                        $args[CC_AJAX_MESSAGE] = _('Message sent');
                    }
                    break;

                case 'confirm':
                    if( CCUser::CurrentUserName() == $username )
                    {
                        CCDatabase::Query("UPDATE cc_tbl_collab_users SET collab_user_confirmed = 1 WHERE " .
                                          "collab_user_collab = $collab_id AND collab_user_user = $user_id");
                        CCDatabase::Query("UPDATE cc_tbl_collabs SET collab_confirmed = 1 WHERE collab_id = $collab_id");
                        $args['user_name'] = $username;
                        $args[CC_AJAX_MESSAGE]= _('You have been confirmed for this project');
                    }
                    else
                    {
                        $args[CC_AJAX_ERROR] = _('You do not have permssion to confirm other users');
                    }
                    break;

                default:
                    $args[CC_AJAX_ERROR] = _('Invalid command');
                    $ok = false;
                    break;
            }
        }
        else
        {
            $args[CC_AJAX_ERROR] = _('Invalid arguments');
        }

        $this->_output($args);
    }

    function _mail_out( $collab_id, $sub_head, $body, $emails='')
    {
        global $CC_GLOBALS;

        $from = $CC_GLOBALS['user_email'];
        if( empty($emails) )
        {
            $collab_users = $this->_get_collab_users($collab_id);
            $emails = array();
            foreach( $collab_users as $CU )
                $emails[] = $CU['user_email'];
            $emails = array_diff( array_unique($emails), array( $from) );
            if( empty($emails) )
                return;
        }
        else
        {
            if( !is_array($emails) )
                $emails = array($emails);
        }

        $collabs = new CCCollabs();
        $collab_row = $collabs->QueryKeyRow($collab_id);
        require_once('cchost_lib/ccextras/cc-mail.inc');
        $mailer = new CCMailer();
        $mailer->From( $from );
        $mailer->Subject( _('Project') . ' "' . $collab_row['collab_name'] .'" ' . $sub_head );
        $mailer->Body( $body );
        foreach( $emails as $email )
        {
            $mailer->To( $email );
            $ok = @$mailer->Send();
            if( !$ok )
                CCDebug::Log("Trying to send notify mail to '$email': '$ok'");
        }
    }

    function _output($args) 
    {
        CCUtil::ReturnAjaxData($args);
    }

    function PostTopic($collab_id)
    {
        $page =& CCPage::GetPage();
        global $CC_GLOBALS, $CC_CFG_ROOT;

        require_once('cchost_lib/ccextras/cc-collab-forms.inc');

        $form = new CCCollabTopicForm();

        if( empty($_POST['collabtopic']) || !$form->ValidateFields() )
        {
            $page->SetTitle('str_collab_message');
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $topics =& CCTopics::GetTable();
            $values['topic_id'] = $topics->NextID();
            $values['topic_upload'] = $collab_id;
            $values['topic_date'] = date('Y-m-d H:i:s',time());
            $values['topic_user'] = CCUser::CurrentUser();
            $values['topic_type'] = 'collab';
            $values['topic_name'] = '';
            $topics->InsertNewTopic($values,0);

            $url = ccl( 'collab', $collab_id );
            $text = _('A new topic has been posted to your collaboration project') . "\n\n$url\n\n" . _('Thanks');
            $this->_mail_out( $collab_id, _('Topic Post'), $text); 

            //CCEvents::Invoke( CC_EVENT_REVIEW, array( &$row ) );

            $url = ccl('collab',$collab_id);
            CCUtil::SendBrowserTo($url);
        }
    }

    function UpdateTags($collab_id,$upload_id)
    {
        $bad = empty($upload_id) && empty($_GET['tags']); 

        if( $bad )
            $args[CC_AJAX_ERROR] = _('Missing arguments');

        if( !$bad )
        {
            $tags = CCUtil::Strip($_GET['tags']);
            $bad = empty($tags);
            if( $bad )
                $args[CC_AJAX_ERROR] = _('Missing tags');
        }

        if( !$bad )
        {
            $uploads = new CCUploads();
            $bad = !$uploads->KeyExists($upload_id);
            if( $bad )
                $args[CC_AJAX_ERROR] = _('Upload record not found');
        }

        if( !$bad )
        {
            require_once('cchost_lib/cc-uploadapi.php');
            CCUploadAPI::UpdateUserTags($tags);
            $args[CC_AJAX_MESSAGE] = _('Tags updated');
            $args['user_tags'] = $new_user_tags;
            $args['upload_id'] = $upload_id;
        }

        $this->_output($args);
    }

    function UploadFile($collab_id)
    {
        global $CC_GLOBALS;

        $collabs = new CCCollabs();
        $collabs->AddJoin( new CCUsers(), 'collab_user' );
        $collab_row = $collabs->QueryKeyRow($collab_id);

        CCUtil::Strip($_POST);

        $ret = 0;
        $msg = '';
        $bad = empty($collab_row)  || empty($_FILES) || empty($_FILES['upfile']) || ($_FILES['upfile']['error'] != 0); 

        if( !$bad )
        {

            $fname = empty($_POST['upname']) ? $_FILES['upfile']['name'] : $_POST['upname'];
            $values['upload_name']        = $fname;
            $values['upload_published']   = 0;
            $values['upload_description'] = sprintf( _('This is part of the %s collaboration project.'),
                                                 '[url=' . ccl('collab',$collab_id) . ']' 
                                                  . $collab_row['collab_name'] . '[/url]' );

            // MUST BE RELATIVE:
            $new_path = $CC_GLOBALS['user-upload-root'] . '/' . $CC_GLOBALS['user_name'];

            $files = new CCCollabUploads();
            $w['collab_upload_collab'] = $collab_id;
            $collab_ids = $files->QueryItems('collab_upload_upload',$w);

            $values['upload_license'] = $_POST['lic'];
            $values['upload_user'] = CCUser::CurrentUser();
            $ccud = $_POST['uptype'];
            require_once('cchost_lib/cc-uploadapi.php');
            $ret = CCUploadAPI::PostProcessNewUpload(   $values, 
                                                        $_FILES['upfile']['tmp_name'],
                                                        $values['upload_name'],
                                                        array( $ccud, 'media'),
                                                        '', // $user_tags,
                                                        $new_path,
                                                        array() );

            if( intval($ret) > 0 )
            {
                $this->_add_upload($collab_id,$ret,$ccud);
            }
            else
            {
                $msg = $ret;
            }
        }

        $html =<<<EOF
        <html>
        <body>
        <script type="text/javascript">
            if( window.parent.upload_done )
                window.parent.upload_done('$ret','$msg');
            else
                alert('can not see it');
        </script>
        </body>
        </html>
EOF;
        print($html);
        exit;
    }

    function Sync($collab_id)
    {
        $files = new CCCollabUploads();
        $w['collab_upload_collab'] = $collab_id;
        $collab_ids = $files->QueryItems('collab_upload_upload',$w);

        // attach all collab samples and pells to remixes...

        if( count($collab_ids) < 2 )
            return; // there's only one (or none) uploads, nothing to attach

        require_once('cchost_lib/cc-dataview.php');

        $dv = new CCDataView();
        $keys = join(',',$collab_ids);

        // Do NOT filter out hidden uploads
        $filter = $dv->MakeTagFilter('-remix','all');
        $sql = "SELECT upload_id FROM cc_tbl_uploads WHERE (upload_id IN ({$keys})) AND {$filter} AND (upload_banned = 0)";
        $source_ids = CCDatabase::QueryItems($sql);

        $filter = $dv->MakeTagFilter('remix','any');
        $sql = "SELECT upload_id FROM cc_tbl_uploads WHERE (upload_id IN ({$keys})) AND {$filter} AND (upload_banned = 0)";
        $remix_ids = CCDatabase::QueryItems($sql);
        
        if( count($source_ids) == 0 || count($remix_ids) == 0 )
            return; // there are only sources (or remixes), nothing to attach

        // check for existing remix sources, we want to preserve
        // non-collab sources and add any new ones we found here...

        require_once('cchost_lib/cc-sync.php');
        $remix_tree = new CCTable('cc_tbl_tree','tree_parent');
        foreach( $remix_ids as $remix_id )
        {
            $tree_source_ids = CCDatabase::QueryItems('SELECT tree_parent FROM cc_tbl_tree WHERE tree_child='.$remix_id);
            $new_sources = array_diff( $source_ids, $tree_source_ids);

            if( $new_sources )
            {
                $source_rows = CCDatabase::QueryRows(
                    'SELECT upload_user, upload_id FROM cc_tbl_uploads WHERE upload_id IN (' . join(',',$new_sources) . ')' );
                $remixer = CCDatabase::QueryItem('SELECT upload_user FROM cc_tbl_uploads WHERE upload_id = ' . $remix_id);
                $parents = array();
                foreach( $source_rows as $source )
                {
                    if( $remixer == $source['upload_user'] )
                        continue; // still can't remix yourself

                    $parents[] = $source;
                    $insert = array( 'tree_child' => $remix_id,
                                     'tree_parent' => $source['upload_id'] );
                    $remix_tree->Insert($insert);
                }
                if( !empty($parents) )
                {
                    CCSync::Remix($remix_id,$parents);
                }
            }
        }

        // have to wait until the tree is complete (I think)
        foreach( $remix_ids as $remix_id )
            $this->_update_license($remix_id);
    }

    function _remove_upload($collab_id,$upload_id)
    {
        $collab_uploads = new CCCollabUploads();
        $w1['collab_upload_upload'] = $upload_id;
        $w1['collab_upload_collab'] = $collab_id;
        $collab_uploads->DeleteWhere($w1);
        $uploads = new CCUploads();
        $uploads->UnsetExtraField($upload_id,'collab');
        $this->Sync($collab_id);
    }

    function _update_license($remix_id)
    {
        require_once('cchost_lib/cc-remix.php');
        $lic_row = CCRemix::GetStrictestLicenseForUpload($remix_id);
        if( empty($lic_row) )
            return;
        $ups = new CCUploads();
        $upsargs['upload_id'] = $remix_id;
        $upsargs['upload_license'] = $lic_row['license_id'];
        $ups->Update($upsargs);
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('collab'),        array( 'CCCollab', 'View'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{collab_id}',
            _('Show a collaboration'), CC_AG_COLLAB );

        CCEvents::MapUrl( ccp('collab','create'), array( 'CCCollab', 'Create'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '',
            _('Create a new collab project'), CC_AG_COLLAB );

        CCEvents::MapUrl( ccp('collab','edit'), array( 'CCCollab', 'Edit'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{collab_id}',
            _('Edit a collab project'), CC_AG_COLLAB  );

        CCEvents::MapUrl( ccp('collab','sync'), array( 'CCCollab', 'Sync'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{collab_id}',
            _('Ajax call to synchronize remixes of a collab proj'), CC_AG_COLLAB  );

        CCEvents::MapUrl( ccp('collab','topic','add'), array( 'CCCollab', 'PostTopic'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{collab_id}',
            _('Ajax call to post a topic to a collab project'), CC_AG_COLLAB  );

        // useradd, removeuser, editcredit, contact
        CCEvents::MapUrl( ccp('collab','user'), array( 'CCCollab', 'User'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{collab_id},{username},{add|remove|credit|contact|confirm}',
             _('Ajax call to maintain user of collab project'), CC_AG_COLLAB  );

        // remove, publish
        CCEvents::MapUrl( ccp('collab','upload'), array( 'CCCollab', 'Upload'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{collab_id},{upload_id},{remove|publish}',
            _('Ajax call for maintainance on uploads of a collab proj.'), CC_AG_COLLAB  );

        CCEvents::MapUrl( ccp('collab','upload','update'), array( 'CCCollab', 'GetUploads'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{collab_id}',
            _('Ajax call to return uploads of a collab proj.'), CC_AG_COLLAB  );

        CCEvents::MapUrl( ccp('collab','users'), array( 'CCCollab', 'GetUsers'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{collab_id}',
            _('Ajax call to return users of a collab proj.'), CC_AG_COLLAB  );

        CCEvents::MapUrl( ccp('collab','upload','file'), array( 'CCCollab', 'UploadFile'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{collab_id}',
            _('Ajax call to post a file to a collab project.'), CC_AG_COLLAB  );

        CCEvents::MapUrl( ccp('collab','upload','tags'), array( 'CCCollab', 'UpdateTags'),  
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{collab_id},{upload_id}?tags=',
            _('Ajax call to set tags on a collab proj.'), CC_AG_COLLAB  );
    }
}


?>
