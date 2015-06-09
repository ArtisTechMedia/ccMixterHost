<?

require_once('cchost_lib/cc-roles.inc');
require_once('cchost_lib/cc-uploadapi.php');

define('CCPLUS_OPTIN_KEY', 'ccplus');

define('SAMPLES_TAGGED', 'samples_tagged');
define('REMIXES_TAGGED', 'remixes_tagged');
define('REMIXES_UNTAGGED', 'remixes_untagged');
define('SUSPICIOUS_REMIXES', 'suspicious');
define('HAND_VERIFIED', 'verified');

function debugP($str)
{
   // print($str);
}

function ccPlusOptInUser($username,$update_uploads=true)
{
    ccRolesAssignRoleToUser(CCUser::IDFromName($username),CCPLUS_OPTIN_KEY);
    if( $update_uploads )
    {
        return array_merge_recursive( ccPlusTagNoSourceUploadsForUser($username), 
                                        ccPlusCheckAndFixTagForRemixesForUser($username) );
    }
}

function ccPlusOptOutUser($username)
{
    ccRoleUnassignRoleToUser(CCUser::IDFromName($username),CCPLUS_OPTIN_KEY);
}

function ccPlusUserIsOptedIn($username)
{
    return ccRoleUserHasRole(CCUser::IDFromName($username),CCPLUS_OPTIN_KEY);
}

function ccPlusMarkUploadsAsVerified($ids)
{
    foreach( $ids as $id )
    {
        ccPlusMarkUploadAsVerified($id);
    }
}

function ccPlusMarkUpload($id)
{
    CCUploadAPI::UpdateCCUD( $id,   'ccplus', 
                                    'ccplus_verified, ccplus_verify, ccplus_nooptin, ');
    debugP('d');
}

function ccPlusUnmarkUpload($id)
{
    CCUploadAPI::UpdateCCUD( $id,   '', 
                                    'ccplus,ccplus_verified, ccplus_verify, ccplus_nooptin, ');
    debugP('q');
}

function ccPlusMarkUploadAsVerified($id)
{
    CCUploadAPI::UpdateCCUD( $id,   'ccplus_verified, ccplus', 
                                    'ccplus_verify, ccplus_nooptin, ');
    debugP('v');
}

function ccPlusMarkUploadAsSuspicious($id)
{
    CCUploadAPI::UpdateCCUD( $id, 'ccplus_verify', 
                                  'ccplus,ccplus_verified,ccplus_nooptin' );
    debugP('s');
}

function ccPlusUserIDs()
{
    return ccRoleUsersWithRole(CCPLUS_OPTIN_KEY);
}

function ccPlusTagNoSourceUploadsForAllUsers()
{
    $user_ids = ccPlusUserIDs();
    $results = array();
    foreach( $user_ids as $user_id )
    {
        $results = array_merge_recursive( $results, ccPlusTagNoSourceUploadsForUser($user_id) );
    }
    
    return $results;
}

function ccPlusCheckAndFixTagForRemixesForAllUsers()
{
    $user_ids = ccPlusUserIDs();
    return ccPlusCheckAndFixTagForRemixesForUsers($user_ids);
}

function ccPlusCheckAndFixTagForRemixesForUsers($user_ids)
{
    $results = array();
    foreach( $user_ids as $user_id )
    {
        $results = array_merge_recursive( $results, ccPlusCheckAndFixTagForRemixesForUser($user_id) );
    }
    
    return $results;
}

function ccPlusTagAllUploadsForUser($user_id)
{
    return array_merge( ccPlusTagNoSourceUploadsForUser($user_id),
                ccPlusCheckAndFixTagForRemixesForUser($user_id) );
}

function ccPlusTagNoSourceUploadsForUser($user_id)
{
    $sql = <<<EOF
SELECT upload_id FROM cc_tbl_uploads 
    WHERE upload_num_sources = 0 AND 
          upload_num_pool_sources = 0 AND 
          upload_tags NOT LIKE '%,ccplus,%' AND 
          upload_user = '{$user_id}'
EOF;
          
    $upload_ids = CCDatabase::QueryItems($sql);
    
    //print_r($upload_ids);
    
    foreach( $upload_ids as $upload_id )
    {
        debugP('+');
        ccPlusMarkUpload($upload_id);
    }

    return array( SAMPLES_TAGGED =>  $upload_ids );
}

function ccPlusCheckAndFixTagForRemixesForUser($user_id)
{
    $inner_select =  <<<EOF
SELECT tree_child AS remix_id, 
    IF( remix.upload_num_sources = SUM( IF( sources.upload_tags REGEXP ',cc(plus|zero),', 1, 0 ) ), 1, 0 ) as sources_tagged, 
    IF( remix.upload_tags LIKE '%,ccplus,%', 1, 0 ) as remix_tagged, 
    IF( remix.upload_tags LIKE '%,ccplus_verified,%', 1, 0 ) remix_verified
    FROM cc_tbl_tree 
        JOIN cc_tbl_uploads remix   ON tree_child  = remix.upload_id 
        JOIN cc_tbl_uploads sources ON tree_parent = sources.upload_id 
    WHERE remix.upload_user = '{$user_id}' 
    GROUP BY remix_id
    
EOF;

    $sql = <<<EOF
SELECT DISTINCT remix_id, pool_tree_child, remix_tagged, remix_verified FROM ({$inner_select}) as t_table 
    LEFT OUTER JOIN cc_tbl_pool_tree ON pool_tree_child = remix_id 
        WHERE (
            (pool_tree_child IS NULL AND remix_tagged != sources_tagged)
         OR (pool_tree_child IS NOT NULL AND remix_tagged = 1)
        )
EOF;
                 
    $rows = CCDatabase::QueryRows($sql);
    $tagged = array();
    $untagged = array();
    $suspiciousRemixes = array();
    foreach( $rows as $row )
    {
        $upload_id = $row['remix_id'];
        
        if( $row['remix_verified'] ) 
        {
            debugP('@');        
        }
        else if(  $row['pool_tree_child'] )
        {
            // This remix uses a sample pool
            // AND was tagged as ccplus for some reason 
            ccPlusUnmarkUpload($upload_id);
            array_push( $untagged, $upload_id );
            debugP('p');
        }
        else if( $row['remix_tagged'] )
        {
            // This remix uses invalid local sources
            // AND was tagged as ccplus for some reason 
            ccPlusMarkUploadAsSuspicious($upload_id);
            array_push( $suspiciousRemixes, $upload_id );
        }
        else
        {
            ccPlusMarkUpload($upload_id);
            array_push( $tagged, $upload_id );
            debugP('=');
        }
    }
    
    return array( REMIXES_TAGGED => $tagged, 
                  REMIXES_UNTAGGED => $untagged, 
                  SUSPICIOUS_REMIXES => $suspiciousRemixes );
}

