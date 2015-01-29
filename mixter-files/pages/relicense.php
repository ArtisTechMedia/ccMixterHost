<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_relicense_init($T,&$targs) {
    
}
?><div >

<style >
#relicenseform 
{
  width: 70%;
}
#relicenseform td,#opener
{
  font-size: 12px;
}
.rname
{
  font-weight: bold;
  padding-left: 5px;
  border-left: 1px solid #665;
  white-space: nowrap;
}
#orgname
{
  color: green;
}
#clearedname
{
  color: rgb(0,155,0);
}
#stuckname
{
  color: rgb(180,180,0);
}
#frozenname
{
  color: red;
}
.cradio
{
  margin: 4px 20px 4px 8px;
  white-space: nowrap;
}
.relic_explain
{
  padding: 10px;
  font-size: 13px;
  border-top: 1px solid #665;
  border-right: 1px solid #665;
  border-left: 1px solid #665;
  margin: 10px 0px 0px 0px;
}
.lastrow
{
  border-bottom: 1px solid #665;
  border-right: 1px solid #665;
  border-left: 1px solid #665;
}
.closeb, .pick
{
  text-align: center;
}
.closeb
{
  border-right: 1px solid #665;
  padding-right: 5px;
}
.openb
{
  border-left: 1px solid #665;
}
#thattr
{
  padding-right: 6px;
  padding-left: 6px;
}
#whatsbetter li
{
  margin-bottom: 8px;
}
#submitdiv
{
  text-align:center;
  margin: 4px;
}
#stuck_lic,#frozen_lic {
  white-space: nowrap;
  padding-bottom: 3px;
  font-size: 9px;
}
</style>
<?

if ( !empty($A['relicense_info']['relicense_form'])) {

?><form  action="${relicense_info/relicensed_url" method="post">
<h1 >Relicensing</h1>
<div  id="opener">
<h2 >Welcome to the new and improved ccMixter.</h2>
<p >Before you jump into the new site we need to tell you that we are now using the Creative Commons <a  href="">Attribution</a> and <a  href="">Attribution NonCommercial</a> style licenses
as our defaults for uploads and we urge you to consider re-licensing your work with these licenses.</p>
<p ><b >Why relicense your works?</b></p>
<p >The licenses we were using previously (the Sampling Plus and NonCommerical Sampling Plus variety) are very good
for keeping a tight reign on your material. This might be an approriate choice for well established artists who wish
to share music in the Commons, however most ccMixter users found the restrictiions <i >too</i> tight since it limited
the amount of exposure their music can get.</p>
<p ><b >What do the new licenses offer the previous one did not?</b></p>
<ul  id="whatsbetter">
<li >With the <b >Attribution</b> license film makers, both commercial and non-commercial can use your remix "as is"
without modification. In addition the material can be used in advertising which is typically great exposure for the
music. <i >Most importantly</i>: the <b >Attribution</b> license can "mix" with other Creative Commons licenses 
such a those used by most CC-friendly labels and other web site while the Sampling licensed music can not.</li>
<li >The <b >Attribution NonCommercial</b> license has all of the same features as the Attribution license with the additional
restriction that your music can not be used in a commercial setting. That means that non commercial video bloggers
and flash animators can use your music as long as they give you credit.</li>
</ul>
<p >The screen below was designed to guide you through the process of re-licensing as quickly and painlessly as possible
so you can go on to use the other new features of the site.</p>
<?

if ( 
if( !empty($A['relicense_info']['originals']) ) { echo  $A['relicense_info']['originals']; } else { 
if( !empty($A['relicense_info']['cleared']) ) { echo  $A['relicense_info']['cleared']; } else { } } ) {

?><p ><b >Consider:</b> If you relicense your music to a less restrictive license (like Attribution and Attribution NonCommercial) then 
ccMixter policy is that you can not go back 
to the more restrictive one (like Sampling Plus and NonCommercial Sampling Plus).</p>
<p ><b >Consider:</b> Remixers thinking about sampling your music will probably appreciate the less restrictive license.</p>
<p ><b >Consider:</b> Relicensing now allows people that have already sampled your work to relicense. Otherwise
they are 'stuck' with the old, more restrictive version.</p>
<p >You can always relicense your work later from each upload's page.</p>
<?
} // END: if

?></div>
</form><?
} // END: if

