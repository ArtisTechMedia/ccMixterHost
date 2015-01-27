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
* $Id: cc-license.inc 12627 2009-05-19 17:59:48Z fourstones $
*
*/

/**
* Module for managing Creative Commons licenses
*
* @package cchost
* @subpackage feature
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-admin.php');
require_once('cchost_lib/cc-license.php');

/**
 * Form class for configuring licenses.
 *
* @package cchost
* @subpackage admin
 */
class CCEditLicensesForm extends CCForm
{
    function CCEditLicensesForm($id='')
    {
        $this->CCForm();
        $meta = array(        
            'license_id' => array( _('License ID'),  $id ? '' : _('This value must be unique'), 1 ),
            'license_url' => array( _('URL to License Page'),  _(''), 1 ),
            'license_name' => array( _('Display Name'),  _(''), 1 ),
            'license_tag' => array( _('Tag'),  _('Tag(s) to be assigned to uploads with this license. Seperate multiple tags with commas.'), 1 ),
            'license_jurisdiction' => array( _('Jurisdiction'),  _(''), 0 ),
            'license_img_small' => array( _('Small Logo'),  _('Fully qualified URL to small version of license image'), 1 ),
            'license_img_big' => array( _('Big Logo'),  _('Fully qualified URL to big version of license image'), 1 ),
            'license_text' => array( _('Description'),  _(''), 0 ),
            );

        $fields = array();
        foreach( $meta as $K => $M )
        {
            $formatter = empty($id) || ($K != 'license_id') ? 'textedit' : 'statictext';
            $fields[$K] = array(
                    'label' => $M[0],
                    'form_tip' => $M[1],
                    'formatter' => $formatter,
                    'flags' => CCFF_POPULATE | (empty($M[2]) ? 0 : CCFF_REQUIRED),
                    );                    
        }

        $licattrs = array(
            'license_permits' => array( _('Permits'),  _('List of attributeds premitted by this license.') ),
            'license_required' => array( _('Required'),  _('List of attributes required by this license.')   ),
            'license_prohibits' => array( _('Prohibits'),  _('List of attributes prohibited by this license.') ),
            );

        $attr = cc_split(',','Reproduction,Distribution,DerivativeWorks,CommercialUse,Attribution,Notice,Sharing,ShareAlike');
        $arrt = array_combine($attr,$attr);
        foreach( $licattrs as $K => $LA )
        {
            $fields[$K] = array(
                    'label' => $LA[0],
                    'form_tip' => $LA[1],
                    'formatter' => 'template',
                    'macro' => 'multi_checkbox',
                    'options' => $arrt,
                    'cols' => 2,
                    'flags' => CCFF_POPULATE,
                    );                    
        }

        $this->AddFormFields($fields);
    }
}

/**
* License event handlers and API
*
*/
class CCLicense
{
    function Edit($license_id='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $admin = new CCAdmin();
        $title = empty($license_id) ? _('Add a License') : _("Edit Licenses");
        $page->SetTitle($title);
        $admin->BreadCrumbs(false,array( 'url' => '', 'text' => $title ));
        $page->SetTitle($title);
        $form = new CCEditLicensesForm($license_id);
        $show = false;
        if( empty($_POST['editlicenses']) )
        {
            $show = true;
            if( !empty($license_id) )
            {
                $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_licenses WHERE license_id=\''.$license_id.'\'');
                $form->PopulateValues($row);
            }
        }
        elseif( !$form->ValidateFields() )
        {
            $show = true;
        }

