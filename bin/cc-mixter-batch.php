<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');
require_once('cchost_lib/cc-query.php');
$home = getenv("HOME");

function ccPlusGenerateBatch($batch_no,$num_uploads)
{
    global $ccmixter_home, $home;

    $batch_no_str = str_pad('' + $batch_no, 3, '0', STR_PAD_LEFT );

    $qstring = 'dataview=injections&f=php&limit='.$num_uploads.'&digrank=1&tags=ccplus,remix,non_commercial&sort=user';
    $query = new CCQuery();
    $args = $query->ProcessAdminArgs($qstring);
    list( $rows ) = $query->Query($args);
    usort($rows, function( $a, $b ) {
        return strcasecmp($a['user_name'], $b['user_name']);
    });
    //print_r($rows); print_r($query); print("\n\n"); exit();

    $csv = "artist,title,id,license,filename,featured_artists,url\n";
    $sh = "mkdir -p {$home}/injest/{$batch_no_str}\n";
    $zip = '';
    $clean = '';
    $chuck_no = 1;
    $chuck_count = 0;
    $CHUNK_SIZE = 250;
    $ids = array();
    foreach($rows as $R)
    {
        $id = $R['upload_id'];
        $sql =<<<EOF
            INSERT INTO cc_tbl_injested (injested_upload,injested_batch) VALUES ({$id},{$batch_no});
EOF;
        CCDatabase::Query($sql);
        $ex = $R['upload_extra'];
        $feat = empty($ex['featuring']) ? "" : $ex['featuring'];
        $user = $R['user_real_name']; //  str_replace(',', '\,', $R['user_real_name']);
        $name = $R['upload_name'] . '-N';    //   str_replace(',', '\,', $R['upload_name']);
        $name = str_replace('"', '""', $name);
        $feat = str_replace('"', '""', $feat);
        $url  = "http://ccmixter.org/files/" . $R['user_name'] . '/' . $R['upload_id'];
        $str = "\"{$user}\", \"{$name}\", {$batch_no_str}-{$id}, {$R['upload_license']}, {$R['file_name']}, \"{$feat}\",{$url}\n";
        
        $csv .= $str;

        if( !$chuck_count )
        {
            $dest = "{$home}/injest/{$batch_no_str}/{$chuck_no}";
            $sh .= "\n#\n#\n#\nFILE_DEST={$dest}\n";
            $sh .= 'mkdir $FILE_DEST' . "\n";
            $clean .= "rm {$dest}/*\n";
            $clean .= "rmdir {$dest}\n";
            $zip .= "zip -j {$home}/injest/{$batch_no_str}/ccmixter-{$batch_no_str}-{$chuck_no}.zip {$dest}/*\n";
        }
        $str = "cp \"{$ccmixter_home}/content/{$R['user_name']}/{$R['file_name']}\" " . '$FILE_DEST' . "\n";
        $sh .= $str;

        if( ++$chuck_count > $CHUNK_SIZE  )
        {
            $chuck_count = 0;
            ++$chuck_no;
        }
    }

    chdir($home);
    $file = fopen("ccmixter-{$batch_no_str}.csv", "w");
    fwrite($file,$csv);
    fclose( $file );
    
    $file = fopen("ccmixter-{$batch_no_str}-package.sh", "w");
    fwrite($file,$sh);
    fclose( $file );

    $file = fopen("ccmixter-{$batch_no_str}-zip.sh", "w");
    fwrite($file,$zip);
    fclose( $file );
    
    $file = fopen("ccmixter-{$batch_no_str}-clean.sh", "w");
    fwrite($file,$clean);
    fclose( $file );
    
    print("\n\nFiles written to {$home}\n\n");
    
}

function perform()
{
    global $argv,$argc;

    $sql2 =<<<EOF
        select max(injested_batch) from cc_tbl_injested
EOF;
    $next = CCDatabase::QueryItem($sql2) + 1;

    if( $argc !== 3 ) {
        print("\n\nUsage:\n");
        print("   php -f " . $argv[0] . " <batch-number> <number-of-uploads>\n\n");
        print("The next available batch number is " . $next . "\n\n");
        exit(1);
    }

    $batch_no = $argv[1];

    $sql =<<<EOF
    select count(*) from cc_tbl_injested where injested_batch = {$batch_no}
EOF;
    $count = CCDatabase::QueryItem($sql);
    if( $count > 0 ) {

        print("\n\nError: That batch number is already in use!\n");
        print("The next available batch number is " . $next . "\n\n");
        exit(1);
    }

    ccPlusGenerateBatch( $argv[1], $argv[2] );
}

perform();

