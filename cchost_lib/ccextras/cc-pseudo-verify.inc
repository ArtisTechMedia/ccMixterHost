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
* $Id: cc-pseudo-verify.inc 10504 2008-07-14 20:49:21Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');
require_once('cchost_lib/cc-getid3.php');

define('CC_PV_DISABLE', 'da' );
define('CC_PV_SIG', 'sig' );
define('CC_PV_VERIFY', 'vrfy' );

class CCPseudoVerifyAdminForm extends CCGridForm
{
    function CCPseudoVerifyAdminForm()
    {
        $this->CCGridForm();
        $this->SetTemplateVar('form_fields_macro','flat_grid_form_fields');

        $help = _('Use this screen to allow uploads of less common and hard to verify file types. Unlike other common media types the degree of support for verifying the efficacy of these file types range greatly.') . "\n" .
                '<p>' . _('A guide to using this screen') . ":</p>\n" .
                '<p>' . _('The <b>Del</b>ete option only applies to types that you add here. Otherwise the types displayed here are culled from your current getID3 installation. (If you upgrade getID3, you should come back here and the new types will automatcially show up.)') . "</p>\n" . 
                '<p>' . _('<b>D/S/V</b> Here you chose what kind of support you wish to have for the file type') . ":</p>\n" . 
                '<ul><li>' . _('<b>D</b>isable the use of this file type, users will see an error if they try to upload this type.') . "</li>\n" . 
                '<li>' . _('<b>S</b>ignature check only. This will allow the file type to be uploaded but with only <i>very</i> minimal amount of verification. In other words, if a virus is embedded into an Office document, this check will not be able to detect it.') . "</li>\n" . 
                '<li>' . _('Attempt to <b>V</b>erify using the getID3 library. This will only work if your version of getID3 supports full analysis of the file type. Many file types in this list require special tools to be installed (see your helperapps directory for details). Others are simply unsupported in which case you have to fall back to <b>S</b>ignature check only.') . "</li>\n" . 
                '</ul>' . 
                '<p>' . _("Make sure to enter the file's <b>Ext</b>ension.") . 
                "</p>\n" . 
                "<p>" . _('<b>Tags</b> will be added to every file uploaded of this type.') . "</p>\n" . 
                '<p>' . _('<b>Signature</b> is only relevant if you chose the <b>S</b>ignature option') . "</p>\n" . 
                '<p>' . _('Use the <b>Add Format</b> button to extend this table and add formats. Because these are not known to getID3, you must use the <b>S</b>ignature only option, otherwise your users will see an error message when trying to upload.') . "</p>\n";

        $this->SetFormHelp($help);

        $heads = array( 
            _('Delete'), 
                _('D/S/V'), 
             _('Ext.'), _('Type'), 
            _('Description'), 
            _('Tags'), _('Signature'), );
        
        $this->SetColumnHeader($heads);

        $configs =& CCConfigs::GetTable();
        $pv =  $configs->GetConfig('pseudo-verify',CC_GLOBAL_SCOPE);
        $ffa = CCPseudoVerifyAPI::_import_getid3_formats();

        if( empty($pv) )
        {
            $ffa['txt'] = array(
                'delete' => false,
                'media-type'  => 'document',
                'tags'        => 'document,txt',
                'default-ext' => 'txt',
                'action'      => CC_PV_SIG,
                'description' => _('Plain Text (ASCII)'),
                'isgetid3'    => false,
                'pattern'     => '^[\s\x21-\x7F]+$'
                );
        }

        foreach( $ffa as $K => $F )
        {
            if( empty($pv[$K]) )
                $pv[$K] = $F;
        }

        foreach( $pv as $K => $F  )
        {
            $a = $this->_get_row('mi', $K, $F);
            $this->AddGridRow( $K, $a );
            $this->SetHiddenField( "mi[$K][isgetid3]",  $F['isgetid3'] );
        }

        $new_fields = array(
                'delete' => false,
                'media-type'  => '',
                'tags'        => '',
                'default-ext' => '',
                'action'      => CC_PV_SIG,
                'description' => '',
                'isgetid3'    => false,
                'pattern'     => ''
                );

        $new_meta_row = $this->_get_row('new','%i%', $new_fields );
        $new_meta_row[0]['formatter'] = 'statictext';
        $this->AddMetaRow($new_meta_row, _('Add Format') );
    }

