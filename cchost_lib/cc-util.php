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
* $Id: cc-util.php 13122 2009-07-30 18:31:02Z fourstones $
*
*/

/**
* @package cchost
* @subpackage util
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**#@+
* Sat, 07 Sep 2002 00:00:01 GMT
* ..actually 'T' prints the entire acronym out
*/
define('CC_RFC822_FORMAT', 'D, d M Y H:i:s '); // T');
define('CC_RFC3339_FORMAT', 'Y-m-d\TH:i:s');
/**#@-*/


define('CC_SEARCH_RECURSE_DEFAULT', false);

define('CC_AJAX_MESSAGE', 'message');
define('CC_AJAX_WARNING', 'warning');
define('CC_AJAX_ERROR',   'err');

/**
*/
function cc_default_file_perms()
{
    global $CC_GLOBALS;

    if( empty($CC_GLOBALS['file-perms']) || !intval($CC_GLOBALS['file-perms']) )
        return 0777;

    return intval($CC_GLOBALS['file-perms']);
}

function cc_default_dir_perm()
{
    return cc_default_file_perms();
}

function cc_temp_dir()
{
    global $CC_GLOBALS;

    if( !empty($CC_GLOBALS['temp-dir']) )
        return $CC_GLOBALS['temp-dir'];
    if( !empty($CC_GLOBALS['php-tal-cache-dir']) )
        return $CC_GLOBALS['php-tal-cache-dir'];
    if( !empty($CC_GLOBALS['logfile-dir']) )
        return $CC_GLOBALS['logfile-dir'] ;
    return getcwd();
}

function cc_split( $a, $b )
{
    //CCDebug::LogVar("split key", $a);
    return explode( $a, $b );
}


/**
*/
function cc_setcookie($name,$value,$expire,$path='',$domain='')
{
    global $CC_GLOBALS;

    if( empty($path) )
        $path = '/';

    // Domain might still be null, that should be ok 
    if( empty($domain) )
        $domain = $CC_GLOBALS['cookie-domain'];

    // if it's empty it must be numeric
    if( empty($expire) )
        $expire = 0; 
    
    $ok = setcookie($name,$value,$expire,$path,$domain);

    return( $ok );
}

/**
*/
function cc_exit()
{
    CCEvents::Invoke(CC_EVENT_APP_DONE);    
    exit();
}

function cc_weight_sorter($a, $b)
{
   return( $a['weight'] > $b['weight'] ? 1 : -1 );
}

/**
*/
class CCUtil
{
    public static function Strip(&$mixed) 
    {
        if( is_array($mixed) )
        {
            $keys = array_keys($mixed);
            foreach( $keys as $key )
                CCUtil::Strip($mixed[$key]);
        }
        else
        {
            CCUtil::StripText($mixed);
        }

        return $mixed;
    }

    /**
     * Encodes HTML safely for UTF-8. Use instead of htmlentities.
     *
     * @param string $var
     * @return string
     */
    public static function HTMLEncode($text)
    {
        return htmlentities($text, CC_QUOTE_STYLE, CC_ENCODING) ;
    }


    public static function StripText(&$text)
    {
        if( is_integer($text) )
            return($text);
        if( empty($text) )
            return(null);
        $text = trim(strip_tags(CCUtil::StripSlash($text)));
        return($text);
    }

    public static function StripSlash(&$mixed)
    {
        if( get_magic_quotes_gpc() == 1 )
        {
            if( is_array($mixed) )
            {
                $keys = array_keys($mixed);
                foreach( $keys as $key )
                    $mixed[$key] = CCUtil::StripSlash($mixed[$key]);
            }
            else
            {
                $mixed = trim(stripslashes( $mixed ));
            }
        }
        return($mixed);
    }

    public static function CleanNumbers($keys)
    {
        if( is_array($keys) )
            $keys = join(':',$keys);
        return preg_split('/([^0-9]+)/',$keys,0,PREG_SPLIT_NO_EMPTY);
    }

    public static function TextToHTML($text,$convert_nl=true)
    {
        if( empty($text) )
            return('');

        $text = str_replace('--','&#8212;', CCUtil::HTMLEncode($text));
        
        if( $convert_nl )
            $text = nl2br($text);

        $regex = '#http://(.*)(\s|$|<)#Ue';
        $repl  = "'<a class=\"cc_external_link\" target=\"_blank\" " .
                 "href=\"http://$1\"><span>' . (strlen('$1') > 25 ? substr('$1',0,25) . '...' : '$1') . '</span></a> $2'";
        $text = preg_replace($regex,$repl,$text);

        return($text);
    }

    public static function CheckTrailingSlash($dir,$slash_required)
    {
        $dir = str_replace('\\','/',$dir);
        if( preg_match('#^(.*)/$#',$dir,$m) )
        {
            if( $slash_required )
                return($dir);
            return( $m[1] );
        }
        if( $slash_required )
            return( $dir . '/' );
        return( $dir );
    }

