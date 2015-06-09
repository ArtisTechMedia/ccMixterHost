<?
define('CC_HOST_CMD_LINE', 1 );
chdir( dirname(__FILE__) . '/..');
$NO_EXTRANEOUS_OUTPUT = true;
require_once('cc-cmd-line.inc');
require_once('cchost_lib/cc-query.php');
require_once('cchost_lib/cc-upload.php');
require_once('cchost_lib/cc-remix.php');
require_once('cchost_lib/cc-uploadapi.php');
require_once('cchost_lib/cc-tags.php');
require_once('cchost_lib/cc-files.php');
require_once('cchost_lib/ccextras/cc-ccplus-api.inc');

/*

type	count	
fixedfiles	[391]	Moved ccplus tag from submit file record to upload record
samplestagged	[825]	These samples/pells were newly tagged as ccplus
attrsources	[278]	These remixes were erroniously tagged (by me) and were untagged
remixestagged	[517]	These remixes were newly tagged as ccplus
remixes_untagged	[0]	These remixes were previously ccplus but were now untagged
suspicious	[40]	These remixes were tagged as ccplus - now ccplus_verify
verified	[6]	These were hand verified by admins
*/
define('ATTR_SOURCES', 'attrsources');
define('FIXED_FILES', 'fixedfiles');
define('NOT_OPT_IN', 'nonoptin');

function ccPlusVerifiedUploads()
{
    $verified = array(
        6134,
        17595,
        20984,
        22692,
        24486,
        33913,
        34503,
        38676,
        39444,
        39698,
        44796,
        44848,
        46471,
        46562,
        46582,
        46636,
        46650,
        47116,
        47419,
        48167,
        48417,
        48427);

    return $verified;
}


function ccPlusVerifiedUsers()
{
    $plusArtists = array( 
        "admiralbob77",
        "airtone",
        "AlexBeroza",
        "alexjc916",
        "audiotechnica",
        "BOCrew",
        "Carosone",
        "casimps1",
        "cdk",
        "ChuckBerglund",
        "Clulow_Forester",
        "copperhead",
        "Coruscate",
        "daniloprates",
        "djlang59",
        "emilyrichards",
        "f_fact",
        "Fireproof_Babies",
        "geertveneklaas",
        "George_Ellinas",
        "go1dfish",
        "greyguy",
        "gurdonark",
        "Haskel",
        "hoop_it_up",
        "Javolenus",
        "JeffSpeed68",
        "jlbrock44",
        "Kirkoid",
        "lancefield",
        "Levihica",
        "Loveshadow",
        "mindmapthat",
        "NiGiD",
        "panumoon",
        "phildann",
        "pieropeluche",
        "Quarkstar",
        "Robbero",
        "RobertWarrington",
        "rocavaco",
        "SackJo22",
        "sbarg",
        "scomber",
        "snowflake",
        "spinmeister",
        "stellarartwars",
        "stohgs",
        "sunhawken",
        "Super_Sigil",
        "teamsmileandnod",
        "TheDICE",
        "unreal_dm",
        "urmymuse",
        "victor",
        "Vidian",
        "VJ_Memes",
        "Wired_Ant",
        "zep_hurme"
    );
    return $plusArtists;
}

function ccPlusAddOptInUsers()
{
    $plusArtists =  ccPlusVerifiedUsers();
    foreach( $plusArtists as $username )
    {
        ccPlusOptInUser($username,false);
    }    
}

function ccPlusFixAttributionSourcedRemixes()
{
    $sql1 = <<<EOF
SELECT upload_id FROM cc_tbl_uploads 
    WHERE upload_num_sources > 0 AND
          upload_tags LIKE '%,ccplus,%'    
EOF;

    $upload_ids = CCDatabase::QueryItems($sql1);
    $untagged = array();
    
    foreach( $upload_ids as $upload_id )
    {
        $sql2 = <<<EOF
SELECT upload_license, tree_child, upload_tags FROM cc_tbl_tree 
    JOIN cc_tbl_uploads ON tree_parent = upload_id
    WHERE tree_child = {$upload_id} AND 
          upload_tags NOT LIKE '%,ccplus,%' AND
          upload_license NOT LIKE 'cczer%' AND
          upload_license LIKE 'attrib%' 
EOF;
                          
        $licrows = CCDatabase::QueryRows($sql2);
        
        if( !empty($licrows) )
        {
            // sources were mislabled because the sources
            // were attribution
            CCUploadAPI::UpdateCCUD( $upload_id, '', 'ccplus' );
            array_push($untagged, $upload_id);
            debugP('-');
        }
    }
    return array( ATTR_SOURCES => $untagged );
}

