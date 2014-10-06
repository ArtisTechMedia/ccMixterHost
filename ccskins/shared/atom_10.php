<?/*
[meta]
    type = feed
    desc = _('Atom 1.0 Feed')
    dataview = atom
    embedded = 1
    formats = atom
    datasource = uploads
[/meta]
[dataview]
function atom_dataview()
{
    $ccf = ccl('files') . '/';
    $ccp = ccl('people') . '/';
    $GM = CCUtil::GetGMZone();

    // 2007-12-18T16:36:37-08:00
    // %Y-%c-%dT%T

    $sql =<<<EOF
        SELECT upload_id, upload_name, upload_name, upload_contest, user_name, user_real_name,
        CONCAT( '$ccf', user_name, '/', upload_id ) as file_page_url, 
        CONCAT( '$ccp', user_name ) as artist_page_url, 
        upload_tags, license_url,
        upload_description as format_text_upload_description,
        CONCAT( DATE_FORMAT(upload_date,'%Y-%c-%dT%T'), '$GM' ) as atom_pubdate
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
                            CC_EVENT_FILTER_FORMAT,
                            ) );
}
[/dataview]
*/

print '<?xml version="1.0" encoding="utf-8" ?>' 
?>

<feed xmlns="http://www.w3.org/2005/Atom">

<title><?= $A['channel_title'] ?></title>
<link rel="self" href="<?= $A['raw_feed_url'] ?>"/>
<link rel="alternate" href="<?= ccl() ?>"/>
<updated><?= $A['atom-pub-date'] ?></updated>
<id><?= $A['feed_url'] ?></id>
<?
if( !empty($A['records']) )
{
    foreach( $A['records'] as $item )
    {
?>
    <entry>
      <id><?= $item['file_page_url'] ?></id>
      <title><?= $item['upload_name'] ?></title>
      <author>
        <name><?= $item['user_real_name'] ?></name>
        <uri><?= $item['artist_page_url'] ?></uri>
      </author>
      <link rel="alternate" href="<?= $item['file_page_url'] ?>" type="text/html" />
      <? 
            if( empty($item['files'][0]['file_format_info']['mime_type']) )
                d($item);
    ?>
      <link rel="enclosure" href="<?= $item['files'][0]['download_url'] ?>" length="<?= $item['files'][0]['file_rawsize'] ?>" 
        type="<?= $item['files'][0]['file_format_info']['mime_type'] ?>"/>

        <?
        $tags = split(',',$item['upload_tags']);
        foreach( $tags as $tag )
        { 
            if( !empty($tag) )
            {
                ?><category term="<?= $tag?>" /><?
            }
        }
?>


      <updated><?= $item['atom_pubdate'] ?></updated>
      <content type="text/plain"><?= $item['upload_description_plain'] ?> 
      
      <?= $T->String('str_download_from') ?>: <?= $item['files'][0]['download_url'] ?>
      </content>
      <link rel="license" href="<?= $item['license_url'] ?>" type="text/html" />
  
  </entry>
<?
    }

} ?>
</feed>
