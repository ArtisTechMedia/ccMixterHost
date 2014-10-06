<?php

chdir( dirname(dirname(__FILE__)) );

define('IN_CC_HOST',1);

require_once('cchost_lib/cc-non-ui.php');

if( $argc < 3 )
{
    usage();
}

$user_id = CCDatabase::QueryItem('SELECT user_id FROM cc_tbl_user WHERE user_name=\''.$argv[1].'\'');

if( empty($user_id) )
{
    print "\nERROR: '{$argv[1]}' is not a known user login name\n\n";
    usage();
}

$table = new CCTable('cc_tbl_user','user_id');
$args['user_id'] = $user_id;
$args['user_password'] = md5($argv[2]);
$table->Update($args);
print "\nPassword for user '{$argv[1]}' has been updated\n";
exit;

function usage()
{
    $usage =<<<EOF
    
Usage: php -f {$argv[0]} USERNAME PASSWORD
\n
EOF;
    print $usage;
    exit;
}

?>
