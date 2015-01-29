<?
define('CC_HOST_CMD_LINE', 1 );        // define this exact way
chdir( dirname(__FILE__) . '/..' );    // must be run from the cchost install root dir
$admin_id = 9;                         // user to log in as (OPTIONAL)
require_once( 'cc-cmd-line.inc' );     // include this file


$sql =<<<EOF
    SELECT topic_id,topic_text,
           SUBSTRING( topic_text, LOCATE("enclosure_size",topic_text), 30) as txt
    FROM cc_tbl_topics
    WHERE topic_text REGEXP "enclosure_size%[0-9]{1,3}(\\\\.|%)"
    ORDER by topic_date desc
EOF;

$rows = CCDatabase::QueryRows($sql);

$table = new CCTable('cc_tbl_topics','topic_id');

foreach( $rows as $R )
{
    preg_match( '/enclosure_size%([0-9\.]+)%/',$R['txt'], $m);
    
    //if( $m[1] > 1024 )
    //    break; // we're done

    $newsize = floor( $m[1] * (1024*1022) );
    
    $args = array();
    $args['topic_id'] = $R['topic_id'];
    $args['topic_text'] = str_replace( "enclosure_size%{$m[1]}%", "enclosure_size%{$newsize}%", $R['topic_text'] );
    $table->Update($args);
    
    print $R['topic_id'] . ': ' . $m[1] . " => " . $newsize . "\n";
}

print "\ndone\n";
?>