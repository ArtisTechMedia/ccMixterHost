<?php
/**
 * Creative Commons has made the contents of this file
 * available under a CC-GNU-GPL license:
 *
 * http://creativecommons.org/licenses/GPL/2.0/
 *
 * A copy of the full license can be found as part of this
 * distribution in the file COPYING.
 *
 * You may use the ccHost software in accordance with the
 * terms of that license. You agree that you are solely 
 * responsible for your use of the ccHost software and you
 * represent and warrant to Creative Commons that your use
 * of the ccHost software will comply with the CC-GNU-GPL.
 *
 * $Id: cc-host-data-dump.php 8952 2008-02-11 21:41:24Z fourstones $
 *
 * Copyright 2005-2006, Creative Commons, www.creativecommons.org.
 * Copyright 2006, Jon Phillips, jon@rejon.org.
 *
 * data_dump.php
 *
 * This script generates a large dump of all the audio submitted to this
 * project using different feed formats (rss, atom, etc). It can output
 * individual files with specific feed formats or all files. Check the usage
 * options for more information.
 *
 * Add to do this script with crontab -e to do this
 * 
 * 15 4 * * 3,6 cd /web/ccmixter/www && /usr/local/apache220/php/bin/php bin/data_dump.php -o atom.xml -t audio -f atom 2>&1 >/dev/null
 * 
 *
 * TODO: Should probably have more error output for bad CLI options.
 */

if( file_exists('../cclib') )
    chdir('..');

// NOTE: at present you might have to increase this memory_limit depending on 
// your configuration.
ini_set('memory_limit', '80M');


// The following is necessary to cycle through startup of the sites
// engine.


error_reporting(E_ALL & ~E_NOTICE);

define('IN_CC_HOST', true);
require_once('cchost_lib/cc-debug.php');
CCDebug::Enable(false);                 // set this to 'true' if you are a
if( !function_exists('gettext') )
   require_once('cchost_lib/ccextras/cc-no-gettext.inc');  
require_once('cc-includes.php');
CCConfigs::Init();                      // config settings established here
$cc_extras_dirs = 'ccextras';
include('cc-inc-extras.php');
require_once('cchost_lib/cc-feed.php');
CCEvents::Invoke(CC_EVENT_APP_INIT);


// The current feed types available.
$feed_types = array('datadump', 'atom', 'rss', 'xspf');


/**
 * Prints usage help options.
 */
function print_help ($opts=array(),$args='')
{
    global $feed_types;
    foreach ($feed_types as $type) {
        $feed_types_str .= "$type ";
    }

    $all_types = join(',',$feed_types);

    echo         "\nThis app dumps listings of tagged content to files in \n",
                 "different feed formats ($all_types).\n\n",
         sprintf("Usage: \n\tphp %s [OPTION]...\n\n", $_SERVER['argv'][0]),
                 "Possible Arguments:\n\n",
                 "  -h\t\t\tGet help for this commandline program.\n",
                 "  -a\t\t\tDump all content in all feed types.\n",
                 "  -o [FILENAME]\t\tThe file to dump xml out to for a type.\n",
                 "  -f [FEEDTYPE]\t\tThe feed format to dump all content ",
                                     "from an \n\t\t\tinstallation. ", 
                                     "Default is atom.\n",
                 "  -t [TAGS]\t\tTags (ex: audio,media,experimental).\n",
                 "\nPossible Feed Types: $feed_types_str\n\n",
                 "Example 1: This outputs to dump.xml all files with tags \n",
                 "audio and media using the RSS feed format. \n\n",
         sprintf("\tphp %s -t audio,media -o dump.xml -f rss\n\n",
                 $_SERVER['argv'][0]),
                 "Example 2: This outputs a dump with tags audio and sample \n",
                 "of the same content in each format to different files.\n\n",
         sprintf("\tphp %s -t audio,sample -a\n",
                 $_SERVER['argv'][0]),
                 "\n";

    if( $opts )
    {
        print_missing($opts,$args);
        exit(0);
    }
            
    exit(1);
}


/**
 * Dumps individual feeds based on some type.
 */
function dump_feed ($feed_type, $dump_file_name, $tag_str)
{
    $dumper = new CCFeed();
    $dumper->_feed_type = $feed_type;
    $dumper->SetIsDump(true);
    if( $dump_file_name )
    {
        $dumper->SetDumpFileName($dump_file_name);
    }
    $opts['limit'] = 0;
    $dumper->SetQueryOptions($opts);
    $dumper->GenerateFeed($tag_str);
}

/**
 * Dumps all feeds to the DUMP_DIR.
 */
function dump_feeds_all($tag_str = '')
{
    global $feed_types;
    foreach( $feed_types as $feed_type)
        dump_feed($feed_type,'',$tag_str);
}

function print_missing($opt, $valid)
{
    foreach( $valid as $V )
    {
        if( empty($opt[$V]) )
            print("\n\n ===> Missing '$V' parameter\n\n");
        elseif( empty($V) )
            print("\n\n ===> Missing value for '$V' parameter\n\n");
    }
}


/**
* here is some sample code that could be used on a Windows
* system where 'getopt' is not implemented -- the only trick
* is that you disable phptal/libs/PEAR.php in order to avoid
* conflicts

if( !function_exists('getopt') )
{
    print("\n\n ===> getopt doesn't exist, trying to find the PEAR version...\n\n");
    @include('go-pear-bundle/Getopt.php');
    if( !class_exists('Console_Getopt') )
        die("\n\nnope, try putting your PEAR directory into the include path");

    function getopt($str)
    {
        global $argv;
        list( $ropts ) = Console_Getopt::getopt($argv,$str);
        $opts = array();
        foreach( $ropts as $a  )
            $opts[$a[0]] = $a[1];
        return $opts;
    }
}
*/

// parse command line options
$opt = getopt('hao:f:t:');

// if there are no arguments passed or -h option, then print help
if ( count($opt) == 0 || isset($opt['h']) )
    print_help();

// printing all feeds with -a is given preference
if ( isset($opt['a']) && !empty($opt['t']) ) {
    dump_feeds_all($opt['t']);
}
else if ( !empty($opt['o']) && !empty($opt['f']) && !empty($opt['t']) ) 
{
    // make sure the proper feed type is input
    foreach ($feed_types as $type) {
        if ($type == $opt['f'])
            $is_feed_type = true;
    }
    if ( ! $is_feed_type ) {
        echo "\nERROR:\n\t Not feed type: " . $opt['f']  . "\n\n";
        print_help();
    }
    dump_feed($opt['f'],$opt['o'],$opt['t']);
} else {
    print_help($opt, array( 'o', 'f', 't' ));
}


exit(0);
?>
