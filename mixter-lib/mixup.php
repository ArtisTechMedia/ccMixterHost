<?php

/*
  $Id: mixup.php 13835 2009-12-25 13:19:34Z fourstones $
*/

define('CC_EVENT_FORMAT_MIXUP', 'fmtmixup' );

define('CC_MIXUP_MODE_DISABLED',  1 );
define('CC_MIXUP_MODE_SIGNUP',    2 );
define('CC_MIXUP_MODE_MIXING',    3 );
define('CC_MIXUP_MODE_REMINDER',  4 );
define('CC_MIXUP_MODE_UPLOADING', 5 );
define('CC_MIXUP_MODE_DONE',      6 );
define('CC_MIXUP_MODE_CUSTOM',    7 );

define('CC_MIXUP_STATUS_DONE',     1 );
define('CC_MIXUP_STATUS_NOT_SURE', 2 );
define('CC_MIXUP_STATUS_CANT',     3 );
define('CC_MIXUP_STATUS_FLAKED',   4 );


CCEvents::AddHandler(CC_EVENT_MAP_URLS,        'mixup_onmapurls');
CCEvents::AddHandler(CC_EVENT_FORMAT_MIXUP,    'mixup_onfiltermixup');
CCEvents::AddHandler(CC_EVENT_API_QUERY_SETUP, 'mixup_onqapiquerysetup' ); 
CCEvents::AddHandler(CC_EVENT_UPLOAD_DONE,     'mixup_onuploaddone' );

function mixup_helper($admin_too=false)
{
    require_once('mixter-lib/mixup.inc');
    if( $admin_too )
        require_once('mixter-lib/mixup_admin.inc');
}

function mixup_api($action,$mixup_id=0,$arg='')
{
    mixup_helper();
    mixup_helper_api($action,$mixup_id,$arg);
}

function mixup_onuploaddone( $upload_id, $op )
{
    if( $op != CC_UF_NEW_UPLOAD )
    {
        return;
    }
    
    $user_id   = CCUser::CurrentUser();
    $mode_type = CC_MIXUP_MODE_UPLOADING;
    
    // is the current user signed up for a mixup that
    // is currently in 'uploading' mode?
    
    $sql =<<<EOF
        SELECT mixup_id, mixup_tag
            FROM cc_tbl_mixups
            JOIN cc_tbl_mixup_mode ON mixup_mode = mixup_mode_id
            JOIN cc_tbl_mixup_user ON mixup_id = mixup_user_mixup
            WHERE mixup_mode_type = {$mode_type} AND
            mixup_user_user = {$user_id}

EOF;

    $mixup_info = CCDatabase::QueryRows($sql);
    if( empty($mixup_info ) )
        return;

    $upload_tags = CCDatabase::QueryRow('SELECT upload_tags FROM cc_tbl_uploads WHERE upload_id='.$upload_id);
    // ::InTags wants a 'record'
    
    $table = new CCTable( 'cc_tbl_mixup_user', 'mixup_user_user');
    
    foreach( $mixup_info as $MI )
    {
        // Is this upload intended for the mixup in question?
        if( CCUploads::InTags($MI['mixup_tag'],$upload_tags) )
        {
            $w['mixup_user_user']  = $user_id;
            $w['mixup_user_mixup'] = $MI['mixup_id'];
            $args['mixup_user_upload'] = $upload_id;
            $table->UpdateWhere($args,$w);
            break;
        }
        
    }
}

