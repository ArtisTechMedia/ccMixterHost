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
* $Id: cc-renderaudio.php 10478 2008-07-12 05:00:06Z fourstones $
*
*/

/**
* @package cchost
* @subpackage audio
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Some linux players prevent us from passing this 
* number in as part of the GET argument so we
* hard wire it here.
*/
define('RADIO_PROMO_INTERVAL', 4); 

define('CC_MAX_PLAYLIST', 100 );

require_once('cchost_lib/cc-render.php');

/**
*/
class CCRenderAudio extends CCRender
{
    function StreamPage()
    {
        $this->_stream_files();
    }

    function StreamRadio()
    {
        $this->_stream_files('',true);
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        /*
        CCEvents::MapUrl( 'files/stream',         array('CCRenderAudio', 'StreamFiles'),          
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{user_name}/{upload_id.m3u}', 
            _('Stream audio file'), CC_AG_RENDER  );
        CCEvents::MapUrl( 'stream/page',          array('CCRenderAudio', 'StreamPage'),           
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', 
            _('Stream all audio on page'), CC_AG_RENDER );
        CCEvents::MapUrl( 'stream/radio',         array('CCRenderAudio', 'StreamRadio'),          
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', 
            _('Stream audio results of query'), CC_AG_RENDER  );
      */
    }


    /**
    * Event handler for {@link CC_EVENT_UPLOAD_ROW}
    *
    * @param array &$record Upload row to massage with display data 
    * @see CCTable::GetRecordFromRow()
    */
    function OnUploadRow(&$record)
    {
        if( empty($record['stream_link']) )
        {
            $link = $this->_get_stream_link($record);
            if( !empty($link) )
                $record['stream_link'] = $link;
        }
    }

    function _get_stream_link(&$record)
    {
        if( !CCUploads::InTags('audio',$record) )
            return(null);

        global $CC_GLOBALS;

        $link['url'] = url_args( ccl('api','query','stream.m3u'), 'f=m3u&ids=' . $record['upload_id'] );
        $link['text'] = _('Stream');

        return($link);
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_MENU}
    * 
    * The handler is called when a menu is being displayed with
    * a specific record. All dynamic changes are made here
    * 
    * @param array $menu The menu being displayed
    * @param array $record The database record the menu is for
    */
    function OnUploadMenu(&$menu,&$record)
    {
        $link = $this->_get_stream_link($record);
        if( empty($link) || !empty($record['upload_banned']) )
            return;

        $menu['stream'] = 
                 array(  'menu_text'  => $link['text'],
                         'weight'     => -1,
                         'group_name' => 'play',
                         'id'         => 'cc_streamfile',
                         'access'     => CC_DONT_CARE_LOGGED_IN,
                         'action'     => $link['url'] );
    }

    function StreamFiles($user,$upload_id_with_m3u)
    {
        list( $upload_id ) = explode('.',$upload_id_with_m3u);
        $where['upload_id'] = $upload_id;
        $this->_stream_files($where);
    }

    function _contest_has_audio($contest_id,$tag)
    {
        $uploads =& CCUploads::GetTable();
        if( empty($tag) )
            $tag = 'audio';
        else
            $tag .= ',audio';
        $uploads->SetTagFilter($tag);
        $where['upload_contest'] = $contest_id;
        $records =& $uploads->GetRecords($where);
        $uploads->SetTagFilter('');
        return count($records) > 0 ;
    }

    function _clean_tags()
    {
        if( !empty($_REQUEST['tags']) )
        {
            $tags = CCUtil::StripText($_REQUEST['tags']);
            return str_replace(' ',',',urldecode($tags));
        }

        return '';
    }

    function _stream_files($where = '',$isRadio = false)
    {
        $args['where'] = $where;
        if( $isRadio )
        {
            $args['promo_tag'] = 'site_promo';
            $args['rand'] = 1;
        }
        $args['format'] = 'm3u';
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        $args = $query->ProcessUriArgs($args);
        list( $results, $mime ) = $query->Query($args);
        header("Content-type: $mime");
        print $results;
        exit;
    }

    function OnApiQuerySetup( &$args, &$queryObj, $validate)
    {
        if( $args['format'] != 'm3u' )
            return;
        $args['dataview'] = 'files';
        $queryObj->GetSourcesFromDataview($args['dataview']);
    }

    function OnApiQueryFormat( &$records, $args, &$results, &$results_mime )
    {
        if( $args['format'] != 'm3u' )
            return;

        $configs =& CCConfigs::GetTable();
        $settings = $configs->GetConfig('remote_files');
        $remoting = !empty($settings['enable_streaming']);

        $streamfile = '';
        $n = count($records);

        if( $n && empty($records[0]['files']) )
        {
            $dv = new CCDataView();
            $info = array( 'e' => array(CC_EVENT_FILTER_FILES) );
            $dv->FilterRecords($records,$info);
        }

        for( $i = 0; $i < $n; $i++ )
        {
            $R =& $records[$i];
            $fcount = count($R['files']);
            $files =& $R['files'];
            for( $fn = 0; $fn < $fcount; $fn++)
                if( $files[$fn]['file_format_info']['media-type'] == 'audio' )
                    break;
            if( $fn == $fcount )
                continue; // this really never should happen

            if( $remoting && !empty($R['files'][$fn]['file_extra']['remote_url']) )
                $surl = $R['files'][$fn]['file_extra']['remote_url'];
            else
                $surl = $R['files'][$fn]['download_url'];

            $url = str_replace(' ', '%20', $surl );
            $streamfile .=  $url . "\n";
        }

        $results = $streamfile;
        $results_mime = 'audio/x-mpegurl';
    }
}


?>