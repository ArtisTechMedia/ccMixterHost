<? if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

$optset = cc_query_get_optset( empty($_GET['optset']) ? 'default' : $_GET['optset'] );
?>
<!-- template query_browser -->
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/rate.css'); ?>" title="Default Style"></link>
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/form.css'); ?>" title="Default Style"></link>
<link rel="stylesheet" type="text/css" href="<?= $T->URL($optset['css']); ?>" title="Default Style"></link>

<div id="browser_client">
    <div id="filter_controls" >
            <div id="browser_filter">
            </div>
    </div>
    <div id="browser_area">
        <div id="browser">
            <?= $T->String('str_getting_data') ;?>
        </div>

        <table id="cc_prev_next_links" style="clear:left">
        <tr>
            <td class="cc_list_list_space">&nbsp;</td>
            <td><a id="browser_prev" class="cc_gen_button  browse_prevnext" style="display:none" href="javascript://browser_prev">
                <span >&lt;&lt;&lt; <?= $T->String('str_prev') ?></span></a>
            </td>
            <td><a id="browser_next" class="cc_gen_button  browse_prevnext" style="display:none" href="javascript://browser_next">
                <span><?= $T->String('str_more') ?> &gt;&gt;&gt;</span></a> </td>
        <? if( !empty($A['is_logged_in']) && !empty($A['enable_playlists']) && !empty($optset['playlist_button']) ) { ?>
            <td> <a id="mi_save_to_playlist" class="cc_gen_button" style="display:none" href="javascript://save to playlist">
                <span><?= $T->String('str_save_to_playlist') ?></span></a></td>
        <? } ?>

        </tr>
        </table>
    </div>
</div>
<script  src="<?= $T->URL('js/query_browser.js')?>" ></script>
<script  src="<?= $T->URL('js/query_filter.js')?>" ></script>
<script  src="<?= $T->URL('js/autocomp.js')?>" ></script>
<script  src="<?= $T->URL('js/autopick.js')?>" ></script>
<script  src="<?= $T->URL('/js/info.js') ?>"></script>
<script  src="<?= $T->URL('js/playlist.js'); ?>"></script>
<?$T->Call('flash_player'); ?>
<script type="text/javascript">
<?
    list( $args, $json_args ) = cc_query_default_args( array( 'limit' => $optset['limit'], 'reqtags' => $optset['reqtags'] ) );
    $A['qstring'] = http_build_query($args);
?>
var filters = new ccQueryBrowserFilters( 
                    { filter_form: 'browser_filter',
                      submit_text: '<?= $T->String('str_see_results') ?>',
                      init_values: <?= $json_args ?>,
                      optset: <?= cc_php_to_json($optset); ?>
                    }); 

new ccQueryBrowser( { filters: filters } );
CC$$('.th',$('browser_filter')).each( function(e) {
    Element.addClassName(e,'med_dark_bg light_color');
});
</script>
