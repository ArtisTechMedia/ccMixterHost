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
* $Id: cc-getid3.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Module for interfacing with GetID3
*
* @package cchost
* @subpackage io
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
define('CCGETID3_PATH_KEY',               'getid3-path'); 
define('CCGETID3_FILEVERIFY_ENABLED_KEY', 'getid3-fileverify-enabled');
define('CCGETID3_FILETAGGER_ENABLED_KEY', 'getid3-filetagger-enabled');
define('CCGETID3_ENABLED_ID3V1',          'getid3-v1');

// todo: contact getID3() folks to aks why this function is missing
// from their libs...
if( !function_exists('IsValidDottedIP') ) { function IsValidDottedIP($str) { return(true); } }


_verify_getid3_install();

/**
* Wrapper for the GetID3 Library
*
*/
class CCGetID3
{
    /**
    * Called internally when we cant find the installation of GetID3
    *
    */
    function BadPath()
    {
        require_once('cchost_lib/cc-page.php');

        global $CC_GLOBALS;
        $getid3path = $CC_GLOBALS[CCGETID3_PATH_KEY] . '/getid3.php';
        $msg = _('GetID3 library integration is not properly installed:') . '<br />' . _("The path does not exist") . '<br />';

        if( CCUser::IsAdmin() )
            $msg .= '<a href="' . ccl('admin','paths') . '">' . _('Click here to edit configuration') . '</a>';
        else
            $msg .= _('Please ask the site administrator to correct this.');

        CCPage::SystemError($msg);
    }

    /**
    * Called internally when GetID3 is not properly installed 
    *
    */
    function NotConfigured()
    {
        CCPage::SystemError(_("GetID3 library integration is not properly installed:") . 
            "<br />". _("Please ask the site administrator to configure properly."));
    }

    /**
    * Initialize the GetID3 library
    * 
    * Sets the following parameters:
    * 
    * <code>
    * $ID3Obj = new getID3;
    * $ID3Obj->option_tag_lyrics3       = false;
    * $ID3Obj->option_tag_apetag        = false;
    * $ID3Obj->option_tags_process      = true;
    * $ID3Obj->option_tags_html         = false;
    * 
    * </code>
    * 
    * @returns object $id3obj Initialized GetID3 library object
    */
    function & InitID3Obj()
    {
        static $ID3Obj;

        if( empty($ID3Obj) && class_exists('getID3') )
        {
            $ID3Obj = new getID3;
            $ID3Obj->option_tag_lyrics3       = false;
            $ID3Obj->option_tag_apetag        = false;
            $ID3Obj->option_tags_process      = true;
            $ID3Obj->option_tags_html         = false;
        }

        return $ID3Obj ;
    }

