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
* $Id: cc-format.php 13251 2009-08-09 17:24:09Z fourstones $
*
*/

/**
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-extras-events.php'); // for EVENT_TOPIC stuff

/**
*/

CCEvents::AddHandler(CC_EVENT_FORM_FIELDS,        array( 'CCFormat', 'OnFormFields'), 'cchost_lib/ccextras/cc-format.inc' );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCFormat', 'OnMapUrls'), 'cchost_lib/ccextras/cc-format.inc' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCFormat' , 'OnGetConfigFields'), 'cchost_lib/ccextras/cc-format.inc'  );

CCEvents::AddHandler(CC_EVENT_FILTER_FORMAT,       array( 'CCFormat', 'OnFilterFormat'), 'cchost_lib/ccextras/cc-format.inc' );

function generator_cc_format($form, $fieldname, $value, $class )
{
    require_once('cchost_lib/ccextras/cc-format.inc');
    return _generator_cc_format($form, $fieldname, $value, $class );
}

/**
* Called from templates to test/convert bbCode-lite text to HTML formatted
*
*/
function cc_format_text($text)
{
    $bb = _cc_is_formatting_on() ;
    if( $bb  )
    {
        $t = _cc_format_format($text);
        return $t;
    }
    return $text;
}

function cc_format_unformat($text)
{
    $attrs = '(b|i|u|red|green|blue|big|small|url|quote|up|left|right|img|query|img|cmd|cmdurl|indent|box|enclosure|var|define)';
    return preg_replace("#\[/?$attrs(=[^\]]+)?\]#U",'',$text);
}

function cc_format_html_to_text($text)
{
    require_once('cchost_lib/smartypants/smartypants.php');
    $text = str_replace(array("\n\r","\n","\r"), '', $text);
    $tokens = _TokenizeHTML($text);
    $text = '';
    foreach( $tokens as $T )
    {
        if($T[0] == 'text')
        {
            if( $text != "\n" )
                $text .= $T[1];
        }
        else {
            if( $T[0] == 'tag'  )
            {
                if( in_array( $T[1], array( '</p>', '</ul>', '</ol>', '</li>',
                                            '</div>', '<br>', '<br />',
                                            '</h1>', '</h2>', '</h3>', '</h4>',
                                            '</tr>', '</table>'
                                           ) ) )
                {
                    $text .= "\n";
                }
            }
        }
    }
    return $text;
}


// old name
function _cc_format_unformat($text)
{
    return cc_format_unformat($text);
}

function validator_cc_format($form, $fieldname)
{
    return $form->validator_textarea($fieldname);
}

function _cc_can_format_edit()
{
    global $CC_GLOBALS;

    return !empty($CC_GLOBALS['format']) ||
           (!empty($CC_GLOBALS['adminformat']) && CCUser::IsAdmin());
}

function _cc_is_formatting_on()
{
    global $CC_GLOBALS;

    return !empty($CC_GLOBALS['format']) ||
           !empty($CC_GLOBALS['adminformat']);
}

function _cc_format_template_tag($tagname,$page)
{
    $value = $page->GetArg($tagname);
    if( $value === null && CCUser::IsAdmin() )
        return $page->String(array('str_invalid_tag',$tagname));
    return $value;
}

function _cc_format_format($text)
{
    require_once('cchost_lib/cc-page.php');
    $page =& CCPage::GetPage();
    $thumbs_up = ccd( $page->Search('images/thumbs_up.gif') );
    
    $quote = $page->String('str_quote');
    require_once('cchost_lib/smartypants/smartypants.php');
    $attrs = '(b|i|u|red|green|blue|big|small|right|left)';
    $text = strip_tags($text);
    $map = array(
                  
                  "/\[$attrs\]/" => '<span class="\1">', 
                  "#\[/$attrs\]#" => '</span>', 
                  "/\[quote=?([^\]]+)?\]/" => '<span class="quote"><span>'. $quote . ' $1</span>', 
                  "#\[/quote\]#" =>  '</span>', 
                  "/\[up]/" => "<img class=\"cc_thumbs_up\" src=\"$thumbs_up\" />", 
                  "#\[/up\]#" => '', 
                  "/\[enclosure=?([^\]]+)?\]/" => '<a href="$1" rel="enclosure">', 
                  "#\[/enclosure\]#" =>  '</a>', 
                  "/\[box]/" => "<div class=\"box\">", 
                  "#\[/box\]#" => '</div>', 
                  "/\[indent=([0-9]+)]/" => '<div class="format_indent" style="padding-left:$1px">',
                  "#\[/indent\]#" => '</div>', 
                  "#\[img=([^\]]+)\]\[/img\]#" => '<img class="format_image" src="$1" />', 
                  );
    $text = preg_replace( array_keys($map), 
                          array_values($map), 
                          $text );

    $text = preg_replace_callback( "#\[var=([^\]]+)]\[/var\]#",
                        function($M) { return _cc_format_template_tag($M[1],$page); },
                        $text ); 

    $text = preg_replace_callback( "#\[define=([^\]]+)\]\[/define\]#", function($M) { return $M[1]; }, $text );

    $text = preg_replace_callback( "#\[cmdurl=([^\]]+)\]\[/cmdurl\]#",
                            function($M) { return ccl($M[1] ); },
                            $text );

    $text = preg_replace_callback(
                  "/\[cmd=([^\]]+)\]/", 
                  function($M) { return "<a rel=\"nofollow\" href=\"" . ccl($M[1]) . "\">"; },
                  $text );

    $text = preg_replace( "#\[/cmd\]#", '</a>', $text );
    
    $text = preg_replace_callback( 
                    "#\[skinimg=([^\]]+)\]\[/skinimg\]#",
                    function($M) { 
                                    $page =& CCPage::GetPage();
                                    $url = ccd($page->Search(array("$M[1]","images/$M[1]")));
                                    return "<img class=\"format_image\" src=\"{$url}\" />";
                                    },
                    $text );
                    
    $text = SmartyPants($text);

    $urls = array( '@(?:^|[^">=\]])(http://[^\s$]+)@m',
                   '@\[url\]([^\[]+)\[/url\]@' ,
                   '@\[url=([^\]]+)\]([^\[]+)\[/url\]@' 
                    );
    $text = preg_replace_callback($urls,'_cc_format_url', $text);

    if( strpos($text,'[query') !== false )
        $text = preg_replace_callback( "#\[query=([^\]]+)\]\[/query\]#",'_cc_format_query',$text);

    $text = nl2br($text);

    if( preg_match('/class="(right|left)/',$text) ) 
        $text .= '<div style="clear:both">&nbsp;</div>';
    
    return $text;
}

function _cc_format_query(&$m)
{
    static $num = 0;
    $qurl = url_args( ccl('api','query'), 'limit=page&f=html&' . urldecode($m[1]));
    ++$num;
    return "<div><div id=\"cath_{$num}\" ></div><script> new Ajax.Updater('cath_{$num}', '{$qurl}', { method: 'get' } );</script></div>";
}

function _cc_format_url(&$m)
{
    $url = $m[1];
    if( empty($m[2]) )
        $text = strlen($url) > 30 ? substr($url,0,27) . '...' : $url;
    else
        $text = $m[2];
    return( " <a title=\"$url\" class=\"cc_format_link\" href=\"$url\">$text</a>" );
}

?>
