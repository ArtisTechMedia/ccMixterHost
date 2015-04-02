<?
define('CC_HOST_CMD_LINE', 1 );
$admin_id = 9;
chdir( dirname(__FILE__) . '/..');
require_once('cc-cmd-line.inc');
require_once('cchost_lib/cc-query.php');
require_once('cchost_lib/cc-upload.php');
require_once('cchost_lib/cc-remix.php');
require_once('cchost_lib/cc-uploadapi.php');
require_once('cchost_lib/cc-tags.php');

$plusArtists = array( 
  "admiralbob77",
  "airtone",
  "AlexBeroza",
  "alexjc916",
  "BOCrew",
  "Carosone",
  "casimps1",
  "cdk",
  "ChuckBerglund",
  "copperhead",
  "daniloprates",
  "djlang59",
  "f_fact",
  "George_Ellinas",
  "go1dfish",
  "greyguy",
  "gurdonark",
  "hoop_it_up",
  "Javolenus",
  "jlbrock44",
  "Kirkoid",
  "lancefield",
  "Levihica",
  "Loveshadow",
  "mindmapthat",
  "NiGiD",
  "panumoon",
  "phildann",
  "pieropeluche",
  "Quarkstar",
  "Robbero",
  "SackJo22",
  "sbarg",
  "scomber",
  "snowflake",
  "spinmeister",
  "stellarartwars",
  "sunhawken",
  "Super_Sigil",
  "teamsmileandnod",
  "TheDICE",
  "unreal_dm",
  "urmymuse",
  "victor",
  "Vidian",
  "VJ_Memes",
  "Wired_Ant",
  "zep_hurme",
);

function perform()
{
    global $plusArtists;
    
    $LICENSE_STRICT_MAX = 10; // attribution, cczero and pd
    
    $count = 0;
    $remixcount = 0;
    $gotit = false;
    print( " **** PASS 1 **** \n");
    $remix_uploads = array();
    foreach( $plusArtists as $user )
    {
        $sql = "SELECT upload_id, license_strict, upload_tags FROM cc_tbl_uploads " .
                  "JOIN cc_tbl_user ON upload_user = user_id " . 
                  "JOIN cc_tbl_licenses ON upload_license = license_id " .
                "WHERE user_name = '$user'";
        $uploads = CCDatabase::QueryRows($sql);
        foreach( $uploads as $upload_meta )
        {
            $upload = $upload_meta['upload_id'];
            $license_strict = $upload_meta['license_strict'];
            $tags = $upload_meta['upload_tags'];
            print( "[1] Checking for {$user}/{$upload}... " );
            // only care about >NC tracks
            if( $license_strict > $LICENSE_STRICT_MAX )
            {
                $sql3 = "SELECT COUNT(pool_tree_pool_parent) FROM cc_tbl_pool_tree WHERE pool_tree_child = ${upload}";
                $pool_item_count = CCDatabase::QueryItem($sql3);
                // pool items can't be ccplus
                if( $pool_item_count == 0 )
                {            
                    if( !CCTag::InTag( 'ccplus', $tags ) )
                    {
                        $sql2 = "SELECT license_strict, upload_tags FROM cc_tbl_tree " .
                                    "JOIN cc_tbl_uploads ON tree_parent = upload_id " .
                                    "JOIN cc_tbl_licenses ON upload_license = license_id " .
                                "WHERE tree_child = ${upload};";
                        $sources_meta = CCDatabase::QueryRows($sql2);
                    
                        if( empty($sources_meta) )
                        {                   
                            // not a remix 
                            CCUploadAPI::UpdateCCUD( $upload, 'ccplus', '' );
                            $str = "updated tag (sample)";
                            $count ++;
                            print($str);
                        }
                        else
                        {
                            // this an NC remix
                            array_push($remix_uploads, array( $user, $upload, $sources_meta) );
                        }
                    }
                }
            }
            print("\n");
        }
    }

    $pass = 1;
    $null_pass_count = false;
    $next_remix_list = $remix_uploads;
    
    while( !$null_pass_count )
    {
        $pass++;
        $pass_count = 0;
        print( " **** PASS $pass **** \n");
        $remix_uploads = $next_remix_list;
        $next_remix_list = array();
        foreach( $remix_uploads as $remix )
        {
            $user = $remix[0];
            $upload = $remix[1];
            $sources_meta = $remix[2];
            print( "[$pass] Checking for {$user}/{$upload}... " );
            $ok = true;
            foreach( $sources_meta as $source_meta )
            {
                $license_strict = $source_meta['license_strict'];
                $tags = $source_meta['upload_tags'];
                if( ($license_strict > $LICENSE_STRICT_MAX) && !CCTag::InTag( 'ccplus', $tags ) )
                {
                    $ok = false;
                    break;
                }
            }
            if( $ok )
            {
                CCUploadAPI::UpdateCCUD( $upload, 'ccplus', '' );
                $str = "updated tag (remix)";
                $remixcount++;
                $pass_count++;
                print($str);
            }
            else
            {
                array_push($next_remix_list, $remix);
            }
            print("\n");
        }    
        $null_pass_count = $pass_count == 0;
    }
    $total = $count + $remixcount;
    print( "Total updated: passes: {$pass} samples: {$count} remixes: {$remixcount} total: {$total} \n" );
}

perform();

?>
