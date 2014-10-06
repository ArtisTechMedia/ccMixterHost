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
* $Id: cc-openid-forms.inc 12470 2009-04-29 14:57:35Z fourstones $
*
*/

/**
* @package cchost
* @subpackage user
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

class CCOpenIDLoginForm extends CCForm
{
    function CCOpenIDLoginForm()
    {
        $this->CCForm();
        $fields['openid_url'] = 
            array( 'label'  => _('OpenID Identity'),
                   'form_tip'   => _('Log in using your OpenID identity'),
                    'class'     => 'cc_openid_edit',
                   'formatter'  => 'textedit',
                   'flags'      => CCFF_REQUIRED);
        $this->AddFormFields($fields);
        $this->SetSubmitText('Log in');        
    }
}

class CCManageOpenIDsForm extends CCGridForm
{
    function CCManageOpenIDsForm($openids)
    {
        $this->CCGridForm();
        $this->SetTemplateVar('form_fields_macro','flat_grid_form_fields');

        $heads = array(
            '', 'str_openid', 'str_openid_action'
            );
        
        $this->SetColumnHeader($heads);

        $count = 0;
        foreach( $openids as $OID  )
        {
            $S = 'S[' . ++$count . ']';

            $a = array(
              array(
                'element_name'  => $S . '[n]',
                'value'      => '',
                'formatter'  => 'statictext',
                'flags'      => CCFF_NOUPDATE ),
              array(
                'element_name'  => $S . '[openid]',
                'value'      => $OID['openid'],
                'class'      => 'cc_openid_edit',
                'formatter'  => 'textedit',
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => $S . '[act]',
                'value'      => $OID['openid_show'] ? 'show' : 'hide',
                'formatter'  => 'radio',
                'nobr'       => true,
                'options'    => array( 'show' => 'str_openid_show',
                                       'hide' => 'str_openid_hide',
                                       'delete' => 'str_openid_stop'),
                'flags'      => CCFF_POPULATE ),
            );
            
            $this->AddGridRow( $count, $a );
        }


        $S = 'new';
        $a = array(
              array(
                'element_name'  => $S . '[n]',
                'value'      => 'str_openid_new_id',
                'formatter'  => 'statictext',
                'flags'      => CCFF_NOUPDATE ),
              array(
                'element_name'  => $S . '[openid]',
                'value'      => '',
                'class'      => 'cc_openid_edit',
                'formatter'  => 'textedit',
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => $S . '[act]',
                'value'      => 'show',
                'formatter'  => 'radio',
                'nobr'       => true,
                'options'    => array( 'show' => 'str_openid_show',
                                       'hide' => 'str_openid_hide',),
                'flags'      => CCFF_POPULATE ),
                );
        // normally we would AddMetaRow but we only want to add
        // one new openid at a time.
        $this->AddGridRow( ++$count, $a );

        $this->SetFormHelp('str_openid_tip1');
        $this->SetFormHelp('<p></p>');
        
    }
}

class CCOpenIDHookUpForm extends CCForm
{
    var $_checked_radio;
    var $_args;
    
    function CCOpenIDHookUpForm($args)
    {
        $this->CCForm();
        $this->_checked_radio = '"checked=1"';
        $this->_args = $args;
        $this->SetHandler(ccl('openid','match'));
        $this->SetSubmitText('str_openid_match_submit');
        $page =& CCPage::GetPage();
        $nic = empty($args['nickname']) ? '' : empty($args['nickname']);
        $email = empty($args['email']) ? '' : empty($args['email']);
        $help = $page->String( array('str_openid_match_help',$args['openid'],$nic,$email) );
        $this->SetHelpText($help);
        $fields = array();
        if( empty($args['matches']) )
        {
            $extip = 'str_openid_existing_tip1';
        }
        else
        {
            $extip = 'str_openid_existing_tip2';
            
            $fields['matches'] = array(  'label'      => 'str_openid_match',
                                   'form_tip'    => 'str_openid_match_tip',
                                   'formatter'   => 'match_user',
                                   'args'       => $args,
                                   'flags'       => CCFF_POPULATE );
        }
        
        $fields['existing_user'] = array(  'label'      => 'str_openid_existing',
                               'form_tip'    => $extip,
                               'formatter'   => 'existing_user',
                               'args'       => $args,
                               'flags'       => CCFF_POPULATE );

        $fields['new_user'] = array(  'label'      => 'str_openid_new',
                               'form_tip'    => 'str_openid_new_tip',
                               'formatter'   => 'new_user',
                               'args'       => $args,
                               'flags'       => CCFF_POPULATE );

        $this->AddFormFields($fields);        
        $this->SetHiddenField('openidinfo',urlencode(serialize($args)));
    }

    function generator_match_user($varname,$value=null,$class='')
    {    
        $html = '';    
        $args = $this->GetFormFieldItem($varname,'args');
        $chk = empty($_POST) ? $this->_checked_radio : '';
        foreach( $args['matches'] as $M )
        {
            if( !empty($_POST['match']) && ($_POST['match'] == $M['user_id']) )
                $chk = 'checked="checked"';
            
          $html .=<<<EOF
<input type="radio" {$chk} name="match" id="match_{$M['user_id']}" value="{$M['user_id']}" />
<label for="match_{$M['user_id']}">{$M['display_name']} ({$M['user_email']})</label><br />
EOF;
           $chk = '';
        }
        $this->_checked_radio = '';
        $page =& CCPage::GetPage();
        $str1 = $page->String('str_openid_password');
        $html .=<<<EOF
<div style="margin: 5px 0px 4px 13px;">
   {$str1} <input type="password" name="select_user_password" />
</div>        
EOF;
        
        return $html;
    }    

    function validator_match_user($fieldname)
    {
        $user_id = sprintf('%d',$_POST['match']);
        if( empty($user_id) )
            return true;
        if( empty($_POST['select_user_password']) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_no_password');
            return false;
        }
        $pw = md5($_POST['select_user_password']);
        $user_id = CCDatabase::QueryItem("SELECT user_id FROM cc_tbl_user WHERE (user_id={$user_id}) AND (user_password='{$pw}')");
        if( empty($user_id) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_bad_password');
            return false;
        }
        $this->_match_user_id = $user_id;
        return true;
    }
    
    function generator_existing_user($varname,$value=null,$class='')
    {
        $args = $this->GetFormFieldItem($varname,'args');
        $page =& CCPage::GetPage();
        $str1 = $page->String('str_openid_exist_user');
        $str2 = $page->String('str_openid_password');
        $chk = (!empty($_POST['match']) && ($_POST['match'] == 'other')) ? 'checked="checked"' : $this->_checked_radio;
        $val = !empty($_POST['user_name'])  ? $_POST['user_name'] : '';
        $html =<<<EOF
<input type="radio" {$chk} name="match" id="other_record" value="other">
<div style="margin: 5px 0px 4px 13px;">
        {$str1} <input type="text" name="user_name" id="user_name" value="{$val}" /><br />
        {$str2} <input type="password" name="user_password" id="user_password" />
</div>        
EOF;
        return $html;
    }
    
    function validator_existing_user($fieldname)
    {
        if( $_POST['match'] != 'other' )
            return true;
        
        if( empty($_POST['user_name']) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_no_username');
            return false;
        }
        $row = CCDatabase::QueryRow("SELECT user_id,user_password FROM cc_tbl_user WHERE user_name='{$_POST['user_name']}'");
        if( empty($row) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_bad_username');
            return false;
        }
        if( empty($_POST['user_password']) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_no_password');
            return false;
        }
        $pw = md5($_POST['user_password']);
        if( $row['user_password'] != $pw )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_bad_password');
            return false;
        }
        $this->_match_user_id = $row['user_id'];
        return true;
    }
    
    function generator_new_user($varname,$value=null,$class='')
    {        
        $args = $this->GetFormFieldItem($varname,'args');
        $page =& CCPage::GetPage();
        $str1 = $page->String('str_openid_new_user');
        $str2 = $page->String('str_openid_display_name');
        $chk = (!empty($_POST['match']) && ($_POST['match'] == 'new')) ? 'checked="checked"' : $this->_checked_radio;
        $fun = empty($args['free_user_name']) ? '' : $args['free_user_name'];
        $val = !empty($_POST['new_name'])  ? $_POST['new_name'] : $fun;
        $full = empty($args['fullname']) ? '' : $args['fullname'];
        $val2 = !empty($_POST['new_full_name'])  ? $_POST['new_full_name'] : $full;
        $html =<<<EOF
<input type="radio" {$chk} name="match" id="new_record" value="new">
<div style="margin: 5px 0px 4px 13px;">
        {$str1} <input type="text" name="new_name" id="new_name" value="{$val}"/><br />
        {$str2} <input type="text" name="new_full_name" id="new_full_name" value="{$val2}"/>
</div>        
EOF;
        return $html;
    }
    
    function validator_new_user($fieldname)
    {
        if( $_POST['match'] != 'new' )
            return true;
        
        if( empty($_POST['new_name']) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_no_newuser');
            return false;
        }
        if( preg_match('/[^a-zA-Z0-9_]/',$_POST['new_name']) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_bad_chars');
            return false;
        }
        $user_id = CCDatabase::QueryItem("SELECT user_id FROM cc_tbl_user WHERE user_name='{$_POST['new_name']}'");
        if( !empty($user_id) )
        {
            $this->SetFieldError($fieldname, 'str_openid_r_username_taken');
            return false;
        }
        $this->_new_user_info = array( $_POST['new_name'], $_POST['new_full_name'] );
        return true;
    }
    
}
?>
