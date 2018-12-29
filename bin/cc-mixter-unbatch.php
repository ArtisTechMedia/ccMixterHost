<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');

function ccPlusUnBatch($batch_no)
{
    $sql =<<<EOF
        DELETE FROM cc_tbl_injested WHERE injested_batch={$batch_no}
EOF;

    CCDatabase::Query($sql);
    print("\n\nBatch deleted\n\n");
}
    
function perform()
{
    global $argv,$argc;

    if( $argc !== 2 ) {
        print("\n\nUsage:\n");
        print("   php -f " . $argv[0] . " <batch-number>\n\n");
        print(" DANGER: This operation deletes information about injested batches and there is no UNDO\n\n");       
        exit(1);
    }
    ccPlusUnBatch( $argv[1] );
}

perform();

