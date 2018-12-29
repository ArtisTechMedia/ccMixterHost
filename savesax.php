<?

define('CC_HOST_CMD_LINE',1);
$admin_id = 9;
chdir( dirname(__FILE__));
require_once('cc-cmd-line.inc');


/*
$aliases = CCDatabase::QueryItems('SELECT tag_alias_alias FROM cc_tbl_tag_alias');
$actualtags = array();
foreach($aliases as $A)
{
    $x = split(',',trim($A));
    foreach( $x as $y )
    {
        $y = trim($y);
        if( !empty($y) )
            $actualtags[] = trim($y);
    }
}
$tags = array_unique($actualtags);
$count = 0;
$missing = array();
foreach($tags as $T)
{
    $X = CCDatabase::QueryItem('SELECT tags_tag FROM cc_tbl_tags WHERE tags_tag = "'.$T.'"');
    if( empty($X) )
    {
        print 'Missing: ' . $T . "\n";
        $missing[] = $T;
    }
}

$missing_user_tags = array();
foreach($missing as $M)
{
    print 'Missing: ' . $M . ': ' . "\n";
    $gones = CCDatabase::QueryRows("SELECT tag_alias_tag FROM cc_tbl_tag_alias WHERE tag_alias_alias LIKE '%{$M}%'");
    foreach( $gones as $gone ) {
        //print ' usertag: ' . $gone['tag_alias_tag'] . "\n";
        $missing_user_tags[] = $gone['tag_alias_tag'];
    }
}
$regex = "(" . join("|",$missing_user_tags) . ")";
//print "Regex: " . $regex . "\n";
$sql = "SELECT upload_id from cc_tbl_uploads WHERE CONCAT(',',upload_tags,',') REGEXP ',{$regex},'";
print "\n" . $sql . "\n";
$ids = CCDatabase::QueryItems( $sql );
var_dump($ids);
*/

