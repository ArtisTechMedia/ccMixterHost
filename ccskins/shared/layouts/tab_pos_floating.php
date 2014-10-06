<?
/*
[meta]
    type = tab_pos
    image = layouts/images/tab_layout002.gif
    desc = _('Floating tabs (sub tabs in client)')
[/meta]
*/

$A["tab_pos"] = array(
    "subclient" => true,
    "floating" => true);
?>
<style type="text/css">
#sub_tabs li {
	display: block;
	float: left;
	margin-right: 9px;
}
</style>