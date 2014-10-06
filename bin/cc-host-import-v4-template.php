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
* $Id: cc-host-import-v4-template.php 9187 2008-02-27 20:33:37Z fourstones $
*/

function do_file($infile,$outfile)
{
    $parser = new CCTALCompiler();
    print( "Compiling \"$infile\" to \"$outfile\"\n" );
    $parser->compile_phptal_file($infile,$outfile);
}

function usage()
{
    $prog = 'cc-host-import-v4-template.php';
    print "\n" . 
          "USAGE: php {$prog} INFILE OUTFILE\n\n" .
          "Note that paths are relative to the root of your installation. OUTFILE should have .php extension.\n\n" .
          "Example: php {$prog} local_files/skin/home.xml new_files/pages/home.php.\n";
    exit;

}
function main()
{
    $dir = dirname( dirname( __FILE__ ) );
    chdir($dir);
    define('IN_CC_HOST',1);
    define('TC_PRETTY', 1 );
    require_once('cchost_lib/cc-tal-parser.php');

    global $argv;

    if( empty($argv) || empty($argv[1]) || empty($argv[2]) )
        usage();

    if( !file_exists($argv[1]) )
        die("Can't find {$argv[1]}\n");

    if( file_exists($argv[2]) )
        die("File already exists: {$argv[2]}\n");

    do_file($argv[1],$argv[2]);
}

main();


?>