<?

/*
  $Id: mixter-filter-podinfo.inc 14109 2010-02-18 20:45:41Z fourstones $
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');
   
function cc_filter_podinfo(&$rows)
{
    foreach( $rows as $K => $V )
    {
        $text = $rows[$K]['topic_text'];
        /*
          example:
          
            enclosure_url%http://ccmixter.org/podcasts/The_Mixin_Kitchen_17.mp3%
            enclosure_size%48266311%
            enclosure_duration%39:08%
            enclosure_type%audio/mp3%
        */
        
        if( strpos($text,'enclosure_url') !== false )
        {
            preg_match_all('/enclosure_(url|size|type|duration)%([^%]+)%/U',$text,$m);
            for( $n = 0; $n < 4; $n++ )
            {
                if( !empty($m[1][$n]) )
                    $rows[$K]['enclosure_' . $m[1][$n]] = $m[2][$n];
            }
        }
        unset($rows[$K]['topic_text']);
        
    }
}

?>
