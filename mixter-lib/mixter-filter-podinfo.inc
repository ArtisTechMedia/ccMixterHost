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
        
        $text = $rows[$K]['topic_text_html'];
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
            
            // ATTENTION: This is officially the worst hack I've
            // ever done to cchost. I promise to do right. At. Some. Point.
            if( !empty($rows[$K]['enclosure_url']) )
            {
                if( empty($rows[$K]['enclosure_size']) )
                    $rows[$K]['enclosure_size'] = '10000000';
                if( empty($rows[$K]['enclosure_duration']) )
                    $rows[$K]['enclosure_duration'] = '40:00';
            }
        }
        unset($rows[$K]['topic_text']);        
    }
}

?>
