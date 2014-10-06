<!-- template license -->
<?

function _t_license_license_enable(&$T,&$A) {
  ?><input  type="checkbox" name="<?= $A['field']['license']['license_id']?>" id="<?= $A['field']['license']['license_id']?>" checked="<?= empty($A['field']['value']) ? null : $A['field']['value']; ?>"></input>
<label  for="<?= $A['field']['license']['license_id']?>"><?= $A['field']['license']['license_text']?></label>
<?if ( !empty($A['field']['license']['license_url'])) {?><div  class="cc_file_license"><a  href="<?= $A['field']['license']['license_url']?>" target="_new">more info...</a></div><?}?><img  class="cc_license_image" src="<?= $T->URL('images/lics/' . $A['field']['license']['license_logo']) ?>" />
<br  />
<?}

function _t_license_license_choice(&$T,&$A) 
{
  ?><table >
<?$carr101 = $A['field']['license_choice'];$cc101= count( $carr101);$ck101= array_keys( $carr101);for( $ci101= 0; $ci101< $cc101; ++$ci101){    $A['license'] = $carr101[ $ck101[ $ci101 ] ];   ?><tr ><td ><img  class="cc_license_image" src="<?= $T->URL('images/lics/' .$A['license']['license_logo']) ?>" /></td>
<td ><input  type="radio" checked="<?= $A['license']['license_checked'] ?>" name="upload_license" value="<?= $A['license']['license_id']?>" id="<?= $A['license']['license_id']?>"></input>
<label  for="<?= $A['license']['license_id']?>"><?= $A['license']['license_text']?></label>
</td></tr>
<?}?></table>
<?}

?>