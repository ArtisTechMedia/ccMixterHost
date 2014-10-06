<?
/*
[meta]
    type = embedded_player
    desc = _('StreamPad[tm]')
[/meta]
*/

$A['flash_player'] = 'util.php/empty';

?>
<!-- player_streampad.php -->

<style text="type/css" >
.playerdiv {
     display: none;
}
</style>

<script type="text/javascript">
var headID = document.getElementsByTagName("head")[0];         
var newScript = document.createElement('script');
newScript.type = 'text/javascript';
newScript.src = 'http://o.aolcdn.com/art/merge?f=/_media/sp/sp-player.js&f=/_media/sp/sp-player-other.js&expsec=86400&ver=9';
headID.appendChild(newScript);
</script>
