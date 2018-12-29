<?

require_once('cchost_lib/cc-roles.inc');
require_once('cchost_lib/cc-uploadapi.php');
require_once('cchost_lib/cc-tags.php');

define('CCPLUS_OPTIN_KEY', 'ccplus');

/*
    user opt-in

    when a user is opted-in, then every new upload and change
    to upload is monitored for elligability in ccPlus
*/

function ccPlusOptInUser($username, $update_uploads=false)
{
    ccRolesAssignRoleToUser(CCUser::IDFromName($username),CCPLUS_OPTIN_KEY);
    if( $update_uploads )
    {
        ccPlusTagAllUploadsForUser($username);
    }
}

function ccPlusOptOutUser($username)
{
    ccRoleUnassignRoleToUser(CCUser::IDFromName($username),CCPLUS_OPTIN_KEY);
}

function ccPlusUserIsOptedIn($username)
{
    return !empty($username) && ccRoleUserHasRole(CCUser::IDFromName($username),CCPLUS_OPTIN_KEY);
}

function ccPlusUserIDs()
{
    return ccRoleUsersWithRole(CCPLUS_OPTIN_KEY);
}

/*
    upload opt-in

    when an upload is marked as opt-in, then every change to it
    is monitored for elligibality in ccPlus
*/
function ccPlusOptInUpload($upload_id,$tag_and_verifty=true) 
{
    $uploads =& CCUploads::GetTable();
    $uploads->SetExtraField($upload_id,CCPLUS_OPTIN_KEY,true);
    if( $tag_and_verifty) {
        return ccPlusTagTree($upload_id);
    }
    return -1;
}

function ccPlusOptOutUpload($upload_id)
{
    $uploads =& CCUploads::GetTable();
    $uploads->UnsetExtraField($upload_id,CCPLUS_OPTIN_KEY);
    ccPlusUnmarkUpload($upload_id);
}

function ccPlusUploadIsOptedIn($upload_id)
{
    $uploads =& CCUploads::GetTable();
    return $uploads->GetExtraField($upload_id,CCPLUS_OPTIN_KEY);
}

/*
    Verify elligibility and tag an upload and do the same for
    all of its sources
*/
function ccPlusTagTree($upload_id) {
    return ccPlusVerifyTree($upload_id,true,0);
}

function ccPlusVerifyTree($upload_id,$tagSources=true,$recurseLevel=0) {
    $sql1 =<<<EOF
        SELECT upload_num_sources, upload_name, upload_num_pool_sources,
            upload_tags, upload_published, upload_banned,
            upload_user, upload_id
        FROM cc_tbl_uploads
        WHERE upload_id = '{$upload_id}'
EOF;
    $R = CCDatabase::QueryRow($sql1);


    if( preg_match('/,ccplus,/',$R['upload_tags']) ) {
        return true;
    }
    
    //print ( sprintf("%s - %s\n",$R['upload_id'],$R['upload_name']) );

    if( !ccPlusUserIsOptedIn($R['upload_user']) || 
        !ccPlusUploadIsOptedIn($upload_id) ||
        !$R['upload_published'] || 
        $R['upload_banned'] || 
        $R['upload_num_pool_sources'] > 0 ) 
    {
        return false;
    }

    $source_ids = CCDatabase::QueryItems("SELECT tree_parent FROM cc_tbl_tree WHERE tree_child = {$upload_id}");

    $has_sources = count($source_ids) > 0;

    $isRemix = preg_match('/,remix,/',$R['upload_tags']);

    if( $isRemix || $has_sources ) {

        // not everything that has sources is a 'remix'
        // e.g. extended_mix, site_promo

        $ok = $has_sources;

        // The sources might no longer available on the site
        // so we can't assume they were ccPlus

        if( $ok ) {
            foreach ($source_ids as $source_id ) {
                if( !ccPlusVerifyTree($source_id,$tagSources,$recurseLevel+1) ) { // N.B. recursion
                    $ok = false;
                    break;
                }
            }
        }

        if( !$ok ) {
            // this is a remix with non-ccPlus sources

            // stems are (hopefully) marked as sample or pell
            // if those tags are not present then the extra files might
            // just be alt takes, previews or formats a remix
            
            if( _ccPlus_has_stems($R) ) {
                // there are stems so we assume they are legit ccPlus
                if( $recurseLevel == 0 || $tagSources) {
                    ccPlusMarkUploadAsStem($upload_id);
                }
                return true;
            } else {
                // no stems here or they are not samples/pells
                return false;
            }
        }
    }

    if( $recurseLevel == 0 || $tagSources) {
        ccPlusMarkUpload($upload_id);
    }
    return true;
}

/*
    Upload tagging
*/
function ccPlusMarkUploadsAsVerified($ids)
{
    foreach( $ids as $id )
    {
        ccPlusMarkUploadAsVerified($id);
    }
}


function ccPlusMarkUpload($id)
{
    CCUploadAPI::UpdateCCUD( $id, 'ccplus', _ccPlus_tags_minus('ccplus'), false );
}

function ccPlusUnmarkUpload($id)
{
    CCUploadAPI::UpdateCCUD( $id, '', _ccPlus_tags_minus(''), false );
}

function ccPlusMarkUploadAsVerified($id)
{
    $arr = array( 'ccplus_verified', 'ccplus');
    CCUploadAPI::UpdateCCUD( $id,  $arr, _ccPlus_tags_minus($arr), false );
}

function ccPlusMarkUploadAsStem($id)
{
    $arr = array( 'ccplus_stem', 'ccplus');
    CCUploadAPI::UpdateCCUD( $id, $arr, _ccPlus_tags_minus($arr), false );
}

function ccPlusMarkUploadAsSuspicious($id)
{
    CCUploadAPI::UpdateCCUD( $id, 'ccplus_verify', _ccPlus_tags_minus('ccplus_verify'), false );
}

function ccPlusTagAllUploadsForUser($user_id)
{
    $user_id = CCUser::IDFromName($user_id);
    $sql = <<<EOF
        SELECT upload_id FROM cc_tbl_uploads WHERE upload_user = {$user_id}
EOF;
    $ids = CCDatabase::QueryItems($sql);
    foreach ($ids as $id) {
        ccPlusOptInUpload($id);
    }
}


function _ccPlus_has_stems($R) {
    return preg_match('/,(sample|acappella),/',$R['upload_tags']) &&
           ( CCDatabase::QueryItem("SELECT COUNT(*) FROM cc_tbl_files WHERE file_upload = {$R['upload_id']}") > 1 );
}

function _ccPlus_tags_minus($tag) {
    $all = array( 'ccplus', 'ccplus_verified', 'ccplus_verify', 'ccplus_nooptin', 'ccplus_stem' );
    if(empty($tag) ) {
        return $all;
    }
    if( !is_array($tag) ) {
        $tag = array($tag);
    }
    return array_diff(  $all, $tag );
}

function _ccPlus_debugP($str)
{
   // print($str);
}

?>