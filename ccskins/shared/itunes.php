<?/*
[meta]
    type = feed
    desc = _('iTunes Podcast Feed')
    dataview = rss_20_topics
    type=podcast
    formats = rss
[/meta]
*/


/*
   ATTN admin: You can add these tags by browsing to: admin/templatetags
*/

if( empty($A['itunes-feed-owner-name'])) {
    $A['itunes-feed-owner-name'] = $A['site-title'];
}

if( empty($A['itunes-feed-copyright-owner'])) {
    $A['itunes-feed-copyright-owner'] = $A['itunes-feed-owner-name'];
}

if( empty($A['itunes-summary'])) {
    $A['itunes-summary'] = $A['channel_description'];
}

if( empty($A['itunes-title'])) {
    $A['itunes-title'] = $A['channel_title'];
}

if( empty($A['itunes-subtitle'])) {
    $A['itunes-subtitle'] = $A['channel_description'];
}
if( empty($A['itunes-logo'])) {
    $A['itunes-logo'] = ccd($A['logo']['src']);
}
else if( strpos($A['itunes-logo'], 'http://') === false ) {
    $A['itunes-logo'] = ccd($A['itunes-logo']);
}
if( empty($A['itunes-url'])) {
    $A['itunes-url'] = $A['home-url'];
}
else if( strpos($A['itunes-url'], 'http://') === false ) {
    $A['itunes-url'] = ccl($A['itunes-url']);
}

print '<?xml version="1.0" encoding="utf-8" ?>' 
?>

<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" 
     xmlns:atom="http://www.w3.org/2005/Atom"
     version="2.0">
<channel>
<title><?= $A['itunes-title']?></title>
<link><?= $A['itunes-url']?></link>
<description><?= $A['itunes-summary']?></description>
<itunes:summary><?= $A['itunes-summary']?></itunes:summary>
<language><?= $A['lang_xml']?></language>
<copyright>&#x2117; &amp; &#xA9; <? print date('Y') . ' ' . $A['itunes-feed-copyright-owner']; ?></copyright>
<itunes:subtitle><?= $A['itunes-subtitle']?></itunes:subtitle>
<itunes:author><?= $A['itunes-feed-owner-name']; ?></itunes:author>
<itunes:owner>
  <itunes:name><?= $A['itunes-feed-owner-name'] ?></itunes:name>
  <itunes:email><?= $A['mail_sender'] ?></itunes:email>
</itunes:owner>
<itunes:image href="<?= $A['itunes-logo'] ?>" />
<pubDate><?= $A['rss-pub-date']?></pubDate>
<lastBuildDate><?= $A['rss-build-date']?></lastBuildDate>
<atom:link href="<?= $A['itunes-feed-url']?>" rel="self" type="application/rss+xml" />

<?

if(!empty($A['itunes-cats'])) {
    print $A['itunes-cats'];
} 
  
if( !empty($A['itunes-keywords'])) {
    ?>
    
    <itunes:keywords><?= $A['itunes-keywords']; ?></itunes:keywords>
    
    <?
}
  
    /***************** ITEM LOOP STARTS HERE *******************/
    
    if( !empty($A['records']) ) { foreach( $A['records'] as $item ) {

    ?>
    <item>
      <title><?= $item['topic_name'] ?></title>
      <pubDate><?= $item['rss_pubdate'] ?></pubDate>
      <itunes:author><?= $item['user_real_name'] ?></itunes:author>
      <itunes:summary><?= $item['topic_text_plain'] ?></itunes:summary>
      <description><?= $item['topic_text_plain']; ?></description>
      <guid><?= $item['topic_permalink'] ?></guid>
      <? if( !empty($item['enclosure_duration']) ) { ?>
         <itunes:duration><?= $item['enclosure_duration']?></itunes:duration>
      <? } ?>
      <? if( !empty($item['enclosure_url']) ) { ?>
         <enclosure url="<?= $item['enclosure_url']?>" length="<?= $item['enclosure_size']?>" type="<?= $item['enclosure_type']?>" />
      <? } ?>
      <? $nsfw = empty($item['topic_nsfw']) ? 'no' : 'yes'; ?>
      <itunes:explicit><?= $nsfw; ?></itunes:explicit>
    </item>
    <?
        } }
    ?>
  </channel>
</rss>