if ( !empty($A['relicense_info']['relicense_form'])) {

?><form  action="${relicense_info/relicensed_url" method="post" id="relicenseform">
<table  cellspacing="0" cellpadding="0">
<?

if ( !empty($A['relicense_info']['originals'])) {

?><tr ><td  colspan="5"><div  class="relic_explain" id="org_explain">These are originals works, you can relicense these here.</div></td></tr>
<tr ><td  class="openb" colspan="3">&nbsp;</td>
<td  class="closeb" colspan="2">Relicense as:</td>
</tr>
<tr ><th  class="openb">Track name</th>
<th >Current license</th>
<th >Keep</th>
<th  id="thattr">Attri-<br  />bution</th>
<th  class="closeb">Attr.Non-<br  />Commercial</th>
</tr>
<?

$carr101 = $A['relicense_info']['originals'];
$cc101= count( $carr101);
$ck101= array_keys( $carr101);
for( $ci101= 0; $ci101< $cc101; ++$ci101)
{ 
   $A['org'] = $carr101[ $ck101[ $ci101 ] ];
   
?><tr >
<td  class="rname" id="orgname"><?= $A['org']['upload_name']?></td>
<td  id="currlic"><?= $A['org']['license_name']?></td>
<td ><input  type="radio" value="keep" name="relicid[<?= $A['org']['upload_id']?>]"></input></td>
<td  class="pick"><input  type="radio" value="attribution" name="relicid[<?= $A['org']['upload_id']?>]" checked="checked"></input></td>
<td  class="closeb"><input  type="radio" value="noncommercial" name="relicid[<?= $A['org']['upload_id']?>]"></input></td>
</tr><?
} // END: for loop

?><tr ><td  class="lastrow" colspan="5">&nbsp;</td></tr>
<?
} // END: if

if ( !empty($A['relicense_info']['cleared'])) {

?><tr ><td  colspan="5"><div  class="relic_explain" id="cleared_explain">These are remixes where the artists you 
                     samples have already relicensed their works, you can relicense these here.</div></td>
</tr>
<tr ><td  class="openb" colspan="3">&nbsp;</td>
<td  class="closeb" colspan="2">Relicense as:</td>
</tr>
<tr ><th  class="openb">Track name</th>
<th >Current license</th>
<th >Keep</th>
<th  id="thattr">Attri-<br  />bution</th>
<th  class="closeb">Attr.Non-<br  />Commercial</th>
</tr>
<?

$carr102 = $A['relicense_info']['cleared'];
$cc102= count( $carr102);
$ck102= array_keys( $carr102);
for( $ci102= 0; $ci102< $cc102; ++$ci102)
{ 
   $A['cleared'] = $carr102[ $ck102[ $ci102 ] ];
   
?><tr >
<td  class="rname" id="clearedname"><?= $A['cleared']['upload_name']?></td>
<td  id="currlic"><?= $A['cleared']['license_name']?></td>
<td ><input  type="radio" value="keep" name="relicid[<?= $A['cleared']['upload_id']?>]"></input></td>
<td  class="pick">
<?

if ( !empty($A['cleared']['permit_by'])) {

?><input  type="radio" value="attribution" checked="checked" name="relicid[<?= $A['cleared']['upload_id']?>]"></input>
<?
} // END: if

if ( !($A['cleared']['permit_by']) ) {

?>n/a<?
} // END: if

?></td>
<td  class="closeb"><input  type="radio" value="noncommercial" name="relicid[<?= $A['cleared']['upload_id']?>]"></input></td>
</tr><?
} // END: for loop

?><tr ><td  class="lastrow" colspan="5">&nbsp;</td></tr>
<?
} // END: if

if ( !empty($A['relicense_info']['stuck'])) {

?><tr ><td  colspan="5"><div  class="relic_explain" id="cleared_explain">These are remixes where the artists you 
    sampled have not relicensed their works yet. <br  /><br  />
<b >HINT: Bookmark this page and check it occasionally to see
    if their status changes.</b>
<br  />
</div></td></tr>
<?

$carr103 = $A['relicense_info']['stuck'];
$cc103= count( $carr103);
$ck103= array_keys( $carr103);
for( $ci103= 0; $ci103< $cc103; ++$ci103)
{ 
   $A['stuck'] = $carr103[ $ck103[ $ci103 ] ];
   
?><tr >
<td  class="rname" id="stuckname"><?= $A['stuck']['upload_name']?></td>
<td  id="stuck_lic"><?= $A['stuck']['license_name']?></td>
<td ></td><td ></td>
<td  class="closeb">&nbsp;</td>
</tr><?
} // END: for loop

?><tr ><td  class="lastrow" colspan="5">&nbsp;</td></tr>
<?
} // END: if

if ( !empty($A['relicense_info']['frozen'])) {

?><tr ><td  colspan="5"><div  class="relic_explain" id="frozen_explain">These are remixes where the artists you 
    samples will not relicense their works, that means you do <b >not</b> have the option to relicense these. </div></td></tr>
<?

$carr104 = $A['relicense_info']['frozen'];
$cc104= count( $carr104);
$ck104= array_keys( $carr104);
for( $ci104= 0; $ci104< $cc104; ++$ci104)
{ 
   $A['froz'] = $carr104[ $ck104[ $ci104 ] ];
   
?><tr >
<td  class="rname" id="frozenname"><?= $A['froz']['upload_name']?></td>
<td  id="frozen_lic"><?= $A['froz']['license_name']?></td>
<td ></td><td ></td>
<td  class="closeb">&nbsp;</td>
</tr><?
} // END: for loop

?><tr ><td  class="lastrow" colspan="5">&nbsp;</td></tr>
<?
} // END: if

?></table>
<div  id="submitdiv"><input  type="submit" name="relicensenow" value="Submit my relicense choices now"></input></div>
<div  id="submitdiv"><input  type="submit" name="relicenselater" value="I'll relicense 'manually' later (or not at all)"></input></div>
</form><?
} // END: if

?></div>