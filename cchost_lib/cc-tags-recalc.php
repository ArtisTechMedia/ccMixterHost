<?

if( !empty($_GET['fixccud']) )
{
    _do_ccud_fix();
    return;
}

print "Applying tag rules\n"; flush();

$qr = CCDatabase::Query('SELECT upload_id,upload_extra FROM cc_tbl_uploads');
$count    = 0;
$table    = new CCTable('cc_tbl_uploads','upload_id');
$tagtable = new CCTable('cc_tbl_tags','tags_tag');
$tagcols  = array( 'tags_tag', 'tags_type' );

while( $row = mysql_fetch_array($qr) )
{
    $ex = unserialize($row['upload_extra']);

    // [ccud] => media,acappella
    // [usertags] => female_vocals,melody
    // [systags] => sampling_plus,audio,mp3,44k,mono,128kbps

    $atags =explode(',',$ex['usertags']);

    if( !empty($atags) )
    {
        // apply aliases...
        
        $stags = "'" . join("', '", $atags) . "'";
        $sql = "SELECT tag_alias_tag,tag_alias_alias FROM cc_tbl_tag_alias WHERE tag_alias_tag IN ({$stags})";
        $aliases = CCDatabase::QueryRows($sql);

        // the new tags (aliases) may not be in the tags db and they need to be (sigh)
        $updates = array();
        
        if( !empty($aliases) )
        {
            $new_user_tags = array();
            foreach($aliases as $alias)
            {
                $newtags =explode(',',$alias['tag_alias_alias']);
                foreach( $newtags as $NT )
                {
                    $NT = trim($NT);
                    if( !empty($NT) )
                    {
                        $new_user_tags[] = $NT;
                        $updates[] = array( $NT, CCTT_USER );
                    }
                }
                $replaces[] = $alias['tag_alias_tag'];
            }
            $atags = array_unique( array_merge( $new_user_tags, array_diff($atags,$replaces) ));

            $tagtable->InsertBatch($tagcols,$updates,true);            
        }
    }

    // weed out system tags...
    if( !empty($atags) )
    {
        $userBit = CCTT_USER;
        $stags = "'" . join("', '", $atags) . "'";
        $sql = "SELECT tags_tag FROM cc_tbl_tags WHERE (tags_type & {$userBit}) <> 0 AND tags_tag IN ({$stags})";
        $atags = CCDatabase::QueryItems($sql);
    }
    
    $ex['usertags'] = join(',',array_unique($atags));
    $upload_tags = join(',', array_unique(explode(',',join(',', array($ex['ccud'],$ex['usertags'],$ex['systags'] )))));
    $uargs = array();
    $uargs['upload_id']    = $row['upload_id'];
    $uargs['upload_tags']  = $upload_tags;
    $uargs['upload_extra'] = serialize($ex);
    $table->Update($uargs);
    
    if(++$count % 100 == 0) print '.';
    if($count % 400 == 0) flush();
    if($count % 8000 == 0) print "\n";
}

print "\nCalculating tag pairs\n";
flush();

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS tptemp';
$sql[] = 'CREATE TABLE tptemp (tag_pair varchar(255),tag_pair_tag varchar(255) )';
$sql[] = 'DROP TABLE IF EXISTS tagtemp';
$sql[] = 'CREATE TABLE tagtemp (tag varchar(255))';

CCDatabase::Query($sql);

$qr       = CCDatabase::Query('SELECT upload_tags FROM cc_tbl_uploads');
$table    = new CCTable('tptemp','tag_pair');
$subtypes = array('remix','sample','acappella','contest_source','contest_sample','original','extended_mix','site_promo');
$filt     = array_merge($subtypes, array('media','audio'));
$cols     = array( 'tag_pair', 'tag_pair_tag ');

$ttable   = new CCTable('tagtemp','tag');
$tcols    = array( 'tag' );

$count = 0;
$misses = array();
while( $row = mysql_fetch_array($qr) )
{
    $tags  = array_filter(split(',',$row[0]));

    $tarr = array();
    foreach($tags as $T)
        $tarr[] = array($T);
    $ttable->InsertBatch($tcols,$tarr);
    
    $types = array_intersect($tags,$subtypes);
    $tags  = array_diff( $tags, $filt );
    if( empty($types) )
    {
        $misses[] = $row[0];
       
    }
    foreach( $types as $type )
    {
        $batch = array();
        foreach( $tags as $tag )
        {
            $batch[] = array( $type, $tag );
        }
        $table->InsertBatch( $cols, $batch );
    }
    if( ++$count % 100 == 0 ) print '.';
    if($count % 400 == 0) flush();
    if($count % 8000 == 0) print "\n";
}

