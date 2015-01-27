<?php
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
* $Id: cc-lic-waiver-opts.php 12730 2009-06-06 05:42:47Z fourstones $
*
*/

/** 
* @package cchost
* @subpackage upload
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');

class CCAdminLicWaiverForm extends CCEditConfigForm
{
    function CCAdminLicWaiverForm()
    {
        $this->CCEditConfigForm('lic-waiver',CC_GLOBAL_SCOPE);
        $this->SetModule(ccs(__FILE__));

        $lics = CCDatabase::QueryRows('SELECT license_id,license_name FROM cc_tbl_licenses ORDER by license_name');
        $licx = array();
        foreach( $lics as $L )
            $licx[$L['license_id']] = $L['license_name'];

        $fields = array(
                    'waivers' =>
                        array(
                                'label' => _('Default License'),
                                'form_tip' => _('When a remix would default to one of these...') ,
                                'formatter' => 'template',
                                'macro' => 'multi_checkbox',
                                'options' => $licx,
                                'cols' => 2,
                                'flags' => CCFF_POPULATE ),
                    'licenses' =>
                        array(
                                'label' => _('Alternatives'),
                                'form_tip' => _('...allow the user to choose from these') ,
                                'formatter' => 'template',
                                'macro' => 'multi_checkbox',
                                'options' => $licx,
                                'cols' => 2,
                                'flags' => CCFF_POPULATE ),
            );

        $this->AddFormFields($fields);
        
        $this->SetFormHelp( _('Use this form to present alternative licenses when a remix would result in a waiving of rights or liberal license.'));
        $this->SetFormHelp( '<br />' );
        $this->SetFormHelp( _('For example, if a remix would result in a CCZero waiver, you can allow users to select an Attribution license.'));
        $this->SetFormHelp( '<br />' );
        $this->SetFormHelp( _('NOTE: Using this form, it is easy to create a situation where an illegal combination of licenses occur.'));
        $this->SetFormHelp( '<br />' );
        $this->SetFormHelp( _('For example, if a remix would result in a ShareAlike license and the user is offered to license the remix as Attribution, that would be a violation of the source\'s ShareAlike license.'));
    }

    function PopulateValues($vals)
    {
        $vals['licenses'] = empty($vals['licenses']) ? '' : join(',',array_keys( $vals['licenses']));
        $vals['waivers'] = empty($vals['waivers']) ? '' : join(',',array_keys( $vals['waivers']));
        parent::PopulateValues($vals);
    }
}

class CCLicWaiver
{
    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _('Configure Upgrade Alternatives');
        $trail1 = array( 'url' => '/license_menu', 'text' => 'Edit System Licenses' );
        $trail2 = array('url'=>'','text'=>$title);
        CCAdmin::BreadCrumbs(true,$trail1,$trail2);
        $page->SetTitle($title);
        $form = new CCAdminLicWaiverForm();
        $page->AddForm( $form->GenerateForm() );
    }
    
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','waiver'),   array('CCLicWaiver','Admin'),
            CC_ADMIN_ONLY, ccs(__FILE__), '', 
            _('Manage license waiver alternatives') , CC_AG_UPLOAD );
    }
}

?>
