<?if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

?>
<!-- template playlist_2_menu -->

<?
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

?>
<span class="cc_playlist_menu_item"><a href="<?= $new_url . $A['args']['upload_id']?>" class="cc_playlist_add_mi pl_menu_item"><span><?=$add_new?></span></a></span>

