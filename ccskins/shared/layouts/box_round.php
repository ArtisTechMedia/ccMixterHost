<?
/*
[meta]
    type  = box_shape
    image = layouts/images/rbox_layout002.gif
    desc  = _('Rounded Boxes [graphic]')
[/meta]    
*/


$A['end_script_text'][] = 'cc_round_boxes();';

?>

<script type="text/javascript" src="<?= $T->URL('js/round-box.js'); ?>"></script>
<link rel="stylesheet" type="text/css" href="<?= $T->URL('layouts/box_round.css'); ?>" title="Default Style" />
<!--[if IE]> 
<link rel="stylesheet" type="text/css" href="<?= $T->URL('layouts/box_round_ie.css'); ?>" title="Default Style" />
<![endif]-->

<style type="text/css">
.cssbox, .cssbox_body, .cssbox_head, .cssbox_head h2
{ background-image: url('<?= $T->URL('images/backbox-mono.gif') ?>') } 
</style>

<!--[if lt IE 7.]> 
<style type="text/css">
.cssbox, .cssbox_body, .cssbox_head, .cssbox_head h2
{ background: transparent; padding: 0px;) } 
.cssbox {
    border: 1px solid #DDD;
    background: #EEE;
    margin: 6px;
    padding: 4px;
}
h2.no_box_header {
    display: none;
}
</style>
<![endif]-->


<script type="text/javascript">
var round_box_enabled = 1;
</script>