    /**
    * Get the default formats handled by the library
    *
    * Returns an array of the following structure:
    * 
    * <code>
    * $file_formats['audio-aiff-aiff'] =  array(
    *     'name'        => 'aif',
    *     'description' => 'AIFF Audio',
    *     'enabled'     => true,
    *     'mediatype'   => 'audio',
    *     );
    * </code>
    * 
    * @returns array $formats Array of format info structures
    */
    function & GetFormats()
    {
        static $file_formats;

        // DEVELOPERS
        //
        // Before adding anything here check out the PsuedoVerifier
        //
        // There's ~100% chance you can handle your file format there
        //

        $getid3_obj = CCGetID3::InitID3Obj();
        if( !isset($getid3_obj) )
        {
            $arg = array(); // invalid installation
            return $arg;
        }

        $getid3_file_formats = $getid3_obj->GetFileFormatArray();

        if( !empty($file_formats) )
            return($file_formats);

         $file_formats = array();

         $file_formats['audio-aiff-aiff'] =  array(
           'name'       => 'aif',
           'description' => 'AIFF ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-au-au'] =  array(
           'name'       => 'au',
           'description' => 'Java (AU) ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-flac-flac'] =  array(
           'name'       => 'flac',
           'description' => 'FLAC ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-midi-midi'] =  array(
           'name'       => 'mid',
           'description' => 'MIDI ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-mp3-mp3'] =  array(
           'name'       => 'mp3',
           'description' => 'MP3 ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-ogg-vorbis'] =  array(
           'name'       => 'ogg',
           'description' => 'OGG/Vorbis ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-real-real'] =  array(
           'name'       => 'rm',
           'description' => 'Real ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-riff-wav'] =  array(
           'name'       => 'wav',
           'description' => 'WAV (Riff) ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['audio-asf-wma'] =  array(
           'name'       => 'wma',
           'description' => 'Windows Media ' . _('Audio'),
           'enabled' => true,
           'mediatype' => 'audio',
           );
         $file_formats['archive-zip-'] =  array(
           'name'       => 'zip',
           'description' => 'ZIP ' . _('Archive'),
           'enabled' => true,
           'mediatype' => 'archive',
           );
         $file_formats['video-riff-avi'] =  array(
           'name'       => 'avi',
           'description' => 'Windows ' . _('Video'),
           'enabled' => true,
           'mediatype' => 'video',
           );
         $file_formats['video-quicktime-quicktime'] =  array(
           'name'       => 'mov',
           'description' => 'Quicktime ' . _('Video'),
           'enabled' => true,
           'mediatype' => 'video',
           );
         $file_formats['video-real-real'] =  array(
           'name'       => 'rmvb',
           'description' => 'Real ' . _('Video'),
           'enabled' => true,
           'mediatype' => 'video',
           );
         $file_formats['video-asf-wmv'] =  array(
           'name'       => 'wmv',
           'description' => 'Windows Media ' . _('Video'),
           'enabled' => true,
           'mediatype' => 'video',
           );
         $file_formats['image-bmp-bmp'] =  array(
           'name'       => 'bmp',
           'description' => 'Windows BMP ' . _('Image'),
           'enabled' => true,
           'mediatype' => 'image',
           );
         $file_formats['image-gif-gif'] =  array(
           'name'       => 'gif',
           'description' => 'GIF ' . _('Image'),
           'enabled' => true,
           'mediatype' => 'image',
           );
         $file_formats['image-jpg-jpg'] =  array(
           'name'       => 'jpg',
           'description' => 'JPG ' . _('Image'),
           'enabled' => true,
           'mediatype' => 'image',
           );
         $file_formats['image-png-png'] =  array(
           'name'       => 'png',
           'description' => 'PNG ' . _('Image'),
           'enabled' => true,
           'mediatype' => 'image',
           );

        if ( isset($getid3_file_formats['svg']) )
            $file_formats['image-xml-svg'] = array(
                'name'       => 'svg',
                'description' => 'Scalable Vector Graphic ' . _('Image'),
                'enabled' => true,
                'mediatype' => 'image',
                );  
        if ( isset($getid3_file_formats['swf']) )
            $file_formats['video-swf-swf'] =  array(
                'name'       => 'swf',
                'description' => 'Flash ' . _('Video'),
                'enabled' => true,
                'mediatype' => 'video',
                );

         return( $file_formats );

      }

    function OnSysPaths(&$fields)
    {
        $fields[CCGETID3_PATH_KEY] =
                        array( 'label'      => _('Path to GetID3 Library'),
                               'formatter'  => 'sysdir',
                               'form_tip'   => _('Local server path to library (e.g. /usrer/lib/getid3/getid3)'),
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED  );
    }

    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields[CCGETID3_FILEVERIFY_ENABLED_KEY] =
                            array( 'label'      => _('GetID3 File Verify Enabled'),
                                   'formatter'  => 'checkbox',
                                   'flags'      => CCFF_POPULATE);

            $fields[CCGETID3_PATH_KEY] =
                            array( 'label'      => _('Path to GetID3 Library'),
                                   'formatter'  => 'textedit',
                                   'form_tip'   => _('Local server path to library (e.g. /usrer/lib/getid3/getid3)'),
                                   'flags'      => CCFF_POPULATE | CCFF_REQUIRED  );

           $fields[CCGETID3_ENABLED_ID3V1] =
                        array( 'label'      => _('Tag ID3v1'),
                               'form_tip'   => _('Tag old style v1 tags as well as v2'),
                               'formatter'  => 'checkbox',
                               'flags'      => CCFF_POPULATE );
        }
    }
}

function _verify_getid3_install()
{
    global $CC_GLOBALS;

    if( empty($CC_GLOBALS[CCGETID3_FILEVERIFY_ENABLED_KEY]) )
        return;

    if( empty($CC_GLOBALS[CCGETID3_PATH_KEY]) )
    {
        CCGetID3::NotConfigured();
    }
    else
    {
        $getid3path = $CC_GLOBALS[CCGETID3_PATH_KEY] . '/getid3.php';
        if( !file_exists($getid3path) )
        {
           CCGetID3::BadPath();
        }
        else
        {
            require_once( $getid3path );
        }
    }

}



?>
