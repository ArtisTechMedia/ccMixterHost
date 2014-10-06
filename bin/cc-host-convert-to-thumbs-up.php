<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-host-convert-to-thumbs-up.php 8952 2008-02-11 21:41:24Z fourstones $
*
* Copyright 2006, Creative Commons, www.creativecommons.org.
* Copyright 2006, Victor Stone.
* Copyright 2006, Jon Phillips, jon@rejon.org.
*/


/*
    Use this utility for a one-time converstion from
    5-star ratings system to the 'recommends' system.

    It will resync the ratings/ranking based on previous
    ratings of 4.5 or higher and treat them all as a
    'Recommend'ation
*/
error_reporting(E_ALL);

if( preg_match( '#[\\\\/]bin$#', getcwd() ) )
    chdir('..');

define('IN_CC_HOST',1);
require_once('cc-config-db.php');
require_once('cchost_lib/cc-table.php');
require_once('cchost_lib/cc-database.php');
require_once('cchost_lib/cc-config.php');
require_once('cchost_lib/cc-defines.php');
require_once('cchost_lib/cc-debug.php');
require_once('cchost_lib/cc-util.php');
if( !function_exists('gettext') )
    require_once('cchost_lib/ccextras/cc-no-gettext.inc');
CCDebug::Enable(true);
do_main();

function do_main()
{
    $tname = 'upgrade_to_thumbs.sql';

    
    $f = fopen($tname,'w');
    if( !$f )
        die( 'Can\'t open temp sql file for writing');

    
    fwrite( $f, "LOCK TABLES cc_tbl_uploads WRITE, cc_tbl_user WRITE;\nUPDATE cc_tbl_uploads SET upload_num_scores = 0\n" );

    $sql =<<<EOF
SELECT 
CONCAT( 'UPDATE cc_tbl_uploads SET upload_num_scores =',
        count( * ), 
        ' WHERE upload_id = ', 
        ratings_upload ) as stm
FROM cc_tbl_ratings
GROUP BY ratings_upload
HAVING avg(ratings_score) > 449.9

EOF;
    q_and_write($sql,$f);

    $sql =<<<EOF
SELECT CONCAT( 'UPDATE cc_tbl_user SET user_rank =', sum( upload_num_scores ) , ' WHERE user_id = ', upload_user ) AS stm
FROM cc_tbl_uploads
GROUP BY upload_user
EOF;

    q_and_write($sql,$f);

    fwrite( $f, "UNLOCK TABLES;\n");
    fclose($f);

    print("temp sql file written\n");
    $f = fopen($tname,'r');
    while( $sql = fgets($f) )
        CCDatabase::Query($sql);
    print("uploads updates\n");
    fclose($f);
    unlink($tname);
    print("temp file deleted\n");
}

function q_and_write($sql,$f)
{
    $qr = CCDatabase::Query($sql) or die( mysql_error() );

    while( $r = mysql_fetch_row($qr) )
    {
        fwrite( $f, $r[0] . "\n" );
    }

}

?>