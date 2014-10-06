<? if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template charts -->
<div >

<style >
table.statstable {
  border-width: 3px;
  border-style: solid;
  margin: 5px;
  float:left;
}

.shead {
  padding: 2px 1px 2px 1px;
  }
</style>
<table >
<tr >
<td >
<ul >
<li ><a  href="<?= $A['root-url']?>/media/charts">Upload ranks (all-time)</a></li>
<li ><a  href="<?= $A['root-url']?>/media/charts/users">User ranks (all-time)</a></li>
<li ><a  href="<?= $A['root-url']?>/media/charts/users/date">Users latest</a></li>
<li ><a  href="<?= $A['root-url']?>/media/charts/upload/name">Uploads (alphabetical)</a></li>
<li ><a  href="<?= $A['root-url']?>/media/charts/users/name">Users (alphabetical)</a></li>
</ul>
</td>
<td >
<?if ( !empty($A['user_recs'])) {?><table  class="statstable light_bg dark_border">
<tr >
<th >Artist</th>
<th >Ratings</th>
<th >remixes</th>
<th >remixed</th>
<th >uploads</th>
</tr>
<?$carr101 = $A['user_recs'];$cc101= count( $carr101);$ck101= array_keys( $carr101);for( $ci101= 0; $ci101< $cc101; ++$ci101){    $A['R'] = $carr101[ $ck101[ $ci101 ] ];   ?><tr >
<td ><a  class="cc_user_link" href="<?= $A['R']['artist_page_url']?>"><?= $A['R']['user_real_name']?></a></td>
<td ><?$T->Call('charts.xml/ratings_dots');
?></td>
<td ><?= $A['R']['user_num_remixes']?></td>
<td ><?= $A['R']['user_num_remixed']?></td>
<td ><?= $A['R']['user_num_uploads']?></td>
</tr><?}?></table><?}if ( !empty($A['upload_recs'])) {?><table  class="statstable light_bg dark_border">
<tr >
<th >name</th>
<th >artist</th>
<th >ratings</th>
<th >remixes</th>
<th >sources</th>
</tr>
<?$carr102 = $A['upload_recs'];$cc102= count( $carr102);$ck102= array_keys( $carr102);for( $ci102= 0; $ci102< $cc102; ++$ci102){    $A['R'] = $carr102[ $ck102[ $ci102 ] ];   ?><tr >
<td ><a  class="cc_user_link" href="<?= $A['R']['artist_page_url']?>"><?= $A['R']['user_real_name']?></a></td>
<td ><a  class="cc_file_link" href="<?= $A['R']['file_page_url']?>"><?= $A['R']['upload_name']?></a></td>
<td  style="white-space: nowrap"><?$T->Call('charts.xml/ratings_dots');
?></td>
<td ><?= $A['R']['upload_num_remixes']?></td>
<td ><?= $A['R']['upload_num_sources']?></td>
</tr><?}?></table><?}?></td>
</tr>
</table>
<?
function _t_charts_ratings_dots(&$T,&$A) {
  if ( !empty($A['R']['ratings_score'])) {$carr103 = $A['R']['ratings'];$cc103= count( $carr103);$ck103= array_keys( $carr103);for( $ci103= 0; $ci103< $cc103; ++$ci103){    $A['i'] = $carr103[ $ck103[ $ci103 ] ];   ?>
  <img  src="<?= $T->URL('/images/stars/dot-' . $A['i'] . '.gif') ?>" height="10" width="10" />
  <?}}if ( !empty($A['R']['ratings_score'])) {?><span >(<?= $A['R']['ratings_score']?>)</span><?}}?></div>