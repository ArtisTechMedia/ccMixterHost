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
* $Id: cc-fileverify.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* GetID3 file verification interface
*
* @package cchost
* @subpackage io
*/

require_once('cchost_lib/cc-admin.php');

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Form for configuration the file format verification module
*
*/
class CCAdminFileVerifyForm extends CCEditConfigForm
{
    /**
    * Constructor
    *
    */
    function CCAdminFileVerifyForm()
    {
        $this->CCEditConfigForm('format-allow');

        $fields = array();
        
        require_once('cchost_lib/cc-getid3.php');
        $formats =& CCGetID3::GetFormats();

        foreach( $formats as $name => $format )
        {
            $fields[$name] =
                       array(  'label'       => "Allow " . $format['description'],
                               'form_tip'    => '(' . $format['name'] . ')',
                               'formatter'   => 'checkbox',
                               'flags'       => CCFF_POPULATE );
        }

        $this->AddFormFields( $fields );
        $this->SetModule( ccs(__FILE__) );
    }
}

/**
* Default file verification API (wrapper for GetID3 library)
*
*/
class CCFileVerify
{
    /**
    * Returns a list of admin accepted file formats.
    * 
    * This method is called by checking for the global '$CC_UPLOAD_VALIDATOR' and then
    * calling $CC_UPLOAD_VALIDATOR->GetValidFileTypes($types).
    * 
    * <code>
    * $type = array();
    * if( isset($CC_UPLOAD_VALIDATOR) )
    * {
    *     $CC_UPLOAD_VALIDATOR->GetValidFileTypes($types);
    * }
    * </code>
    * 
    * @param array &$types Outbound parameter to put the valid format types
    * @returns bool $havetypes true if there is at least one type
    */
    public static function GetValidFileTypes(&$types)
    {
        require_once('cchost_lib/cc-getid3.php');
        $configs =& CCConfigs::GetTable();
        $allowed = $configs->GetConfig('format-allow');
        $formats =& CCGetID3::GetFormats();

        if( empty($formats) ) { // installation error
            return false;
        }

        foreach($allowed as $allow => $value )
        {
            if( $value )
                $types[] = $formats[$allow]['name'];
        }

        return( count($types) > 0 );
    }

    /**
    * Validates a file to be of a certain type.
    *
    * This method is called by checking for the global '$CC_UPLOAD_VALIDATOR' and then
    * calling $CC_UPLOAD_VALIDATOR->FileValidate($formatinfo).
    * 
    * <code>
    * $format_info = new CCFileFormatInfo('/some/path/to/file');
    * 
    * if( isset($CC_UPLOAD_VALIDATOR) )
    * {
    *     if( $CC_UPLOAD_VALIDATOR->FileValidate($format_info) )
    *     {
    *         // validated ok
    *     }
    *     else
    *    {
    *        // handle problems
    *       $errors = $format_info->GetErrors();
    *    }
    * }
    * </code>
    *
    * @see CCFileFormatInfo::CCFileFormatInfo()
    * @see CCUploadAPI::PostProcessNewUpload()
    * @param object $formatinfo Database record of upload
    * @returns boolean $renamed true if file was replaced
    */
    function FileValidate(&$formatinfo)
    {
        require_once('cchost_lib/cc-getid3.php');
        $retval = false;
        $path = $formatinfo->GetFilePath();

        CCDebug::QuietErrors();
        $debug = CCDebug::Enable(false);

        $id3 =& CCGetID3::InitID3Obj();
        $tags = $id3->analyze($path);

        CCDebug::Enable($debug);
        CCDebug::RestoreErrors();

        //CCDebug::LogVar('GetID3 tags',$tags);

        if( !empty($tags['warning']) )
        {
            $formatinfo->SetWarnings($tags['warning']);
        }

        if( empty( $tags['fileformat'] ) )
        {
            $formatinfo->SetErrors(_('Unknown format'));
        }
        elseif( !empty($tags['error']) )
        {
           $formatinfo->SetErrors($tags['error']);
        }
        else
        {
            $name = $this->_parse_format_name($tags);

            if( $name )
            {
                $formats =& CCGetID3::GetFormats();
                if( array_key_exists($name,$formats) )
                {
                    $configs =& CCConfigs::GetTable();
                    $allowed = $configs->GetConfig('format-allow');
                    if( empty($allowed[$name]) )
                    {
                        $formatinfo->SetErrors(_("File type is not allowed"));
                    }
                    else
                    {
                        $this->_ID3_to_format_info($tags,$format_data,$name);
                        $formatinfo->SetData($format_data);
                        $retval = true;
                    }
                }
            }
            else
            {
                $formatinfo->SetErrors(_("Unknown data format"));
            }
        }
        
        $errs = $formatinfo->GetErrors();
        if( !$retval && empty($errs) )
        {
            // a sleazy catch-all 
            $formatinfo->SetErrors(_('File can not be verified'));
        }

        return( $retval );
    }

    /**
    * Event handler for {@link CC_EVENT_GET_MACROS}
    *
    * @param array &$record Upload record we're getting macros for (if null returns documentation)
    * @param array &$file File record we're getting macros for
    * @param array &$patterns Substituion pattern to be used when renaming/tagging
    * @param array &$mask Actual mask to use (based on admin specifications)
    */
    function OnGetMacros(&$record,&$file,&$patterns,&$mask)
    {
        if( empty($record) )
        {
            $patterns['%ext%']      = "File extension";
            $patterns['%filename%'] = "%title% + %ext% ";
            return;
        }

        if( !empty($file['file_format_info']['default-ext']) )
        {
            $patterns['%ext%']      = $file['file_format_info']['default-ext'];
            $patterns['%filename%'] = $patterns['%title%'] . '.' .  $patterns['%ext%'];
        }
    }

