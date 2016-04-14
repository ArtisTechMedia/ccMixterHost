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
    $IPs = array_map( function($s) { return sprintf("user_last_known_ip LIKE '%s%%'", CCUtil::EncodeIP($s)); },
            array( 
              '103.238.68.234',
              '202.62.17.209',
              '111.93.250.130',
              '171.61.26.228',
              '202.67.40.50',
              '190.85.79.100',
            ));

    $IPs = implode(' OR ', $IPs);

    $where =<<<EOF
      ({$IPs} OR
       user_whatido LIKE 'http%' OR
       user_real_name LIKE 'http%' OR
       user_name IN (
         'osephdbeck10', 'juntiles01', 'inspira888', 
         'upercamp01', 'JannetteEReid', 'mjanifar', 
         'jason1025', 'GiftFlowersUSA',
         'priyamuna1998', 'oliviagomez1', 'nicolewis18', 
         'sadieerin', 'maximilianruth', 
         'Tagnotez', 'buyprem', 
         'sereialab', '_voice', 'cre8syndic8', 
         'juntiles02', 'walkerparasabata17'
        )
       )
EOF;

    $sql =<<<EOF
      SELECT user_id FROM cc_tbl_user WHERE ({$where}) AND user_id > 1000
EOF;

    $count = 'SELECT COUNT(*) FROM cc_tbl_user';
    $before = CCDatabase::QueryItem($count);
    $ids = CCDatabase::QueryItems($sql);

    if( count($ids) > 0 ) {
      $ids_str = implode(',',$ids);

      $sql = <<<EOF
        DELETE FROM cc_tbl_user WHERE user_id IN (${ids_str});
EOF;
      print_r($sql); exit;

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
