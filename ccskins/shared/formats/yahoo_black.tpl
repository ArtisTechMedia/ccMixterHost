<?PHP 
/*%%
[old_meta]
    type     = format
    desc     = _('Y!(tm) Embed Player (sleek black)')
    dataview = passthru
[/old_meta]
%%
*/
/*
    $autoplay = empty($_GET['autoplay']) ? '0' : '1';
    $bgcolor = empty($_GET['bgcolor']) ? 'e6e6e6' : $_GET['bgcolor'];
    $height = empty($_GET['height']) ? '40' : $_GET['height'];
    $url = $A['query-url'] . urlencode($A['qstring'] . '&f=xspf'); 


 <embed src="http://webjay.org/flash/dark_player"
   flashVars='playlist_url=%(#url)%&rounded_corner=1&skin_color_1=0,-100,-29,18&skin_color_2=0,-100,-27,20'
    width="300"
    height="%(#height)%"
    name="xspf_player"
    id="xspf_player"
    wmode='transparent' 
    pluginspage='http://www.adobe.com/go/getflashplayer'
    type="application/x-shockwave-flash"
    />

*/
/*
[meta]
    type     = embed
    dataview = passthru
    desc     = _('Fabricio Zuardi's Player - (Yahoo Repl.)')
[/meta]
*/

?>

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