    /**
    * Event handler for {@link CC_EVENT_GET_SYSTAGS}
    *
    * @param array $record Record we're getting tags for 
    * @param array $file Specific file record we're getting tags for
    * @param array $tags Place to put the appropriate tags.
    */
    function OnGetSysTags(&$record,&$file,&$tags)
    {
        if( empty($file['file_format_info']) )
            return;

        $F = $file['file_format_info'];

        if( !is_array($F) || !array_key_exists('format-name',$F) )
            return;

        $names = array( 'media-type', 'default-ext', 'sr', 'ch', 'br' );

        foreach( $names as $name )
        {
            if( isset($F[$name]) )
                $tags[] = $F[$name];
        }
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items, $scope)
    {
        if( $scope == CC_GLOBAL_SCOPE )
            return;

        $items += array( 
        'format-allow'   => array( 'menu_text'  => _('File Formats'),
                         'menu_group' => 'configure',
                         'access' => CC_ADMIN_ONLY,
                          'help'  => _('Pick which file formats are allowed to be uploaded'),
                         'weight' => 10,
                         'action' =>  ccl('admin','formats')
                         ),
            );
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'admin/formats',  array('CCFileVerify', 'ConfigureFormats'), 
            CC_ADMIN_ONLY, ccs(__FILE__), '', _('Show file type admin form'), CC_AG_UPLOAD );
    }

    /**
    * Show the configure formats form
    *
    * @see CCAdminFileVerifyForm::CCAdminFileVerifyForm()
    */
    function ConfigureFormats()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = _("Edit Allowable File Formats");
        CCAdmin::BreadCrumbs(false,array('url'=>'','text'=>$title));
        $page->SetTitle($title);

        $form = new CCAdminFileVerifyForm($this);
        $page->AddForm( $form->GenerateForm() );
    }

    /**
    * Internal: maps GetID3 meta-info tags to our own meta-info format
    * 
    * @access private
    */
    function _ID3_to_format_info(&$id3obj,&$F, $name)
    {
        require_once('cchost_lib/cc-getid3.php');
        $formats =& CCGetID3::GetFormats();
        list( $mediatype ) = cc_split('-',$name);
        $F['media-type']  = $mediatype;
        $F['format-name'] = $name;
        $F['default-ext'] = $formats[$name]['name'];

        if( !empty($id3obj['mime_type']) )
        {
            $F['mime_type'] = $id3obj['mime_type'];
        }
        else
        {
            $F['mime_type'] = 'octect/stream'; // todo: is this right?
        }

        if( !empty($id3obj['audio']['sample_rate']) )
        {
            $v = $id3obj['audio']['sample_rate'];
            $F['sr'] = number_format($v/1000) . "k";
        }

        if( !empty($id3obj['audio']['channelmode']) )
        {
            $v = $id3obj['audio']['channelmode'];
            $F['ch'] = $v;
        }

        if( !empty($id3obj['video']['resolution_x']) )
        {
            $F['dim'] = array( $id3obj['video']['resolution_x'],
                                $id3obj['video']['resolution_y'] );
        }

        if( !empty($id3obj['playtime_string']) )
        {
            $v = $id3obj['playtime_string'];
            if( $v == '0:00' )
                $v = '0:01';
            $F['ps'] = $v;
        }

        if( !empty($id3obj['bitrate']) )
        {
            if( !empty($id3obj['audio']['bitrate_mode']) )
            {
                if( $id3obj['audio']['bitrate_mode'] == 'vbr') 
                    $F['br'] = 'VBR';
                elseif( $id3obj['audio']['bitrate_mode'] == 'cbr') 
                    $F['br'] = 'CBR';
            }
            else
            {
                $v = $id3obj['bitrate'];
                $F['br'] = number_format(($v/1000)) . "kbps";
            }
        }
        
        if( !empty($id3obj['zip'])  )
        {   
            $files = $id3obj['zip']['files']; 
            $this->_walk_zip_files(null,$files,"",$zipdir);
            $F['zipdir'] = $zipdir;
        }

    }

    /**
    * Internal: Build an array with a listing of files contained in the zip file
    * 
    * @access private
    */
    function _walk_zip_files($k,$v,$curdir,&$R)
    {
        if( is_array($v) )
        {
            foreach( $v as $k2 => $v2 )
                $this->_walk_zip_files($k2,$v2,"$curdir$k/",$R);
        }
        else
        {
            if( $v > CC_1MG )
                $v = number_format($v/CC_1MG,2) . "MB";
            elseif ( $v > 1000 )
                $v = number_format($v/1024,2) . "KB";

            $R['files'][] = "$curdir$k  ($v)";
        }
    }

    /**
    * Internal: parse out what the media type and data formats for a file 
    *
    * @param array $A Array of GetID3 tags
    */
    function _parse_format_name($A)
    {
        $media_type = null;

        if( isset($A['video']) )
        {
            if( isset( $A['video']['dataformat'] ) )
            {
                if( isset($A['playtime_string']) )
                    $media_type = 'video';
                else
                    $media_type = 'image';

                $dataformat = $A['video']['dataformat'];
            }
        }
        elseif( isset($A['audio']) )
        {
            if( isset($A['audio']['dataformat'])  )
            {
                $media_type = 'audio';
                $dataformat = $A['audio']['dataformat'];
            }
        }
        elseif( isset($A['zip']) )
        {
            $media_type = 'archive';
            $dataformat = '';
        }

        $name = null;
        if( $media_type && isset($A['fileformat']) )
            $name = "$media_type-{$A['fileformat']}-$dataformat";

        return($name);
    }

}

?>
