<!-- template playlist_favs.php -->
<?
function _t_playlist_favs_link(&$T, &$A)
{
    if( empty($A['upload_id']) )
        die("no \$A['upload_id'] found");
    ?>
    <div class="fav_playlist_block" id="fav_playlist_<?=$A['upload_id'];?>"></div>
   <?
}

function _t_playlist_favs_hook(&$T, &$A)
{
?>
<script type="text/javascript" src="<?= $T->URL('js/playlist_favs.js') ?>"></script>

<script>
new favsPlaylistHook();
</script>
<?    
    
}

?>
