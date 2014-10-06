<? /*
[meta]
    type     = embed
    dataview = links_by_dl
    desc     = _('Fabricio Zuardi's Button Player - Requires Flash')
[/meta]
*/ ?>

<div  id="cc_mplayer">
<?
if( empty($A['records']) )
{
    print '<!-- no records match! --></div>';
    return;
}
$qstring = $A['qstring'];
parse_str($qstring,$qargs);
if( empty($qargs['rand']) )
{
    $url = $A['query-url'] . $qstring . '&format=xspf';
}
else
{
    unset($qargs['rand']);
    $qargs['ids'] = $A['records']['0']['upload_id'];
    $url = $A['query-url'];
    foreach( $qargs as $k => $v )
        $url .= "$k=$v&";
    $url .= 'format=xspf';
}
$url = urlencode($url);
?>
<div style="float:left; margin-right:12px;">
    <object  type="application/x-shockwave-flash" 
       data="<?= $A['root-url']?>cchost_lib/xspf_player/musicplayer.swf?&playlist_url=<?= $url ?>&" width="17" height="17">
       <param name="movie" value="<?= $A['root-url']?>cchost_lib/xspf_player/musicplayer.swf?&playlist_url=<?= $url ?>&"></param>
    </object>
</div>
<span  class="cc_songinfo"><a  class="cc_file_link" href="<?= $A['records']['0']['file_page_url']?>" class="cc_songtitle"><?= $A['records']['0']['upload_name']?></a> by <a  href="<?= $A['records']['0']['artist_page_url']?>" class="cc_artistname cc_user_link"><?= $A['records']['0']['user_real_name']?></a></span>&nbsp;

</div>