function mixup_onqapiquerysetup( &$args, &$queryObj, $validate)
{
    if( empty($args['datasource']) ||
       (
            ($args['datasource'] != 'mixups') &&
            ($args['datasource'] != 'mixup_users')
       )
      )
    {
        if( !empty($args['mixup'])) {
            $urlp = ccl('people') . '/';

            $queryObj->sql_p['joins'][] = 'cc_tbl_mixup_user ON mixup_user_upload=upload_id';
            $queryObj->sql_p['joins'][] = 'cc_tbl_user mixee ON mixup_user_other=mixee.user_id';
            $queryObj->where[] = 'mixup_user_mixup = ' . $args['mixup'];
            $queryObj->columns[] = "IF( mixup_user_other,  CONCAT( '{$urlp}', mixee.user_name ), '' ) as mixee_page_url";
            $queryObj->columns[] = cc_fancy_user_sql('mixee_name', 'mixee');
        }
        return;
    }

    
    // The query engine ignores the 'sort' field for datasources it
    // does not know, so we hack in the SQL for our table
    
    if( empty($args['sort'])) {
        $args['sort'] = 'date';
    }
    
    $is_user = $args['datasource'] == 'mixup_users';

    // I think this will work ??
    if( !$is_user && !CCUser::IsAdmin() )
    {
        $queryObj->where[] = '(mixup_hidden <> 1) OR (mixup_hidden IS NULL)';
    }

    switch( $args['sort'] ) {
        case 'name':
            $queryObj->sql_p['order'] = $is_user ? 'user_name' : 'mixup_display';
            break;
        case 'mixer':
            $queryObj->sql_p['order'] = $is_user ? 'mixer.user_name' : 'mixup_date';
            break;
        case 'status':
            $queryObj->sql_p['order'] = $is_user ? 'mixup_user_confirmed' : 'mixup_date';
            break;
        default:
            $args['sort'] = 'date';
        case 'date':
            $queryObj->sql_p['order'] = $is_user ? 'mixup_user_date' : 'mixup_date';
            break;
    }
    
    if( empty($args['ord'])) {
        $args['ord'] = 'DESC';
    }
    
    $queryObj->sql_p['order'] .= ' ' . $args['ord'];

    // our 'user' is actually a remixer in mixup_user table
    
    if( !empty($args['user']) )
    {
        $queryObj->where[] = 'mixer.user_name = \'' . $args['user'] . '\'';
        unset($args['user']);
    }
    
    // We add a 'mixup' parameter for queries
    
    if( !empty($args['mixup']) ) {
        $field = $is_user ? 'mixup_user_mixup' : 'mixup_id';
        $queryObj->where[] = $field . ' = ' . $args['mixup'];
    }

}

function mixup_helper_addpatt($x)
{
    return '%' . $x . '%';
}

function mixup_onfiltermixup(&$rows)
{
    /*
     Several of the fields have %-% style patterns that need replacing.
     
     The replacement values are found in other fields
    */
    
    $c = count($rows);
    if( $c > 0 )
    {
        // these are the fields that potentially need replacing:
        $fields = array( 'mixup_desc_html', 'mixup_desc_plain', 'mixup_mode_desc_html', 'mixup_mode_desc_plain');
        
        // here's all the columns in each row:
        $cols = array_keys($rows[0]);

        // these are cols in the row that (actually) need replacing
        $need_replacing = array_intersect($fields,$cols);
        
        if( empty($need_replacing)) {
            // there's nothing to replace
            return;
        }
        
        // these are fields that potentially have replacement values:
        $replace_keys = array_diff($cols,$fields);

        // these are the (potentially) replacement fields with '%' surrounding the names
        $replace_pats = array_map('mixup_helper_addpatt',$replace_keys);

        // loop through all rows:
        $keys = array_keys($rows);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $rows[$keys[$i]];
            
            // create an array of the potential replace values
            $values = array();
            foreach( $replace_keys as $K )
            {
                $values[] = $R[$K];
            }

            // loop through 
            foreach( $need_replacing as $NR )
            {
                $R[$NR] = str_replace($replace_pats,$values,$R[$NR]);
            }
        }    
    }
}

function mixup_onmapurls()
{
    CCEvents::MapUrl( 'mixup',  'mixup_view', 
        CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '[mixup_name]', _('Main mixup display'),
        'mixup' );

    CCEvents::MapUrl( 'api/mixup',  'mixup_api', 
        CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{signup|remove|status}/mixup_id', _('ajax api for a mixup'),
        'mixup' );

    CCEvents::MapUrl( 'mixup/confirm',  'mixup_confirm', 
        CC_MUST_BE_LOGGED_IN, ccs(__FILE__), 'mixup_id', _('confirm a mixup user'),
        'mixup' );
    
    CCEvents::MapUrl( 'admin/mixup',  'mixup_admin', 
        CC_ADMIN_ONLY, ccs(__FILE__), '', _('admin mixup'),
        'mixup' );
    
    CCEvents::MapUrl( 'admin/mixup/massmail',  'mixup_admin_massmail', 
        CC_ADMIN_ONLY, ccs(__FILE__), '', _('admin mixup'),
        'mixup' );
}


