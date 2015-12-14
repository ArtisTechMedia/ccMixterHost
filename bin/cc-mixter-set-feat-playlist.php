<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/cc-query.php');

function d($obj) {
  print("\n\n");
  print_r($obj);
  print("\n\n");
  exit();
}

function setFeatPlaylist()
{
    $sql =<<<EOF
select CAST(SUBSTRING(topic_text,LOCATE('info&ids=',topic_text)+9,8) as SIGNED) as num 
  from cc_tbl_topics where topic_type = 'feat_playlist';
EOF;

    $ids = CCDatabase::QueryItems($sql);
    $ids = join($ids,',');

      $sql =<<<EOF
        UPDATE cc_tbl_cart SET cart_subtype = 'featured' where cart_id in ({$ids}) 
EOF;
      CCDatabase::Query($sql);
}

function perform()
{
    setFeatPlaylist();
}

perform();
