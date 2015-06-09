<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..');
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');
              
print "hello\n";
function ccPlusGenerateBatch($batch_no_str)
{
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
    
    print ("genre,artist,title,id,filename,featured_artists\n");
    $csv = '';
    $sh = '';
    $chuck_no = 1;
    $chuck_count = 0;
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
            $sh .= "FILE_DEST=/var/www/ccmixter/dashgo/001/{$chuck_no}\n";
        }
        $str = "mv /var/www/ccmixter/content/{$R['user_name']}/{$R['file_name']} " . '$FILE_DEST' . "\n";
        $sh .= $str;
        if( ++$chuck_count > 100  )
        {
            $chuck_count = 0;
        }
    }

    print $csv;
    print $sh;
    /*  
    $file = fopen("ccmixter-001.csv", "w");
    fwrite($file,$csv);
    fclose( $file );
    
    $file = fopen("ccmixter-001-package.sh", "w");
    fwrite($file,$csv);
    fclose( $file );
    */
    
    
}

function perform()
{
    ccPlusGenerateBatch('001');
}

perform();
