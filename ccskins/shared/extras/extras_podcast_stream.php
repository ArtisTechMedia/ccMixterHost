<?/*

[meta]
    type  = extras
    desc  = _('Podcast and Stream this page (for audio)')
[/meta]

*/

if( empty($A['qstring']) || (!empty($A['page_datasource']) && ($A['page_datasource'] != 'uploads')) )
    return;

$qstring = $A['qstring'] .'&limit=page';
$q = $A['q'];

?>  
<p><?=$T->String('str_media')?></p>
<ul>
<?

    if( !empty($A['get']['offset']) ) 
    {
        $offs = $A['get']['offset']; 
    } 
    else
    {
        $offs = 0;
    } 

    if ( !empty($A['enable_playlists'])) 
    {
        $script = true;
        ?><li><a id="mi_play_page" href="javascript://play page" onclick="ppage()"><?=$T->String('str_play_this_page')?></a></li><?
    }
    else
    {
        $script = false;
    }

    $url  = $A['home-url'] . 'api/query/stream.m3u' . $q .'f=m3u&' . $qstring;
    if( strstr( $url, 'offset=' ) === false )
        $url .= '&offset=' . $offs;
    $url2 = $A['query-url'] . 'f=rss&' . $qstring . '&offset=' . $offs;
    $url3 = 'f=html&' . $qstring . '&offset=' . $offs . '&t=download';
?>   
<li><a id="mi_stream_page" href="<?=$url?>"><?= $T->String('str_stream_this_page') ?></a></li>
<li><a id="mi_podcast_page" title="<?= $T->String('str_drag_this_link') ?>" href="<?=$url2?>"><?= $T->String('str_podcast_this_page')?></a></li>
<li id="mi_download_page_parent"><a id="mi_download_page" href=""><?= $T->String('str_download_this_page')?></a></li>
</ul>
<script type="text/javascript">
$('mi_download_page').href = query_url + '<?= $url3 ?>';
new modalHook( [ 'mi_download_page' ] );
<?
if( $script )
{
?>
    function ppage() { 
        var url = query_url + 'popup=1&t=playable_list&&offset=<?= $offs ?>&<?= $qstring ?>';
        var dim = "height=400,width=650";
        var win = window.open( url, 'cchostplayerwin', "status=1,toolbar=0,location=0,menubar=0,directories=0," +
                      "resizable=1,scrollbars=1," + dim );
    }
<? 
} 
?>
</script>
