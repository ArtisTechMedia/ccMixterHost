<?
/*
[meta]
    type = button_style
    desc = _('Rounded rectangle')
[/meta]
*/
?>

<? $T->PrintOnce('js/DD_roundies_0.0.2a.js'); ?>

<script type="text/javascript" >
 DD_roundies.addRule('a.cc_gen_button', '4px',true);
 DD_roundies.addRule('a.small_button', '4px',true);
</script>

<style type="text/css">

a.small_button,
#do_remix_search,
a.cc_gen_button {
    color: #443;
    background: white url('<?= $T->URL('images/native-box-bg.png') ?>') repeat-x top left;
}

a.cc_gen_button {
    display: block;
    padding: 0 0 0 5px;
    text-decoration: none;
    white-space: nowrap;
    font-family: Verdana;
    font-size: 9px;
    font-weight: normal;
    cursor: pointer;
    border: 1px solid #555;
}

a.cc_gen_button span {
    display: block;
    text-align: center;
    padding: 2px 7px 3px 2px;
}

a.cc_gen_button:hover,
a.small_button:hover,
#do_remix_search:hover  {
    color: black;
    background: white;
    text-decoration: none;
    border: 1px solid black;
}

a.cc_gen_button:hover span {
}

td a.small_button { 
   margin-bottom: 8px;
}

a.small_button, #do_remix_search {
	padding: 3px; /* 0px 0px 0px 2px; */
	border: 1px solid #888;
	font-weight: normal;
	font-size: 9px;
}

a.small_button span, #do_remix_search span {
	padding: 0px 2px 4px 0px;
}

</style>

