<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template recommends -->
<h1><?= $T->String('str_recommends_browser') ?></h1>
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/playlist.css'); ?>" title="Default Style"></link>
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/info.css'); ?>" title="Default Style"></link>
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/recommends.css'); ?>" title="Default Style"></link>
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/rate.css'); ?>" title="Default Style"></link>

<div id="browser_client">
    <div id="featured" class="dark_border med_bg light_color">
        <h3 ><?= sprintf($T->String('str_recommended_by_s'),$A['get']['fullname']);?></h3>
        <div class="featured_info dark_color dark_border light_bg"><?= sprintf($T->String('str_recommends_s_2') ,$A['get']['fullname']);?></div>
    </div>
    <div id="browser_head">
        <div id="limit_picker_container">
            <?= $T->String('str_filter_limit') ;?>: <select  id="limit_picker"></select>
        </div>
    </div>
    <div id="browser">
        <?= $T->String('str_getting_data') ;?>
    </div>
    <div id="q"></div>
    <table id="cc_prev_next_links"><tbody ><tr >
        <td class="cc_list_list_space">&nbsp;</td>
        <td><a id="browser_prev" class="cc_gen_button  browse_prevnext" style="display:none" href="javascript://browser_prev">
            <span >&lt;&lt;&lt; <?= $T->String('str_prev') ?></span></a>
        </td>
        <td><a id="browser_next" class="cc_gen_button  browse_prevnext" style="display:none" href="javascript://browser_next">
            <span><?= $T->String('str_more') ?> &gt;&gt;&gt;</span></a></td>
    </tr></tbody></table>
    <div style="clear:both">&nbsp;</div>
</div><!-- browser client -->
<script  src="<?= $T->URL('/js/info.js') ?>"></script>
<script  src="<?= $T->URL('js/playlist.js'); ?>"></script>
<? $T->Call('flash_player'); ?>
<script type="text/javascript">
var ruser = '<?= $A['get']['ruser']?>';
var fullname = '<?= $A['get']['fullname']?>';
</script>
<script  src="<?= $T->URL('js/recommends.js'); ?>" /></script>
<script  src="<?= $T->URL('js/query_browser.js'); ?>" /></script>
<script type="text/javascript">
new ccQueryBrowser( { filters: new ccReccommendFilter() } );
</script>
<? $A['qstring'] = 'reccby=' . $A['get']['ruser']; 
cc_content_feed($A['qstring'],array('str_recommended_by_s',$A['get']['ruser']));
?>
