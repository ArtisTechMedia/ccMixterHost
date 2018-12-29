<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');
require_once('cchost_lib/cc-query.php');
$home = getenv("HOME");
        
function d($obj) {
  print("\n\n");
  print_r($obj);
  print("\n\n");
  exit();
}

function fixStellaWarts($batch_no_str)
{
    $instruments = array();

    $query = new CCQuery();
    $args = $query->ProcessAdminArgs('f=php&category=instr&pair=remix&dataview=tags');
    list( $tagrecs ) = $query->Query($args);
    for( $i = 0; $i < count($tagrecs); $i++ ) {
      $instruments[] = $tagrecs[$i]['tags_tag'];
    }

    $sql =<<<EOF
        SELECT upload_id, upload_extra, upload_tags FROM cc_tbl_uploads 
                 JOIN cc_tbl_user  ON upload_user = user_id 
             WHERE user_name = 'stellarartwars'
EOF;

    $total_old = 0;
    $total_new = 0;

    $rows = CCDatabase::QueryRows($sql);
    for( $r = 0; $r < count($rows); ++$r) {
      $row = $rows[$r];
      $ex = $row['upload_extra'];
      $upload_extra_obj = unserialize($ex);
      $old_user_tags = preg_split('/,/', $upload_extra_obj['usertags']);
      $user_tags = array_intersect($old_user_tags, $instruments);
      $user_rejected = array_diff($old_user_tags, $instruments);
      $user_tag_str = join(',',$user_tags);
      $upload_tags = preg_replace('/(^,|,$)/', '', $row['upload_tags']);
      $upload_tags = preg_split('/,/',$upload_tags);
      $total_old += count($upload_tags);
      $upload_tags = array_diff($upload_tags, $user_rejected);
      $total_new += count($upload_tags);

      $upload_extra_obj['usertags'] = join(',',$user_tags);
      $upex = addslashes(serialize($upload_extra_obj));
      $tags = ',' . addslashes(join(',',$upload_tags)) . ',';

      $sql =<<<EOF
        UPDATE cc_tbl_uploads 
          SET upload_extra = "{$upex}", 
              upload_tags = "{$tags}" 
          WHERE upload_id = {$row['upload_id']}
EOF;
      CCDatabase::Query($sql);
    }

    print_r( 'old: ' . $total_old . ' new: ' . $total_new . "\n");
    //d($upload_tags); exit();
}

function perform()
{
    fixStellaWarts('002');
}

perform();
