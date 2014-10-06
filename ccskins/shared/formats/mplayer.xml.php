<? /*
[meta]
    type     = embed
    dataview = passthru
    desc     = _('Fabricio Zuardi's Music Player - Requires Flash')
[/meta]
*/ ?>
<div  id="cc_mplayer">
<?
$s_h   = empty($A['height']) ? 15 : $A['height'];
$h_ply = empty($A['player']) ? 'xspf_player_slim.swf' : $A['player']; 
$s_w   = 400;
$s_url = urlencode($A['query-url'] . $A['qstring'] . '&format=xspf');

?>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" 
        codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" 
        id="xspf_player" align="middle" height="<?= $s_h?>" width="<?= $s_w?>" player_title="ccHost Player">
    <param  name="movie" value="<?= $A['root-url']?>cchost_lib/xspf_player/<?= $h_ply?>?playlist_url=<?= $s_url?>&1=1"></param>
    <param  name="quality" value="high"></param>
    <param  name="bgcolor" value="#e6e6e6"></param>
    <embed  src="<?= $A['root-url']?>cchost_lib/xspf_player/<?= $h_ply?>?playlist_url=<?= $s_url?>&1=1" 
            quality="high" bgcolor="#e6e6e6" name="xspf_player" player_title="ccHost Player" type="application/x-shockwave-flash" 
            pluginspage="http://www.macromedia.com/go/getflashplayer" align="center" height="<?= $s_h?>" width="<?= $s_w?>"></embed>
</object>
</div>