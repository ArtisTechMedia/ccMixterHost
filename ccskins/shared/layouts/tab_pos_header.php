<?
/*
[meta]
    type = tab_pos
    image = layouts/images/tab_layout001.gif
    desc = _('Tabs in the header')
[/meta]
*/

$A["tab_pos"] = array(
    "in_header" => true,
    "subclient" => true );
?>
<style type="text/css">
#tabs li {
  float: left;
  display: block;
  padding-right: 13px;
  }
#sub_tabs li {
	display: block;
	float: left;
	margin-right: 9px;
}
</style>