    public static function AccessError($file='',$lineo='')
    {
        $str = "Access attempt from: {$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']}\n{$_SERVER['HTTP_USER_AGENT']}";
        CCDebug::Log($str);
        print("<pre>$str</pre>");
        if( CCUser::IsAdmin() )
            CCDebug::StackTrace();
        else
            exit;
    }

    public static function SendBrowserTo($newurl='')
    {
        if( empty($newurl) )
        {
            if( !empty($_POST['http_referer']) )
                $newurl = htmlspecialchars(urldecode($_POST['http_referer']));
        }
        if( empty($newurl) )
        {
            $newurl = cc_get_root_url();
        }

        if( !empty($_GET['popup']) && (strpos($newurl,'popup=') === false) )
            $newurl = url_args($newurl,'popup=1');

        cc_send_no_cache_headers();
        header("Location: $newurl");
        exit;
    }

    public static function ReturnAjaxData($obj,$inHeader=true)
    {
        require_once('cchost_lib/zend/json-encoder.php');
        $text = CCZend_Json_Encoder::encode($obj);
        if( $inHeader )
            header( "X-JSON: $text");
        header( 'Content-type: text/plain');
        print( '(' . $text . ')' );
        exit;
    }

    public static function ReturnAjaxMessage($msg,$type = CC_AJAX_MESSAGE)
    {
        $obj[$type] = $msg;
        CCUtil::ReturnAjaxData($obj);
    }

    public static function IsHTTP()
    {
        return( !empty($_SERVER['HTTP_HOST']) );
    }

    public static function Send404($exit=true,$file='',$line='')
    {
        header("HTTP/1.0 404 Not Found");
        if( $exit )
        {
            if( $file )
            {
                $file = ' ' . ccs($file);
                if( $line )
                    $file .= ' (' . $line . ')';
            }
            print(_('file not found') . $file );
            exit;
        }
    }

    public static function HashString($str)
    {
        return '' . sprintf('%08X',crc32(trim($str)));
    }

    public static function MakeSubdirs($pathname,$mode='')
    {
        if( empty($mode) )
            $mode = cc_default_dir_perm();

        // Check if directory already exists
        if (is_dir($pathname) || empty($pathname)) {
            return true;
        }
     
        // Ensure a file does not already exist with the same name
        if (is_file($pathname)) {
            trigger_error('MakeSubdirs() File exists', E_USER_WARNING);
            return false;
        }
     
        // Crawl up the directory tree
        $next_pathname = substr($pathname, 0, strrpos($pathname, '/'));
        if (CCUtil::MakeSubdirs($next_pathname, $mode)) {
            if (!file_exists($pathname)) {
                $umask = umask(0);
                $ok = mkdir($pathname, $mode);
                umask($umask);
                return($ok);
            }
        }
     
        return false;
    }

    public static function BaseFile($path)
    {
        $base = basename($path);
        $ex = explode('.',$base);
        if( count($ex) > 1 )
            $base = basename($path, '.' . $ex[ count($ex)-1 ]);
        return($base);
    }

    public static function LegalFileName($name_to_cleans)
    {
        if( strlen($name_to_cleans) > 255 )
        {
            $ext = '';
            if( preg_match( '/\.[^\.]+$/',$name_to_cleans,$m) )
                $ext = $m[0];
            $name_to_cleans = substr( $name_to_cleans, 0, 254 - strlen($ext) );
        }
        $goodchars = 'a-zA-Z0-9\.\(\)\-';
        $result = preg_replace( "/[^$goodchars]+/", '_', $name_to_cleans );
//      $arr = array( "goodchars" => $goodchars, "org" => $name_to_cleans, "result" => $result );
//      CCDebug::PrintVar($arr);
        return $result;
    }

    /**
    * Parse the W3C date/time format, a subset of ISO 8601. PHP date parsing
    * functions do not handle this format.
    * See http://www.w3.org/TR/NOTE-datetime for more information.
    * Originally from MagpieRSS (http://magpierss.sourceforge.net/).
    *
    * @param $date_str A string with a potentially W3C DTF date.
    * @return A timestamp if parsed successfully or -1 if not.
    */
    public static function ParseW3cdtfDate($date_str) 
    {
        $regex = '/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/';
        if( preg_match($regex, $date_str, $match)) 
        {
            list( $year, $month, $day, $hours, $minutes, $seconds ) 
                = array($match[1], $match[2], $match[3], $match[4], $match[5], $match[6]);

            // calc epoch for current date assuming GMT
            $epoch = gmmktime($hours, $minutes, $seconds, $month, $day, $year);

            if ($match[10] != 'Z') 
            { // Z is zulu time, aka GMT
                list($tz_mod, $tz_hour, $tz_min) = array($match[8], $match[9], $match[10]);
                // zero out the variables
                if (!$tz_hour)
                    $tz_hour = 0;
                if (!$tz_min)
                    $tz_min = 0;
            }

            $offset_secs = (($tz_hour * 60) + $tz_min) * 60;
            // is timezone ahead of GMT?  then subtract offset
            if ($tz_mod == '+')
                $offset_secs *= -1;

            $epoch += $offset_secs;
            return $epoch;
        }

        return false;
    }

