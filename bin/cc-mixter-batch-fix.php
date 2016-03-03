<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );

$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');


$opt_in_users = array(
'admiralbob77',
'airtone',
'alexberoza',
'alexjc916',
'anomaly_jonez',
'assoverteakettle',
'audiotechnica',
'beluga',
'bocrew',
'bradstanfield',
'carosone',
'casimps1',
'cdk',
'chuckberglund',
'clulow_forester',
'copperhead',
'coruscate',
'csoul',
'daniloprates',
'debbizo',
'djlang59',
'djvadim',
'dokashiteru',
'donkeyhorsemule',
'doxent',
'emilyrichards',
'fireproof_babies',
'fluffy',
'f_fact',
'geertveneklaas',
'george_ellinas',
'getatmic',
'go1dfish',
'grapes',
'greyguy',
'gurdonark',
'haskel',
'hoop_it_up',
'javolenus',
'jeffspeed68',
'jlbrock44',
'keytronic',
'kirkoid',
'lancefield',
'levihica',
'loveshadow',
'mindmapthat',
'morgantj',
'murat_ses',
'nickleus',
'nigid',
'nurykabe',
'onlymeith',
'panumoon',
'patronski',
'per',
'phildann',
'pieropeluche',
'politicbot',
'porchcat',
'presentlylaura',
'quarkstar',
'ramblinglibrarian',
'reiswerk',
'reusenoise',
'robbero',
'robertwarrington',
'rocavaco',
'sackjo22',
'sbarg',
'scomber',
'septahelix',
'siobhand',
'snowflake',
'smojos',
'spinmeister',
'stellarartwars',
'stohgs',
'stringfactory',
'subliminal',
'sunhawken',
'super_sigil',
'teamsmileandnod',
'thedice',
'unreal_dm',
'urmymuse',
'victor',
'vidian',
'vividsoundlab',
'vj_memes',
'wilburson',
'wired_ant',
'wolfsebastian',
'zep_hurme' );


/*
$ccp_users = join(',',ccPlusUserIDs());

$sql =<<<EOF
  SELECT LOWER(user_name) FROM cc_tbl_user WHERE user_id IN ({$ccp_users}) ORDER BY user_name;
EOF;

  $rows = CCDatabase::QueryItems($sql);
  $diff= array_diff($opt_in_users,$rows);
  print "\n\nArtists signed but not marked in ccMixter database as ccPlus:\n";
  foreach ($diff as $u) {
    print($u . ', ');
  }
  print "\n\n";
 exit;
*/

function _addQuotes($str)
{
    return "'{$str}'";
}

$quotedUsernamesArr = array_map('_addQuotes', $opt_in_users );
$quotedUsernames = implode(',',$quotedUsernamesArr);

$sql =<<<EOF

 SELECT upload_id,upload_name,user_name,upload_extra,upload_tags,date_format(upload_date,'%Y/%m/%d') as date
 FROM cc_tbl_uploads
 JOIN cc_tbl_user ON upload_user = user_id
 WHERE upload_tags LIKE '%,ccplus,%' AND
       upload_tags NOT LIKE '%,big_summer_fest,%' AND
       LOWER(user_name) NOT IN ({$quotedUsernames})
 ORDER BY user_name ASC
EOF;

  // print "\n" . $sql . "\n";

  $rows = CCDatabase::QueryRows($sql);

  print "Uploads by wrong artists: ------\n";

  foreach( $rows as $R ) {
    $url = 'http://ccmixter.org/files/' . $R['user_name'] . '/' . $R['upload_id'];
    print "{$R['date']},{$R['user_name']},{$R['upload_name']},{$url}\n";
    ccPlusMarkUploadAsSuspicious($R['upload_id']);
  }

$sql =<<<EOF

 SELECT upload_id,upload_num_sources,upload_name,user_name,upload_extra,upload_tags,date_format(upload_date,'%Y/%m/%d') as date
 FROM cc_tbl_uploads
 JOIN cc_tbl_user ON upload_user = user_id
 WHERE upload_tags LIKE '%,ccplus,%' AND
       upload_tags LIKE '%,remix,%' AND
       upload_num_sources < 1 AND
       upload_published > 0 AND
       upload_banned < 1
 ORDER BY user_name ASC
EOF;

  $rows = CCDatabase::QueryRows($sql);

  print "\nUploads (remixe) with no sources: ------\n";

  foreach( $rows as $R ) {
    $url = 'http://ccmixter.org/files/' . $R['user_name'] . '/' . $R['upload_id'];
    print "{$R['date']},{$R['user_name']},{$R['upload_name']},{$url}\n";
    ccPlusMarkUploadAsSuspicious($R['upload_id']);
  }

?>