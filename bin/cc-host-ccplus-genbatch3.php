<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');
require_once('cchost_lib/cc-query.php');
$home = getenv("HOME");
$skip_ids = array(
  16626,22364,37792,18947,30344,26756,33345,30389,34503,46920,34402,25375,
36562,
37950,
46385,
48787,
50496,
28033,
35784,
38351,
42249,
44139,
37804,
18561,
37822,
35987,
37088,
37806,
38049,
42577,
29172,
49524,
27213,
24843,
37143,
20959,
49945,
40557,
31130,
35761,
21790,
22808,
26890,
27584,
29376,
31832,
32692,
35935,
38101,
38113,
40036,
43377,
44137,
45199,
45975,
47616,
48144,
49799,
50492,
34447,
40429,
43022,
46605,
46684,
48167,
50502,
46383,
50475,
50506,
23549,
26739,
33501,
35060,
38681,
39181,
42318,
42401,
43552,
43979,
49330,
50511,
36627,
38319,
42421,
36705,
40379,
43967,
44551,
46576,
47944,
48516,
50477,
39209,
28085,
35733,
36043,
36812,
37306,
37888,
40483,
41569,
44742,
44977,
50688,
48178,
36280,
37228,
40377,
35645,
35951,
36073,
36526 );



function ccPlusGenerateBatch($batch_no_str)
{
  global $ccmixter_home, $home, $skip_ids;

    $query = new CCQuery();
    $args = $query->ProcessAdminArgs('dataview=ids&f=php&limit=209&digrank=1&tags=ccplus,remix,non_commercial');
    list( $idrecs ) = $query->Query($args);
    $ids = array();
    for( $i = 0; $i < count($idrecs); $i++ ) {
      $idrec = $idrecs[$i];
      $id = $idrec['upload_id'];
      $ids[] = $id;
    }
    $ids = array_values(array_diff( $ids, $skip_ids));
    //print_r($ids); print("boo"); exit();
    $ids = join($ids,',');


    $sql =<<<EOF
        SELECT user_real_name, upload_name, upload_license, user_name, upload_id, file_name,  upload_extra FROM cc_tbl_uploads 
                 JOIN cc_tbl_files ON upload_id = file_upload 
                 JOIN cc_tbl_user  ON upload_user = user_id 
             WHERE file_order = 0 
                  AND upload_id IN ({$ids}) 
             ORDER BY user_name
             LIMIT 100
EOF;

    $rows = CCDatabase::QueryRows($sql);

    $csv = "artist,title,id,license,filename,featured_artists,url\n";
    $sh = "mkdir -p {$home}/dashgo/{$batch_no_str}\n";
    $zip = '';
    $clean = '';
    $chuck_no = 1;
    $chuck_count = 0;
    $CHUNK_SIZE = 250;
    foreach($rows as $R)
    {
        $ex = unserialize($R['upload_extra']);
        $feat = empty($ex['featuring']) ? "" : $ex['featuring'];
        $user = $R['user_real_name']; //  str_replace(',', '\,', $R['user_real_name']);
        $name = $R['upload_name'] . '-N';    //   str_replace(',', '\,', $R['upload_name']);
        $name = str_replace('"', '""', $name);
        $feat = str_replace('"', '""', $feat);
        $url  = "http://ccmixter.org/files/" . $R['user_name'] . '/' . $R['upload_id'];
        $str = "\"{$user}\", \"{$name}\", {$batch_no_str}-{$R['upload_id']}, {$R['upload_license']}, {$R['file_name']}, \"{$feat}\",{$url}\n";
        
        $csv .= $str;

        if( !$chuck_count )
        {
            $dest = "{$home}/dashgo/{$batch_no_str}/{$chuck_no}";
            $sh .= "\n#\n#\n#\nFILE_DEST={$dest}\n";
            $sh .= 'mkdir $FILE_DEST' . "\n";
            $clean .= "rm {$dest}/*\n";
            $clean .= "rmdir {$dest}\n";
            $zip .= "zip -j {$home}/dashgo/{$batch_no_str}/ccmixter-{$batch_no_str}-{$chuck_no}.zip {$dest}/*\n";
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
    
    print("Files written to {$home}\n");
    
}

function perform()
{
    ccPlusGenerateBatch('003');
}

perform();

