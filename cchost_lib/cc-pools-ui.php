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
* $Id: cc-pools-ui.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* Module for admin management of sample pools
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to ccHost');

require_once('cchost_lib/cc-pools.php');


/**
*/
class CCPoolUI
{
    function Pool($pool_id='',$alpha='')
    {
        if( !empty($pool_id) )
        {
            $pool_id = sprintf('%0d',$pool_id);
            $pool_id = CCUtil::StripText($pool_id);
        }

        if( empty($pool_id) )
            return $this->ShowPools();

        $pools =& CCPools::GetTable();
        if( !$pools->KeyExists($pool_id) )
            return;

        if( !empty($alpha) )
            $alpha = $alpha{0};

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $pool = CCDatabase::QueryRow('SELECT pool_description,pool_name,pool_id,pool_site_url FROM cc_tbl_pools WHERE pool_id='.$pool_id);
        $page->SetTitle( 'str_pool_name_s', $pool['pool_name'] );
        $page->PageArg( 'pool_info', $pool );
        $this->_build_bread_crumb_trail($pool['pool_id'],$pool['pool_name']);

        $page->PageArg('pool_id',$pool_id,'pool_alpha');
        $page->PageArg('pool_alpha_char',$alpha);

        $where =<<<END
            (pool_item_pool = $pool_id) AND 
            ((pool_item_num_remixes > 0) OR (pool_item_num_sources > 0))
END;
        if( !empty($alpha) )
            $where .= " AND (pool_item_artist LIKE '{$alpha}%')";

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=pool_listing&sort=user&ord=ASC');
        $sqlArgs['where'] = $where;
        $query->QuerySQL($args,$sqlArgs);

    }
    
