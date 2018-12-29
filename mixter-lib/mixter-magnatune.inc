<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: mixter-magnatune.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/*
    HOW THE MAGNATUNE SONG INFO INTERACTS WITH ccMixter
    ----------------------------------------------------

    cc_tbl_pool_item is a ccHost native table that is used for Magnatune
    albums (not album cuts). 
    
    The native key is the pool_item_id field. This key is used in 
    cc_tbl_pool_tree to keep track of remixes done against
    the Magnatune catalog. 
    
    cc_tbl_pool_tree.pool_tree_pool_parent acts as a foreign key 
    when tracking remixes.

    cc_tbl_pool_item.albumsku is used as a foreign key to the table
    magnatune_song_info which is a copy of the 
    information published on the Magnatune site.

    magnatune_loops is a special table to represent the loop
    packs that were prepared for the Magnatune contest. These
    are hosted at magnatune.com and this table refers to those
    zip files.

    To import the Magnatune data see offline/magnatune_import/*

    Places the data is used:

    -- When posting a remix, the user can search the 'Magnatune
       sample pool' -- this is a search against cc_tbl_pool_item
       filtered on Magnatune's pool_id. 

    -- After the upload the pool_tree table is updated to reflect
       the related Magnatune sources and the new local remixes.

    -- When displaying a remix and it's sources, the pool_item 
       table is where the name of the album and artist comes from

    -- On the Magnatune samples page (mixter-files/magnatune_loops.xml)
       The magnatune_loops table is used to list out sample packs
       and the magnatune_song_info table is used to list out the
       entire Magnatune catalog.
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-pool-generic.php');

class CCMagnatune extends CCPoolGeneric
{
    function Maintain()
    {
        $sql =<<<EOF
INSERT INTO magnatune_loop_artists 
SELECT DISTINCT loop_artist, artist, artistdesc FROM magnatune_loops, magnatune_song_info WHERE loop_artist = page ORDER BY artist
EOF;
        
    }

    function Loops()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        $this->check_for_loop_artists();

        $args['loop_preview_url']  = ccc('magnatune','view','contest','sources') . '#loops';
        $args['album_preview_url'] = ccc('library', 'albumlist') . '#catalog';

        $sql = 'SELECT * FROM magnatune_loop_artists';
        $args['artists'] = CCDatabase::QueryRows($sql);

        $filter = '';
        $args['genre_filter_on'] = false;
        if( !empty($_GET['genre']) )
        {
            $genre = CCUtil::StripText($_GET['genre']);
            if( !empty($genre) && ($genre != 'all') )
            {
                $filter = ' WHERE magnatunegenres LIKE \'%' . $genre . '%\' ';
                $args['genre_filter_on'] = true;
                $args['current_genre'] = $genre;
            }
        }
        $sql = 'SELECT DISTINCT artist, albumname, artistdesc, page, albumsku ' .
                "FROM magnatune_song_info $filter ORDER BY artist" ;
        $qr = CCDatabase::Query($sql);

        $args['albums_by_artist'] = array();
        $albums_by_artist =& $args['albums_by_artist'];
        while( $row = mysql_fetch_assoc($qr) )
        {
            if( empty($albums_by_artist[$row['artist']]) )
                $albums_by_artist[$row['artist']] = $row;
            $albums_by_artist[$row['artist']]['albums'][] = $row;
        }


        $g = empty($args['current_genre']) ? 'all' : $args['current_genre'];
        $sql = "SELECT DISTINCT magnatunegenres FROM magnatune_song_info";
        $all_genres = CCDatabase::QueryItems($sql);
        $all_genres_str = implode(',', $all_genres);
        $all_genres = explode(',',$all_genres_str);
        $all_genres = array_unique($all_genres);
        sort($all_genres);
        $args['genres'] = $all_genres;

        //CCDebug::PrintVar($args['genres']);
        $page->PageArg('mt', $args);
        $page->ViewFile('magnatune_loops.php');
    }

    function LoopStream($loopid_wext)
    {
        preg_match('/(.*)\.m3u$/',$loopid_wext,$m);
        if( !empty($m[1]) )
        {
            $id = $m[1];
            $row = CCDatabase::QueryRow("SELECT loop_filename,loop_artist FROM magnatune_loops WHERE loop_id = '$id'");
            $name = urlencode($row['loop_filename']);
            $path = "http://magnatune.com/extra/remix/loops/{$row['loop_artist']}/$name.mp3";
            header("Content-type: audio/x-mpegurl");
            print "$path\n";
        }
        exit;
    }

    function SongStream($loopid_wext)
    {
        preg_match('/(.*)\.m3u$/',$loopid_wext,$m);
        if( !empty($m[1]) )
        {
            $id = $m[1];
            $path = CCDatabase::QueryItem("SELECT download_mp3 FROM magnatune_song_info WHERE songid = '$id'");
            header("Content-type: audio/x-mpegurl");
            print "$path\n";
        }
        exit;
    }

    function LoopList($loop_artist='')
    {
        if( empty($loop_artist) )
            CCUtil::Send404(true);

        $artist_name = CCDatabase::QueryItem("SELECT artist FROM magnatune_song_info WHERE page = '$loop_artist' LIMIT 1");
        $loops = CCDatabase::QueryRows("SELECT * FROM magnatune_loops WHERE loop_artist = '$loop_artist'");
        $args['mt']['loop_stream_url']   = ccl('library','loopstream');
        $args['mt']['loop_preview'] = array( 'loop_artist' => $loop_artist,
                                       'loop_artist_name' => $artist_name,
                                       'loops' => $loops );
        $this->_show_preview($args,'loop_preview');
    }

    function AlbumList($album_pick)
    {
        $sql = "SELECT download_mp3, trackname, songid, artist, albumname ".
               "FROM magnatune_song_info WHERE albumsku = '$album_pick'";
        $songs = CCDatabase::QueryRows($sql);
        $args['mt']['song_stream_url']   = ccl('library','songstream');
        $args['mt']['album_preview'] = array( 'album' => addslashes($songs[0]['albumname']),
                                        'artist' => addslashes($songs[0]['artist']),
                                        'songs' => $songs);
        $this->_show_preview($args,'album_preview');
    }

    function _show_preview(&$args,$macro)
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-template.php');
        global $CC_GLOBALS;
        $file = CCPage::GetViewFile('magnatune_preview.php');
        $template = new CCSkinMacro( $file . '/' . $macro);
        $template->SetAllAndPrint($args);
        exit;
    }


    /**
    * Event handler for mapping urls to methods
    *
    * @see CCEvents::MapUrl
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('library','loops'),      array('CCMagnatune','Loops'),      CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','','ccMixter');
        CCEvents::MapUrl( ccp('library','loopstream'), array('CCMagnatune','LoopStream'), CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','','ccMixter');
        CCEvents::MapUrl( ccp('library','songstream'), array('CCMagnatune','SongStream'), CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','','ccMixter');
        CCEvents::MapUrl( ccp('library','albumlist'),  array('CCMagnatune','AlbumList'), CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','','ccMixter');
        CCEvents::MapUrl( ccp('library','looplist'),   array('CCMagnatune','LoopList'), CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','','ccMixter');
        CCEvents::MapUrl( ccp('mt','csv'),             array('CCMagnatune','csv'), CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','','ccMixter');
    }

    function check_for_loop_artists()
    {
        $tables = CCDatabase::ShowTables();
        if( in_array( 'magnatune_loop_artists', $tables ) )
            return;

        $sql =<<<EOF

CREATE TABLE `magnatune_loop_artists` (
  `loop_artist` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `artistdesc` varchar(255) NOT NULL
);
EOF;

        CCDatabase::Query($sql);

        $sql =<<<EOF
INSERT INTO `magnatune_loop_artists` (`loop_artist`, `artist`, `artistdesc`) VALUES 
('artemis', 'Artemis', 'Trip-hop/electro-pop with siren vocals'),
('beth_quist', 'Beth Quist', 'electro-Balkan/Indian meets New Age.'),
('thornside', 'Burnshee Thornside', 'funny, funky blues'),
('cargo_cult', 'Cargo Cult', 'electro-rock with a tribal, world flair.'),
('chris_juergensen', 'Chris Juergensen', 'Crystalline Jazz Guitar/Rip Roaring Blues'),
('electric_frankenstein', 'Electric Frankenstein', 'high energy punk rock & roll'),
('emmas_mini', 'Emmas Mini', 'glamorously femme electropop'),
('falik', 'Falik', 'middle-eastern infused with electronica.'),
('farallon', 'Farallon Recorder Quartet', 'renaissance recorder ensemble'),
('four_stones', 'Four Stones', 'groove soaked ambient chill'),
('hans_christian', 'Hans Christian', 'new age meets Indian cello'),
('ivilion', 'Ivilion', 'massive tribal ambient compositions.'),
('jade_leary', 'Jade Leary', 'darkly beautiful psych rock'),
('jag', 'Jag', 'authentic 1920''s solo blues guitar'),
('jan_hanford', 'Jan Hanford', 'solo piano'),
('jay_kishor', 'Jay Kishor', 'Indian classical sitar and surbahar.'),
('jeff_wahl', 'Jeff Wahl', 'acoustic new age and jazz guitar.'),
('johannesburg', 'Johannesburg Philharmonic', '19th century symphonic.'),
('john_williams', 'John Williams', 'bluesy and jazzy guitar'),
('lizzi', 'Lizzi', 'groovy downtempo rock'),
('masley', 'Michael Masley', '21st century hammered dulcimer.'),
('norine', 'Norine Braun', 'jazzed up, funk-tinged eclectic pop.'),
('rcr', 'Rocket City Riot', 'high energy rock and roll'),
('elijah', 'Saint Elijah Childrens Choir', 'Ukrainian children''s choir'),
('seismic_anamoly', 'Seismic Anamoly', 'instrumental hard rock'),
('serenissima', 'Serenissima', 'virtuoso Baroque violin sonatas'),
('shiva_in_exile', 'Shiva in Exile', 'Gothic Arab/Indian World Music'),
('solace', 'Solace', 'classical middle-eastern with excursions into electronica.'),
('soulprint', 'SoulPrint', 'soulful hard rock'),
('spinecar', 'Spinecar', 'heavy modern rock.'),
('thursday_group', 'Thursday Group', 'progressive Jazz.'),
('tim_rayborn', 'Tim Rayborn', 'Middle Eastern and medieval European music'),
('trancevision', 'TranceVision', 'entrancing world Pop'),
('paternoster', 'Vito Paternoster', 'Bach on the Cello'),
('wickedboy', 'Wicked boy', 'a wicked mix of ambient/jungle/trash trance'),
('williamson', 'Williamson', 'instrumental electronic space pop');

EOF;

        CCDatabase::Query($sql);
    }
}

?>
