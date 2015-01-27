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
* $Id: cc-disable-site.inc 10478 2008-07-12 05:00:06Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

class CCDisableSiteForm extends CCEditConfigForm
{
    function CCDisableSiteForm()
    {
        $this->CCEditConfigForm('config');


        $fields['site-disabled'] =
               array(  'label'      => _('Disable Site'),
                       'form_tip'   => _('Check this to disable your site from users'),
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);
        $fields['enable-password'] =
               array( 'label'       => _('Admin Password'),
                       'form_tip'   => _('This password will allow admins to continue passed disabling.'),
                       'formatter'  => 'disable_site_password',
                       'nomd5'      => true,
                       'flags'      => CCFF_POPULATE | CCFF_REQUIRED );
        $fields['disabled-msg'] =
               array( 'label'       => _('Site Disabled Message'),
                       'form_tip'   => _('This file is displayed when the admins have temporarily disabled the site.'),
                       'formatter'  => 'disable_site_localdir',
                       'flags'      => CCFF_POPULATE  );

        $this->AddFormFields($fields);
        $this->SetModule( ccs(__FILE__) );
    }

    function generator_disable_site_password($varname,$value='',$class='')
    {
        return $this->generator_password($varname,$value,$class);
    }

    function validator_disable_site_password($fieldname)
    {
        if( empty($_POST['site-disabled']) )
            return true;
        return $this->validator_password($fieldname);
    }

    function generator_disable_site_localdir($varname,$value='',$class='')
    {
        return $this->generator_localdir($varname,$value,$class);
    }

    function validator_disable_site_localdir($fieldname)
    {
        if( empty($_POST['site-disabled']) )
            return true;
        return $this->validator_localdir($fieldname);
    }
}



/**
*
*
*/
class CCDisableSite
{
    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _('Disable Site');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);
        $form = new CCDisableSiteForm();
        $page->AddForm($form->GenerateForm());
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            global $CC_GLOBALS;

            $items += array(
                'disablesite'   => array( 
                                 'menu_text'    => _('Disable Site'),
                                 'menu_group'   => 'configure',
                                 'help'         => _('Disable site when doing maintainence.'),
                                 'access'       => CC_ADMIN_ONLY,
                                 'weight'       => 60,
                                 'action'       =>  ccl('admin', 'disable')
                                 ),
                );
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','disable'), 
                          array('CCDisableSite','Admin'), CC_ADMIN_ONLY,
                          ccs(__FILE__), '', 
                          _('Disable site from non-admin users'), 
                          CC_AG_MISC_ADMIN);
    }


}



?>
