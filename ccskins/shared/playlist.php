<?if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template playlist -->
<?

function _t_playlist_playlist_create_dyn(&$T,&$A) {
  ?><link  rel="stylesheet" type="text/css" href="<?= $T->URL('css/playlist.css') ?>" title="Default Style"></link>
  <div id="dyn_filter_editor_parent">
<div  id="dyn_filter_editor" class="box" >
<div  id="filter_form" >
</div>
</div>
</div>
<script  src="<?= $T->URL('js/query_filter.js')?>" ></script>
<script  src="<?= $T->URL('js/autocomp.js')?>" ></script>
<script  src="<?= $T->URL('js/autopick.js')?>" ></script>
<script type="text/javascript">
    
<? 
    $optset = cc_query_get_optset('default', 'json' );
?>
var filters = new ccQueryBrowserFilters( 
    { submit_text: '<?= $T->String($A['plargs']['submit_text']) ?>',
      init_values: <?= $A['plargs']['edit_query']?>,
      query_url: '<?= $A['plargs']['submit_url']?>' + q,
      format: 'page',
      optset: <?= $optset ?>,
      onFilterSubmit: function(event) {
          var qstring = window.filters.queryURL(true);
          var promo_tag = '<?= $A['plargs']['promo_tag']?>';
          if( promo_tag.length > 0 )
            qstring += '&promo_tag=' + promo_tag;
          window.location.href = qstring;
          return false;
        }
     } );

  </script>
<?}

function _t_playlist_playlist_popup(&$T,&$A) 
{

   $edit_url = ccl('playlist','edit') . '/';
   $rem_url  = ccl('api','playlist','remove') . '/';
   $rem_from = $T->String('str_pl_remove_from');
   $edit     = $T->String('str_pl_edit');
   $add_url  = ccl('api','playlist','add') . '/';
   $add      = $T->String('str_pl_add_to');
   $new_url  = ccl('api','playlist','new') . '/';
   $add_new      = $T->String('str_pl_add_to_new');

    foreach( $A['args']['with'] as $R )
    {  
       ?><span class="cc_playlist_menu_item"><a class="pl_menu_item" href="<?= $rem_url . $A['args']['upload_id'] . '/' . $R['cart_id']?>"><span><?= $rem_from ?> <span class="cc_playlist_name"><?= $R['cart_name']?></span></span></a> (<a class="cc_playlist_edit" href="<?= $edit_url . $R['cart_id'] ?>"><?= $edit ?></a>)</span><?
    }

    foreach( $A['args']['without'] as $R )
    { 
     ?><span class="cc_playlist_menu_item"><a  class="pl_menu_item" href="<?= $add_url . $A['args']['upload_id'] . '/' . $R['cart_id']?>"><span><?= $add ?> <span class="cc_playlist_name"><?= $R['cart_name']?></span></span></a> (<a  class="cc_playlist_edit" href="<?= $edit_url . $R['cart_id'] ?>"><?= $edit ?></a>)</span><?
    }
 
    ?><span class="cc_playlist_menu_item"><a href="<?= $new_url . $A['args']['upload_id']?>" class="cc_playlist_add_mi pl_menu_item"><span><?=$add_new?></span></a></span><?
}



function _t_playlist_playlist_menu(&$T,&$A) 
{
?>
<link  rel="stylesheet" type="text/css" href="<?= $T->URL('css/playlist.css') ?>" title="Default Style"></link>
<script  src="<?= $T->URL('/js/info.js') ?>"></script>
<script  src="<?= $T->URL('js/playlist.js') ?>" ></script>
<script type="text/javascript">
function playlist_hook_menu() {
    new ccPlaylistMenu();
}
</script>
<?
}
?>
