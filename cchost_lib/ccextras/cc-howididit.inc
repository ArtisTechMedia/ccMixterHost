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
* $Id: cc-howididit.inc 13253 2009-08-09 20:04:44Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

/**
*/
class CCHowIDidItForm extends CCForm
{
    function CCHowIDidItForm()
    {
        $this->CCForm();
        $hidi = new CCHowIDidIt();
        $this->AddFormFields($this->_get_fields());
    }
}

/**
* Main API for media blogging
*/
class CCHowIDidIt
{
    /*-----------------------------
        MAPPED TO URLS
    -------------------------------*/

    function Browse()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_hidi_browse');
        $page->AddMacro('howididit_browse');
    }

    function Detail($upload_id)
    {
        $args['root-url'] = ccd();
        $args['howididit_upload'] = $upload_id;
        $args['howididit_fields'] = $this->_get_fields();
        $args['howididit_info'] = $this->_get_data($upload_id);
        require_once('cchost_lib/cc-template.php');
        $template = new CCSkinMacro( 'howididit' );
        print( $template->SetAllAndParse($args) );
        exit;
    }

    function HowIDidIt($upload_id)
    {
        $fields = $this->_get_fields();
        $data = $this->_get_data($upload_id);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle('str_hidi_title');
        if( empty($data) )
            CCUtil::Send404();
        $page->PageArg('howididit_upload',$upload_id);
        $page->PageArg('howididit_fields',$fields);
        $page->PageArg('howididit_info',$data,'howididit');
    }

    function _get_data($upload_id)
    {
        $uploads =& CCUploads::GetTable();
        $upload_id = sprintf('%d',$upload_id);
        if( !$uploads->KeyExists($upload_id) )
            return null;
        $upload_extra = CCDatabase::QueryItem('SELECT upload_extra FROM cc_tbl_uploads WHERE upload_id='.$upload_id);
        $upload_extra = unserialize($upload_extra);
        if( empty($upload_extra['howididit']) )
            return;
        $data =& $upload_extra['howididit'];
        $keys = array_keys($data);
        foreach( $keys as $key )
        {
            if( !empty($data[$key]) )
                $data[$key] = nl2br($data[$key]);
        }
        return $data;
    }

    function nlfix($str)
    {
        return( preg_replace( "/[\n\r]+/","<br />",$str) );
    }

    function EditHowIDidIt($upload_id)
    {
        require_once('cchost_lib/cc-upload.php');
        require_once('cchost_lib/cc-uploadapi.php');

        CCUpload::CheckFileAccess(CCUser::CurrentUser(),$upload_id);
        $uploads =& CCUploads::GetTable();
        if( !$uploads->KeyExists($upload_id) )
            die('Invalid upload id for how i did it');

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        list( $upload_name, $upload_extra ) = CCDatabase::QueryRow(
                'SELECT upload_name, upload_extra FROM cc_tbl_uploads WHERE upload_id=' . $upload_id, false );
        $page->SetTitle( 'str_hidi_edit', $upload_name );
        $form = new CCHowIDidItForm();
        $is_post = !empty($_POST['howididit']);
        $upload_extra = unserialize($upload_extra);
        if(  !$is_post && !empty($upload_extra['howididit']) )
            $form->PopulateValues($upload_extra['howididit']);
        if( !$is_post || !$form->ValidateFields() )
        {
            $page->AddForm($form->GenerateForm());
        }
        else
        {
            $form->GetFormValues($values);
            //CCDebug::PrintVar($values);
            $has_values = false;
            foreach( $values as $name => $val )
            {
                if( !empty($val) )
                {
                    $has_values = true;
                    break;
                }
            }
            if( $has_values )
            {
                $upload_extra['howididit'] = $values;
                $args['upload_extra'] = serialize($upload_extra);
                $args['upload_id'] = $upload_id;
                $uploads->Update($args);
                CCUploadAPI::UpdateCCUD($upload_id,'how_i_did_it','');
                $url = ccl('howididit',$upload_id);
                $page->Prompt('str_hidi_saved_changes', "<a href=\"$url\">","</a>");
            }
            else
            {
                if( !empty($upload_extra['howididit']) )
                {
                    unset($upload_extra['howididit']);
                    $args['upload_extra'] = serialize($upload_extra);
                    $args['upload_id'] = $upload_id;
                    $uploads->Update($args);
                }

                CCUploadAPI::UpdateCCUD($upload_id,'','how_i_did_it');

                $user_name = CCDatabase::QueryItem(
                                'SELECT user_name FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$upload_id);
                $url = ccl('files',$user_name,$upload_id);
                $page->Prompt('str_hidi_no_rec',"<a href=\"$url\">","</a>");
            }
                    
        }
    }

    function _get_fields()
    {
        $fields = array(
            'tools' => array(
                'label'     => 'str_hidi_tools_i_used',
                'form_tip'  => 'str_hidi_what_software',
                'formatter' => 'textarea',
                'flags'     => CCFF_POPULATE),
            'samples' => array(
                'label'     => 'str_hidi_samples_i_used',
                'form_tip'  => 'str_hidi_where_did_you',
                'formatter' => 'textarea',
                'flags'     => CCFF_POPULATE),
            'origial' => array(
                'label'     => 'str_hidi_original_samples',
                'form_tip'  => 'str_hidi_what_material',
                'formatter' => 'textarea',
                'flags'     => CCFF_POPULATE),
            'process' => array(
                'label'     => 'str_hidi_process',
                'form_tip'  => 'str_hidi_how_did_you',
                'formatter' => 'textarea',
                'flags'     => CCFF_POPULATE),
            'other' => array(
                'label'     => 'str_hidi_other_notes',
                'form_tip'  => 'str_hidi_share_your_feelings',
                'formatter' => 'textarea',
                'flags'     => CCFF_POPULATE)
            );

        return $fields;
    }


    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('howididit'),      array('CCHowIDidIt','HowIDidIt'),   
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{upload_id}', 
            _('Show How I Did It Page for an upload'), CC_AG_HIDI );

        CCEvents::MapUrl( ccp('howididit','browse'), array('CCHowIDidIt','Browse'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '',
            _('Show How I Did It browser'), CC_AG_HIDI  );

        CCEvents::MapUrl( ccp('howididit','detail'), array('CCHowIDidIt','Detail'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{upload_id}',
            _('Ajax callback for hidi details'), CC_AG_HIDI  );

        CCEvents::MapUrl( ccp('edithowididit'),  array('CCHowIDidIt','EditHowIDidIt'),   
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{upload_id}', 
            _('Edit How I Did It Page'), CC_AG_HIDI );
    }

}


?>
