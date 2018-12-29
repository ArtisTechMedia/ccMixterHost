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
* $Id: cc-publicize.inc 12683 2009-05-31 19:36:44Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require('cchost_lib/ccextras/mp3-svg.inc');

/**
*/
class CCWaveImage
{

    function ImageFor($upload_id,$file_id)
    {
        $path = $this->_localPathForUpload($upload_id,$file_id);

        if( $path )
        {
            $width  = empty($_GET['w']) ? 500 : CCUtil::CleanNumber($_GET['w']);
            $height = empty($_GET['h']) ? 100 : CCUtil::CleanNumber($_GET['h']);
            $convert = new MP32SVG($path, $width, $height);
            $convert->sendFile();
            $convert->cleanup();
            exit();
        }
        exit();

    }

    function OnFileDone(&$file)
    {
        // this might be a file replace
        $convert = new MP32SVG($file['local_path'], 0, 0);
        $convert->removeCachedFile();
    }

    function OnDeleteFile( $file_id, $path )
    {
        $convert = new MP32SVG($path, 0, 0);
        $convert->removeCachedFile();
    }

    function OnDeleteUpload(&$record)
    {
        $all_paths = $this->_localPathForUpload($record['upload_id'],'*');
        for( $i = 0; $i < count($all_paths); $i++ )
        {
            $path = $all_paths[$i];
            $convert = new MP32SVG($path, 0, 0);
            $convert->removeCachedFile();
        }
    }

    function _localPathForUpload($upload_id,$file_id)
    {
        require_once('cchost_lib/cc-dataview.php');

        $sql = "SELECT upload_id, user_name, upload_contest FROM cc_tbl_uploads " .
                  "JOIN cc_tbl_user ON upload_user=user_id " .
                  "WHERE upload_id={$upload_id}";
        $recs = CCDatabase::QueryRows($sql);
        $e = array( 'e' => array( CC_EVENT_FILTER_DOWNLOAD_URL ) );
        $dv = new CCDataView();
        $dv->FilterRecords( $recs, $e );
        $path = '';
        $paths = array();
        $all = $file_id == '*';
        foreach( $recs[0]['files'] as $f ) 
        {
            if( strcmp('audio-mp3-mp3', $f['file_format_info']['format-name']) === 0 )
            {
                if( $all )
                {
                    $paths[] = $f['local_path'];
                }
                else if( empty($file_id) || $file_id == $f['file_id'])
                {
                    $path = $f['local_path'];
                    break;
                }
            }
        }
        return $all ? $paths : $path;
    }
    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('waveimage'), array( 'CCWaveImage', 'ImageFor' ),  
                CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{upoadid}/{fileid}', _('Returns a .png image of the sound wav'), CC_AG_UPLOADS  );

    }

}


?>
