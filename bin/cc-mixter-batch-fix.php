<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );

$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');

// TODO

/*

  remix form and 'add file' form needs smarts

*/

$opt_in_users = array(
  'admiralbob77',
  'airtone',
  'AlexBeroza',
  'alexjc916',
  'Anomaly_Jonez',
  'assoverteakettle',
  'audiotechnica',
  'Beluga',
  'BOCrew',
  'bradstanfield',
  'Carosone',
  'casimps1',
  'cdk',
  'ChuckBerglund',
  'ciggiburns',
  'Clulow_Forester',
  'copperhead',
  'Coruscate',
  'csoul',
  'daniloprates',
  'debbizo',
  'djlang59',
  'dokashiteru',
  'donkeyhorsemule',
  'doxent',
  'emilyrichards',
  'Fireproof_Babies',
  'fluffy',
  'f_fact',
  'geertveneklaas',
  'George_Ellinas',
  'getatmic',
  'go1dfish',
  'grapes',
  'greyguy',
  'gurdonark',
  'Haskel',
  'hoop_it_up',
  'Javolenus',
  'JeffSpeed68',
  'jlbrock44',
  'keytronic',
  'Kirkoid',
  'lancefield',
  'Levihica',
  'Loveshadow',
  'mindmapthat',
  'MissJudged',
  'morgantj',
  'murat_ses',
  'nickleus',
  'NiGiD',
  'Nurykabe',
  'onlymeith',
  'panumoon',
  'Patronski',
  'Per',
  'phildann',
  'pieropeluche',
  //'PoliticBot',
  'PorchCat',
  'presentlylaura',
  'Quarkstar',
  'ramblinglibrarian',
  'Reiswerk',
  'reusenoise',
  'Robbero',
  'RobertWarrington',
  'rocavaco',
  'SackJo22',
  'sbarg',
 // 'scomber',
  'septahelix',
  'SiobhanD',
  'SmoJos',
  'snowflake',
  'spinmeister',
  'stellarartwars',
  'stohgs',
  'stringfactory',
  'subliminal',
  'sunhawken',
  'Super_Sigil',
  'teamsmileandnod',
  'TheDICE',
  'unreal_dm',
  'urmymuse',
  'victor',
  'Vidian',
  'vividsoundlab',
  'VJ_Memes',
  'wilburson',
  'Wired_Ant',
  'wolfsebastian',
  'zep_hurme'
);

$opt_in_users = array_map('strtolower', $opt_in_users);

function compareUsers()
{
  global $opt_in_users;

  $ccp_users = join(',',ccPlusUserIDs());

  $sql =<<<EOF
    SELECT LOWER(user_name) FROM cc_tbl_user WHERE user_id IN ({$ccp_users}) ORDER BY user_name;
EOF;

    $rows = CCDatabase::QueryItems($sql);
    // returns the values in array1 that are not present in any of the other arrays.
    $diff= array_diff($opt_in_users,$rows);
    print "\n\nArtists were signed but not marked in ccMixter database as ccPlus (now opt-ing in):\n";
    foreach ($diff as $u) {
      print($u . ', ');
      ccPlusOptInUser($u);
    }
    $diff= array_diff($rows,$opt_in_users);
    print "\n\nArtists marked in ccMixter database as ccPlus but not signed (ignoring):\n";
    foreach ($diff as $u) {
      print($u . ', ');
    }
    print "\n";
}

function clearAllccPlus() {
  $sql =<<<EOF
    SELECT upload_id FROM cc_tbl_uploads 
      WHERE (upload_tags LIKE '%,ccplus,%') 
        OR (upload_tags LIKE '%,ccplus_verify,%')
        OR (upload_tags LIKE '%,ccplus_stem,%')
EOF;
  
  print("\nUnmarking updloads\n");

  $counter = 0;

  $ids = CCDatabase::QueryItems($sql);
  if( !empty($ids) ) {
    foreach ($ids as $id) {
      ccPlusUnmarkUpload($id);
        if( ++$counter % 50 == 0 ) {
        print(".");
      }
    }    
  }

  print("\nDone unmarking " . $counter . " uploads\n");
}

function _addQuotes($str)
{
    return "'{$str}'";
}

function _optinUploadsForUser($user_name) {
    $user_id = CCUser::IDFromName($user_name);
    $ids = CCDatabase::QueryItems("SELECT upload_id FROM cc_tbl_uploads WHERE upload_user = {$user_id}");
    foreach ($ids as $id) {
      ccPlusOptInUpload($id,false);
    }
}

function tagAllccPlus() {

  // special case Vadim's stems
  print("\nTagging Vadim files\n");
  foreach( array( 53146, 53142, 53140 ) as $vadimID ) {
      ccPlusMarkUpload($vadimID);    
  }

  print("\nopt-ing in users uploads\n");
  global $opt_in_users;
  foreach ($opt_in_users as $user_name) {
    _optinUploadsForUser($user_name);
  }
  
  print("\nTagging files\n");  
  foreach ($opt_in_users as $user_name) {
    print ($user_name . ", ");
    _optinUploadsForUser($user_name);
    ccPlusTagAllUploadsForUser($user_name);
  }

  print ("\n\n");
}


function testIdunno() {
  ccPlusVerifyTree(16626);
}

compareUsers();
clearAllccPlus();
tagAllccPlus();

?>