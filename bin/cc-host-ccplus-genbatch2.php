<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');
require_once('cchost_lib/cc-query.php');
$home = getenv("HOME");
        

function ccPlusGenerateBatch($batch_no_str)
{
  global $ccmixter_home, $home;

    $query = new CCQuery();
    $args = $query->ProcessAdminArgs('dataview=ids&f=php&limit=300&digrank=1&tags=ccplus,remix');
    list( $idrecs ) = $query->Query($args);
    $ids = array();
    $skip = array(16626,22364,37792,18947,30344,26756,33345,30389,34503,46920,34402,25375);
    for( $i = 0; $i < count($idrecs); $i++ ) {
      $idrec = $idrecs[$i];
      $id = $idrec['upload_id'];
      $found = false;
      for( $n = 0; $n < count($skip); $n++ ) {
        if( $skip[$n] == $id ) {
          $found = true;
          break;
        }
      }
      if( !$found ) {
        $ids[] = $id;
      }
    }
    $ids = join($ids,',');

    $sql =<<<EOF
        SELECT user_real_name, upload_name, upload_license, user_name, upload_id, file_name,  upload_extra FROM cc_tbl_uploads 
                 JOIN cc_tbl_files ON upload_id = file_upload 
                 JOIN cc_tbl_user  ON upload_user = user_id 
             WHERE file_order = 0 
                  AND upload_license LIKE 'nonco%' 
                  AND upload_id IN ({$ids}) 
             ORDER BY user_name
             LIMIT 200
EOF;

    $rows = CCDatabase::QueryRows($sql);

    $csv = "artist,title,id,license,filename,featured_artists\n";
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
        $name = $R['upload_name'];    //   str_replace(',', '\,', $R['upload_name']);
        $feat = str_replace('"', '""', $feat);
        $str = "{\"{$user}\", \"{$name}\", {$batch_no_str}-{$R['upload_id']}, {$R['upload_license']}, {$R['file_name']}, \"{$feat}\"\n";
        
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
    ccPlusGenerateBatch('002');
}

perform();