function ccPlusFixUploadsWithSumbitFormTags()
{
    $sql11 = "SELECT DISTINCT file_upload as upload_id  FROM cc_tbl_files WHERE file_extra REGEXP '[^a-z]ccplus'";
    $upload_ids = CCDatabase::QueryItems($sql11);
    $sql9 = "SELECT file_id, file_extra FROM cc_tbl_files WHERE file_extra REGEXP '[^a-z]ccplus'";
    $rows = CCDatabase::QueryRows($sql9);
    
    // 1. Whack the ccplus tag in the file's CCUD
    foreach( $rows as $F )
    {
        $filexes_sr = $F['file_extra'];
        $filexes = unserialize($filexes_sr);
        $tags = CCTag::TagSplit($filexes['ccud']);
        $diff = array_diff($tags,array('ccplus'));
        $filexes['ccud'] = join(',',$diff);
        $F['file_extra'] = serialize($filexes);
        $file =& CCFiles::GetTable();
        $file->Update($F);
        debugP('*');
    }
    
    // 2. Force the ccplus into the upload's CCUD
    //    (later we'll evaluate if the upload should even be tagged with CCUD)
    foreach( $upload_ids as $upload_id )
    {
        CCUploadAPI::UpdateCCUD( $upload_id, 'ccplus', '' );
        debugP('$');
    }
    
    return array( FIXED_FILES => $upload_ids );
}

function ccPlusHandVerifySuspiciousUploads($results)
{
    $ids = $results[SUSPICIOUS_REMIXES];
    $verified = ccPlusVerifiedUploads();
    $v = array();
    $s = array();
    foreach( $ids as $id )
    {
        if( in_array($id, $verified) )
        {
            ccPlusMarkUploadAsVerified($id);
            array_push($v,$id);
        }
        else
        {
            ccPlusMarkUploadAsSuspicious($id);
            array_push($s,$id);
        }
    }
    
    return array( SUSPICIOUS_REMIXES => $s,
                  HAND_VERIFIED => $v );
}

function _addQuotes($str)
{
    return "'{$str}'";
}

function ccPlusUntagNonOptInUploads()
{
    $quotedUsernamesArr = array_map('_addQuotes', ccPlusVerifiedUsers());
    $quotedUsernames = implode(',',$quotedUsernamesArr);
    $verified = ccPlusVerifiedUploads();
    $v = array();
    $n = array();
    $sql = <<<EOF
        SELECT upload_id FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user = user_id
            WHERE upload_tags LIKE '%,ccplus,%' AND
                  user_name NOT IN ({$quotedUsernames}) 
            ORDER BY user_name   
EOF;

    $upload_ids = CCDatabase::QueryItems($sql);
    
    foreach( $upload_ids as $id )
    {
        if( in_array($id, $verified) )
        {
            ccPlusMarkUploadAsVerified($id);
            array_push($v,$id);
        }
        else
        {        
            CCUploadAPI::UpdateCCUD( $id, 'ccplus_nooptin', 'ccplus,ccplus_verify,ccplus_verified' );
            array_push($n,$id);
            debugP('T');
        }
    }
    
    return array( NOT_OPT_IN => $n, 
                    HAND_VERIFIED => $v
                     );
}


function perform()
{
    ccPlusAddOptInUsers();

    $results = array_merge_recursive(   ccPlusFixUploadsWithSumbitFormTags(), 
                                        ccPlusTagNoSourceUploadsForAllUsers(),
                                        ccPlusUntagNonOptInUploads(),
                                        ccPlusFixAttributionSourcedRemixes(),
                                        ccPlusCheckAndFixTagForRemixesForAllUsers()) ;   
        
    $r2 = ccPlusHandVerifySuspiciousUploads($results);
    
    foreach( $r2 as $k => $v )
    {
        $results[$k] = $v;
    }
    
    printHeader($results);
    
    foreach( $results as $key => $ids )
    {
        report($key,$ids,$key);
    }
}



perform();

