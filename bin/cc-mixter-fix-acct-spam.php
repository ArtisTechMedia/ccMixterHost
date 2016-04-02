<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
//require_once('cchost_lib/cc-query.php');

function d($obj) {
  print("\n\n");
  print_r($obj);
  print("\n\n");
  exit();
}

function _addQuotes($str)
{
    return "'{$str}'";
}


function fixAcctSpam()
{
    $sql =<<<EOF
    SELECT DISTINCT user_last_known_ip FROM cc_tbl_user WHERE 
      (user_email LIKE '%.pl' OR 
       user_email LIKE '%@cz.%' OR 
       user_email LIKE '%.eu' OR
       user_email LIKE '%@lv.%' OR
       user_email LIKE '%@sk.%' OR
       user_email LIKE '%.info' 
       )
      AND user_num_uploads = 0 
      AND user_description > ''
      AND user_id > 50000
EOF;

    //print ("\n" . $sql . "\n"); exit;
    $count = 'SELECT COUNT(*) FROM cc_tbl_user';
    $before = CCDatabase::QueryItem($count);
    $IPs = CCDatabase::QueryItems($sql);

    $quotedIPsArr = array_map('_addQuotes', $IPs );
    $quotedIPs = implode(',',$quotedIPsArr);

    $sql = <<<EOF
      DELETE FROM cc_tbl_user WHERE user_last_known_ip IN (${quotedIPs});
EOF;
    if( count($quotedIPsArr) > 0 ) {
      CCDatabase::Query($sql);
    }

    $cnt = CCDatabase::QueryItem($count);
    print ("\nrecords removed: " . ($before - $cnt) . "\n");
    print("\ndone ******\n\n");
}

function perform()
{
    fixAcctSpam('002');
}

perform();
