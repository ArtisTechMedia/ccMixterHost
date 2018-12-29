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
* $Id: cc-file-props.php 12718 2009-06-04 07:21:54Z fourstones $
*
*/

/**
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* @package cchost
* @subpackage admin
*/
class CCFileProps
{
    function GetProps($format_dir,$type,$ret_files=true,$tdirs='',$must_have=null)
    {
        global $CC_GLOBALS;
        require_once('cchost_lib/cc-template.php');
        if( empty($tdirs) )
        {
            $tdirs = CCUtil::SplitPaths($CC_GLOBALS['template-root'], CC_DEFAULT_SKIN_SEARCH_PATHS );
        }
        else
        {
            if( is_string($tdirs) )
                $tdirs = CCUtil::SplitPaths($tdirs);
        }
        $this->_check_slashes($tdirs);
        $results = array();
        $seen = array();
        $this->_scan_dir( $results, $tdirs, $format_dir, $type, $ret_files, $must_have, $seen );
        return $results;
    }

    function GetFileProps($filename)
    {
        global $xx;

        if( filesize($filename) > (100 * 1024) )
            return null;
        $text = file_get_contents($filename);
        if( !preg_match('#.*\[meta\](.*)\[/meta\].*#Ums',$text,$m) )
            return null;
        $lines = split("\n",$m[1]);
        $props = array();
        foreach( $lines as $line )
        {
            $line = trim($line);
            if( empty($line) )
                continue;
            $parts = split('=',$line);
            $prop_name = array_shift($parts);
            $props[ trim($prop_name) ] = trim( join( '=', $parts) );
        }
        $this->_get_wrapped_prop($props,'desc');
        $this->_get_wrapped_prop($props,'page_title');
        return $props;
    }

    function _get_wrapped_prop(&$props,$pt)
    {
        if( !empty($props[$pt]) )
        {
            if( preg_match("/^_\(['\"](.+)['\"]\)$/",$props[$pt],$m) )
                $props[$pt] = _($m[1]);
        }
    }
    
    function _scan_dir( &$match_files, $source, $format_dir, $type, $ret_files, $must_have, &$seen )
    {
        foreach( $source as $dir )
        {
            if( substr($dir,-6) == 'images' || in_array($dir,$seen) )
                continue;

            $seen[] = $dir;

            if( $format_dir )
            {
                $format_path = $dir . '/' . $format_dir;
                if( !file_exists( $format_path ) )
                    continue;
            }
            else
            {
                $format_path = $dir;
            }
            $subdirs = array();
            $_files = glob( $format_path . '/*.*' ) ;
            if( $_files !== false )
            {
                foreach( $_files as $ffile)
                {
                    if( is_dir($ffile) )
                        continue;

                    $props = $this->GetFileProps($ffile);

                    if( $props && empty($props['type']) )
                        die("Missing meta 'type' in $ffile");

                    if( !$props || ($props['type'] != $type) )
                        continue;

                    if( $must_have && empty($props[$must_have]) )
                        continue;

                    if( $ret_files )
                    {
                        if( empty($props['desc']) )
                        {
                            $match_files[$ffile] = $ffile;
                        }
                        else
                        {
                            $match_files[$ffile] = $props['desc'];
                        }
                    }
                    else
                    {
                        $props['id'] = $ffile;
                        $match_files[] = $props;
                    }
                }
            }
            
            $subdirs = glob( $dir . '/*', GLOB_ONLYDIR );
            if( !empty($subdirs) )
                $this->_scan_dir($match_files, $subdirs, $format_dir, $type, $ret_files, $must_have, $seen );
        }

        return $match_files;
    }

    function GetMultipleProps( $types )
    {
        global $CC_GLOBALS;
        require_once('cchost_lib/cc-template.php');
        $tdirs = CCUtil::SplitPaths($CC_GLOBALS['template-root'], CC_DEFAULT_SKIN_SEARCH_PATHS );
        $this->_check_slashes($tdirs);
        $results = array();
        $seen = array();
        $this->_multiple_scan_dir($results, $tdirs, $types, $seen);
        return $results;
    }

    function _multiple_scan_dir(&$match_files, $base_dirs, $types, &$seen )
    {
        foreach( $base_dirs as $dir )
        {
            if( substr($dir,-6) == 'images' || in_array( $dir, $seen ) )
                continue;

            $seen[] = $dir;

            $_files = glob( $dir . '/*.*' ) ;
            if( $_files !== false )
            {
                foreach( $_files as $ffile )
                {
                    if( is_dir($ffile) || $ffile{0} == '.' )
                        continue;

                    $props = $this->GetFileProps($ffile);
      
                    if( $props && empty($props['type']) )
                        die("Missing meta 'type' in $ffile");

                    if( !$props )
                        continue;

                    foreach( $types as $type )
                    {
                        if( $props['type'] != $type )
                            continue;

                        if( empty($props['desc']) )
                        {
                            $match_files[$type][$ffile] = $ffile;
                        }
                        else
                        {
                            $match_files[$type][$ffile] = $props['desc'];
                        }
                    }
                }
            }
            
            $subdirs = glob( $dir . '/*', GLOB_ONLYDIR );
            if( !empty($subdirs) )
                $this->_multiple_scan_dir($match_files, $subdirs, $types, $seen );
        }

        return $match_files;
    }

    function _check_slashes(&$tdirs)
    {
        $tdirs = array_filter($tdirs);
        $k = array_keys($tdirs);
        $c = count($k);
        for( $i = 0; $i < $c; $i++ )
        {
            $tdirs[$k[$i]] = CCUtil::CheckTrailingSlash($tdirs[$k[$i]],false);
        }
    }
}

?>