print "\nCleaning up pair\n";
flush();
$sql = array();
$sql[] = 'DELETE FROM cc_tbl_tag_pair';
$sql[] =<<<EOF
        INSERT INTO cc_tbl_tag_pair
          SELECT tag_pair, tag_pair_tag, COUNT(*) as tag_pair_count
          FROM tptemp
          GROUP BY tag_pair,tag_pair_tag
EOF;
$sql[] = 'DROP TABLE tptemp';
$sql[] = 'UPDATE cc_tbl_tags SET tags_count = 0';
CCDatabase::Query($sql);

print "Recalculating tag counts\n";
flush();
$tagt = new CCTable('cc_tbl_tags','tags_tag');
$qr = CCDatabase::Query('SELECT COUNT(*) as tags_count, tag as tags_tag FROM tagtemp GROUP BY tag');
while( $row = mysql_fetch_assoc($qr) )
{
    $tagt->Update($row,true);
}
$sql = array();
$sql[] = 'DELETE FROM cc_tbl_tags WHERE tags_count < 1 AND tags_type = ' . CCTT_USER;
$sql[] = 'DROP TABLE tagtemp';
$sql[] = 'UPDATE cc_tbl_tags SET tags_tag = LOWER(tags_tag)'; // some tags ended up upper case which breaks INSERT
CCDatabase::Query($sql);

print "done\n";

function _do_ccud_fix()
{
    // somewhere along the line the 'tag' part of
    // alias rules crept into some ccud areas of
    // a few upload_extra fields. Hopefully, by
    // the time you read this I will have fixed
    // the bug that caused this to happen, but
    // just I don't or it doesn't take, here's
    // how to clean it up (something that has to
    // happen at least once in any event)
    
    // to invoke it, add fixccud=OFFENDING_TAGLIST
    
    $tags = explode(',',$_GET['fixccud']);
    if( empty($tags) )
        die('missing offending tag list');

    $dv = new CCDataView();
    $exp = $dv->MakeTagFilter($tags,'any');
    $qr = CCDatabase::Query('SELECT upload_id,upload_extra FROM cc_tbl_uploads WHERE ' . $exp);

    $exp = $dv->MakeTagFilter($tags,'any','tag_alias_tag');
    $rules = CCDatabase::QueryRows('SELECT tag_alias_tag as tag,tag_alias_alias as rule FROM cc_tbl_tag_alias WHERE ' . $exp);

    if( empty($rules) )
    {
        print "Could not find any rules for those tags\n";
        return;
    }
    
    print "Applaying rules:\n------------\n";
    foreach( $rules as $R )
    {
        print "  {$R['tag']} =&lt; {$R['rule']}\n";
    }
    print "------------------\n\n";
    
    $table = new CCTable('cc_tbl_uploads','upload_id');
    
    $count = 0;
    while( $row = mysql_fetch_assoc($qr) )
    {
        ++$count;
        $ex = unserialize($row['upload_extra']);
        print "UPLOAD: {$row['upload_id']}\n - ccud: {$ex['ccud']}\n - usertags: {$ex['usertags']}\n";
        $ccud = explode(',',$ex['ccud']);
        $utags = explode(',',$ex['usertags']);
        foreach( $rules as $R )
        {
            $ccud = array_diff($ccud,array($R['tag']));
            $rtags = explode(',',$R['rule']);
            $utags = array_unique(array_merge($utags,$rtags));
        }
        $munge = array_merge($ccud,$utags,explode(',',$ex['systags']));
        $ex['ccud'] = join(',',$ccud);
        $ex['usertags'] = join(',',$utags);
        print "   updated to:\n - ccud: {$ex['ccud']}\n - usertags: {$ex['usertags']}\n";
        $upargs['upload_id'] = $row['upload_id'];
        $upargs['upload_tags'] = join(',', array_unique(explode(',',join(',', $munge) ) ) );
        $upargs['upload_extra'] = serialize($ex);
        $table->Update($upargs);
    }
    
    print "\n\nFixed {$count} records (the tag counts will be wrong until you do a total reset)";

}

?>