    function ShowPools()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->_build_bread_crumb_trail();
        $page->SetTitle('str_pools_link');
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=pools_list&datasource=pools&sort=');
        $query->Query($args);
    }

    function PoolHook($cmd='',$pool_id='')
    {
        $pool_id = sprintf('%d',$pool_id);
        if( empty($pool_id) )
            exit;
        
        switch($cmd)
        {
            case 'alpha':
            {
                $sql =<<<END
                    SELECT DISTINCT LOWER(SUBSTRING(`pool_item_artist`,1,1)) c
                       FROM `cc_tbl_pool_item` WHERE                  
                    (pool_item_pool = $pool_id) AND 
                    ((pool_item_num_remixes > 0) OR (pool_item_num_sources > 0))
                    ORDER BY c
END;
                $args= CCDatabase::QueryItems($sql);
                break;
            }
        }

        if( !empty($args) )
            CCUtil::ReturnAjaxData($args);
        exit;
    }

    function Item($pool_item_id='')
    {
        if( !empty($pool_item_id) )
            $pool_item_id = sprintf('0%d',$pool_item_id);
        if( empty($pool_item_id) )
            return;

        list( $pool_id, $pool_name, $pool_item_name ) = 
            CCDatabase::QueryRow('SELECT pool_id, pool_name, pool_item_name FROM cc_tbl_pool_item JOIN cc_tbl_pools '.
                                   'ON pool_item_pool=pool_id WHERE pool_item_id='.$pool_item_id,false);
        $this->_build_bread_crumb_trail($pool_id,$pool_name,$pool_item_id,$pool_item_name);

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=pool_item&ids='.$pool_item_id);
        $query->Query($args);
        $page =& CCPage::GetPage();
        $page->SetTitle( 'str_pool_item_page' );
    }

    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        require_once('cchost_lib/cc-admin.php');
        $title = _("Sample Pools Administration");
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);


        $args =
            array(
                array( 'action' => ccl( 'admin', 'pools', 'settings' ),
                       'menu_text' => _('Sample Pool Settings'),
                       'help' => _('Edit global settings for interacting with remote pools') ),
                array( 'action' => ccl( 'admin', 'pools', 'settings' ),
                       'menu_text' => _('Add Remote Sample Pool'),
                       'help' => _('Add a new remote pool for searching and interacting') ),
                array( 'action' => ccl( 'admin', 'pools', 'addwrapper' ),
                       'menu_text' => _('Create Sample Pool Wrapper'),
                       'help' => _('Wrap a remote pool for searching (items to be added manually)') ),
                array( 'action' => ccl( 'admin', 'pools', 'manage' ),
                       'menu_text' => _('Manage Sample Pools'),
                       'help' => _('Manage pools known to this site.') ),
                array( 'action' => ccl( 'admin', 'pools', 'approve' ),
                       'menu_text' => _('Approve Trackbacks'),
                       'help' => _('Approve trackbacks and remote remixes') ),
                array( 'action' => ccl( 'admin', 'trackbacks', 'manage' ),
                       'menu_text' => _('Manage Trackbacks'),
                       'help' => _('Edit, delete and otherwise manage remote remixes') ),
               );
        $page->PageArg('client_menu',$args,'print_client_menu');
    }

    function _delete_trackback($pool_item_id)
    {
        // For delete if the item has never been approved then it's just a matter of 
        // deleting the pool_item with the trackback info. However if the admin is
        // deleting an approved trackback, then we remove the pool_item, clear the tree 
        // and re-synch but will NOT remove the ttype-tags from any associated uploads.
        // This must be manually by the admins. (it is simply too much bookkeeping to
        // track all the ttype-tags that might or might not be relevant)
        //
        // So the lesson is: When you approve something make sure you really mean to
        //
        $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_pool_item WHERE pool_item_id='.$pool_item_id);
        if( empty($row) )
            return;
        
        CCDatabase::Query('DELETE FROM cc_tbl_pool_item WHERE pool_item_id='.$pool_item_id);
        $id = $pool_item_id;
        $uids = CCDatabase::QueryItems("SELECT pool_tree_parent FROM cc_tbl_pool_tree WHERE (pool_tree_pool_parent = $id) OR " 
                                        . "(pool_tree_pool_child = $id)");

        $uids = array_unique( array_filter($uids) );
        if( !empty($uids) )
        {
            CCDatabase::Query("DELETE FROM cc_tbl_pool_tree WHERE (pool_tree_pool_parent = $id) OR (pool_tree_pool_child = $id)");
            require_once('cchost_lib/cc-sync.php');
            foreach( $uids as $upload_id )
            {
                CCSync::Upload($upload_id);
            }
        }
    }

    function ApproveTrackback($pool_item_id,$upload_id=0)
    {
        //
        // for approval the trackbacks are pool items with the pool_item_approved set to 0
        // and inside pool_item_extra['upload_id'] is a comma separated list of upload_id's 
        // to attach the trackback to. This is done by createing a record in the
        // pool_tree table
        //
        // during approve we create tags on the upload based on pool_item_extra['ttype']
        //
        // The upload needs to be synch'd on num_pool remixes
        // 

        $sql = "UPDATE cc_tbl_pool_item SET pool_item_approved = 1, pool_item_num_sources =  (pool_item_num_sources+1) WHERE pool_item_id="
                     . $pool_item_id;

        CCDatabase::Query($sql);

        $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_pool_item WHERE pool_item_id='.$pool_item_id);
        $ex = empty($row['pool_item_extra']) ? array() : unserialize($row['pool_item_extra']);

        if( empty($upload_id) )
        {
            if( !empty($ex['upload_id']) )
                $need_tags = cc_split(',',$ex['upload_id']);
        }
        else
        {
            $need_tags = array($upload_id);
        }

        if( empty($need_tags) )
            return;

        require_once('cchost_lib/cc-sync.php');
        require_once('cchost_lib/cc-uploadapi.php');

        $pool_tree = new CCPoolTree();
        foreach( $need_tags as $upload_id )
        {
            $x['pool_tree_parent'] = $upload_id;
            $x['pool_tree_pool_child'] = $pool_item_id;
            $row = $pool_tree->QueryRow($x);
            if( empty($row) )
                $pool_tree->Insert($x);

            if( !empty($ex['ttype']) )
                CCUploadAPI::UpdateCCUD($upload_id,'trackback,in_' . $ex['ttype'] ,'');

            CCEvents::Invoke( CC_EVENT_TRACKBACKS_APPROVED, array( &$x ) );

            CCSync::Upload($upload_id);
        }
    }


    function ItemDelete($pool_item_id)
    {
        $this->_delete_trackback($pool_item_id);
        CCUtil::ReturnAjaxMessage(_('Pool item deleted'));
    }


    function Approve($submit='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        require_once('cchost_lib/cc-admin.php');
        $title = _("Approve Pending Trackbacks");
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'),'text'=>_("Sample Pools Administration")),
                                  array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        if( !empty($_POST['action'])  )
        {
            // this is called when admin wants to 1) approve, 2) delete or 3) noop a trackback

            $approved = array();
            $upload_ids = array();
            foreach( $_POST['action'] as $id => $action )
            {
                if( $action == 'nothing' )
                    continue;
                $id = CCUtil::StripText($id);
                if( $action == 'delete' )
                {
                    $this->_delete_trackback($id);
                }
                elseif( $action == 'approve' )
                {
                    $this->ApproveTrackback($id);
                }
            }
            
        }


        $pool_id = $this->GetWebSamplePool();
        $cce = ccl('admin','poolitem','edit') . '/';
        $sql =<<<EOF
        SELECT pool_item_id, pool_item_url, pool_item_name, pool_item_download_url,
               pool_item_extra, pool_name, pool_item_artist,
               CONCAT( '{$cce}', pool_item_id ) as item_edit_url
        FROM cc_tbl_pool_item
        JOIN cc_tbl_pools     ON pool_item_pool       = pool_id
        WHERE (pool_item_approved = 0) and pool_item_pool = {$pool_id}
EOF;

        $records = CCDatabase::QueryRows($sql);
        $ccl = ccl('files') . '/';
        $k = array_keys($records);
        $c = count($k);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$k[$i]];
            $R['pool_item_extra'] = unserialize($R['pool_item_extra']);
            if( !empty($R['pool_item_extra']['upload_id']) )
            {
                $sql =<<<EOF
                SELECT  upload_name,user_real_name,user_name,
                        CONCAT( '{$ccl}', user_name, '/', upload_id ) as file_page_url
                FROM cc_tbl_uploads   
                JOIN cc_tbl_user      ON upload_user          = user_id
                WHERE upload_id IN ({$R['pool_item_extra']['upload_id']})
EOF;
                $R['uploads'] = CCDatabase::QueryRows($sql);
            }
        }
