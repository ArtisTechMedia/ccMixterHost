<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..' );
$ccmixter_home = getcwd();
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');
$home = getenv("HOME");
              
function ccPlusGenerateBatch($batch_no_str)
{
    global $ccmixter_home, $home;
    
    $plusArtists = ccPlusUserIDs();
    $arrstr = implode(',',$plusArtists);

    // 'acoustic', 'ambient', 'dance', 'downtempo', 'rock', 'trip_hop', 'chill', 'funk'
    // hip_hop jazz 
    $genre =<<<EOF
            IF( upload_tags LIKE '%,acoustic,%', 'acoustic', 
                  IF( upload_tags LIKE '%,ambient,%', 'ambient', 
                      IF( upload_tags LIKE '%,dance,%', 'dance', 
                          IF( upload_tags LIKE '%,hip_hop,%', 'hip_hop', 
                              IF( upload_tags LIKE '%,jazz,%', 'jazz', 
                                  IF( upload_tags LIKE '%,blues,%', 'blues', 
                                      IF( upload_tags LIKE '%,downtempo,%', 'downtempo', 
                                          IF( upload_tags LIKE '%,rock,%', 'rock', 
                                              IF( upload_tags LIKE '%,trip_hop,%', 'trip_hop', 
                                                  IF( upload_tags LIKE '%,chill,%', 'chill', 
                                                      IF( upload_tags LIKE '%,electronic,%', 'electronic', 
                                                        'misc'))))))))))) as genre
EOF;

    $sql =<<<EOF
        SELECT user_real_name, upload_name, user_name, upload_id, file_name, {$genre}, upload_extra FROM cc_tbl_uploads 
                 JOIN cc_tbl_files ON upload_id = file_upload 
                 JOIN cc_tbl_user  ON upload_user = user_id 
             WHERE file_order = 0 AND upload_num_sources > 0 AND 
                   upload_tags LIKE '%,ccplus,%' AND 
                   upload_tags LIKE '%,remix,%' AND 
                   upload_user IN ({$arrstr}) 
             ORDER BY genre, user_name
EOF;
                
    $rows = CCDatabase::QueryRows($sql);
    
    $csv = "genre,artist,title,id,filename,featured_artists\n";
    $sh = "mkdir {$home}/dashgo\n";
    $sh .= "mkdir {$home}/dashgo/{$batch_no_str}\n";
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
        $str = "{$R['genre']}, \"{$user}\", \"{$name}\", {$batch_no_str}-{$R['upload_id']}, {$R['file_name']}, \"{$feat}\"\n";
        
        $csv .= $str;

        if( !$chuck_count )
        {
            $dest = "{$home}/dashgo/{$batch_no_str}/{$chuck_no}";
            $sh .= "\n#\n#\n#\nFILE_DEST={$dest}\n";
            $sh .= 'mkdir $FILE_DEST' . "\n";
            $clean .= "rm {$dest}/*\n";
            $clean .= "rmdir {$dest}\n";
            $zip .= "zip {$ccmixter_home}/dashgo/{$batch_no_str}/ccmixter-{$batch_no_str}-{$chuck_no}.zip {$dest}/*\n";
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
    ccPlusGenerateBatch('001');
}

perform();
