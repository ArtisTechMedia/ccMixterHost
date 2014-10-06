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
* $Id: update_v_5_1_lic_editing.inc 12730 2009-06-06 05:42:47Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
class v_5_1_lic_editing extends CCUpdate
{
    function Update()
    {
        global $CC_GLOBALS;
        
        $this->_check_for_field('cc_tbl_licenses','license_img_small', 'varchar(255)');
        $this->_check_for_field('cc_tbl_licenses','license_img_big', 'varchar(255)');
        $lics = CCDatabase::QueryRows('SELECT license_id,license_logo,license_img_big FROM cc_tbl_licenses');
        require_once('cchost_lib/cc-license.php');
        $db =& CCLicenses::GetTable();
        foreach( $lics as $L )
        {
            if( !empty($L['license_img_big']) )
                continue;
            $args = array();
            $args['license_id'] = $L['license_id'];
            $args['license_img_small'] = ccd('ccskins/shared/images/lics/small-'.$L['license_logo']);
            $args['license_img_big']   = ccd('ccskins/shared/images/lics/'.$L['license_logo']);
            $db->Update($args);
        }
        $page =& CCPage::GetPage();
        $page->Prompt('License editing enabled');
        
        $has_cc0 = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_licenses WHERE license_id = \'cczero\'');
        if( !$has_cc0 )
        {
            $cc0 = array( 'license_id'        => 'cczero' ,
                            'license_url'       => 'http://creativecommons.org/publicdomain/zero/1.0/',
                           'license_name'       => 'CC0 (CC Zero)',
                           'license_permits'    => 'Reproduction,Distribution,DerivativeWorks',
                           'license_prohibits'  => '',
                           'license_required'   => '',
                           'license_img_small'  => 'http://i.creativecommons.org/l/zero/1.0/80x15.png',
                           'license_img_big'    => 'http://i.creativecommons.org/l/zero/1.0/88x31.png',
                           'license_tag'        => 'cczero',
                           'license_text'       => _('<strong>CC0</strong>: Use this if you wish to waive all rights to your work. Once these rights are waived, you cannot reclaim them. In particular, if you are an artist or author who depends upon copyright for your income, Creative Commons <b>does not recommend</b> that you use this tool.')
                           );
            $db->Insert($cc0);
            $page->Prompt(_('CC0 (CC Zero) license installed'));
        }

        $configs =& CCConfigs::GetTable();
        $lics = $configs->GetConfig('licenses');
        if( !empty($lics) )
        {
            if( empty($CC_GLOBALS['v_5_1_minilic']) )
            {
                $lix = $lics;
            }
            else
            {
                $lix = array();
                foreach( $lics as $L => $on )
                    if( $on == 'on' )
                        $lix[] = $L;                
            }
            $lix = join(',',$lix);
            $submit_forms = $configs->GetConfig('submit_forms');
            $keys = array_keys($submit_forms);
            $c = count($keys);
            for( $i = 0; $i < $c; $i++ )
            {
                $F =& $submit_forms[ $keys[$i] ];
                $F['licenses'] = $lix;
            }
            $configs->SaveConfig('submit_forms',$submit_forms,'',false);
            $page->Prompt(_('Saved license info to each submit form'));
        }
    }
}

?>