    public static function GetTimeZone()
    {
        static $TZ;
        if( !isset($TZ) )
        {
            $TZ = date('T');
            if ( strlen($TZ) > 3 )
                $TZ = preg_replace('/[^A-Z]/','',$TZ);
        }
        return $TZ;
    }

    public static function GetGMZone()
    {
        static $GM;
        if( !isset($GM) )
        {
            $GM = date('O');
            if( strpos(':',$GM) === false )
                $GM = preg_replace('/00$/',':00',$GM);
        }
        return $GM;
    }

    public static function FormatDate($fmt,$date)
    {
        
        $TZ = CCUtil::GetTimeZone();
        $GM = CCUtil::GetGMZone();

        $d = date($fmt,$date);

        if( $fmt == CC_RFC822_FORMAT )
        {
            $d .= $TZ;
        }
        elseif( $fmt == CC_RFC3339_FORMAT )
        {
            $d .= $GM;
        }

        return( $d );
    }

    // rippped from phpBB2

    public static function EncodeIP($dotquad_ip)
    {
        if( preg_match('/::[0-9]+$/',$dotquad_ip) )
        {
            // this is a mac dev environment (sheesh)
            return '127.0.0.1';
        }
        $ip_sep = explode('.', $dotquad_ip);
        return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
    }

    public static function DecodeIP($int_ip)
    {
        $hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
        return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
    }

    static function SplitPaths($paths, $must_haves='')
    {
        $str = preg_replace('/(.*);?$/U', '\1', $paths);
        $dirs = cc_split(';',$str);
        if( $must_haves )
        {
            if( !is_array($must_haves) )
                $must_haves = cc_split(';',$must_haves);

            foreach( $must_haves as $must_have )
            {
                $must_have = CCUtil::CheckTrailingSlash($must_have,false);

                if( empty($dirs) || 
                    (
                        !in_array( $must_have . '/', $dirs  ) && 
                        !in_array( $must_have, $dirs )
                    ) 
                )
                {
                    $dirs[] = $must_have;
                }
            }
        }

        return $dirs;
    }

    public static function SearchPath($target,$look_here_first,$then_here,$real_path=true,$recurs=CC_SEARCH_RECURSE_DEFAULT)
    {
        if( !is_array($target) )
            $target = array($target);

        foreach( $target as $T )
        {
            if( file_exists($T) )
                return $real_path ? realpath($T) : $T;
        }
            
        $dirs = $look_here_first;
        if( !is_array($dirs) )
            $dirs = cc_split(';',$dirs);
        if( !empty($then_here) )
        {
            if( !is_array($then_here) )
                $then_here = cc_split(';',$then_here);
            $dirs = array_merge($dirs,$then_here);
        }
        $clean_dirs = array();
        foreach( $dirs as $dir )
        {
            $dir = trim($dir);
            if( empty($dir) )
                continue;
            $clean_dirs[] = CCUtil::CheckTrailingSlash($dir,true);
        }

        // I would love to use '{' braces for this but there seems to
        // be a limit (120?) on the size of the string so fk-it.

        foreach( $clean_dirs as $dir )
        {
            foreach( $target as $T )
            {
                $hit = CCUtil::_inner_search($T,$dir,$recurs);
                if( !empty($hit) )
                    return $real_path ? realpath($hit[0]) : $hit[0];
            }
        }
        return null;
    }

    static function _inner_search($target,$dir,$recurs)
    {
        $hit = glob($dir . $target);
        if( !empty($hit) || !$recurs )
            return $hit;
        $subdirs = glob( $dir . '*',GLOB_ONLYDIR|GLOB_NOSORT|GLOB_MARK);
        if( empty($subdirs) )
            return null;
        foreach( $subdirs as $dir )
        {
            $hit = CCUtil::_inner_search($target,$dir,$recurs);
            if( !empty($hit) )
                return $hit;
        }
        return null;
    }

}

if( !function_exists('array_combine') )
{
    function array_combine($keys,$values)
    {
        $c = count($keys);
        $dest = array();
        $vkeys = array_keys($values);
        for( $i = 0; $i < $c; $i++ )
        {
            $dest[$keys[$i]] = $values[$vkeys[$i]];
        }
        return $dest;
    }
}


?>
