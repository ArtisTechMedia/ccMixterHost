<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

/*
[meta]
    type = list
    desc = _('Playlist style')
    dataview = playlist_line
[/meta]
*/?>
<!-- template playlist_show_one -->
<style>
#inner_content {
    width: 85%;
    margin: 0px auto;
}
</style>
<link  rel="stylesheet" type="text/css" href="<?= $T->URL('css/playlist.css') ?>" title="Default Style"></link>
<link  rel="stylesheet" type="text/css" href="<?= $T->URL('css/info.css') ?>"  title="Default Style"></link>
<script  src="<?= $T->URL('/js/info.js') ?>"></script>
<? $A['player_options'] = 'autoHook: false';?>
<script  src="<?= $T->URL('js/playlist.js') ?>" ></script>
<?
    $T->Call('playlist_list_lines');
    $T->Call('flash_player');
?>
<script type="text/javascript">
    new ccPlaylistMenu();
    <? if( !empty($A['args']['playlist']['cart_id']) ) { ?>
    new ccPagePlayer(<?= $A['args']['playlist']['cart_id']?>);
    <? } ?>
</script>

<? $T->Call('prev_next_links'); ?>
