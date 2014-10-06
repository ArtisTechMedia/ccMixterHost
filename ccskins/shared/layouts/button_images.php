<?
/*
[meta]
    type = button_style
    desc = _('Images')
[/meta]
*/
?>
<style type="text/css">
a.cc_gen_button {
    display: block;
    background: url('<?= $T->URL('images/button_left.gif'); ?>') no-repeat left top;
    padding: 0 0 0 5px;
    text-decoration: none;
    color: #666;
    white-space: nowrap;
    font-family: Verdana;
    font-size: 9px;
    font-weight: normal;
    cursor: pointer;
}

a.cc_gen_button span {
    display: block;
    text-align: center;
    padding: 5px 7px 6px 2px;
    background: url('<?= $T->URL('images/button_right.gif'); ?>') no-repeat right top;
}

a.cc_gen_button:hover {
    background-position: 0% -23px;
    color:black;
	text-decoration: none;
}

a.cc_gen_button:hover span {
    background-position: 100% -23px;
}

td a.small_button { 
   margin-bottom: 8px;
}

a.small_button, #do_remix_search {
	padding: 0px 0px 0px 2px;
	border: 1px solid #888;
	font-weight: normal;
    background: url('<?= $T->URL('images/button_small.gif'); ?>') no-repeat left top; 
	font-size: 9px;
}

a.small_button:hover, #do_remix_search:hover {
	text-decoration: none;
    background: url('<?= $T->URL('images/button_small_hover.gif'); ?>') no-repeat right top;
}

a.small_button span, #do_remix_search span {
	padding: 0px 2px 4px 0px;
}

</style>