function mixup_helper_get_mixup_confirm_opts()
{
    return array( 
                CC_MIXUP_STATUS_DONE     => "I'm done now or expect to finish in time",
                CC_MIXUP_STATUS_NOT_SURE => "I'm not 100% sure if I'll have it in time",
                CC_MIXUP_STATUS_CANT     => "I will definitely not be able to finish in time"
            );    
}

function mixup_confirm($mixup_id)
{
    mixup_helper();
    require_once('cchost_lib/cc-page.php');
    require_once('cchost_lib/cc-form.php');
    
    $page =& CCPage::GetPage();
    $page->SetTitle('Mixup Confirmation');

    $mixup_id = sprintf( '%0d', $mixup_id );
    if( empty($mixup_id) )
        die('Invalid mixup id');
    $mode = mixup_helper_get_mode_type($mixup_id);
    if( empty($mode) )
        die('Invalid mixup id');
    if( ($mode != CC_MIXUP_MODE_MIXING) && ($mode != CC_MIXUP_MODE_REMINDER) )
    {
        $page->Prompt('This mixup is not in mixing mode!');
        return;
    }
    $user_id = CCUser::CurrentUser();;
    $sql = "SELECT mixup_user_confirmed FROM cc_tbl_mixup_user WHERE mixup_user_mixup = {$mixup_id} AND mixup_user_user = {$user_id}";
    $row = CCDatabase::QueryRow($sql);
    if( empty($row) )
    {
        $page->Prompt('You are not signed up for the mixup.');
        return;
    }

    $form = new CCForm();
    $fields =  array(
                'mixup_user_confirmed' => array(
                    'label' => "Confirmation",
                    'formatter' => 'radio',
                    'options' => mixup_helper_get_mixup_confirm_opts(),
                    'value' => 2,
                    'flags' => CCFF_POPULATE
                )
            );

    $form->AddFormFields($fields);
    $form->SetHelpText('Please confirm whether you will be able to finish your remix assignment. If you can not, that\'s OK, we can make other arrangments');

    if( empty($_POST) || !$form->ValidateFields() )
    {
        if( !empty($row) )
            $form->PopulateValues($row);
        $page->AddForm( $form->GenerateForm() );
    }
    else
    {
        $form->GetFormValues($values);
        mixup_helper_update_confirm_remix($mixup_id,$user_id,$values['mixup_user_confirmed']);
        $prompt = 'Thanks for letting us know your status! Bookmark this page and use it again if your status changes.';        
        $mixurl = mixup_helper_get_mixup_url($mixup_id);
        $page->Prompt( $prompt . "<br /><br /><a href=\"{$mixurl}\">Go to mixup now...</a>");
    }
}

function mixup_helper_update_confirm_remix($mixup_id,$user_id,$status)
{
    $table  = new CCTable('cc_tbl_mixup_user','mixup_user_id');
    $args['mixup_user_user'] = $user_id;
    $args['mixup_user_mixup'] = $mixup_id;
    $key = $table->QueryKey($args);
    $uargs['mixup_user_id'] = $key;
    $uargs['mixup_user_confirmed'] = $status;
    $table->Update($uargs);
}


function mixup_admin($cmd='',$arg='')
{
    mixup_helper(true);
    mixup_admin_helper($cmd,$arg);
}



function mixup_view($mixup=null)
{
    require_once('cchost_lib/cc-query.php');
    $query = new CCQuery();
 
    if( !isset($mixup)  )
    {
        $mixup = 'all';
    }
    
    if( isset($mixup)) {
        
        if( $mixup === 'all' )
        {
            $args = $query->ProcessAdminArgs('t=mixup_all&title=Previous Secret Mixups&ord=desc');
        }
        else
        {
            if( !is_int($mixup)) {
                $mixup = CCDatabase::QueryItem('SELECT mixup_id FROM cc_tbl_mixups WHERE mixup_name="'.$mixup.'"');
            }
            $args = $query->ProcessAdminArgs('t=mixups&limit=1&paging=off&mixup='.$mixup);
        }
    }
    /*
    else {
        $args = $query->ProcessAdminArgs('t=mixups&limit=1&ord=asc&sort=date&paging=off');
    }
    */
    
    $query->Query($args);
    
}

function mixup_admin_massmail($mixup_id,$mode='')
{
    mixup_helper(true);
    mixup_helper_admin_massmail($mixup_id,$mode);

}

?>