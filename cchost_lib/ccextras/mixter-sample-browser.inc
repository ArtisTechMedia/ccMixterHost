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
* $Id: mixter-sample-browser.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*
*/
define( 'CC_TS_POPULAR', 1 );
define( 'CC_TS_ALPHA',   2 );
define( 'CC_TS_DEFAULT', CC_TS_ALPHA );

define( 'CC_TS_MINTAGS', 3 );

define('CC_AG_SAMPLE_BROWSER', 'Sample Browser');

class CCSampleBrowser
{
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('samples','list'),   array('CCSampleBrowser', 'SamplesList'),  
                            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'[?tags=][&bpm=][&lic=]','',CC_AG_SAMPLE_BROWSER );
        CCEvents::MapUrl( ccp('samples'),   array('CCSampleBrowser', 'SamplesBrowse'),  
                            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','',CC_AG_SAMPLE_BROWSER );
        CCEvents::MapUrl( ccp('samples','stream'),   array('CCSampleBrowser', 'SamplesStream'),  
                            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'{file_id_with_m3u}','',CC_AG_SAMPLE_BROWSER );
        CCEvents::MapUrl( ccp('samples','search'),   array('CCSampleBrowser', 'SamplesSearch'),  
                            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'?term=','',CC_AG_SAMPLE_BROWSER );

        CCEvents::MapUrl( ccp('samples','lics'),   array('CCSampleBrowser', 'LicsForTags'),  
                            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),'','',CC_AG_SAMPLE_BROWSER );

    }

    function SamplesSearch()
    {
        $term = empty($_GET['term']) ? '' : CCUtil::Strip($_GET['term']);
        if( empty($term) )
        {
            print('<span class="sb_msg">' . 
                   _('Enter a search phrase first...') . 
                  '</span>');
            exit;
        }

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessAdminArgs('f=php&dataview=default&tags=sample&search='.$term);
        list( $recs ) = $query->Query($args);
        $this->_list_records($recs,"\"$term\"", _('Search phrase'));
    }

    function SamplesStream($file_id_with_m3u='')
    {
        if( empty($file_id_with_m3u) )
            CCUtil::Send404();
            
        $file_id = sprintf('%d',str_replace('.m3u', '', $file_id_with_m3u));
        if( empty($file_id) )
            CCUtil::Send404();
        
        $sql =<<<EOF
            SELECT upload_contest, user_name, contest_short_name, file_name
            FROM cc_tbl_files
            JOIN cc_tbl_uploads ON file_upload=upload_id
            JOIN cc_tbl_user ON upload_user=user_id
            LEFT OUTER JOIN cc_tbl_contests ON upload_contest=contest_id
            WHERE file_id = {$file_id}
EOF;
        $F = CCDatabase::QueryRow($sql);
        if( $F )
        {
            global $CC_GLOBALS;
            if( $F['contest_short_name'] )
            {
                $download_url = ccd($CC_GLOBALS['contest-upload-root'],$F['contest_short_name'],$F['user_name'],$F['file_name']);
            }
            else
            {
                $download_url = ccd($CC_GLOBALS['user-upload-root'],$F['user_name'],$F['file_name']);
            }

            header("Content-type: audio/x-mpegurl");
            print( $download_url . "\n" );
        }
        // there's no point in doing error messages since this is
        // fed to media player, I guess we could return 404...
        exit;
    }

    function _default_msg()
    {
        return '<span class="sb_msg">' . 
                  _('Select a combination of tags from the list or enter a phrase and hit \'Go\' ...') . 
               '</span>';
    }

    function SamplesList()
    {
        if( empty($_GET['tags']) )
        {
            print( $this->_default_msg() );
            exit;
        }

        $tags = $_GET['tags'];

        if( !empty($_GET['tf']) && in_array( $_GET['tf'], array( 'any', 'all' ) ) )
            $match = $_GET['tf'];
        else
            $match = 'any';


        require_once('cchost_lib/cc-dataview.php');
        $dv = new CCDataView();
        $tag_filter = $dv->MakeTagFilter($tags,$match);

        if( !empty($_GET['bpm']) && ($_GET['bpm'] != '-') )
        {
            $bpm = $_GET['bpm'];
            $bpm_where = 'AND ' . $dv->MakeTagFilter('bpm_'. $bpm);
        }
        else
        {
            $bpm = '';
            $bpm_where = '';
        }

        if( !empty($_GET['lic']) && ($_GET['lic'] != '-') )
        {
            $lic = $_GET['lic'];
            $lic_where = " AND (license_tag = '$lic')";
        }
        else
        {
            $lic = '';
            $lic_where = '';
        }

        $sample_filter = $dv->MakeTagFilter('sample');

        $where = "($sample_filter) AND ( ($tag_filter) $bpm_where  $lic_where)";
        $ccp = ccl('people') . '/';
        $ccu = ccl('files') . '/';
        $sql =<<<EOF
            SELECT upload_id, upload_name, upload_extra, upload_contest, user_name, user_real_name,
                CONCAT( '$ccp', user_name ) as artist_page_url,
                CONCAT( '$ccu', user_name, '/', upload_id ) as file_page_url
            FROM cc_tbl_uploads
            JOIN cc_tbl_user ON upload_user=user_id
            JOIN cc_tbl_licenses ON upload_license=license_id
            WHERE (upload_banned=0) AND (upload_published=1) AND ($where)
            ORDER BY upload_date DESC
EOF;
        $recs = CCDatabase::QueryRows($sql);
        $e = array( 'e' => array( CC_EVENT_FILTER_EXTRA, CC_EVENT_FILTER_FILES, CC_EVENT_FILTER_DOWNLOAD_URL ) );
        $dv->FilterRecords( $recs, $e );

        if( strpos( $tags, ',' ) !== false )
             $term = str_replace(',',', ',$tags) . ' (' . $match . ')';
        else
            $term = $tags;

        if( $bpm )
            $term .= " ($bpm BPM)";

        if( $lic )
            $term .= " [cc:$lic]";

        $this->_list_records($recs,$term, _('Tags'));
    }

    function _list_records(&$recs,$term,$term_caption)
    {
        global $CC_GLOBALS;

        $args = $CC_GLOBALS;
        $args['records']         =& $recs;
        $args['results_caption'] = sprintf( 'returns %d results', count($args['records']) );
        $args['term']            = $term;
        $args['term_caption']    = $term_caption;

        if( empty($args['records']) )
        {
            print('<span class="sb_msg">' .
                  sprintf( _('Could not find any matches for  "%s"...'), $term ) .
                  '</span>');
            exit;
        }

        require_once('cchost_lib/cc-template.php');
        $template = new CCSkinMacro( 'sample_browser.php/browser_list');
        $template->SetAllAndPrint($args);
        exit;
    }

    function SamplesBrowse()
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');

        $args = array(); // $CC_GLOBALS;
        list( $tags, $bpms ) = $this->_get_tags();

        $args['tags_tags']      =& $tags;
        $args['bpms']           =& $bpms;
        $args['tag_sorts']      =& $this->_get_tag_sorts();
        $args['tag_filters']    =& $this->_get_tag_filters();
        $args['tag_limiters']   =& $this->_get_tag_limiters();
        $args['picker_caption'] = _('Search using tags:');
        $args['search_caption'] = _('Search for phrase:');
        $args['bpm_caption']    = _('Limit to BPM:');
        $args['search_text']    = _('Go');
        $args['default_msg']    = $this->_default_msg();
        $args['licenses']       = $this->_get_lic_filters('sample',true);
        $file = CCPage::GetViewFile('sample_browser.php');
        $args['sample_browser'] = $file . '/browser_page';

        //$args['browser_macro']  = 'sample_browser.xml/browser_page';

        foreach( $args as $K => $V )
            CCPage::PageArg( $K, $V );

        CCPage::AddMacro( 'sample_browser' );

    }


    function LicsForTags($tag_filter='')
    {
        $tag_filter = CCUtil::StripText($tag_filter);
        if( empty($tag_filter) )
            CCUtil::Send404();
        $lics = $this->_get_lic_filters($tag_filter,true);
        CCUtil::ReturnAjaxData($lics);
    }

    function _get_lic_filters($tag_filter,$collapse=false)
    {
        require_once('cchost_lib/cc-dataview.php');
        $dv = new CCDataView();
        $filter = $dv->MakeTagFilter($tag_filter);
        $group_key = $collapse ? 'license_tag' : 'upload_license';
        $sql = "SELECT upload_license FROM cc_tbl_uploads JOIN cc_tbl_licenses ON upload_license=license_id WHERE $filter GROUP BY $group_key";
        $used_lics = CCDatabase::QueryItems($sql);
        $sql = "SELECT license_tag,license_id,license_name FROM cc_tbl_licenses WHERE license_id IN ('" 
               . join("','",$used_lics) . "') ORDER BY license_name";
        $rows = CCDatabase::QueryRows($sql);
        if( empty($_GET['lic']) )
            $sellic = '-';
        else
            $sellic = $_GET['lic'];
        $lics = array();
        $lics[] = array( 'value' => '-', 
                       'text' => '-- ' . _('All Licenses'),
                       'selected' => $sellic == '-'
                     );
        foreach( $rows as $row )
        {
            $lics[] = array( 'value' => $collapse ? $row['license_tag'] : $row['license_id'],
                             'text'  => trim($row['license_name']),
                             'selected' => $sellic == $row['license_id'] );
        }

        return $lics;
    }

    function _get_tags()
    {
        require_once('cchost_lib/cc-dataview.php');
        $dv = new CCDataView();
        $where = $dv->MakeTagFilter('sample');
        $sql = "SELECT upload_extra FROM cc_tbl_uploads WHERE (upload_banned=0) AND (upload_published=1) AND $where";
        $rows = CCDatabase::QueryRows($sql);
        $count = count($rows);
        $tagstrs = array();
        $bpms = array();
        if( empty($_GET['bpm']) )
            $selbpm = '-';
        else
            $selbpm = $_GET['bpm'];
        for( $i = 0; $i < $count; $i++ )
        {
            $ex = unserialize($rows[$i]['upload_extra']);
            if( !empty($ex['usertags']) )
               $tagstrs[] = $ex['usertags'];
            if( preg_match( '/bpm_([0-9][^,$]+)/', $ex['ccud'], $m ) )
            {
                $bpms[ $m[1] ] = array( 'bpm' => $m[1],
                                        'selected' => $selbpm == $m[1] );
            }
        }
        ksort($bpms);
        $bpm_head['-']   =  array( 'bpm' => '-',         'selected' => $selbpm == '-' );
        $bpm_head['b60']  = array( 'bpm' => 'below_60',  'selected' => $selbpm == 'below_60' );
        $bpm_tail['a180'] = array( 'bpm' => 'above_180', 'selected' => $selbpm == 'above_180' );
        $bpm_final = array_merge( $bpm_head, $bpms, $bpm_tail );

        //CCDebug::Enable(true);

        $all_tags = strtolower( join(',', $tagstrs ) );
        require_once('cchost_lib/cc-tags.php');
        $all_tags = CCTag::TagSplit($all_tags);

        if( !empty( $_GET['ts'] ) )
            $ts = intval($_GET['ts']);
        if( empty($ts) )
            $ts = CC_TS_DEFAULT;

        $tag_counts = array_count_values($all_tags);

        $tag_rows = array();
        switch( $ts )
        {
            case CC_TS_POPULAR:
                foreach( $tag_counts as $tag => $count )
                {
                    $key = $count * 1000;
                    while( array_key_exists( $key, $tag_rows ) )
                        ++$key;
                    $tag_rows[$key] = array( 'tags_tag' => $tag,
                                       'tags_count' => $count );
                }
                krsort($tag_rows);
                break;

            case CC_TS_ALPHA:
                ksort($tag_counts);
                if( empty($_GET['sa']) )
                {
                    $min = CC_TS_MINTAGS;
                }
                else
                {
                    if( $_GET['sa'] == 'all' )
                        $min = 0;
                    else
                        $min = intval($_GET['sa']);
                }
                foreach( $tag_counts as $tag => $count )
                {
                    if( !$min || $count >= $min )
                    {
                        $tag_rows[] = array( 'tags_tag' => $tag,
                                             'tags_count' => $count );
                    }
                }
                break;
        }

        
        return array( &$tag_rows, &$bpm_final );
    }

    function & _get_tag_sorts()
    {
        if( empty($_GET['ts']) )
            $sel = CC_TS_DEFAULT;
        else
            $sel = intval($_GET['ts']);

        $sels = array( 
            array( 'value' => CC_TS_POPULAR,
                   'text'  => 'Sort by popular',
                    'selected' => $sel == CC_TS_POPULAR ),
            array( 'value' => CC_TS_ALPHA,
                   'text'  => 'Sort alphabetical',
                    'selected' => $sel == CC_TS_ALPHA ),
            );

        return $sels;
    }

    function & _get_tag_filters()
    {
        if( !empty($_GET['tf']) && in_array( $_GET['tf'], array( 'any', 'all' ) ) )
            $sel = $_GET['tf'];
        else
            $sel = 'any';

        $sels = array( 
            array( 'value' => 'any',
                   'text'  => _('Match any tags'),
                    'selected' => $sel == 'any' ),
            array( 'value' => 'all',
                   'text'  => _('Match all tags'),
                    'selected' => $sel == 'all' ),
            );

        return $sels;
    }

    function & _get_tag_limiters()
    {
        if( !empty($_GET['sa']) && in_array( $_GET['sa'], array( CC_TS_MINTAGS, 'all' ) ) )
            $sel = $_GET['sa'];
        else
            $sel = CC_TS_MINTAGS;

        $sels = array( 
            array( 'value' => CC_TS_MINTAGS,
                   'text'  => _('Popular only'),
                    'selected' => $sel == CC_TS_MINTAGS ),
            array( 'value' => 'all',
                   'text'  => _('Show all'),
                    'selected' => $sel == 'all' ),
            );

        return $sels;
    }
}

?>