    function _get_row($pre,$K,$F)
    {
        $del_type = $F['isgetid3'] ? 'statictext' : 'checkbox';

        $a = array(
              array(
                'element_name'  => "{$pre}[$K][delete]",    
                'value'      => false,
                'formatter'  => $del_type,
                'flags'      => CCFF_NONE ),
              array(
                'element_name'  => "{$pre}[$K][action]",    
                'value'      => $F['action'],
                'formatter'  => 'radio',
                'nobr'       => true,
                'options'    => array( CC_PV_DISABLE => '',
                                       CC_PV_SIG => '', 
                                        CC_PV_VERIFY => ''),
                'flags'      => CCFF_POPULATE ),
              array(
                'element_name'  => "{$pre}[$K][default-ext]",  // key
                'value'      => $F['default-ext'],
                'class'      => 'cc_form_input_short',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => "{$pre}[$K][media-type]",    // group
                'value'      => $F['media-type'],
                'class'      => 'cc_form_input_short',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
              array(
                'element_name'  => "{$pre}[$K][description]",  // --
                'value'      => $F['description'],
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED ),
            );

        $a[] = array(
                'element_name'  => "{$pre}[$K][tags]",    
                'value'      => $F['tags'],
                'class'      => 'cc_form_input_short',
                'formatter'  => 'textedit',
                'flags'      => CCFF_REQUIRED );

        $a[] = array(
            'element_name'  => "{$pre}[$K][pattern]",      // pattern
            'value'      => htmlspecialchars($F['pattern']),
            'class'      => 'cc_form_input_short',
            'formatter'  => 'textedit',
            'flags'      => CCFF_NONE );

        return $a;
    }
}

class CCPseudoVerifyAPI
{
    function CCPseudoVerifyAPI()
    {
        $this->_not_empty = true;
    }

    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','pverify'), array( 'CCPseudoVerifyAPI', 'Admin' ),
                          CC_ADMIN_ONLY, ccs(__FILE__), '', 
                          _('Display verification options form.'), CC_AG_UPLOADS );
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
        if( empty($file['file_format_info']['tags']) )
            return;

        require_once('cchost_lib/cc-tags.php');

        $newtags = CCTag::TagSplit($file['file_format_info']['tags']);
    
