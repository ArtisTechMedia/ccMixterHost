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
* $Id: cc-upload.php 9924 2008-05-24 22:31:03Z fourstones $
*
*/

/**
* @package cchost
* @subpackage upload
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


// -----------------------------
//  Upload UI
// -----------------------------
class CCUpload
{

    public static function ShowAfterSubmit($upload_id)
    {
        CCUpload::_build_bread_crumb_trail($upload_id,true,'str_submit_after');
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=after_submit&ids='.$upload_id);
        $query->Query($args);
    }

    static function _build_bread_crumb_trail($upload_id,$do_edit,$cmd)
    {
        $trail[] = array( 'url' => ccl(), 
                          'text' => 'str_home');
        
        $trail[] = array( 'url' => ccl('people'), 
                          'text' => 'str_people' );

        $sql = 'SELECT user_real_name, upload_name, user_name FROM cc_tbl_uploads ' .
                'JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$upload_id;

        list( $user_real_name, $upload_name, $user_name ) = CCDatabase::QueryRow($sql, false);

        $trail[] = array( 'url' => ccl('people',$user_name), 
                          'text' => $user_real_name );

        if( $cmd != 'str_file_deleted' )
            $trail[] = array( 'url' => ccl('files',$user_name, $upload_id), 
                              'text' => '"' . $upload_name . '"' );

        if( $do_edit )
        {
            $trail[] = array( 'url' => ccl('files','edit', $user_name, $upload_id), 
                              'text' => 'str_file_edit' );
        }

        $trail[] = array( 'url' => '', 'text' => $cmd );

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail,true);
    }

    public static function AdminUpload($upload_id)
    {
        $uploads =& CCUploads::GetTable();
        $record = CCDatabase::QueryRow('SELECT upload_extra,upload_date,upload_name,upload_license FROM cc_tbl_uploads WHERE upload_id='.$upload_id);
        $record['upload_extra'] = unserialize($record['upload_extra']);
        if( empty($record) )
            return;
        $name = $record['upload_name'];
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle(sprintf(_("Administrator Functions for '%s'"), $name));
        require_once('cchost_lib/cc-upload-forms.php');
        $form = new CCAdminUploadForm($record);
        if( empty($_POST['adminupload']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);

            $uarg['upload_id'] = $upload_id;
            if( !empty($values['upload_date']) )
                $uarg['upload_date'] = $values['upload_date'];
            $uarg['upload_license'] = $values['upload_license'];
            $uploads->Update($uarg);

            require_once('cchost_lib/cc-uploadapi.php');

            CCUploadAPI::UpdateCCUD($upload_id,$values['ccud'],$record['upload_extra']['ccud']);
            $user_name = CCDatabase::QueryItem('SELECT user_name FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$upload_id);
            $url = ccl('files',$user_name,$upload_id);
            $link1 = "<a href=\"$url\">";
            $page->Prompt(sprintf(_("Changes saved to '%s'. Click %shere%s to see results"), 
                        $name, $link1, '</a>'));
        }
    }


    public static function Delete($upload_id)
    {
        CCUpload::CheckFileAccess(CCUser::CurrentUser(),$upload_id);
        $uploads =& CCUploads::GetTable();
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_file_deleting');
        if( empty($_POST['confirmdelete']) )
        {
            CCUpload::_build_bread_crumb_trail($upload_id,false,'str_file_deleting');
            $pretty_name = $uploads->QueryItemFromKey('upload_name',$upload_id);
            require_once('cchost_lib/cc-upload-forms.php');
            $form = new CCConfirmDeleteForm($pretty_name);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            CCUpload::_build_bread_crumb_trail($upload_id,false,'str_file_deleted');
            require_once('cchost_lib/cc-uploadapi.php');
            CCUploadAPI::DeleteUpload($upload_id);
            $page->Prompt('str_file_deleted');
        }
    }

    public static function AddMacroRef(&$row,$macro_group, $macro)
    {
        if( empty($row[$macro_group]) || !in_array( $macro, $row[$macro_group] ) )
        {
            $row[$macro_group][] = $macro;
        }
    }

    public static function CheckFileAccess($usernameorid,$upload_id)
    {
        if( CCUser::IsAdmin() )
            return(true);
        if( !CCUser::IsLoggedIn() )
            CCUtil::AccessError();
        CCUser::CheckCredentials($usernameorid);
        $uploads =& CCUploads::GetTable();
        if( preg_match('/[a-zA-Z]/',$usernameorid) )
            $usernameorid = CCUser::IDFromName($usernameorid);
        $fileowner = $uploads->QueryItemFromKey('upload_user',$upload_id);
        // $s = "arg: $usernameorid / owner: $fileowner";
        if(  $fileowner != $usernameorid )
            CCUtil::AccessError();
    }

    public static function GetUploadField(&$fields,$field_name = 'upload_file_name')
    {
        require_once('cchost_lib/cc-uploadapi.php');
        $verifier =& CCUploadAPI::GetVerifier();
        $types = '';
        if( isset($verifier) )
            $verifier->GetValidFileTypes($types);

        if( empty($types) )
        {
            $form_tip = 'str_file_specify';
        }
        else
        {
            $form_tip = array( 'str_file_valid_types', implode(', ',$types) );
        }

        $fields[$field_name] = 
                           array(  'label'      => 'str_file',
                                   'formatter'  => 'upload',
                                   'form_tip'   => $form_tip,
                                   'flags'      => CCFF_REQUIRED  );
    }

    public static function IsRemix($upload_id_or_record)
    {
        return CCUpload::HasTag($upload_id_or_record,'remix');
    }

    public static function HasTag($upload_id_or_record,$tag)
    {
        $upload_id = is_array($upload_id_or_record) ? $upload_id_or_record['upload_id'] : $upload_id_or_record;
        $uploads =& CCUploads::GetTable();
        $tags = $uploads->QueryItemFromKey( 'upload_tags', $upload_id );
        require_once('cchost_lib/cc-tags.php');
        return CCTag::InTag($tag,$tags);
    }
    
    public static function GetTagFields(&$form,$tag_field_name='upload_tags',$insert_how = 'before',$insert_where = 'upload_description')
    {
        require_once('cchost_lib/cc-tags.inc');
        $tags =& CCTags::GetTable();
        $where['tags_type'] = CCTT_USER;
        $tags->SetOffsetAndLimit(0,'25');
        $tags->SetOrder('tags_count','DESC');
        $pop_tags = $tags->QueryKeys($where);

        $fields[$tag_field_name] =
            array( 'label'      => 'str_tags',
                   'formatter'  => 'tagsedit',
                   'form_tip'   => 'str_comma_separated',
                   'flags'      => CCFF_NONE );

        $fields['popular_tags'] =
                    array( 'label'      => 'str_popular_tags',
                           'target'     => $tag_field_name,
                           'tags'       => $pop_tags,
                           'formatter'  => 'metalmacro',
                           'macro'      => 'popular_tags',
                           'form_tip'   => 'str_click_on_these',
                           'flags'      => CCFF_STATIC | CCFF_NOUPDATE );

        $form->InsertFormFields( $fields, $insert_how, $insert_where );
    }

    public static function AddSuggestedTags(&$form,$suggested_tags, $how = 'before', $where = 'popular_tags' )
    {
        if( empty($suggested_tags) )
            return;

        if( !is_array($suggested_tags) )
        {
            require_once('cchost_lib/cc-tags.php');
            $suggested_tags = CCTag::TagSplit($suggested_tags);
        }

        $fields['suggested_tags'] =
                        array( 'label'      => 'str_suggested_tags',
                               'target'     => 'upload_tags',
                               'tags'       => $suggested_tags,
                               'formatter'  => 'metalmacro',
                               'macro'      => 'popular_tags',
                               'form_tip'   => 'str_click_on_these',
                               'flags'      => CCFF_STATIC | CCFF_NOUPDATE );

        $form->InsertFormFields( $fields, $how, $where );
    }

    public static function PostProcessNewUploadForm( &$form, $ccud_tags, $relative_dir, $parents = null)
    {
        $form->GetFormValues($values);
        $current_path = $values['upload_file_name']['tmp_name'];
        $new_name     = $values['upload_file_name']['name'];
        $user_tags    = $values['upload_tags'];

        // All fields here that start with 'upload_' are 
        // considered to be fields in the CCUploads table
        // so....
        // Destroy the $_FILES object so it doesn't get
        // confused with that 

        unset($values['upload_file_name']);

        require_once('cchost_lib/cc-uploadapi.php');

        $ret = CCUploadAPI::PostProcessNewUpload(   $values, 
                                                    $current_path,
                                                    $new_name,
                                                    $ccud_tags,
                                                    $user_tags,
                                                    $relative_dir,
                                                    $parents );
        if( is_string($ret) )
        {
            $form->SetFieldError('upload_file_name',$ret);
            return(0);
        }

        return($ret);
    }

    public static function PostProcessEditUploadForm($form, $record, $relative_dir)
    {
        $form->GetFormValues($upload_args);

        require_once('cchost_lib/cc-uploadapi.php');

        $ret = CCUploadAPI::PostProcessEditUpload( $upload_args, $record, $relative_dir );

        if( is_string($ret) )
        {
            $form->SetFieldError('upload_file_name',$ret);
            return(0);
        }

        return( intval($record['upload_id']) );
    }

}



?>
