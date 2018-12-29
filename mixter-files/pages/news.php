<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

?>
<style >
.reader-publisher-module { /* outter div */
}

#readerpublishermodule0 { /* outter div ID */
}

#readerpublishermodule0 h3, .headline { /* headline */
    font-size: 14px;
}

#readerpublishermodule0 ul { /* links list */
}

#readerpublishermodule0 ul li { /* links list container */
   margin-bottom: 5px;
}

a.i { /* topic link */
}

div.s { /* "from " container */
}

div.s a { /* "from" link */
  font-weight: normal;
}

div.f { /* 'read more' link container */

}

.newsitem {
    margin-bottom: 8px;
    margin-left: 3em;
    margin-top: 3px;
    border: 1px solid #DDD;
    padding: 0.8em;
}

.newsitem font {
    display: block;
    margin-top: 0.8em;
}

.nlink {
    background-color: #DDD;
    padding: 2px;
    margin:  0.8em -0.8em -0.8em -0.8em;
}

.ndate {
    font-style: italic;
    display: block;
    float: right;
}

.ndesc b {
    font-weight: normal;
    font-style: italic;
}

#newstable td {
    vertical-align: top;
}
</style>

<table id="newstable">
<tr>
<? // print_news_items(); ?>
<td>
<script  type="text/javascript" src="http://www.google.com/reader/ui/publisher.js"></script>
<script  type="text/javascript" src="http://www.google.com/reader/public/javascript/user/12748645413098479754/label/friend-of-ccmixter?n=25&callback=GRC_p(%7Bc%3A'-'%2Ct%3A'Friends%20of%20ccMixter'%2Cs%3A'true'%7D)%3Bnew%20GRC"></script>
</td></tr></table>

<?
function print_news_items()
{
    print '<td style="width:45%;padding-right:4em;">';
    print '<h3 class="headline" style="margin-botttom:1.5em;">ccMixter in the News</h3>';
    print '<br />';
    
   $items = cc_get_feed_items('http://www.google.com/alerts/feeds/12748645413098479754/4912859173467429734'); 
   foreach( $items as $item )
   {
      ?><div class="newsitem">
             <div class="ndesc"><?= $item['description'] ?></div>
             <div class="nlink"><span class="ndate"><?= $item['date_timestamp'] ?></span> <a href="<?= $item['link'] ?>">read more...</a></div>
        </div>
      <?
   }
   
   print '</td>';
}

function dnw($str)
{
    //print $str . '<br />' . "\n";
}

function cc_get_feed_items($url,$cache_time=360,$max_items=20) // 86400 = 24*60*60
{
    global $CC_GLOBALS;

    $path = cc_temp_dir() . '/' . md5($url) . '.tmp';
    if( file_exists($path) )
    {
        dnw('got file: ' . $path);

        $items = file_get_contents($path);
        $items = unserialize($items);
    }

    $admin_over = CCUser::IsAdmin() && !empty($_GET['xnews']);

        dnw('admin_over: (' . $admin_over . ')');;
    
    $is_expired = empty($cache_time) || empty($items) || ( (filemtime($path) + $cache_time) < time() );

        dnw('cache_time: (' . $cache_time . ')');
        dnw('is_expired: (' . $is_expired . ')');

    if( $admin_over || $is_expired )
    {
        require_once('cchost_lib/cc-feedreader.php');
        $fr = new CCFeedReader();
        $xml = $fr->cc_parse_url($url);
        $new_items =& $xml->items;

        if( empty($items) )
        {
            foreach( $new_items as $new_item )
            {
                if( stristr( $new_item['link'], 'google.com' ) !== false )
                    continue;
                $items[] = $new_item;
            }
        }
        else
        {
            $prepend_these = array();

            foreach( $new_items as $new_item )
            {
                    dnw( 'new: (' . $new_item['link'] . ') ');
    
                if( stristr( $new_item['link'], 'google.com' ) !== false )
                    continue;

                $found = false;
                foreach( $items as $item )
                {
                    if( $item['link'] == $new_item['link'] )
                    {
                        $found = true;
                        break;
                    }
                }
                if( !$found )
                    $prepend_these[] = $new_item;
            }

            $items = array_merge( $prepend_these, $items );
        }

        $copy = $items;
        if( count($copy) > $max_items )
            $copy = array_slice( $copy, 0, $max_items );
        $text = serialize($copy);
        $f = fopen($path,'w');
        fwrite($f,$text);
        fclose($f);
    }

    return $items;
}
?>