/*
$qr = CCDatabase::Query('SELECT upload_id,upload_tags,upload_extra FROM cc_tbl_uploads WHERE upload_tags LIKE \'%sax%\'');

while( $row = mysql_fetch_assoc($qr) )
{
    $ex = unserialize($row['upload_extra']);
    print '"' .
         $row['upload_id'] .
         '" => array( \'' .
         $row['upload_tags'] .
         "', \"" .
         $ex['usertags'] .
         "\" ),\n";
}
*/
$fixarray = array(
"2411" => array( 'media,remix,bpm_095_100,trackback,in_web,funky,loops,rap,saxaphone,nc_sampling_plus,audio,mp3,44k,stereo,128kbps', "funky,loops,rap,saxaphone" ),
"3085" => array( 'media,remix,bpm_085_090,buddhism,electric_piano,instrumental,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "buddhism,electric_piano,instrumental,male_vocals,saxaphone" ),
"1204" => array( 'media,original,electronic,male_vocals,saxaphone,attribution,audio,mp3,44k,stereo,192kbps', "electronic,male_vocals,saxaphone" ),
"1526" => array( 'magnatune,contest_entry,remix,bpm_080_085,afrobeat,bass,drums,female_vocals,male_vocals,percussion,saxaphone,violin,water,non_commercial_share_alike,audio,mp3,44k,stereo,192kbps', "afrobeat,bass,drums,female_vocals,male_vocals,percussion,saxaphone,violin,water" ),
"6443" => array( 'media,remix,trackback,in_video,acid_jazz,chimes,experimental,guitar,instrumental,saxaphone,sampling_plus,audio,mp3,44k,stereo,CBR', "acid_jazz,chimes,experimental,guitar,instrumental,saxaphone" ),
"2887" => array( 'media,remix,bpm_120_125,bass,creative_collective,disfish,drums,guitar,indie_college_sound,male_vocals,rock,saxaphone,attribution,audio,mp3,44k,stereo,VBR', "bass,creative_collective,disfish,drums,guitar,indie_college_sound,male_vocals,rock,saxaphone" ),
"3667" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3668" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3669" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3670" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3671" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3672" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3673" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3674" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3675" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3676" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3677" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3678" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3679" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3680" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3681" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"3682" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "saxaphone" ),
"6948" => array( 'media,remix,bpm_090_095,trackback,in_web,hip_hop,loops,saxaphone,synthesizer,sampling_plus,audio,mp3,44k,stereo,CBR', "hip_hop,loops,saxaphone,synthesizer" ),
"3972" => array( 'sample,media,bpm_090_095,funk,samples,saxaphone,soul,attribution,audio,wma,44k,stereo,CBR,mp3', "funk,samples,saxaphone,soul" ),
"4018" => array( 'media,remix,editorial_pick,bpm_095_100,hip_hop,male_vocals,rap,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "hip_hop,male_vocals,rap,saxaphone" ),
"4238" => array( 'media,remix,bpm_090_095,broken_beat,cello,chill,downtempo,electronic,experimental,funk,jazz,piano,saxaphone,trip_hop,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "broken_beat,cello,chill,downtempo,electronic,experimental,funk,jazz,piano,saxaphone,trip_hop" ),
"4384" => array( 'media,remix,bpm_095_100,acoustic,bass,chill,flute,glass,instrumental,jazz,nu_jazz,saxaphone,trip_hop,attribution,audio,mp3,44k,stereo,CBR', "acoustic,bass,chill,flute,glass,instrumental,jazz,nu_jazz,saxaphone,trip_hop" ),
"8638" => array( 'vieux,contest_sample,bpm_110_115,sample,trackback,in_video,acoustic,bass,clav,clavinet,drums,guitar,organ,saxaphone,trumpet,non_commercial,archive,zip', "acoustic,bass,clav,clavinet,drums,guitar,organ,saxaphone,trumpet" ),
"4923" => array( 'media,remix,bpm_090_095,trackback,in_podcast,acoustic,bass,flute,hip_hop,jazz,jazzy,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "acoustic,bass,flute,hip_hop,jazz,jazzy,saxaphone" ),
"5616" => array( 'media,remix,bpm_120_125,bubblewrap,experimental,saxaphone,trance,non_commercial,audio,mp3,44k,stereo,CBR', "bubblewrap,experimental,saxaphone,trance" ),
"5732" => array( 'contest_entry,crammed,remix,bpm_110_115,trackback,in_video,ambient,found_sounds,guitar,hip_hop,instrumental,saxophones,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,found_sounds,guitar,hip_hop,instrumental,saxophones" ),
"6340" => array( 'sample,media,remix,cello,horn,oboe,orchestral,saxaphone,violin,non_commercial,archive,zip', "cello,horn,oboe,orchestral,saxaphone,violin" ),
"6435" => array( 'media,remix,bpm_090_095,trackback,in_podcast,downtempo,instrumental,jazz,saxaphone,smooth,sampling_plus,audio,mp3,44k,stereo,CBR', "downtempo,instrumental,jazz,saxaphone,smooth" ),
"6456" => array( 'media,remix,bpm_090_095,downtempo,female_vocals,saxaphone,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "downtempo,female_vocals,saxaphone" ),
"6473" => array( 'sample,media,bpm_120_125,downtempo,hip_hop,instrumental,loops,pop,saxaphone,trip_hop,attribution,audio,mp3,44k,stereo,CBR', "downtempo,hip_hop,instrumental,loops,pop,saxaphone,trip_hop" ),
"6506" => array( 'media,remix,how_i_did_it,bpm_080_085,editorial_pick,bass,downtempo,female_vocals,guitar,loops,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "bass,downtempo,female_vocals,guitar,loops,saxaphone,synthesizer" ),
"9400" => array( 'media,remix,bpm_100_105,drums,funky,male_vocals,melody,mondharp,pop,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "drums,funky,male_vocals,melody,mondharp,pop,saxaphone" ),
"6814" => array( 'media,remix,bpm_140_145,chill,experimental,female_vocals,jazz,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "chill,experimental,female_vocals,jazz,saxaphone" ),
"6901" => array( 'media,remix,bpm_120_125,female_vocals,marimba,piano,saxaphone,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "female_vocals,marimba,piano,saxaphone" ),
"7045" => array( 'media,remix,bpm_100_105,beat,downtempo,dyonix,electronic,guitare,hip_hop,instru,saxaphone,trip_hop,attribution,audio,mp3,44k,stereo,CBR', "beat,downtempo,dyonix,electronic,guitare,hip_hop,instru,saxaphone,trip_hop" ),
"7066" => array( 'media,remix,bpm_120_125,experimental,funny,guitar,male_vocals,nonsense,saxaphone,sampling_plus,audio,mp3,44k,stereo,CBR', "experimental,funny,guitar,male_vocals,nonsense,saxaphone" ),
"7772" => array( 'media,remix,bpm_065_070,bass,guitar,kawai,saxaphone,vocals,non_commercial,audio,mp3,44k,stereo,CBR', "bass,guitar,kawai,saxaphone,vocals" ),
"7167" => array( 'media,remix,bpm_130_135,female_vocals,jazz,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,jazz,saxaphone" ),
"7585" => array( 'sample,media,saxaphone,attribution,audio,mp3,44k,stereo,VBR', "saxaphone" ),
"7320" => array( 'media,remix,bpm_070_075,12_string,bandeon,bass,female_vocals,guitar,saxaphone,vocals,non_commercial,audio,mp3,44k,stereo,CBR', "12_string,bandeon,bass,female_vocals,guitar,saxaphone,vocals" ),
"7488" => array( 'sample,media,bpm_above_180,brass,brazil,calmantes,canastra,independent,metais,nervoso,pop,rock,saxaphone,tromb,trumpet,non_commercial,audio,mp3,44k,stereo,CBR', "brass,brazil,calmantes,canastra,independent,metais,nervoso,pop,rock,saxaphone,tromb,trumpet" ),
"7526" => array( 'media,remix,sample,chill,downtempo,experimental,saxaphone,soundtrack,spoken_word,attribution,audio,mp3,44k,stereo,VBR', "chill,downtempo,experimental,saxaphone,soundtrack,spoken_word" ),
"7548" => array( 'sample,media,bpm_100_105,saxaphone,woodwinds,attribution,audio,mp3,44k,stereo,CBR', "saxaphone,woodwinds" ),
"7795" => array( 'media,remix,bpm_075_080,bass,female_vocals,fender,fretless,guitar,kawai,saxaphone,vocals,non_commercial,audio,mp3,44k,stereo,CBR', "bass,female_vocals,fender,fretless,guitar,kawai,saxaphone,vocals" ),
"7599" => array( 'sample,media,saxofoon,attribution,audio,mp3,44k,stereo,CBR', "saxofoon" ),
"7642" => array( 'media,remix,bpm_130_135,downtempo,guitar,instrumental,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,guitar,instrumental,saxaphone" ),
"7647" => array( 'sample,media,bpm_105_110,saxaphone,attribution,archive,zip', "saxaphone" ),
"7702" => array( 'media,remix,bpm_120_125,bass,drums,guitar,instrumental,latin,piano,saxaphone,trombone,trumpet,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,guitar,instrumental,latin,piano,saxaphone,trombone,trumpet" ),
"7726" => array( 'media,remix,bpm_135_140,male_vocals,saxaphone,spoken_word,trip_hop,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "male_vocals,saxaphone,spoken_word,trip_hop" ),
"7744" => array( 'media,remix,avant_garde,brass,chill,electro,electronic,instrumental,jazz,kcentric,saxaphone,stefsax,attribution,audio,mp3,44k,stereo,CBR', "avant_garde,brass,chill,electro,electronic,instrumental,jazz,kcentric,saxaphone,stefsax" ),
"7757" => array( 'media,remix,in_video,trackback,funky,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "funky,saxaphone" ),
"7786" => array( 'sample,media,bpm_120_125,funky,saxaphone,attribution,archive,zip', "funky,saxaphone" ),
"7949" => array( 'media,remix,experimental,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "experimental,saxaphone" ),
"8393" => array( 'media,remix,bpm_140_145,bass,flute,funk,funky,happy_new_year,instrumental,jazz,new_years_party,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,flute,funk,funky,happy_new_year,instrumental,jazz,new_years_party,saxaphone" ),
"8443" => array( 'media,remix,bpm_140_145,funky,hip_hop,male_vocals,rap,saxaphone,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "funky,hip_hop,male_vocals,rap,saxaphone" ),
"8534" => array( 'media,remix,bpm_120_125,trackback,in_album,chill,dance,electronic,female_vocals,house,no_glowsticks,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "chill,dance,electronic,female_vocals,house,no_glowsticks,saxaphone" ),
"8598" => array( 'media,remix,bpm_095_100,trackback,in_podcast,drums,funky,guitar,hip_hop,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "drums,funky,guitar,hip_hop,male_vocals,saxaphone" ),
"8701" => array( 'media,remix,bpm_110_115,downtempo,saxaphone,world,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,saxaphone,world" ),
"8838" => array( 'contest_entry,vieux,remix,bpm_110_115,male_vocals,saxaphone,world,non_commercial,audio,mp3,44k,stereo,CBR', "male_vocals,saxaphone,world" ),
"8937" => array( 'contest_entry,vieux,remix,bpm_110_115,east_europe,female_vocals,gypsy,male_vocals,mali,middle,saxaphone,spoken_word,trumpet,violin,yiddish,non_commercial,audio,mp3,44k,stereo,CBR', "east_europe,female_vocals,gypsy,male_vocals,mali,middle,saxaphone,spoken_word,trumpet,violin,yiddish" ),
"9058" => array( 'media,remix,bpm_085_090,bass,downtempo,electronic,ethno,europe,female_vocals,folk,saxaphone,yiddish,sampling_plus,audio,mp3,44k,stereo,CBR', "bass,downtempo,electronic,ethno,europe,female_vocals,folk,saxaphone,yiddish" ),
"9117" => array( 'media,remix,bpm_095_100,female_vocals,lounge,saxaphone,non_commercial,audio,mp3,44k,stereo', "female_vocals,lounge,saxaphone" ),
"9518" => array( 'media,remix,bpm_065_070,funky,jazz,male_vocals,melody,organ,piano,pop,rock,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "funky,jazz,male_vocals,melody,organ,piano,pop,rock,saxaphone" ),
"9577" => array( 'media,remix,bpm_105_110,bop,jazz,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,VBR', "bop,jazz,saxaphone,spoken_word" ),
"10249" => array( 'media,remix,bpm_140_145,female_vocals,funk,hip_hop,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo', "female_vocals,funk,hip_hop,saxaphone,spoken_word" ),
"10255" => array( 'sample,media,acoustic,bass,blues,blue_lou,congas,fun,horn,jazz,jazzy,keyboard,piano,saxaphone,shuffle,straight_ahead,trumpet,waltz,attribution,archive,zip', "acoustic,bass,blues,blue_lou,congas,fun,horn,jazz,jazzy,keyboard,piano,saxaphone,shuffle,straight_ahead,trumpet,waltz" ),
"10376" => array( 'media,remix,bpm_120_125,trackback,in_podcast,breaks,chill,electronic,female_vocals,male_vocals,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "breaks,chill,electronic,female_vocals,male_vocals,saxaphone,synthesizer" ),
"10601" => array( 'media,remix,bpm_120_125,bass,female_vocals,fender,fretless,gibson,guitar,sopran_sax,vocals,non_commercial,audio,mp3,44k,stereo,CBR', "bass,female_vocals,fender,fretless,gibson,guitar,sopran_sax,vocals" ),
"10729" => array( 'contest_entry,curve,remix,bass,drums,funky,saxaphone,trumpet,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,funky,saxaphone,trumpet" ),
"11239" => array( 'sample,media,bpm_080_085,trackback,in_video,bass,clav,clavinet,drums,guitar,saxaphone,non_commercial,archive,zip', "bass,clav,clavinet,drums,guitar,saxaphone" ),
"11701" => array( 'media,remix,ambient,chill,downtempo,drums,instrumental,saxaphone,non_commercial,audio,mp3,24k,stereo,CBR', "ambient,chill,downtempo,drums,instrumental,saxaphone" ),
"11730" => array( 'media,remix,funky_samba_thrumpet_sax_,attribution,audio,mp3,44k,stereo,VBR', "funky_samba_thrumpet_sax_" ),
"11770" => array( 'sample,media,bpm_125_130,ambient,blues,chill,electro,electronic,instrumental,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,blues,chill,electro,electronic,instrumental,saxaphone,synthesizer" ),
"11771" => array( 'sample,media,crazy,experimental,lunatic,orchestral,saxaphone,violin,non_commercial,audio,mp3,44k,stereo,CBR', "crazy,experimental,lunatic,orchestral,saxaphone,violin" ),
"21204" => array( 'media,remix,bpm_090_095,bass,drums,guitar,male_vocals,saxaphone,spoken_word,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,guitar,male_vocals,saxaphone,spoken_word,synthesizer" ),
"13086" => array( 'media,remix,editorial_pick,in_video,trackback,in_web,drums,instrumental,saxaphone,attribution,audio,mp3,44k,stereo,VBR,CBR', "drums,instrumental,saxaphone" ),
"12130" => array( 'sample,media,bpm_100_105,bass,clarinet,drums,guitar,keyboard,organ,rhodes,saxaphone,non_commercial,archive,zip', "bass,clarinet,drums,guitar,keyboard,organ,rhodes,saxaphone" ),
"12138" => array( 'sample,media,bpm_065_070,drums,guitar,moog,organ,rhodes,saxaphone,trumpet,non_commercial,archive,zip', "drums,guitar,moog,organ,rhodes,saxaphone,trumpet" ),
"21210" => array( 'media,remix,bpm_105_110,band_in_your_head,bongos,busker,female_vocals,guitar,happiness,saxaphone,street_performance,non_commercial,audio,mp3,44k,stereo,VBR', "band_in_your_head,bongos,busker,female_vocals,guitar,happiness,saxaphone,street_performance" ),
"12409" => array( 'media,remix,editorial_pick,how_i_did_it,bpm_095_100,in_video,trackback,in_podcast,instrumental,loops,melody,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "instrumental,loops,melody,saxaphone" ),
"12430" => array( 'media,remix,bpm_130_135,dance,disco,electric,electronic,female_vocals,guitar,house,piano,rhodes,saxaphone,straightbeat,trumpet,wurlitzer,non_commercial,audio,mp3,44k,stereo,CBR', "dance,disco,electric,electronic,female_vocals,guitar,house,piano,rhodes,saxaphone,straightbeat,trumpet,wurlitzer" ),
"12491" => array( 'sample,media,avant_garde,jazz,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "avant_garde,jazz,saxaphone" ),
"12510" => array( 'sample,media,bpm_130_135,alto_sax,melodic_string,phrase,scale,attribution,audio,mp3,44k,mono,CBR', "alto_sax,melodic_string,phrase,scale" ),
"12521" => array( 'media,remix,bpm_095_100,chill,downtempo,electronic,female_vocals,hip_hop,male_vocals,music_box,pads,rhodes,saxaphone,scratching,spoken_word,trip_hop,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "chill,downtempo,electronic,female_vocals,hip_hop,male_vocals,music_box,pads,rhodes,saxaphone,scratching,spoken_word,trip_hop" ),
"12543" => array( 'sample,media,guitar,saxaphone,non_commercial,archive,zip', "guitar,saxaphone" ),
"12533" => array( 'sample,media,bari,phrases,saxaphone,short_loops,attribution,archive,zip', "bari,phrases,saxaphone,short_loops" ),
"12534" => array( 'sample,media,phrases,saxaphone,short_loops,attribution,archive,zip', "phrases,saxaphone,short_loops" ),
"12535" => array( 'sample,media,horn,loops,phrases,saxaphone,attribution,archive,zip', "horn,loops,phrases,saxaphone" ),
"12536" => array( 'sample,media,horn,loops,phrases,saxaphone,attribution,archive,zip', "horn,loops,phrases,saxaphone" ),
"13026" => array( 'media,remix,how_i_did_it,bpm_100_105,trackback,in_video,drums,female_vocals,funky,hip_hop,horn,saxaphone,scratching,trip_hop,non_commercial,audio,mp3,44k,stereo,CBR', "drums,female_vocals,funky,hip_hop,horn,saxaphone,scratching,trip_hop" ),
"13002" => array( 'sample,media,experimental,guitar,saxaphone,non_commercial,archive,zip', "experimental,guitar,saxaphone" ),
"13003" => array( 'sample,media,experimental,saxaphone,non_commercial,archive,zip', "experimental,saxaphone" ),
"13004" => array( 'sample,media,experimental,saxaphone,non_commercial,archive,zip', "experimental,saxaphone" ),
"13112" => array( 'remix,media,funky,guitar,instrumental,saxaphone,soul,attribution,audio,mp3,44k,stereo,CBR', "funky,guitar,instrumental,saxaphone,soul" ),
"13344" => array( 'sample,media,bpm_085_090,instrumental,saxaphone,non_commercial,audio,mp3,44k,mono,CBR,flac,VBR', "instrumental,saxaphone" ),
"13244" => array( 'media,remix,bpm_120_125,bass,fretless,lounge,male_vocals,organ,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "bass,fretless,lounge,male_vocals,organ,saxaphone" ),
"15225" => array( 'media,remix,bpm_090_095,bass,drums,guitar,loops,male_vocals,melody,rap,saxaphone,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "bass,drums,guitar,loops,male_vocals,melody,rap,saxaphone" ),
"13864" => array( 'media,remix,bpm_120_125,trackback,in_video,downtempo,electronic,hammond,instrumental,middle_east,saxaphone,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "downtempo,electronic,hammond,instrumental,middle_east,saxaphone" ),
"13920" => array( 'media,remix,bop,jazz,new_harlem_renaissance,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "bop,jazz,new_harlem_renaissance,saxaphone,spoken_word" ),
"14050" => array( 'media,remix,bpm_095_100,acoustic,bass,battle,battle_track,bebop_meets_hip_hop,chop_shop,dj_battle,experimental,female_vocals,flute,funky,ghettolounge,ghetto_lounge,hip_hop,innovative_mobility,jazz,lyrical_inventions,male_vocals,orchestral,piano,producer_battle,rap,samples,saxaphone,scat,scratching,trumpet,violin,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "acoustic,bass,battle,battle_track,bebop_meets_hip_hop,chop_shop,dj_battle,experimental,female_vocals,flute,funky,ghettolounge,ghetto_lounge,hip_hop,innovative_mobility,jazz,lyrical_inventions,male_vocals,orchestral,piano,producer_battle,rap,samples,saxaphone,scat,scratching,trumpet,violin" ),
"14416" => array( 'media,remix,bpm_110_115,female_vocals,nu_jazz,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,nu_jazz,piano,saxaphone" ),
"14676" => array( 'media,remix,how_i_did_it,bpm_090_095,cowbell,female_vocals,flute,jazz,poem,poetry,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "cowbell,female_vocals,flute,jazz,poem,poetry,saxaphone,spoken_word" ),
"14744" => array( 'media,remix,bpm_100_105,drums,guitar,male_vocals,orchestral,piccolo,saxaphone,spoken_word,attribution,audio,mp3,44k,stereo,CBR', "drums,guitar,male_vocals,orchestral,piccolo,saxaphone,spoken_word" ),
"14773" => array( 'media,remix,bpm_130_135,electronica,house,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "electronica,house,saxaphone" ),
"14974" => array( 'media,remix,bpm_100_105,bass,drums,female_vocals,guitar,saxaphone,spoken_word,attribution,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,guitar,saxaphone,spoken_word" ),
"15134" => array( 'media,remix,bpm_120_125,trackback,in_podcast,bass,drums,female_vocals,guitar,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,guitar,piano,saxaphone" ),
"15146" => array( 'media,remix,bpm_080_085,ambient,bass,drums,female_vocals,funky,loops,piano,saxes,synthesizer,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "ambient,bass,drums,female_vocals,funky,loops,piano,saxes,synthesizer" ),
"15258" => array( 'media,remix,bpm_085_090,bass,drums,female_vocals,guitar,loops,pop,saxaphone,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,guitar,loops,pop,saxaphone" ),
"15297" => array( 'media,remix,bass,drums,guitar,male_vocals,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,guitar,male_vocals,saxaphone,synthesizer" ),
"15300" => array( 'media,remix,how_i_did_it,bpm_090_095,funky,hip_hop,loops,old_school,rap,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "funky,hip_hop,loops,old_school,rap,saxaphone" ),
"15313" => array( 'media,remix,bpm_125_130,bass,electronic,female_vocals,funky,house,loops,melody,piano,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "bass,electronic,female_vocals,funky,house,loops,melody,piano,saxaphone,synthesizer" ),
"15324" => array( 'media,remix,bpm_100_105,ambient,bass,drums,female_vocals,loops,piano,saxaphone,synthesizer,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "ambient,bass,drums,female_vocals,loops,piano,saxaphone,synthesizer" ),
"15356" => array( 'media,remix,bpm_090_095,drums,electronic,male_vocals,piano,rap,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "drums,electronic,male_vocals,piano,rap,saxaphone" ),
"15396" => array( 'acappella,media,featured,bpm_080_085,trackback,in_remix,female_vocals,melody,pop,rock,soprano_saxophone,vocals,non_commercial,audio,mp3,48k,stereo,CBR', "female_vocals,melody,pop,rock,soprano_saxophone,vocals" ),
"15405" => array( 'media,remix,bpm_070_075,chill,female_vocals,laidback_sax,melody,piano,romantic,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "chill,female_vocals,laidback_sax,melody,piano,romantic" ),
"15602" => array( 'media,remix,bpm_120_125,editorial_pick,bass,drums,female_vocals,guitar,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,guitar,male_vocals,saxaphone" ),
"15741" => array( 'media,remix,bpm_075_080,ambient,bass,drums,female_vocals,guitar,loops,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,bass,drums,female_vocals,guitar,loops,saxaphone,synthesizer" ),
"19574" => array( 'media,remix,bpm_085_090,female_vocals,fretless,guitar,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "female_vocals,fretless,guitar,saxaphone" ),
"15981" => array( 'media,remix,bpm_100_105,ambient,bass,chill,drums,female_vocals,flute,guitar,saxaphone,spoken_word,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,bass,chill,drums,female_vocals,flute,guitar,saxaphone,spoken_word,synthesizer" ),
"15985" => array( 'sample,media,bpm_095_100,guitar,hip_hop,loops,melody,saxaphone,shot,soprano_saxophone,wah_wah,non_commercial,audio,mp3,44k,mono,CBR,archive,zip', "guitar,hip_hop,loops,melody,saxaphone,shot,soprano_saxophone,wah_wah" ),
"16021" => array( 'sample,media,bpm_090_095,bass,horn,instrumental,loops,organ,rhodes,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR,archive,zip', "bass,horn,instrumental,loops,organ,rhodes,saxaphone" ),
"16107" => array( 'media,remix,bpm_130_135,cello,dark,dirty,dub,dubstep,electonica,glitch,grimey,jazz,orchestral,saxaphone,violin,non_commercial,audio,mp3,44k,stereo,CBR', "cello,dark,dirty,dub,dubstep,electonica,glitch,grimey,jazz,orchestral,saxaphone,violin" ),
"16412" => array( 'media,remix,bpm_060_065,ambient,bass,cello,chill,drums,female_vocals,flute,guitar,kora,male_vocals,saxaphone,synthesizer,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "ambient,bass,cello,chill,drums,female_vocals,flute,guitar,kora,male_vocals,saxaphone,synthesizer" ),
"18560" => array( 'media,remix,bpm_140_145,drums,female_vocals,guitar,orchestral,piano,saxaphone,synthesizer,violin,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "drums,female_vocals,guitar,orchestral,piano,saxaphone,synthesizer,violin" ),
"16491" => array( 'media,remix,bpm_120_125,bass,drums,female_vocals,guitar,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,guitar,saxaphone" ),
"16525" => array( 'media,remix,bass,drums,female_vocals,piano,saxaphone,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,piano,saxaphone" ),
"16562" => array( 'sample,media,bpm_120_125,acoustic,instrumental,saxaphone,soprano_saxophone,non_commercial,audio,mp3,32k,stereo,VBR', "acoustic,instrumental,saxaphone,soprano_saxophone" ),
"22423" => array( 'media,remix,bpm_110_115,acoustic,female_vocals,guitar,new_orleans,noisecollector,sagetyrtle,saxaphone,stefax,street_marching_band,swing,trombone,tuba,sampling_plus,audio,mp3,44k,stereo,CBR', "acoustic,female_vocals,guitar,new_orleans,noisecollector,sagetyrtle,saxaphone,stefax,street_marching_band,swing,trombone,tuba" ),
"16737" => array( 'media,remix,bass,cello,chill,downtempo,drums,male_vocals,piano,saxaphone,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "bass,cello,chill,downtempo,drums,male_vocals,piano,saxaphone" ),
"16845" => array( 'media,remix,bpm_080_085,bass,drums,female_vocals,guitar,male_vocals,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,guitar,male_vocals,saxaphone,synthesizer" ),
"16905" => array( 'media,remix,bpm_110_115,female_vocals,hip_hop,male_vocals,mash_up,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,hip_hop,male_vocals,mash_up,saxaphone" ),
"16998" => array( 'media,remix,cello,male_vocals,piano,saxaphone,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "cello,male_vocals,piano,saxaphone" ),
"21683" => array( 'media,remix,bpm_075_080,acoustic,downtempo,female_vocals,fretless,guitar,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "acoustic,downtempo,female_vocals,fretless,guitar,saxaphone" ),
"17101" => array( 'media,remix,how_i_did_it,bpm_095_100,chill,downtempo,hip_hop,jazzy,kcentric,male_vocals,nu_jazz,rap,saxaphone,stefsax,non_commercial_share_alike,audio,mp3,44k,stereo,VBR', "chill,downtempo,hip_hop,jazzy,kcentric,male_vocals,nu_jazz,rap,saxaphone,stefsax" ),
"17348" => array( 'media,remix,bpm_120_125,bass,downtempo,drum,free_jazz,instrumental,saxaphone,viola,non_commercial,audio,mp3,44k,stereo,VBR', "bass,downtempo,drum,free_jazz,instrumental,saxaphone,viola" ),
"17119" => array( 'media,remix,bpm_090_095,chill,chillout,downtempo,electronic,female_vocals,hip_hop,piano,saxaphone,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "chill,chillout,downtempo,electronic,female_vocals,hip_hop,piano,saxaphone" ),
"17205" => array( 'media,remix,ambient,bass,drums,electronic,guitar,male_vocals,piano,saxaphone,synthesizer,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "ambient,bass,drums,electronic,guitar,male_vocals,piano,saxaphone,synthesizer" ),
"23705" => array( 'media,remix,ambient,downtempo,experimental,male_vocals,saxaphone,spoken_word,whitecube,attribution,audio,mp3,44k,stereo,CBR', "ambient,downtempo,experimental,male_vocals,saxaphone,spoken_word,whitecube" ),
"17672" => array( 'media,remix,bpm_100_105,acoustic_guitar_harmonics,blues,brushkit,female_vocals,flute,guitar,jazz,piano,saxaphone,sleaze,non_commercial,audio,mp3,44k,stereo,CBR', "acoustic_guitar_harmonics,blues,brushkit,female_vocals,flute,guitar,jazz,piano,saxaphone,sleaze" ),
"17762" => array( 'media,remix,bpm_065_070,acoustic,bass,hammond,leslie,louis_armstrong,male_vocals,new_orleans,organ,panu_moon,saxaphone,uprightpiano,non_commercial,audio,mp3,44k,stereo,CBR', "acoustic,bass,hammond,leslie,louis_armstrong,male_vocals,new_orleans,organ,panu_moon,saxaphone,uprightpiano" ),
"17823" => array( 'media,remix,bpm_075_080,bass,bassoon,drums,female_vocals,kalimba,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,bassoon,drums,female_vocals,kalimba,saxaphone" ),
"17835" => array( 'media,remix,bpm_140_145,303,acidline,bass,brad_sucks,breaks,club,dnb,drums,electro,electronic,fast,fatboy_slim_style,female_vocals,guitar,male_vocals,mash_up,mellotron,melody,moby_style,piano,pop,rock,soprano_saxophone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "303,acidline,bass,brad_sucks,breaks,club,dnb,drums,electro,electronic,fast,fatboy_slim_style,female_vocals,guitar,male_vocals,mash_up,mellotron,melody,moby_style,piano,pop,rock,soprano_saxophone,synthesizer" ),
"18106" => array( 'media,remix,bpm_110_115,bass,drums,instrumental,jazz,male_vocals,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,instrumental,jazz,male_vocals,saxaphone,synthesizer" ),
"18187" => array( 'media,remix,bass,drums,guitar,male_vocals,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,guitar,male_vocals,piano,saxaphone" ),
"18203" => array( 'media,remix,bpm_120_125,bass,chill,downtempo,drums,guitar,hip_hop,instrumental,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "bass,chill,downtempo,drums,guitar,hip_hop,instrumental,saxaphone" ),
"18234" => array( 'media,remix,bpm_120_125,fail,female_vocals,flute,holidays,lounge,procrastination,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "fail,female_vocals,flute,holidays,lounge,procrastination,saxaphone,spoken_word" ),
"18279" => array( 'media,remix,bpm_130_135,acoustic,bass,female_vocals,honkytonk_piano,jazz,live,quartet,saxaphone,swing,torch,nc_sampling_plus,audio,mp3,44k,stereo,CBR', "acoustic,bass,female_vocals,honkytonk_piano,jazz,live,quartet,saxaphone,swing,torch" ),
"18285" => array( 'media,remix,bpm_085_090,club,downtempo,female_vocals,rain,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "club,downtempo,female_vocals,rain,saxaphone" ),
"18471" => array( 'media,remix,bpm_120_125,couch,guitar,happy_new_year,jazz,lounge,male_vocals,nye_party,saxaphone,upbeat,web_surfing,sampling_plus,audio,mp3,44k,stereo,CBR', "couch,guitar,happy_new_year,jazz,lounge,male_vocals,nye_party,saxaphone,upbeat,web_surfing" ),
"18799" => array( 'media,remix,bpm_075_080,bass,drums,guitar,male_vocals,piano,saxaphone,non_commercial_share_alike,audio,mp3,44k,stereo,CBR', "bass,drums,guitar,male_vocals,piano,saxaphone" ),
"18840" => array( 'media,remix,bpm_125_130,breaks,chill,drums,electronic,female_vocals,funk,jazz,nu_jazz,organ,organ_solo,saxaphone,soul,uptempo,non_commercial,audio,mp3,44k,stereo,VBR', "breaks,chill,drums,electronic,female_vocals,funk,jazz,nu_jazz,organ,organ_solo,saxaphone,soul,uptempo" ),
"19952" => array( 'media,remix,bpm_085_090,trackback,in_video,female_vocals,fretless,guitar,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,fretless,guitar,saxaphone" ),
"23868" => array( 'media,remix,downtempo,male_vocals,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,male_vocals,piano,saxaphone" ),
"19488" => array( 'media,remix,bpm_090_095,funky,guitar,melody,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "funky,guitar,melody,saxaphone" ),
"19609" => array( 'media,remix,bpm_125_130,trackback,in_video,chill,female_vocals,fretless,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "chill,female_vocals,fretless,saxaphone" ),
"19692" => array( 'media,remix,bpm_115_120,experimental,fretless,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "experimental,fretless,male_vocals,saxaphone" ),
"19730" => array( 'media,remix,bpm_085_090,trackback,in_video,downtempo,female_vocals,fretless,guitar,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,female_vocals,fretless,guitar,saxaphone" ),
"19736" => array( 'sample,media,bpm_085_090,bass,gandhi_vox,guitar,sample_pack,saxaphone,non_commercial,archive,zip', "bass,gandhi_vox,guitar,sample_pack,saxaphone" ),
"19771" => array( 'media,remix,bpm_095_100,ambient,saxaphone,spoken_word,zither,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,saxaphone,spoken_word,zither" ),
"19806" => array( 'media,remix,bpm_090_095,brass,clav,groove,hip_hop,jazz,male_vocals,organ,porn,saxaphone,scomber365,shuffle,soul,urban,non_commercial,audio,mp3,44k,stereo,CBR', "brass,clav,groove,hip_hop,jazz,male_vocals,organ,porn,saxaphone,scomber365,shuffle,soul,urban" ),
"19961" => array( 'media,remix,bpm_060_065,acoustic,ambient,chillout,downtempo,electronic,guitar,saxaphone,trifonic,non_commercial,audio,mp3,44k,stereo,CBR', "acoustic,ambient,chillout,downtempo,electronic,guitar,saxaphone,trifonic" ),
"19995" => array( 'media,remix,bpm_095_100,experimental,jazz_rap,male_vocals,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "experimental,jazz_rap,male_vocals,saxaphone,spoken_word" ),
"20189" => array( 'media,remix,acoustic,bass,drums,guitar,male_vocals,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "acoustic,bass,drums,guitar,male_vocals,piano,saxaphone" ),
"20420" => array( 'media,remix,bpm_060_065,bass,downtempo,female_vocals,jazz,melody,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "bass,downtempo,female_vocals,jazz,melody,piano,saxaphone" ),
"20572" => array( 'media,remix,bpm_120_125,drums,funky,guitar,male_vocals,pop,saxaphone,scomber365,non_commercial,audio,mp3,44k,stereo,CBR', "drums,funky,guitar,male_vocals,pop,saxaphone,scomber365" ),
"20748" => array( 'media,remix,bpm_120_125,cello,jazz,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "cello,jazz,saxaphone" ),
"20809" => array( 'media,remix,bpm_110_115,trackback,in_video,anchor_mejans,bass_clarinet,clarinet,downtempo,hepepe,jazz,mikolaj_trzaska,minimal,piano,spoken_word,stefsax,non_commercial,audio,mp3,44k,stereo,VBR', "anchor_mejans,bass_clarinet,clarinet,downtempo,hepepe,jazz,mikolaj_trzaska,minimal,piano,spoken_word,stefsax" ),
"20810" => array( 'media,remix,bpm_115_120,female_vocals,guitar,latin,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,guitar,latin,piano,saxaphone" ),
"23192" => array( 'media,remix,bpm_100_105,female_vocals,music_box,saxaphone,spoken_word,tabla,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,music_box,saxaphone,spoken_word,tabla" ),
"20967" => array( 'media,remix,bpm_125_130,trackback,in_podcast,bass,drums,female_vocals,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "bass,drums,female_vocals,male_vocals,saxaphone" ),
"20990" => array( 'media,remix,bpm_080_085,how_i_did_it,acoustic,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "acoustic,piano,saxaphone" ),
"21046" => array( 'sample,media,bpm_090_095,bass,beats,chill,downbeat,downtempo,electric_piano,jazzy,loops,saxaphone,non_commercial,archive,zip', "bass,beats,chill,downbeat,downtempo,electric_piano,jazzy,loops,saxaphone" ),
"21104" => array( 'media,remix,promo,bumper,female_vocals,posh_tart,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "bumper,female_vocals,posh_tart,saxaphone,spoken_word" ),
"21213" => array( 'media,remix,bpm_100_105,downtempo,male_vocals,melody,orchestral,piano,pop,sad,saxaphone,violin,non_commercial,audio,mp3,44k,stereo,VBR', "downtempo,male_vocals,melody,orchestral,piano,pop,sad,saxaphone,violin" ),
"21460" => array( 'media,remix,bpm_090_095,bass,drums,guitar,heat,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,guitar,heat,saxaphone,spoken_word" ),
"21778" => array( 'media,remix,funky,german,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "funky,german,male_vocals,saxaphone" ),
"21830" => array( 'media,remix,bpm_085_090,guitar,hip_hop,live,lots_happening,male_vocals,retro,rock,saxaphone,scomber_junior,synthesizer,non_commercial,audio,mp3,44k,stereo,VBR', "guitar,hip_hop,live,lots_happening,male_vocals,retro,rock,saxaphone,scomber_junior,synthesizer" ),
"21867" => array( 'sample,media,bpm_110_115,echo,ethereal,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "echo,ethereal,saxaphone" ),
"21988" => array( 'media,remix,bpm_120_125,bass,drums,female_vocals,guitar,percussion,piano,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,female_vocals,guitar,percussion,piano,saxaphone,synthesizer" ),
"22015" => array( 'media,remix,bpm_115_120,acidified,experimental,female_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "acidified,experimental,female_vocals,saxaphone" ),
"22040" => array( 'media,remix,bpm_085_090,bass,chill,drums,electric,eyes,hip_hop,hults,joshua,magic,new,piano,pop,saxaphone,spoken_word,vocals,your,non_commercial,audio,mp3,44k,stereo,CBR', "bass,chill,drums,electric,eyes,hip_hop,hults,joshua,magic,new,piano,pop,saxaphone,spoken_word,vocals,your" ),
"22063" => array( 'media,remix,bpm_115_120,acoustic,bass,blues,clarinet,female_vocals,guitar,jurgen_herrmann,raymond_blanchet,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "acoustic,bass,blues,clarinet,female_vocals,guitar,jurgen_herrmann,raymond_blanchet,saxaphone" ),
"22067" => array( 'sample,media,bpm_120_125,bass,loops,piano,poetry,saxaphone,trombone,trumpet,non_commercial,audio,wma,44k,stereo,CBR', "bass,loops,piano,poetry,saxaphone,trombone,trumpet" ),
"22186" => array( 'media,remix,experimental,instrumental,saxaphone,trip_hop,non_commercial,audio,mp3,44k,stereo,CBR', "experimental,instrumental,saxaphone,trip_hop" ),
"22187" => array( 'sample,media,sopransaxophon,non_commercial,audio,mp3,44k,stereo,CBR', "sopransaxophon" ),
"22188" => array( 'media,remix,drums,experimental,loops,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "drums,experimental,loops,male_vocals,saxaphone" ),
"22253" => array( 'sample,media,bpm_100_105,funky,guitar,hip_hop,male_vocals,melody,pop,saxaphone,slap_bass,synthesizer,non_commercial,archive,zip', "funky,guitar,hip_hop,male_vocals,melody,pop,saxaphone,slap_bass,synthesizer" ),
"22389" => array( 'media,remix,bpm_100_105,bass,drums,funky,hip_hop,rap,saxaphone,snarecajon,synthesizer,non_commercial,audio,mp3,44k,stereo,VBR', "bass,drums,funky,hip_hop,rap,saxaphone,snarecajon,synthesizer" ),
"22512" => array( 'media,remix,bass,downtempo,drums,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "bass,downtempo,drums,saxaphone,spoken_word" ),
"22513" => array( 'media,remix,ambient,female_vocals,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,female_vocals,saxaphone,synthesizer" ),
"22514" => array( 'media,remix,ambient,bass_clarinet,downtempo,drums,female_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,bass_clarinet,downtempo,drums,female_vocals,saxaphone" ),
"22569" => array( 'media,remix,bpm_080_085,how_i_did_it,duet,female_vocals,male_vocals,piano,saxaphone,synthesizer,attribution,audio,mp3,44k,stereo,CBR', "duet,female_vocals,male_vocals,piano,saxaphone,synthesizer" ),
"22579" => array( 'media,remix,downtempo,female_vocals,guitar,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,female_vocals,guitar,saxaphone" ),
"22592" => array( 'media,remix,bpm_170_175,editorial_pick,bass,breaks,chill,electronic,female_vocals,jungle,keyboards,male_vocals,saxaphone,smooth,smooth_dnb,synthesizer,nc_sampling_plus,audio,mp3,44k,stereo,VBR', "bass,breaks,chill,electronic,female_vocals,jungle,keyboards,male_vocals,saxaphone,smooth,smooth_dnb,synthesizer" ),
"22606" => array( 'media,remix,downtempo,female_vocals,guitar,jazz,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,female_vocals,guitar,jazz,saxaphone" ),
"22789" => array( 'media,remix,bass_clarinet,downtempo,drums,guitar,loops,male_vocals,saxaphone,attribution,audio,mp3,44k,stereo,CBR', "bass_clarinet,downtempo,drums,guitar,loops,male_vocals,saxaphone" ),
"23032" => array( 'sample,media,bpm_120_125,bass,beat,gelach,loops,mantra,saxaphone,sitar,tantrical,non_commercial,archive,zip', "bass,beat,gelach,loops,mantra,saxaphone,sitar,tantrical" ),
"23116" => array( 'media,remix,bpm_065_070,ballad,bass,female_vocals,harmonies,harp,les_paul,long_lost_love,narva9,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "ballad,bass,female_vocals,harmonies,harp,les_paul,long_lost_love,narva9,saxaphone,synthesizer" ),
"23414" => array( 'media,remix,bpm_120_125,female_vocals,guitar,loops,male_vocals,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,guitar,loops,male_vocals,saxaphone,synthesizer" ),
"23468" => array( 'media,remix,downtempo,female_vocals,guitar,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,female_vocals,guitar,saxaphone" ),
"23500" => array( 'sample,media,downtempo,guitar,instrumental,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,guitar,instrumental,saxaphone" ),
"23512" => array( 'media,remix,bass,drums,experimental,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "bass,drums,experimental,saxaphone,spoken_word" ),
"23520" => array( 'media,remix,bpm_085_090,bass,boodah_toade,cajon,funky,hip_hop,rap,saxaphone,synthesizer,vocoder,non_commercial,audio,mp3,44k,stereo,VBR', "bass,boodah_toade,cajon,funky,hip_hop,rap,saxaphone,synthesizer,vocoder" ),
"23601" => array( 'media,remix,bpm_065_070,trackback,in_video,ambient,bass,chill,downtempo,drums,fminor,guitar,hip_hop,male_vocals,rap,saxaphone,slow_airy_groove,violin,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,bass,chill,downtempo,drums,fminor,guitar,hip_hop,male_vocals,rap,saxaphone,slow_airy_groove,violin" ),
"23626" => array( 'media,remix,bass,downtempo,drums,female_vocals,guitar,male_vocals,pop,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,downtempo,drums,female_vocals,guitar,male_vocals,pop,saxaphone" ),
"23714" => array( 'media,remix,experimental,guitar,jazz,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "experimental,guitar,jazz,male_vocals,saxaphone" ),
"23736" => array( 'media,remix,trackback,in_web,ambient,piano,saxaphone,whitecube,attribution,audio,mp3,44k,stereo,CBR', "ambient,piano,saxaphone,whitecube" ),
"23737" => array( 'media,remix,bpm_065_070,trackback,in_video,ambient,ballad,bass,blues,chill,downtempo,drums,f_minor,guitar,hip_hop,loops,male_vocals,melody,portuguese,saxaphone,slap_johnson,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,ballad,bass,blues,chill,downtempo,drums,f_minor,guitar,hip_hop,loops,male_vocals,melody,portuguese,saxaphone,slap_johnson" ),
"23836" => array( 'media,remix,bpm_085_090,acoustic,downtempo,female_vocals,fretless,guitar,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "acoustic,downtempo,female_vocals,fretless,guitar,saxaphone" ),
"23900" => array( 'media,remix,female_vocals,guitar,jazz,saxaphone,trumpet,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,guitar,jazz,saxaphone,trumpet" ),
"24014" => array( 'media,remix,downtempo,female_vocals,fretlessguitar,male_vocals,piano,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,female_vocals,fretlessguitar,male_vocals,piano,saxaphone" ),
"24052" => array( 'media,remix,bpm_095_100,bass,drums,funky,guitar,male_vocals,saxaphone,songboy3,synthesizer,non_commercial,audio,mp3,44k,stereo,VBR', "bass,drums,funky,guitar,male_vocals,saxaphone,songboy3,synthesizer" ),
"24112" => array( 'media,remix,bpm_085_090,bass,deleve,drums,funky,guitar,hip_hop,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "bass,deleve,drums,funky,guitar,hip_hop,saxaphone" ),
"24119" => array( 'media,remix,bpm_110_115,bass,beckfords,cajon,drums,funky,male_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,VBR', "bass,beckfords,cajon,drums,funky,male_vocals,saxaphone" ),
"24163" => array( 'media,remix,female_vocals,guitar,jazz,saxaphone,synthesizer,non_commercial,audio,mp3,44k,stereo,CBR', "female_vocals,guitar,jazz,saxaphone,synthesizer" ),
"24229" => array( 'media,remix,ambient,downtempo,female_vocals,guitar,jazz,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,downtempo,female_vocals,guitar,jazz,saxaphone" ),
"24230" => array( 'media,remix,bpm_090_095,chill,electro,erosion,female_vocals,instrumental,kaos_fx_56,londonbridgez,piano,saxaphone,shaker,stefsax,stylus,non_commercial,audio,mp3,44k,stereo,CBR', "chill,electro,erosion,female_vocals,instrumental,kaos_fx_56,londonbridgez,piano,saxaphone,shaker,stefsax,stylus" ),
"24264" => array( 'media,remix,bpm_100_105,downtempo,electronic,female_vocals,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "downtempo,electronic,female_vocals,saxaphone" ),
"24285" => array( 'media,remix,ambient,experimental,female_vocals,saxaphone,spoken_word,non_commercial,audio,mp3,44k,stereo,CBR', "ambient,experimental,female_vocals,saxaphone,spoken_word" ),
"24337" => array( 'media,remix,bpm_095_100,bass,guitar,loops,pop,saxaphone,non_commercial,audio,mp3,44k,stereo,CBR', "bass,guitar,loops,pop,saxaphone" ),
);

$goo = file_get_contents('ancient');
$goo = str_replace("\\\\'","\\'",$goo);
$text = '$data = array( ' . $goo  . ');';
eval($text);
$keys = array_keys($data);
foreach( $keys as $upload_id )
{
    $ex = unserialize(str_replace('\\n',"\n",$data[$upload_id][1]));
    if( empty($ex) )
    {
        die( substr($data[$upload_id][1],600));
    }
    $data[$upload_id][1] = $ex['usertags'];
}
$cnt1 = count($fixarray);
$cnt2 = count($data);
$fixarray = array_merge($fixarray,$data);
$cnt3 = count($fixarray);

print "Does {$cnt1} + {$cnt2} = {$cnt3} ??\n ";

$table = new CCTable('cc_tbl_uploads','upload_id');
foreach( $fixarray as $upload_id => $data )
{
    $extra = CCDatabase::QueryItem('SELECT upload_extra FROM cc_tbl_uploads WHERE upload_id = '. $upload_id);
    if( $extra )
    {
        print 'Updating: ' . $upload_id . "\n";
        $extra = unserialize($extra);
        $extra['usertags'] = $data[1];
        $extra = serialize($extra);
        $args = array();
        $args['upload_id']    = $upload_id;
        $args['upload_tags']  = $data[0];
        $args['upload_extra'] = $extra;
        $table->Update($args);        
    }
    else
    {
        print 'Skipping: ' . $upload_id . "\n";
    }
}

?>
