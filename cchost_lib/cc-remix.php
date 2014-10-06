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
* $Id: cc-remix.php 12720 2009-06-04 08:23:02Z fourstones $
*
*/

/** 
* Module for handling Remix UI
*
* @package cchost
* @subpackage upload
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
 * Remix API
 *
 */
class CCRemix
{
    /**
    * Display UI for managing remix ('I Sampled This') list
    *
    * @param integer $upload_id Uplaod ID to edit remixes for
    */
    function EditRemixes($upload_id)
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-pools.php');
        require_once('cchost_lib/cc-upload.php');
        require_once('cchost_lib/cc-remix-forms.php');
        require_once('cchost_lib/cc-page.php');

        CCUpload::CheckFileAccess(CCUser::CurrentUserName(),$upload_id);

        $uploads =& CCUploads::GetTable();
        $name = $uploads->QueryItemFromKey('upload_name',$upload_id);
        $msg = array('str_remix_editing',$name);
        $this->_build_bread_crumb_trail($upload_id,$msg);
        CCPage::SetTitle($msg);

        $form = new CCEditRemixesForm($upload_id);
        $show = false;
        if( empty($_REQUEST['editremixes']) )
        {
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            // this will do a AddForm if it has to
            $this->OnPostRemixForm($form, '', '', $upload_id);
        }
    }

    /**
    * @access private
    */
    function _build_bread_crumb_trail($upload_id,$text)
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

        $trail[] = array( 'url' => ccl('files',$user_name, $upload_id), 
                          'text' => '"' . $upload_name . '"' );

        $trail[] = array( 'url' => ccl('files','edit', $user_name, $upload_id), 
                           'text' => 'str_file_edit');

        $trail[] = array( 'url' => '', 'text' => $text );

        CCPage::AddBreadCrumbs($trail);
    }


    /**
    * Function called in repsonse to submit on a remix form
    *
    * @param object &$form CCForm object
    * @param string $relative_dir Target directory of upload
    * @param string $ccud System tag to attach to upload
    * @param integer $remixid Upload id of remix editing
    */
    function OnPostRemixForm(&$form, $relative_dir, $ccud = CCUD_REMIX, $remixid = '')
    {
        require_once('cchost_lib/cc-pools.php');
        require_once('cchost_lib/cc-sync.php');
        require_once('cchost_lib/cc-tags.php');

        $is_update  = !empty($remixid);
        $uploads    =& CCUploads::GetTable();
        $pool_items =& CCPoolItems::GetTable();

        $remix_sources = array();
        $pool_sources  = array();

        $have_sources =  CCRemix::_check_for_sources( 'remix_sources', $uploads,    $form, $remix_sources );
        $have_sources |= CCRemix::_check_for_sources( 'pool_sources',  $pool_items, $form, $pool_sources );

        if( ($is_update || $have_sources) && $form->ValidateFields() )
        {
            $remixes = new CCTable('cc_tbl_tree','nonesense');
            $pool_tree = new CCTable('cc_tbl_pool_tree','nonesene');

            if( $is_update )
            {
                CCSync::RemixDetach($remixid);
                $where1['tree_child'] = $remixid;
                $remixes->DeleteWhere($where1);
                $where2['pool_tree_child'] = $remixid;
                $pool_tree->DeleteWhere($where2);
            }
            else
            {
                $remixid = CCUpload::PostProcessNewUploadForm($form,
                                                               $ccud,
                                                               $relative_dir,
                                                               $remix_sources);
            }


            if( $remixid )
            {
                CCRemix::_update_remix_tree('remix_sources', $remixid, 'tree_parent', 
                                        'tree_child', $remixes);

                CCRemix::_update_remix_tree('pool_sources', $remixid, 
                                        'pool_tree_pool_parent', 'pool_tree_child', $pool_tree);

                if( $is_update )
                {
                    // license might have changed
                    if( $have_sources )
                    {
                        $upargs['upload_license'] = $form->GetFormValue('upload_license');
                        $upargs['upload_id'] = $remixid;
                        $uploads->Update($upargs);
                    }

                    // ccud might have changed...
                    // for both license and ccud let's just recalc all the tags...

                    $current_tags = $uploads->QueryItemFromKey('upload_tags',$remixid);

                    if( CCTag::InTag( CCUD_ORIGINAL . ',' . CCUD_REMIX, $current_tags ) )
                    {
                        require_once('cchost_lib/cc-uploadapi.php');

                        $ccuda = array( CCUD_ORIGINAL, CCUD_REMIX  );

                        CCUploadAPI::UpdateCCUD( $remixid, $ccuda[$have_sources], $ccuda[!$have_sources] );
                    }
                }

                
                $url = ccl('files', CCUser::CurrentUserName(), $remixid );

                if( !empty($pool_sources) )
                {
                    CCSync::PoolSourceRemix($pool_sources);
                    CCPool::NotifyPoolsOfRemix($pool_sources,$url);
                }

                CCSync::Remix($remixid,$remix_sources);

                CCEvents::Invoke(CC_EVENT_SOURCES_CHANGED, array( $remixid, &$remix_sources) );

                if( $is_update )
                {
                    $user_name = CCDatabase::QueryItem('SELECT user_name FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$remixid);
                    CCUtil::SendBrowserTo(ccl('files',$user_name,$remixid));
                }
                else
                {
                    CCUpload::ShowAfterSubmit($remixid);
                }
                return false;
            }
        }

        require_once('cchost_lib/cc-page.php');
        CCPage::AddForm( $form->GenerateForm() );
        return false;
    }


    /**
    * @access private
    */
    function _update_remix_tree($field, $remixid, $parentf, $childf, &$table)
    {
        if( empty($_POST[$field]) )
            return;

        $sourceids = array_keys($_POST[$field]);
        if( empty($sourceids) )
            return;

        $all_fields = array();
        foreach( $sourceids as $sourceid )
        {
            $fields = array();
            $fields[$parentf] = CCUtil::StripText($sourceid);
            $fields[$childf]  = $remixid;
            $all_fields[] = $fields;
        }

        $table->InsertBatch( array($parentf, $childf), $all_fields );
        return($sourceids);
    }

    /**
    * @access private
    */
    function _check_for_sources( $field, &$table, &$form, &$remix_sources )
    {
        if( !empty($_POST[$field]) )
        {
            //
            // This means the user has actually identified and 
            // checked off some remix sources
            //
            $remix_check_boxes = array_keys($_POST[$field]);
            if( $field == 'pool_sources' )
            {
                $remix_sources = $table->QueryRowsFromKeys($remix_check_boxes,true);
            }
            else
            {
                $sources_set = join(',',$remix_check_boxes);
                $sql =<<<EOF
                SELECT upload_id, user_real_name, user_name, upload_name, upload_user, upload_contest, upload_tags
                FROM cc_tbl_uploads
                JOIN cc_tbl_user ON upload_user = user_id
                WHERE upload_id IN ({$sources_set})
EOF;
                $remix_sources = CCDatabase::QueryRows($sql);
            }
            if( !empty($remix_sources) )
            {
                $form->SetTemplateVar( $field, $remix_sources );
                return(true);
            }
        }
        return( false );
    }

    /**
    * Calculate the strictest license given a set of uploads
    *
    */
    function RemixLicenses()
    {
        global $CC_GLOBALS;
        $rowx = CCRemix::GetStrictestLicense($_GET['remix_sources'],$_GET['pool_sources']);

        $row = array();
        foreach( array('license_id','license_url','license_name') as $K )
            $row[$K] = $rowx[$K];
        $configs =& CCConfigs::GetTable();
        $lic_waiver = $configs->GetConfig('lic-waiver');
        if( !empty($lic_waiver['waivers']) )
        {
            $waivers = array_keys($lic_waiver['waivers']);
            if( in_array($row['license_id'],$waivers) )
            {
                $lics = array_keys($lic_waiver['licenses']);
                if( !in_array($row['license_id'],$lics) )
                    $lics[] = $row['license_id'];
                $lics = "'" . join("','",$lics) . "'";
                $lics = CCDatabase::QueryRows('SELECT license_id,license_name,license_url FROM cc_tbl_licenses WHERE ' .
                                            " license_id IN ({$lics}) ");
                $row['options'] = $lics;
            }
        }                
        CCUtil::ReturnAjaxData($row);
    }

    function GetStrictestLicenseForUpload($upload_id)
    {
        $remix_sources = CCDatabase::QueryItems('SELECT tree_parent FROM cc_tbl_tree WHERE tree_child = '.$upload_id);
        $remix_sources = empty($remix_sources) ? '' : join(',',$remix_sources);
        $pool_sources  = CCDatabase::QueryItems('SELECT pool_tree_pool_parent FROM cc_tbl_pool_tree WHERE pool_tree_child = '.$upload_id);
        $pool_sources  = empty($pool_sources) ? '' : join(',',$pool_sources);
        if( empty($remix_sources) && empty($pool_sources) )
        {
            return array();
        }
        $row = CCRemix::GetStrictestLicense($remix_sources,$pool_sources);
        return $row;
    }

    function GetStrictestLicense($remix_sources,$pool_sources)
    {
        $rows_r = $rows_p = array();
        if( !empty($remix_sources) )
        {
            $sql = 'SELECT DISTINCT upload_license FROM cc_tbl_uploads WHERE upload_id IN (' . $remix_sources . ')';
            $rows_r = CCDatabase::QueryItems($sql);
        }
        if( !empty($pool_sources) )
        {
            $sql = 'SELECT DISTINCT pool_item_license FROM cc_tbl_pool_item WHERE pool_item_id IN (' . $pool_sources . ')';
            $rows_p = CCDatabase::QueryItems($sql);
        }
        $rows = array_unique(array_merge($rows_r,$rows_p));
        require_once('cchost_lib/cc-lics-chart.inc');
        $license = '';
        foreach( $rows as $L )
        {
            $license = $license ? cc_stricter_license( $L, $license ) : $L;
        }
        $lics = new CCTable('cc_tbl_licenses','license_id');
        $row = $lics->QueryKeyRow($license);
        return $row;
    }

    /**
    * Event hander for {@link CC_EVENT_DELETE_UPLOAD}
    * 
    * @param array $record Upload database record
    */
    function OnUploadDelete( &$row )
    {
        $id = $row['upload_id'];
        $where = "(tree_parent = $id) OR (tree_child = $id)";
        $tree = new CCTable('cc_tbl_tree','nonesense');
        $tree->DeleteWhere($where);
    }


    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('file','remixes'), array( 'CCRemix', 'EditRemixes'), 
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{upload_id}', _("Displays 'Manage Remixes' for upload"), CC_AG_UPLOAD );
        CCEvents::MapUrl( ccp('remixlicenses'), array( 'CCRemix', 'RemixLicenses'), 
            CC_MUST_BE_LOGGED_IN, ccs(__FILE__), '{upload_id}', _("Ajax callback to calculate licenses"), CC_AG_UPLOAD );
    }


}


?>
