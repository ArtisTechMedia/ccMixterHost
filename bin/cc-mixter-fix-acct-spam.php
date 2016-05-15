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


function fixAcctSpam($_ips,$_emails)
{
    if( $_ips ) {
      $IPs = array_map( function($s) { return sprintf("user_last_known_ip LIKE '%s%%'", CCUtil::EncodeIP($s)); }, $_ips);
      $IPs = implode(' OR ', $IPs);
    } else {
      $IPs = '0';
    }

    if( $_emails ) {
      $emails = array_map( function($s) { return sprintf("user_email LIKE '%%%s%%'",$s); }, $_emails );
      $emails = implode(' OR ', $emails);
    } else {
      $emails = '0';
    }

    $sql =<<<EOF
      SELECT user_id FROM cc_tbl_user WHERE ({$IPs} OR {$emails}) AND user_id > 1000
EOF;

    //print "\nSQL: -------\n{$sql}\n\n";// exit;

    $count = 'SELECT COUNT(*) FROM cc_tbl_user';
    $before = CCDatabase::QueryItem($count);
    $ids = CCDatabase::QueryItems($sql);

    if( count($ids) > 0 ) {
      $ids_str = implode(',',$ids);

      $sql = <<<EOF
        DELETE FROM cc_tbl_user WHERE user_id IN (${ids_str});
EOF;
      //print_r($sql); exit;

      CCDatabase::Query($sql);
    }

    $cnt = CCDatabase::QueryItem($count);
    print ("\nrecords removed: " . ($before - $cnt) . "\n");
    print("\ndone ******\n\n");
}

function perform()
{
  global $argv,$argc;

  //var_dump($argv); exit;

  $ips = null;
  $emails = null;

  for( $i = 1; $i < $argc; $i++ ) {
    $arg = $argv[$i];
    if( preg_match('/^(ips|emails)=(.*)$/',$arg,$m) ) {
      if( $m[1] == 'ips') {
        $ips = explode(';', $m[2] );
      } else if( $m[1] == 'emails') {
        $emails = explode(';', $m[2]);
      }
    }
  }
  
  fixAcctSpam($ips,$emails);
}

perform();
