<?/*
[meta]
    type = feed
    desc = _('XSPF 1.0 Playlist')
    formats = xspf
    datasource = uploads
    dataview = xspf
    embedded = 1
[/meta]
[dataview]
function xspf_dataview()
{
    $avatar_sql = cc_get_user_avatar_sql();

    $sql =<<<EOF
        SELECT $avatar_sql, upload_id, upload_name, upload_name, upload_contest, user_name, user_real_name,
        license_url
        %columns%
        FROM cc_tbl_uploads
        JOIN cc_tbl_user ON upload_user=user_id
        JOIN cc_tbl_licenses ON upload_license=license_id
        %joins%
        %where%
        %order%
        %limit%
EOF;

    return array(   'sql' => $sql,
                    'e' => array(
                            CC_EVENT_FILTER_DOWNLOAD_URL,
                            ) );
}
[/dataview]
*/

print '<?xml version="1.0" encoding="utf-8" ?>' 
?>

<playlist version="1" xmlns="http://xspf.org/ns/0/">

<title><?= $A['channel_title'] ?></title>
<creator><?= $A['root-url'] ?></creator>
<annotation><?= $A['channel_description'] ?></annotation>
<info><?= $A['channel_description'] ?></info>
<location><?= $A['raw_feed_url'] ?></location>
<date><?= $A['xspf-pub-date'] ?></date>

<trackList>
<?
if( !empty($A['records']) )
{
    foreach($A['records'] as $item) 
    {
?>
    <track>
        <location><?= $item['download_url'] ?></location>
        <identifier><?= $item['upload_id'] ?></identifier>
        <title><?= $item['upload_name'] ?></title>
        <creator><?= $item['user_real_name'] ?></creator>
        <duration><?= $item['files'][0]['file_rawsize'] ?></duration>
        <meta rel="<?= $item['license_url'] ?>"><?= $item['license_url'] ?></meta>
        <image><?= $item['user_avatar_url'] ?></image>        
    </track>
<?
    }
}
?>
</trackList>
</playlist>
