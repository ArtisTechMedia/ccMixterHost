<?php

/*
  $Id: mixter-dump-all2.inc 14094 2010-02-12 17:18:49Z fourstones $
*/

// MAIN
define('CC_HOST_CMD_LINE', 1 );      // define this exact way
chdir( dirname(__FILE__) . '/..');
$admin_id = 9;                       // user to log in as (OPTIONAL)
require_once( 'cc-cmd-line.inc' );    // include this file
cc_do_datadump();


function cc_do_datadump( $tags='media', $out_rss_file = 'dump/rss.xml' )
{
    $count = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_uploads');
    
    require_once('cchost_lib/cc-query.php');
    $query = new CCQuery();
    $query->sql_p['limit'] = $count; // this disables the Query engine limiter 
    $qstring = 'f=php&dataview=rss_20&rettype=' . CCDV_RET_RESOURCE;
    if( $tags )
        $qstring .= '&tags=' . $tags;
    $args = $query->ProcessAdminArgs($qstring);
    list( $qr  ) = $query->Query($args);
    
    $dv = $query->dataview;
    $out_rss = fopen($out_rss_file,'w');
    if( !$out_rss_file )
        die("ERROR: Could not write to $out_rss_file ");

    cc_do_helper_write_head($out_rss);
  
    $count = mysql_num_rows($qr);
    $BLOCKSIZE = min($count,1100);
    $num_blocks = (integer)($count / $BLOCKSIZE);
    $mod = $count % $BLOCKSIZE;
    
    for( $i = 0; $i < $num_blocks; $i++ )
    {
        cc_do_helper_dump_block($qr,$BLOCKSIZE,$dv,$out_rss);
    }

    if( $mod )
        cc_do_helper_dump_block($qr,$mod,$dv,$out_rss);
        
    fwrite($out_rss,"\n  </channel>\n</rss>");
    fclose($out_rss);
    chmod($out_rss_file,0777);
    print "Data written to {$out_rss_file}\n";
}

function cc_do_helper_write_head($out_rss)
{
    fwrite($out_rss,'<?xml version="1.0" encoding="utf-8" ?>');

    $date = date('D, m M Y h:i:s');

    $rss_head =<<<EOF
    <?xml version="1.0" encoding="utf-8" ?>
    <rss version="2.0" 
       xmlns:content="http://purl.org/rss/1.0/modules/content/"
       xmlns:cc="http://creativecommons.org/ns#"   
       xmlns:dc="http://purl.org/dc/elements/1.1/"
       xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
       xmlns:media="http://search.yahoo.com/mrss/"
       xmlns:atom="http://www.w3.org/2005/Atom"
       >
    <channel>
    <title>ccMixter (Full Data Dump)</title>
    <link>http://ccmixter.org/</link>
    <description>Download, Sample, Cut-up, Share.</description>
    <language>en-US</language>
    <atom:link href="http://ccmixter.org/api/query" rel="self" type="application/rss+xml" />
    <image> 
        <url>http://ccmixter.org/mixter-files/images/cc-mixter-logo.png</url>
        <link>http://ccmixter.org/</link> 
        <title>ccMixter</title> 
        <height>36</height>
        <width>131</width>
    </image>
    <pubDate>{$date} PDT</pubDate>
    <lastBuildDate>{$date} PDT</lastBuildDate>
EOF;
    
    fwrite($out_rss,$rss_head);    
}

function cc_do_helper_dump_block($qr,$sz,&$dv,$out_rss)
{
    $records = array();
    for( $n = 0; $n < $sz; $n++ )
    {
        $records[] = mysql_fetch_assoc($qr);
    }

    $dv->FilterRecords($records);
    cc_do_helper_dump_recs($records,$out_rss);
}

function cc_do_helper_dump_recs(&$records,$out_rss)
{
    $c = count($records);
    for( $i = 0; $i < $c; $i++ )
    {
        $row =& $records[$i];
    
        $tagstr = '';
        $tags = cc_split(',',trim($row['upload_tags'],','));
        foreach($tags as $tag ) {
            $tagstr .= "\n        <category>$tag</category>";
        }
        $rss =<<<EOF
        
        <item>
            <title>{$row['upload_name']}</title>
            <link>{$row['file_page_url']}</link>
            <pubDate>{$row['rss_pubdate']}</pubDate>
            <dc:creator>{$row['user_real_name']}</dc:creator>
            <description>{$row['upload_description_plain']} </description>
            <content:encoded><![CDATA[{$row['upload_description_html'] }]]></content:encoded>
            {$tagstr}
            <guid>{$row['file_page_url']}</guid>
            <cc:license>{$row['license_url'] }</cc:license>
            <media:thumbnail url="{$row['user_avatar_url']}"></media:thumbnail>
EOF;

        foreach( $row['files'] as $F )
        {
            $rss .=<<<EOF
    
            <enclosure url="{$F['download_url']}" length="{$F['file_rawsize']}" type="{$F['file_format_info']['mime_type']}"></enclosure>
EOF;
        }
        
        $rss .= "\n</item>";
    
        fwrite($out_rss,$rss);
    }
    
}
?>
