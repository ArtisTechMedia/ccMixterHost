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
* $id$
*
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

//CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,  array( 'CCFacebook',    'OnAdminMenu');
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCFacebook' , 'OnGetConfigFields' ));

CCEvents::AddHandler(CC_EVENT_FORM_FIELDS,    array( 'CCFacebook', 'OnFormFields'));
CCEvents::AddHandler(CC_EVENT_FORM_POPULATE,  array( 'CCFacebook', 'OnFormPopulate'));
CCEvents::AddHandler(CC_EVENT_FORM_VERIFY,    array( 'CCFacebook', 'OnFormVerify'));


require_once('cchost_lib/cc-page.php');

class CCFacebook
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
            $fields['facebook_allow_login'] =
               array(  'label'      => _('Allow Facebook login'),
                       'form_tip'   => _('Check this to allow users to login via Facebook account'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE );
        }
    }
    
    /**
    * Event handler for {@link CC_EVENT_FORM_FIELDS}
    *
    * @param object &$form CCForm object
    * @param object &$fields Current array of form fields
    */
    function OnFormFields(&$form,&$fields)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['facebook_allow_login']) )
            return;
        
        if( is_a($form,'CCUserLoginForm') || is_subclass_of($form,'CCUserLoginForm') ||
                    is_subclass_of($form,'ccuserloginform') )
        {

            if( empty($fields['facebook-login']) )
            {
                $fields['facebook-login'] = 
                            array( 'label'  => 'facebook login',
                                   'form_tip'   => 'log in using your FaceBook account',
                                   'formatter'  => 'metalmacro',
                                   'macro'      => 'facebook.tpl/facebook_login',
                                   'flags'      => CCFF_NOUPDATE);
                                
//                $page =& CCPage::GetPage();
//                $page->AddScriptBlock('facebook.tpl/facebook_script',true);   
            }
        }
    }

    /**
    * Event handler for {@link CC_EVENT_FORM_POPULATE}
    * 
    * @param object &$form CCForm object
    * @param array &$values Current values being applied to form fields
    */
    function OnFormPopulate(&$form,&$values)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['facebook_allow_login']) )
            return;
        
        if( is_subclass_of($form,'CCUserLoginForm') ||
                    is_subclass_of($form,'ccuserloginform') )
        {
        }
    }


    /**
    * Event handler for {@link CC_EVENT_FORM_VERIFY}
    * 
    * @param object &$form CCForm object
    * @param boolean &$retval Set this to false if fields fail to verify
    */
    function OnFormVerify(&$form,&$retval)
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['facebook_allow_login']) )
            return;
        
        if( is_subclass_of($form,'CCUserLoginForm') ||
                    is_subclass_of($form,'ccuserloginform') )
        {
        }
        
        // $form->SetFormValue('upload_bpm',$bpm );

        return true;
    }


}
?>