        if( $show )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $licdb =& CCLicenses::GetTable();
            foreach( array('license_permits','license_required','license_prohibits') as $LA )
            {
                if( empty($values[$LA]) )
                {
                    $values[$LA] = '';
                }
                else
                {
                    $keys = array_keys($values[$LA]);
                    $values[$LA] = join(',',$keys);
                }
            }
            if( empty($license_id) )
            {
                $licdb->Insert($values);
            }
            else
            {
                $values['license_id'] = $license_id;
                $licdb->Update($values);
            }
            CCUtil::SendBrowserTo(ccl('license_menu'));            
        }
        
    }
    /**
    * Event handler for {@link CC_EVENT_GET_MACROS}
    *
    * @param array &$record Upload record we're getting macros for (if null returns documentation)
    * @param array &$file File record we're getting macros for
    * @param array &$patterns Substituion pattern to be used when renaming/tagging
    * @param array &$mask Actual mask to use (based on admin specifications)
    */
    function OnGetMacros(&$record, &$file, &$patterns, &$masks)
    {
        if( empty($record) )
        {
            $patterns['%license_url%'] = _("License URL");
            $patterns['%license%']     = _("License name");
        }
        else
        {
            if( empty($record['license_url']) )
            {
                if( !empty($record['upload_license']) )
                {
                    $licrow = CCDatabase::QueryRow(
                                'SELECT license_url, license_name FROM cc_tbl_licenses WHERE license_id="'.
                                $record['upload_license'] . '"');
                    $patterns['%license_url%'] = $licrow['license_url'];
                    $patterns['%license%'] = $licrow['license_name'];
                }
            }
            else
            {
                $patterns['%license_url%'] = $record['license_url'];
                $patterns['%license%'] = $record['license_name'];
            }

        }
    }

    /**
    * Event handler for {@link CC_EVENT_GET_SYSTAGS}
    *
    * @param array $record Record we're getting tags for 
    * @param array $file Specific file record we're getting tags for
    * @param array $tags Place to put the appropriate tags.
    */
    function OnGetSysTags(&$record,&$file,&$tags)
    {
        if( empty($record) )
            return;

        if( empty($record['upload_license']) )
            $record['upload_license'] = CCDatabase::QueryItem('SELECT upload_license FROM cc_tbl_uploads WHERE upload_id='.$record['upload_id']);
        $licenses =& CCLicenses::GetTable();
        $where['license_id'] = $record['upload_license'];
        $lic_tags = $licenses->QueryItem('license_tag',$where);
        $lic_tags = cc_split(',',$lic_tags);
        foreach( $lic_tags as $T )
            $tags[] = trim($T);
    }

    function OnFileDone(&$file)
    {
        if( !defined('IN_MIXTER_PORT') )
        {
            $sha1 = @sha1_file( $file['local_path'] ) ;
            $file['file_extra']['sha1'] = $this->_hex_to_base32($sha1);
        }
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items, $scope)
    {
        if( $scope != CC_GLOBAL_SCOPE )
            return;

        $items += array( 
            'licenses'=> array( 'menu_text'  => _('License'),
                                'menu_group' => 'configure',
                                'access'     => CC_ADMIN_ONLY,
                                'help'       => _('Edit the licenses in the system'),
                                'weight'     => 16,
                                'action'     => ccl('license_menu') )
                        );
    }

    /**
    * Show and process a form for admins to pick which licenses work on the site
    */
    function Admin()
    {
        CCUtil::SendBrowserTo(ccl('license_menu'));
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/license',  array('CCLicense', 'Admin'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show admin license form'), CC_AG_MISC_ADMIN   );
        CCEvents::MapUrl( 'admin/licenses',  array('CCLicense', 'Edit'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show edit licenses form'), CC_AG_MISC_ADMIN   );
    }

    /**
    * Internal: used for hashing files
    */
    function _hex_to_base32($hex) 
    {
      $b32_alpha_to_rfc3548_chars = array(
        '0' => 'A',
        '1' => 'B',
        '2' => 'C',
        '3' => 'D',
        '4' => 'E',
        '5' => 'F',
        '6' => 'G',
        '7' => 'H',
        '8' => 'I',
        '9' => 'J',
        'a' => 'K',
        'b' => 'L',
        'c' => 'M',
        'd' => 'N',
        'e' => 'O',
        'f' => 'P',
        'g' => 'Q',
        'h' => 'R',
        'i' => 'S',
        'j' => 'T',
        'k' => 'U',
        'l' => 'V',
        'm' => 'W',
        'n' => 'X',
        'o' => 'Y',
        'p' => 'Z',
        'q' => '2',
        'r' => '3',
        's' => '4',
        't' => '5',
        'u' => '6',
        'v' => '7'
      );
      $b32_alpha = '';
      for ($pos = 0; $pos < strlen($hex); $pos += 10) {
        $hs = substr($hex,$pos,10);
        $b32_alpha_part = base_convert($hs,16,32);
        $expected_b32_len = strlen($hs) * 0.8;
        $actual_b32_len = strlen($b32_alpha_part);
        $b32_padding_needed = $expected_b32_len - $actual_b32_len;
        for ($i = $b32_padding_needed; $i > 0; $i--) {
          $b32_alpha_part = '0' . $b32_alpha_part;
        }
        $b32_alpha .= $b32_alpha_part;
      }
      $b32_rfc3548 = '';
      for ($i = 0; $i < strlen($b32_alpha); $i++) {
        $b32_rfc3548 .= $b32_alpha_to_rfc3548_chars[$b32_alpha[$i]];
      }
      return $b32_rfc3548;
    }
}



?>
