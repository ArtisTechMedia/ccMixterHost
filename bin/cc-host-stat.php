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
* $Id: cc-host-stat.php 12729 2009-06-06 05:42:01Z fourstones $
*
* Copyright 2006, Creative Commons, www.creativecommons.org.
* Copyright 2006, Victor Stone.
* Copyright 2006, Jon Phillips, jon@rejon.org.
*/

$this_file = basename(__FILE__);

$help =<<<EOF
  usage: 

  {$this_file}  [-s start_date_string] [-e end_date_string] [-q] [-v]

  date strings are passed to strtotime()
  default start is '7 days ago'
  default end   is 'now'

  use -q to supress file names in output
  use -v for verbose
  
  examples:
    // Get log since Feb, 23
    php {$this_file} -s 2006-02-23 

    // Get log for Jan
    php {$this_file} -s "Jan 1, 2006" -e "Feb 1, 2006"

    // Get log for the last 3 days
    php {$this_file} -s "-3 days ago"

    // Get log for the last week (no args needed):
    php {$this_file}

  if you are running the cgi mode command line (as opposed to cli) then you
  should add some needed defines:

    // Get log for the last week (no args needed):
    php -q -d html_errors=Off -d register_argc_argv=On statcchost.php

  NOTE: actually this script has not been tested in cli mode

EOF;


define('CC_HOST_ROOT_DIR', dirname(dirname(__FILE__)) );

error_reporting(E_ALL);

$authorinfo['fourstones'] = 'Victor Stone <fourstones@users.sourceforge.net>';
$authorinfo['kidproto'] = 'Jon Phillips  <jon@creativecommons.org>';

$args      = get_args();

$verbose   = !empty($args['v']);
$date_from = get_time_arg($args,'s','-21 days');
$date_to   = get_time_arg($args,'e','tomorrow');

$cmd = "svn -v log -r $date_from:$date_to";

chdir(CC_HOST_ROOT_DIR);

if( $verbose )
  print("EXECUTING: $cmd\n");

$results = shell_exec($cmd);

$regex = '#'. 
         'r([0-9]+) \| ([^\s]+) \| ([^\s]+) .*[0-9]+ lines?\n' .
         'Changed paths:\n(.*)\n\n' .
         '(.*)\n--------------------------------' .
         '#Us';
                   
preg_match_all( $regex, $results, $matches, PREG_SET_ORDER );

// 1 - revision
// 2 - author
// 3 - date
// 4 - files
// 5 - comments

$arr = array();
foreach( $matches as $M )
{
    $arr[$M[3]][$M[2]][] = 
        array( 'r' => $M[1],
               'c' => $M[5],
               'f' => $M[4] );
}

$keys = array_keys($arr);
sort($keys);
$count = count($keys);
for( $i = $count-1; $i >= 0; $i--) 
{
    $date =& $keys[$i];
    $authors =& $arr[ $date ];

    foreach( $authors as $author => $updates )
    {
        if( empty($authorinfo[$author]) )
            $ainfo = $author;
        else
            $ainfo = $authorinfo[$author];

        print( "$date  $ainfo\n\n");
        foreach( $updates as $U )
        {
            $files = preg_split("/\n/",$U['f']);
            if( empty($args['q']) )
            {
                foreach( $files as $file )
                {
                    $f = preg_replace('#^\s+[A-Z]\s.*/trunk/(.*)$#','\1',$file);
                    print "\t* {$f} {$U['r']}:\n";
                }
            }
            else
            {
                print "\t* {$U['r']}:\n";
            }
            $comments = split("\n",$U['c']);
            foreach( $comments as $comment )
            {
                $comment = trim($comment);
                print(wordwrap("\t$comment\n", 70, "\n\t"));
            }
        }
        print("\n");
    }
}


function get_args()
{
    global $help;

    if( empty($argv) )
    {
        if( empty($_SERVER['argv']) )
        {
            return array();
        }
        $argv = $_SERVER['argv'];
        $argc = $_SERVER['argc'];
    }

    $args = array();
    for( $i=1; $i < $argc; $i++ )
    {
        switch($argv[$i])
        {
            case '-s':
            case '-e':
                if( ($i >= ($argc-1)) || empty($argv[$i + 1]) )
                    die("Missing argument for {$argv[$i]}\n");
                $args[ $argv[$i]{1} ] = $argv[$i + 1];
                ++$i;
                break;

            case '-q':
                $args['q'] = true;
                break;
                
            case '-v':
                $args['v'] = true;
                break;

            case '-h':
            case '--h':
            case '--help':
                print($help);
                exit;

            default:
                die( "Unknown argument: {$argv[$i]}");
        }
    }

    return $args;
}


function get_time_arg($args,$arg,$default)
{
    global $verbose;

    $used_default = false;

    if( empty($args[$arg]) )
    {
        $used_default = true;
        $date_str = $default;
    }
    else
    {
        $date_str = $args[$arg];
        if( $verbose )
            print("User arg '$arg': $date_str\n");
    }

    $time = strtotime($date_str);

    if( ($time === false) || $time == -1 )
    {
        $used_default = true;
        $time = strtotime($default);
    }

    if( $verbose  )
        print("Arg '$arg' : $date_str [$time] ($default)\n");

    return '{' . date('Y-m-d',$time) . '}';
}
?>