function docForSection($section)
{
    switch( $section )
    {
        case FIXED_FILES:
        {
            return 'Moved ccplus tag from submit file record to upload record';
        }
        case SAMPLES_TAGGED:
        {
            return 'These samples/pells were newly tagged as ccplus';
        }
        case REMIXES_TAGGED:
        {
            return 'These remixes were newly tagged as ccplus';
        }
        case REMIXES_UNTAGGED:
        {
            return 'These remixes were previously ccplus but were now untagged';
        }
        case SUSPICIOUS_REMIXES:
        {
            return 'These remixes were tagged as ccplus - now ccplus_verify';
        }
        case ATTR_SOURCES:
        {
            return 'These remixes were erroniously tagged (by me) and now untagged';
        }
        case HAND_VERIFIED:
        {
            return 'These were hand verified by admins';
        }
        case NOT_OPT_IN:
        {
            return 'These were tagged by users w/o OptIn aggreements and now ccplus_nooptin.';
        }
    }
}

function printHeader($results)
{
?><html>
<head>
<style>
body, table, td {
    font-family: Avenir-Next, Verdana;
    font-size: 10px;    
}
.sources_table {
    display: none;
    background-color: #CFC;
    margin: 3px;
    padding: 4px;
    width: 75%;
}

.files_table {
    display: none; 
    background-color: #BBF;
    margin: 3px;
}

i {
    color: brown;
}
.tags {
    font-size: 9px;
}
.record {
    padding: 8px;
}
</style>
<script>
function toggle_sources(id)
{
    var div = document.getElementById( id );
    if( div.style.display != "block" )
        div.style.display = "block";
    else
        div.style.display = "none";
}
</script>
</head>    
<body>
<?

    print "<table><tr><th>type</th><th>count</th><th></th></tr>\n";    
    foreach( $results as $name => $arr )
    {
        $count = count($arr);
        $doc = docForSection($name);
        print("<tr><td><a href=\"#{$name}\">{$name}</td><td>[{$count}]</td><td>{$doc}</td></tr>\n");
    }
        
    print("</table>");    
}

function htmlForFiles($ids,$name,$sources = true,$depth=0)
{
    if( $depth > 2 )
    {
        return '...';
    }
    $ids = implode(',',$ids);
    $sql = "SELECT upload_id, upload_name, upload_tags, user_name FROM cc_tbl_uploads " .
            "JOIN cc_tbl_user ON upload_user = user_id WHERE upload_id IN ({$ids}) ORDER BY user_name";
    $rows = CCDatabase::QueryRows($sql);
    $html = '<div class="record">';
    foreach( $rows as $R )
    {
        $id = $R['upload_id'];
        $html .= '<div class="line">';
        $html .= "<a href=\"/files/{$R['user_name']}/{$id}\">{$R['upload_name']}</a> by <i>{$R['user_name']}</i>";
        if( $sources )
        {
            $sql3 = "SELECT tree_parent FROM cc_tbl_tree WHERE tree_child = {$id}";
            $src_ids = CCDatabase::QueryItems($sql3);
            if( !empty($src_ids) )
            {        
                $tid = "t_{$name}_{$depth}_{$id}";
                $html .= " <button class=\"sources_link\" onclick=\"toggle_sources('{$tid}');\">sources</button>";
                $html .= "<div id=\"{$tid}\" class=\"sources_table\">";
                $html .= htmlForFiles($src_ids,$name,true,$depth+1);
                $html .= "</div>";
            }        
        }
        $tags = str_replace(',', ', ', $R['upload_tags']);
        $tags = str_replace('ccplus,',           '<b>ccplus</b>,',                             $tags);
        $tags = str_replace('ccplus_verify,',    '<b style="color:red">ccplus_verify</b>,',    $tags);
        $tags = str_replace('ccplus_verified,',  '<b style="color:green">ccplus_verified</b>,',$tags);
        $tags = str_replace('ccplus_nooptin,',   '<b style="color:blue">ccplus_nooptin</b>,',  $tags);
        $html .= ' ' . $tags;
        $html .= "</div>\n";
    }
    $html .= '</div>';
    return $html;
}

function report($name,$ids,$linkname)
{
    print "<h2><a name=\"{$linkname}\">$name</h2>\n";
    global $CC_GLOBALS;
    $CC_GLOBALS['querylimit'] = 20000; // FIXME

    if( empty($ids) )
    {
        print '<h4>(none)<h4>';
        return;
    }
    print htmlForFiles($ids,$linkname);
}
