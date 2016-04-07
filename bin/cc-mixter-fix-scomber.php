<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');
require_once('cchost_lib/cc-query.php');


function fixScomber()
{
    $instruments = array();

    $query = new CCQuery();
    $args = $query->ProcessAdminArgs('f=php&dataview=ids&user=scomber&tags=ccplus');
    list( $rows ) = $query->Query($args);
    print("\nDe-ccplusing.");
    foreach ($rows as $row) {
      ccPlusMarkUploadAsSuspicious($row['upload_id']);
      print(".");
    }
    print("\ndone\n\n");
}

function perform()
{
    fixScomber();
}

perform();
