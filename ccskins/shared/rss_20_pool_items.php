<?/*
[meta]
    type = feed
    desc = _('RSS 2.0 Feed for Pool Items')
    formats = rss
    datasource = pool_items
    dataview = pool_item_list
[/meta]
*/

print '<?xml version="1.0" encoding="utf-8" ?>' 
?>
<rss version="2.0" 
   xmlns:content="http://purl.org/rss/1.0/modules/content/"
   xmlns:cc="http://backend.userland.com/creativeCommonsRssModule"   
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   >
  <channel>
    <title><?= $A['channel_title'] ?></title>
    <link><?= $A['home-url'] ?></link>
    <description><?= $A['channel_description'] ?></description>
    <language>en-us</language>

    <pubDate><?= $A['rss-pub-date'] ?></pubDate>
    <lastBuildDate><?= $A['rss-build-date'] ?></lastBuildDate>
    <?
        if( !empty($A['records']) ) { foreach( $A['records'] as $item ) {

    ?>
    <item>
      <title><?= $item['pool_item_name'] ?></title>
      <link><?= $item['pool_item_url'] ?></link>
      <pubDate><?= $item['pool_item_date'] ?></pubDate>
      <dc:creator><?= $item['pool_item_artist'] ?></dc:creator>
      <author><?= $item['pool_item_artist'] ?></author>
      <description><?= $item['pool_item_description_plain'] ?></description>
      <content:encoded  type="html"><![CDATA[<?= $item['pool_item_description'] ?>]]></content:encoded>
      <guid><?= $item['pool_item_url'] ?></guid>
      <cc:license><?= $item['license_url'] ?></cc:license>
    </item>
    <?
        } }
    ?>
  </channel>
</rss>
