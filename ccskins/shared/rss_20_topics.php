<?/*
[meta]
    type = feed
    desc = _('RSS 2.0 Feed for Topics')
    formats = rss
    dataview = rss_20_topics
[/meta]
*/

print '<?xml version="1.0" encoding="utf-8" ?>' 
?>

<rss version="2.0" 
   xmlns:content="http://purl.org/rss/1.0/modules/content/"
   xmlns:cc="http://creativecommons.org/ns#"   
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
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
      <title><?= $item['topic_name'] ?></title>
      <link><?= $item['topic_permalink'] ?></link>
      <pubDate><?= $item['rss_pubdate'] ?></pubDate>
      <dc:creator><?= $item['user_real_name'] ?></dc:creator>
      <description><?= $item['topic_text_plain'] ?></description>
      <content:encoded><![CDATA[<?= $item['topic_text_html'] ?>]]></content:encoded>
      <guid><?= $item['topic_permalink'] ?></guid>
      <cc:license><?= $A['topics_license_url'] ?></cc:license>
      <? if( !empty($item['enclosure_url']) ) { ?>
         <enclosure url="<?= $item['enclosure_url']?>" length="<?= $item['enclosure_size']?>" type="<?= $item['enclosure_type']?>"></enclosure>
      <? } ?>
    </item>
    <?
        } }
    ?>
  </channel>
</rss>