//d($records);
        $page->PageArg('records',$records,'pool_approvals');
    }

    public static function GetWebSamplePool()
    {
        $pool_id = CCDatabase::QueryItem('SELECT pool_id FROM cc_tbl_pools WHERE pool_short_name = \'_web\'');
        if( empty($pool_id) )
        {
            require_once('cchost_lib/cc-pools.php');
            $pools = new CCPools();
            $a['pool_id'] = $pools->NextID();
            $a['pool_name'] = _('Trackback Sitings');
            $a['pool_short_name'] = '_web';
            $a['pool_description'] = _('People link to us!');
            // pool_api_url can ba a local module for searching the pool
            // classname:module_path
            // CCMagnatune:mixter-lib/mixter-magnatune.inc
            $a['pool_api_url'] = '';
            $a['pool_site_url'] = ccl();
            $a['pool_ip'] = '255.0.0.0';
            $a['pool_banned'] = 0;
            $a['pool_search'] = 0;
            $a['pool_default_license'] = '';
            $a['pool_auto_approve'] = 0;
            $pools->Insert($a);
            $pool_id = $a['pool_id'];
        }
        return $pool_id;
    }

    function Manage($pool_id='')
    {
        if( !empty($pool_id) )
        {
            $this->ManageItems($pool_id);
            return;
        }
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        require_once('cchost_lib/cc-admin.php');
        $title = _("Manage Sample Pools");
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'),'text'=>_("Sample Pools Administration")),array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        $pools =& CCPools::GetTable();
        $rows = $pools->QueryRows('');
        $args = array();
        foreach( $rows as $pool_row )
        {
            $args[] = array(
                     'actions' =>
                        array( 
                            array(
                                'action'    => ccl( 'admin', 'pool', 'edit', $pool_row['pool_id'] ),
                                'menu_text' =>  _('Propeties')
                               ),
                            array( 'action'    => ccl('admin','pools','manage', $pool_row['pool_id'] ),
                                   'menu_text' => _('Items')
                                )
                             ),
                       'help' => $pool_row['pool_name'] 
                     );
        }
        $page->PageArg('use_buttons', 1 );
        $page->PageArg('client_menu',$args,'print_client_menu');

    }

    function Settings()
    {
        require_once('cchost_lib/cc-feedreader.php');
        require_once('cchost_lib/cc-pools-forms.php');
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        require_once('cchost_lib/cc-admin.php');
        $title = _('Sample Pools Settings');
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'),'text'=>_("Sample Pools Administration")),array('url'=>'','text'=>$title));
        $page->SetTitle($title);


        $form = new CCAdminPoolsForm();
        $form->ValidateFields(); // you have to call this to get values out... hmmm
        $values = array();
        $form->GetFormValues($values);

        if( !empty($_POST['doitnow_pool-push-hub'] ) )
        {
            if( empty($values['pool-push-hub']) )
            {
                $form->SetFieldError( 'pool-push-hub', _("This can not be left blank.") );
            }
            else
            {
                $me = urlencode(ccl( 'api' ));
                $url = CCRestAPI::MakeUrl( $values['pool-push-hub'], 'poolregister', $me );
                $fr = new CCFeedStatusReader();
                $xml = $fr->cc_parse_url($url);
                //CCDebug::PrintVar($xml);
                if( $xml && ($xml->status['status'] == 'ok') )
                {
                    $page->Prompt(_('Registration with sample pool succeeded.'));
                }
            }
        }
        elseif( !empty($_POST['doitnow_pool-pull-hub'] ) )      
        {
            $form->GetFormValues($values);
            if( empty($values['pool-pull-hub']) )
            {
                $form->SetFieldError( 'pool-pull-hub', _("This can not be left blank.") );
            }
            else
            {
                $pools =& CCPools::GetTable();
                $where['pool_api_url'] = $values['pool-pull-hub'];
                if( $pools->CountRows($where) == 0 )
                {
                    $api = new CCPool();
                    $pool = $api->AddPool($where['pool_api_url']);
                    if( !$pool )
                        $pool = _('Could not add pool');
                    if( is_string($pool) )
                    {
                        $form->SetFieldError( 'pool-pull-hub', $pool );
                    }
                    else
                    {
                        $url = ccl( 'admin', 'pool', 'edit', $pool['pool_id'] );
                        CCUtil::SendBrowserTo($url);
                    }
                }
                else
                {
                    $page->Prompt(_("That Sample Pool is already registered here."));
                }
            }
        }
        elseif( !empty($_POST['adminpools']) )
        {
            $configs =& CCConfigs::GetTable();
            $configs->SaveConfig($this->_typename, $values);
            $page->Prompt(_("Settings saved"));
        }

        $page->AddForm( $form->GenerateForm() );
    }

    function ManageItems($pool_id)
    {
        $pool_id = sprintf('%d',$pool_id);
        if( empty($pool_id) )
            CCUtil::Send404();
        $names = CCDatabase::QueryRow('SELECT pool_short_name,pool_name FROM cc_tbl_pools WHERE pool_id='.$pool_id);
        $title = sprintf(_('Manage Pool Items for %s'),$names['pool_name']);
        $this->_manage_local_items($title,$names['pool_short_name']);
    }
    
    function TrackbackManage()
    {
        $title = _("Manage Trackbacks");
        $this->_manage_local_items($title,'_web');
    }

    function _manage_local_items($title,$pool_short_name)
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'),'text'=>_("Sample Pools Administration")),
                                  array('url'=>'','text'=>$title));
        $page->SetTitle($title);
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('t=pool_item_admin&match='.$pool_short_name.'&title='.$title.'&sort=id&ord=desc');
        $query->Query($args);
    }
    
    function AddWrapper()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _("Add Pool Wrapper");
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'),'text'=>_("Sample Pools Administration")),
                                  array('url'=> ccl('admin','pools','manage'), 'text'=> _("Manage Sample Pools")),
                                  array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        require_once('cchost_lib/cc-pools-forms.php');
        $form = new CCAddPoolWrapperForm();
        if( empty($_POST['addpoolwrapper']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            require_once('cchost_lib/cc-pools.php');
            $form->GetFormValues($info);
            $api = new CCPool();
            $api->AddPoolWrapper($info);
            $name = $info['pool_name'] . ' (' . $info['pool_short_name'] . ')';
            $page->Prompt(sprintf(_("New Pool Wrapper Added: %s"),$name));
            $this->Manage();
        }
        
    }
    
    function Edit($pool_id)
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _("Edit Pool Information");
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'), 'text'=>_("Sample Pools Administration")),
                                  array('url'=> ccl('admin','pools','manage'), 'text'=> _("Manage Sample Pools")),
                                  array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        require_once('cchost_lib/cc-pools-forms.php');

        $form = new CCAdminEditPoolForm();
        $show = true;

        $pools =& CCPools::GetTable();
        if( empty( $_POST['admineditpool'] ) )
        {
            $row = $pools->QueryKeyRow($pool_id);            
            $form->PopulateValues($row);
        }
        else
        {
            $show = !$form->ValidateFields();
        }

        if( $show )
        {
            if( CCPool::IsPoolWrapper($row['pool_api_url']) )
            {
                $local_fields = array(
                        'manage' => 
                           array(  'label'      => _('Manage Items'),
                                   'formatter'  => 'button',
                                   'url'        => ccl('admin', 'pools','manage',$pool_id),
                                   'flags'      => CCFF_NOUPDATE | CCFF_STATIC ),
                        'additems' => 
                           array(  'label'      => _('Add Items'),
                                   'formatter'  => 'button',
                                   'url'        => ccl('admin', 'pools','additems',$pool_id),
                                   'flags'      => CCFF_NOUPDATE | CCFF_STATIC ),
                    );
                $form->AddFormFields( $local_fields );
            }
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $values['pool_id'] = $pool_id;
            $pools->Update($values);
            $page->Prompt(_("Changes to pool saved"));
            $this->Manage();
        }
    }

    function AddItems($pool_id)
    {
        $pool_id = sprintf('%d',$pool_id);
        $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_pools WHERE pool_id='.$pool_id);    
        if( empty($row['pool_id'])  )
            CCUtil::Send404();
                    
        require_once('cchost_lib/cc-pools-forms.php');
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = sprintf(_('Add Item To %s Wrapper'),$row['pool_name']);
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'),'text'=>_("Sample Pools Administration")),
                                  array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        $form = new CCAddPoolItemsForm();
        if( empty($_POST['addpoolitems']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $items = $_POST['pi'];
            $keys = array_keys($items);
            for( $i = 0; $i < count($keys); $i++ )
            {
                $I =& $items[ $keys[$i] ];
                $I['pool_item_pool'] = $pool_id;
                $I['pool_item_approved'] = 1;
            }
            $table =& CCPoolItems::GetTable();
            $columns = $form->_get_column_order();
            $columns[] = 'pool_item_pool';
            $columns[] = 'pool_item_approved';
            $table->InsertBatch($columns,$items);
            CCUtil::SendBrowserTo( ccl('admin','pools','manage',$pool_id) );
        }        
    }
    
    function ItemEdit($pool_item)
    {
        $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_pool_item WHERE pool_item_id='.$pool_item);
        if( empty($row) )
            CCUtil::Send404();
        $row['pool_item_extra'] = empty($row['pool_item_extra']) ? array() : unserialize($row['pool_item_extra']);
        require_once('cchost_lib/cc-form.php');
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _('Edit Pool Item');
        CCAdmin::BreadCrumbs(true,array('url'=> ccl('admin','pools'),'text'=>_("Sample Pools Administration")),
                                  array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        $form = new CCGenericForm();
        $fields = array();
        foreach( array( 'pool_item_url' => _('Page URL'), 'pool_item_download_url' => _('Download URL'), 
            'pool_item_description' => _('Description'), 'pool_item_name' => _('Name'),
            'pool_item_artist' => _('Artist'), 'pool_item_approved' => _('Approved') ) as $field => $name)
        {
            $fields[$field] = array(
                    'label' => $name,
                    'formatter' => 'textedit',
                    'value' => $row[$field],
                    'flags' => CCFF_NONE 
                    );
        }
        foreach( array( 'ttype' => _('Link Type'), 'poster' => _('Poster'), 'email' => _('email') ) as $field => $name )
        {
            if( !empty($fields[$field]) )
            {
                $fields[$field] = array(
                        'label' => $name,
                        'formatter' => 'textedit',
                        'value' => $row['pool_item_extra'][$field],
                        'flags' => CCFF_NONE 
                        );
            }
        }

        $fields['embed'] = array(
                'label' => 'Embed code',
                'formatter' => 'textarea',
                'value' => empty($row['pool_item_extra']['embed']) ? '' : htmlentities($row['pool_item_extra']['embed']),
                'flags' => CCFF_NONE 
                );

        $form->AddFormFields($fields);
        //[pool_item_license] => noncommercial

        if(  empty($_POST['generic']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $values['pool_item_extra'] = $row['pool_item_extra'];
            foreach( array( 'ttype', 'poster', 'email', 'embed' ) as $field )
            {
                if( isset($values[$field]) )
                {
                    $values['pool_item_extra'][$field] = $values[$field];
                    unset($values[$field]);
                }
            }
            $values['pool_item_extra'] = serialize($values['pool_item_extra']);
            $values['pool_item_id'] = $pool_item;
            $table = new CCPoolItems();
            $table->Update($values);
            $form->SendToReferer(); // this will exit if possible
            $url = ccl('pools','item',$pool_item); // otherwise go here...
            CCUtil::SendBrowserTo($url);
        }
    }

    function Delete($pool_id)
    {
        /*
        $pools =& CCPools::GetTable();
        $pools->DeleteKey($pool_id);
        CCPage::Prompt("Pool deleted");
        */
        $page =& CCPage::GetPage();
        $page->Prompt(_("This is not implemented."));
    }

    /**
    * Event hander for {@link CC_EVENT_DELETE_UPLOAD}
    * 
    * @param array $record Upload database record
    */
    function OnUploadDelete( &$row )
    {
        $id = $row['upload_id'];
        $where = "(pool_tree_parent = $id) OR (pool_tree_child = $id)";
        $tree = new CCPoolTree();
        $tree->DeleteWhere($where);
    }

    function _build_bread_crumb_trail($pool_id='',$pool_name='',$pool_item='',$pool_item_name='')
    {
        $trail = array();
        $trail[] = array( 'url' => ccl(), 'text' => 'str_home' );
        if( $pool_id )
        {
            $trail[] = array( 'url' => ccl('pools'), 'text' => 'str_pools_link' );
            if( $pool_item )
            {
                $trail[] = array( 'url' => ccl('pools','pool',$pool_id), 'text' => $pool_name );
                $trail[] = array( 'url' => '', 'text' => $pool_item_name );
            }
            else
            {
                $trail[] = array( 'url' => '', 'text' => $pool_name );
            }

        }
        else
        {
            $trail[] = array( 'url' => '', 'text' => 'str_pools_link' );
        }

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }



    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp( 'pools', 'pool_hook'),     array( 'CCPoolUI', 'PoolHook'), 
            CC_DONT_CARE_LOGGED_IN , ccs(__FILE__) , '{poolid},{cmd}',
            _('Ajax callback hook'), CC_AG_SAMPLE_POOL); 
        CCEvents::MapUrl( ccp( 'pools'),     array( 'CCPoolUI', 'Pool'),
            CC_DONT_CARE_LOGGED_IN , ccs(__FILE__) , '{poolid}', 
            _('List sample pools'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'pools', 'pool'),     array( 'CCPoolUI', 'Pool'),    
            CC_DONT_CARE_LOGGED_IN , ccs(__FILE__) , '{poolid}', 
            _('Show sample pool'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'pools', 'item' ),    array( 'CCPoolUI', 'Item'),    
            CC_DONT_CARE_LOGGED_IN , ccs(__FILE__) , '{poolitemid}', 
            _('Show a sample pool item'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'pools', 'search' ),    array( 'CCPool', 'Search'),
            CC_DONT_CARE_LOGGED_IN , 'cchost_lib/cc-pools.php' , '{poolid}', 
            _('Search a given pool (perhaps remotely) with a string'), CC_AG_SAMPLE_POOL );

        CCEvents::MapUrl( ccp( 'admin', 'pools'),                array( 'CCPoolUI', 'Admin'),    
            CC_ADMIN_ONLY , ccs(__FILE__) , '',
            _('Display admin pools menu'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pools', 'settings' ),   array( 'CCPoolUI', 'Settings'), 
            CC_ADMIN_ONLY , ccs(__FILE__) , '',
            _('Display pool admin settings form'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pools', 'manage' ),     array( 'CCPoolUI', 'Manage'),   
            CC_ADMIN_ONLY , ccs(__FILE__) , '', _('Display list of pools to admin'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pool',  'edit' ),       array( 'CCPoolUI', 'Edit'),     
            CC_ADMIN_ONLY , ccs(__FILE__) , '{poolid}', _('Edit properties of pool'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pools',  'addwrapper' ),       array( 'CCPoolUI', 'AddWrapper'),     
            CC_ADMIN_ONLY , ccs(__FILE__) , '', _('Add a pool wrapper'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pools',  'additems' ),  array( 'CCPoolUI', 'AddItems'),     
            CC_ADMIN_ONLY , ccs(__FILE__) , '', _('Edit a pool wrapper\'s items'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pool',  'delete' ),     array( 'CCPoolUI', 'Delete'),   
            CC_ADMIN_ONLY , ccs(__FILE__) , '{poolid}', _('Delete a sample pool'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pools', 'approve' ),    array( 'CCPoolUI', 'Approve'),   
            CC_ADMIN_ONLY , ccs(__FILE__) , '', _('Display admin pool approval menu'), 
            CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'pools', 'approve', 'item' ),    
            array( 'CCPoolUI', 'ApproveItem'),   CC_ADMIN_ONLY , ccs(__FILE__) , '{poolitem}', 
            _('Approve a remote remix'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'poolitem',  'edit' ),  array( 'CCPoolUI', 'ItemEdit'),     
            CC_ADMIN_ONLY , ccs(__FILE__) , '{poolitemid}', _('Edit properties of pool item'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp( 'admin', 'poolitem',  'delete' ),  array( 'CCPoolUI', 'ItemDelete'),     
            CC_ADMIN_ONLY , ccs(__FILE__) , '{poolitemid}', _('Delete pool item'), CC_AG_SAMPLE_POOL );

        CCEvents::MapUrl( ccp( 'admin', 'trackbacks', 'manage'  ),  array( 'CCPoolUI', 'TrackbackManage'),     
            CC_ADMIN_ONLY , ccs(__FILE__) , '', _('Manage latest trackbacks'), CC_AG_SAMPLE_POOL );
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        if( $scope == CC_LOCAL_SCOPE )
            return;

        global $CC_GLOBALS;
        
        $enabled = empty($CC_GLOBALS['allow-pool-ui']) ? false : $CC_GLOBALS['allow-pool-ui'];

        if( $enabled )
        {
            $items += array( 
                'pool' => array( 'menu_text'  => _('Sample Pools'),
                                 'menu_group' => 'configure',
                                 'help' => _('Managing sample pools and trackbacks'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 10000,
                                 'action' =>  ccl('admin','pools')
                                 ),
                    );
                    
        }
    }

}

?>