        $tags = array_merge($tags,$newtags);
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items, $scope)
    {
        if( $scope != CC_GLOBAL_SCOPE )
            return;

        $items += array( 
            'pverify'   => array( 'menu_text'  => _('Pseudo Verify'),
                         'menu_group' => 'configure',
                         'access' => CC_ADMIN_ONLY,
                          'help'  => _('Configure usage of exotic and other dangerous file types'),
                         'weight' => 1000,
                         'action' =>  ccl('admin','pverify')
                         ),
            );
    }

    function _import_getid3_formats()
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS[CCGETID3_FILEVERIFY_ENABLED_KEY]) )
        {
            // admin might have shut off getid3 option but this psuedo-verify
            // editor requires it
            $CC_GLOBALS[CCGETID3_FILEVERIFY_ENABLED_KEY] = true;
            _verify_getid3_install();
        }


        $basepath = CCUtil::CheckTrailingSlash($CC_GLOBALS[CCGETID3_PATH_KEY],true);
        $getid3_obj = CCGetID3::InitID3Obj();
        $ffa = $getid3_obj->GetFileFormatArray();

        $media_ffa = CCGetID3::GetFormats();

        $desc = array( 
				'ac3'  => _('Dolby AC-3 / Dolby Digital'),
				'adif' => _('AAC - ADIF format'),
				'adts' => _('AAC - ADTS format'),
				'avr'  => _('Audio Visual Research'),
				'bonk' => _('Bonk v0.9+'),
				'la'   => _('Lossless Audio'),
				'lpac' => _('Lossless Predictive Audio Compression'),
				'mac'  => _("Monkey's Audio Compressor"),
				'mod'  => _('MODule (assorted sub-formats)'),
				'it'   => _('MODule (Impulse Tracker)'),
				'xm'   => _('MODule (eXtended Module)'),
				's3m'  => _('MODule (ScreamTracker)'),
				'mpc'  => _('Musepack / MPEGplus'),
				'ofr'  => _('OptimFROG'),
				'rkau' => _('RKive AUdio compressor'),
				'shn'  => _('MKW Shorten'),
				'tta'  => _('TTA Lossless Audio Compressor'),
				'voc'  => _('Creative Voice'),
				'vqf'  => _('Vector Quantization Format'),
				'wv'   => _('WavPack (v4.0+)'),
				'bink' => _('Bink / Smacker'),
				'flv' => _('FLash Video'),
				'matroska' => _('Mastroka (audio/video)'),
				'mpeg' => _('Moving Pictures Experts Group (audio/video)'),
				'nsv'  => _('Nullsoft Streaming Video'),
				'riff' => _('WAV / AVI / CD-audio / SDSS'),
				'pcd'  => _('Kodak Photo CD'),
				'tiff' => _('Tagged Information File Format'),
				'iso'  => _('(ISO) CD-ROM Image'),
				'rar'  => _('RAR compressed data'),
				'szip' => _('SZIP compressed data'),
				'tar'  => _('TAR compressed data'),
				'gz'  => _('GZIP compressed data'),
				'pdf'  => _('Adobe PDF'),
				'msoffice' => _('Office (Word, Excel, Powerpoint, etc.)'),
            );

        $formats = array();
        foreach( $ffa as $K => $info )
        {
            $found = false;
            foreach( $media_ffa as $K2 => $MF )
            {
                list( $d, $d2, $d3 ) = split('-', $K2);
                if( ($d2 == $K) || ($d3 == $K) || ($MF['name'] == $K) )
                {
                    $found = true;
                    break;
                }
            }

            if( $found )
                continue;

            if( strpos($info['group'],'-') === false )
            {
                $tags = 
                $group = $info['group'];
            }
            else
            {
                list( ,$group ) = split('-', $info['group']);
                $tags = str_replace('-', ',', $info['group']);
            }

            if( $group == 'misc' )
            {
                $group = 
                $tags = 'document';
            }

            $tags .= ',' . $K;

            $formats[$K]['media-type']  = $group;
            $formats[$K]['tags']        = $tags;
            $formats[$K]['pattern']     = $info['pattern'];
            $formats[$K]['default-ext'] = $K;
            $formats[$K]['action']      = CC_PV_DISABLE;
            $formats[$K]['isgetid3']    = true;
            $formats[$K]['description'] = empty($desc[$K]) ?
                                           '(??)' : $desc[$K];
        }

        return $formats;
    }

    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _('Configure Pseudo Verifier');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);

        $form = new CCPseudoVerifyAdminForm();

        if( empty($_POST['pseudoverifyadmin']) || !$form->ValidateFields() )
        {
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            CCUtil::StripSlash($_POST);

            $types = $_POST['mi'];

            if( !empty($_POST['new']) )
            {
                foreach( $_POST['new'] as $newtype )
                {
                    if( empty($newtype['default-ext']) )
                        continue;
                    $newtype['isgetid3'] = false;
                    $types[ $newtype['default-ext'] ] = $newtype;
                }
            }

            $save_types = array();
            $c = count($types);
            $keys = array_keys($types);
            for( $i = 0; $i < $c; $i++ )
            {
                $K = $keys[$i];
                $T =& $types[$K];

                if( empty($T['delete']) )
                    $save_types[$K] = $T;
            }

            $configs =& CCConfigs::GetTable();
            $configs->SaveConfig('pseudo-verify',$save_types,CC_GLOBAL_SCOPE,false);
            
            CCPage::Prompt(_('New pseudo verification options saved'));
        }
    }

   function GetValidFileTypes(&$types)
   {
        require_once('cchost_lib/cc-fileverify.php');
        $old_validator = new CCFileVerify();
        $old_validator->GetValidFileTypes($types);

        $configs =& CCConfigs::GetTable();
        $pv = $configs->GetConfig('pseudo-verify');

        foreach( $pv as $K => $T )
            if( $T['action'] != CC_PV_DISABLE )
                $types[] = $K;

        return $types;
   }

    function FileValidate(&$formatinfo)
    {
        require_once('cchost_lib/cc-fileverify.php');
        $old_validator = new CCFileVerify();
        $retval = $old_validator->FileValidate($formatinfo);
        if( $retval )
            return true;

        $path = $formatinfo->GetFilePath();

        $got_ext = preg_match( '/\.([a-z0-9]+)$/', strtolower($path), $m );

        if( !$got_ext )
        {
            $formatinfo->SetErrors( _('Cannot determine file type'));
            return false;
        }

        $configs =& CCConfigs::GetTable();
        $pv = $configs->GetConfig('pseudo-verify');
        if( empty($pv[$m[1]]) )
        {
            $formatinfo->SetErrors( _('Unknown pseudo-format'));
            return false;
        }

        $FF = $pv[$m[1]];

        if( $FF['action'] == CC_PV_DISABLE )
        {
            $formatinfo->SetErrors( _('That file format is not allowed.'));
            return false;
        }

        global $CC_GLOBALS;

        if( $FF['action'] == CC_PV_VERIFY )
        {
            if( empty($CC_GLOBALS[CCGETID3_FILEVERIFY_ENABLED_KEY]) )
            {
                $CC_GLOBALS[CCGETID3_FILEVERIFY_ENABLED_KEY] = true;
            }

            CCDebug::QuietErrors();
            $debug = CCDebug::Enable(true);

            $id3 =& CCGetID3::InitID3Obj();
            $tags = $id3->analyze($path);

            CCDebug::Enable($debug);
            CCDebug::RestoreErrors();

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
                if( !empty($tags['zip']) )
                {
                    $this->_walk_zip_files(null,$tags['zip']['files'],"",$zipdir);
                    $FF['zipdir'] = $zipdir;
                }
                $retval = true;
            }
        }
        elseif( $FF['action'] == CC_PV_SIG )
        {
            if (!$f = @fopen($path, 'rb')) 
            {
                $formatinfo->SetErrors(_('Could not open file.'));
            }
            else
            {
                $bytes = fread($f,2048);
                $retval = preg_match('/'.$FF['pattern'].'/s', $bytes);
                if( !$retval )
                {
                    $formatinfo->SetErrors(_('Invalid signature for file type'));
                }
                fclose($f);
            }
        }
        else
        {
            $formatinfo->SetErrors(_('Invalid action request'));
        }

        if( $retval )
        {
            $FI['tags'] = $FF['tags'];
            $FI['default-ext'] = $FF['default-ext'];
            $FI['media-type'] = $FF['media-type'];
            $FI['mime_type'] = empty($FF['mime_type']) ? 'octect/stream' : $FF['mime_type'];
            if( !empty($FF['zipdir']) )
                $FI['zipdir'] = $FF['zipdir'];
            $formatinfo->SetData( $FI );
            $formatinfo->_errors = '';
        }

        return $retval;
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

}
?>
