<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

?><div><?


//------------------------------------- 
function _t_magnatune_preview_album_preview($T,&$A) {
  
?><p  class="lp_results_head">
  To listen to a track from "<?= $A['mt']['album_preview']['album']?>" by <b><?= $A['mt']['album_preview']['artist']?></b> click on a <img  src="/cctemplates/ccmixter/hear-button-fg.gif" /> button. To download a track right-click on a 
  <img  src="/cctemplates/ccmixter/down-button-fg.gif" /> link below and select 'Save Target As...'
 </p>
<table  cellspacing="0" cellpadding="0" style="white-space:nowrap">
<?

$carr101 = $A['mt']['album_preview']['songs'];
$cc101= count( $carr101);
$ck101= array_keys( $carr101);
for( $ci101= 0; $ci101< $cc101; ++$ci101)
{ 
   $A['cut'] = $carr101[ $ck101[ $ci101 ] ];
   ?><tr>
    <td  class="sbutton"><a  href="<?= $A['mt']['song_stream_url']?>/<?= $A['cut']['songid']?>.m3u" class="hear_button">&nbsp;</a></td>
    <td  class="sbutton"><a  href="<?= $A['cut']['download_mp3']?>" class="down_button">&nbsp;</a></td>
    <td>"<?= $A['cut']['trackname']?>"</td>
    </tr><?
} // END: for loop

?></table>
<?
} // END: function album_preview


//------------------------------------- 
function _t_magnatune_preview_loop_preview($T,&$A) {
  
?><p  class="lp_results_head">Download the entire 
    <a  href="http://magnatune.com/extra/remix/loops/<?= $A['mt']['loop_preview']['loop_artist']?>.zip"><?= $A['mt']['loop_preview']['loop_artist_name']?> Loop Set</a>
    in ZIP archive format.
  </p>
<table  cellspacing="0" cellpadding="0" style="white-space:nowrap">
<?

$carr102 = $A['mt']['loop_preview']['loops'];
$cc102= count( $carr102);
$ck102= array_keys( $carr102);
for( $ci102= 0; $ci102< $cc102; ++$ci102)
{ 
   $A['loop'] = $carr102[ $ck102[ $ci102 ] ];
   ?><tr>
    <td  class="sbutton"><a  class="hear_button" href="<?= $A['mt']['loop_stream_url']?>/<?= $A['loop']['loop_id']?>.m3u" 
    id="mt_streamfile">&nbsp;</a></td>
    <td>"<?= $A['loop']['loop_filename']?>"</td>
    </tr><?
} // END: for loop

?></table>
<?
} // END: function loop_preview

?></div>