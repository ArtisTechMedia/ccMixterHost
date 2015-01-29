<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_curve_home_init($T,&$targs) {
    
}
?><style > 
#inner_content {
    width: 80%;
    margin: 0px auto;
}

  h2 { margin-top: 25px; }
</style>
<h1  id="ccttite">Creative Commons &amp; ((c)urve)music tm </h1>
<div  style="margin-right: 5%">
<p >
<a  href="http://www.creativecommons.org">Creative Commons</a> and 
    <a  href="http://curvemusic.net">(&copy;urve)music&trade;</a> (the &trade; is for 'Talent Management') are pleased 
    to offer the audio source 
    files from several tracks from Zone's 'MADRUGADA' and Tamy's 'Sou Mais Bossa' albums online under a 
    Creative Commons <a  href="http://creativecommons.org/licenses/by-nc/3.0">Attribution-NonCommercial</a> license, 
    so that producers worldwide can use the sounds in remixes and new compositions. 
  </p>

<p >
	In order to use these files you need an 'unzip' utility (for Windows we suggest the fine and free <a  href="http://www.7-zip.org/">7-zip</a>)
  and the <a  href="http://flac.sourceforge.net/download.html">FLAC decoder</a>.
</p>
<br  style="clear:both" />
<hr  />
<h2 >Ready for Remixing</h2>
<p >
  You can download the a cappellas and pre-cut loops here. Right-click (Mac: control-click) on the buttons below
  and select 'Save Target As..'.
</p>
<?
$A['srcs'] = cc_query_fmt('tags=curve,contest_sample');

?><table  class="looptable">
<?

$carr101 = $A['srcs'];
$cc101= count( $carr101);
$ck101= array_keys( $carr101);
for( $ci101= 0; $ci101< $cc101; ++$ci101)
{ 
   $A['R'] = $carr101[ $ck101[ $ci101 ] ];
   
?><tr >
<td ><b  class="uname"><?= $A['R']['upload_name']?></b>
</td>
<td >[<a class="cc_file_link" href="<?= $A['R']['file_page_url']?>">details</a>]</td>
<td  style="padding-left:40px">
<table >
<?

$carr102 = $A['R']['files'];
$cc102= count( $carr102);
$ck102= array_keys( $carr102);
for( $ci102= 0; $ci102< $cc102; ++$ci102)
{ 
   $A['F'] = $carr102[ $ck102[ $ci102 ] ];
   
?><tr ><td ><a  id="cc_downloadbutton" href="<?= $A['F']['download_url']?>"><span ><?= $A['F']['file_nicname']?> <?= $A['F']['file_filesize']?></span></a></td></tr>
<?
} // END: for loop

?><tr ><td >
<?

if ( isset($A['R']['zipdirs']) ) {

?>
        ZIP Contents:
          
<?

$carr103 = $A['R']['zipdirs'];
$cc103= count( $carr103);
$ck103= array_keys( $carr103);
for( $ci103= 0; $ci103< $cc103; ++$ci103)
{ 
   $A['zip'] = $carr103[ $ck103[ $ci103 ] ];
   
?><div  class="cc_zipdir_head">ZIP: "<?= $A['zip']['name']?>"</div>
<ul  class="cc_zipdir">
<?

$carr104 = $A['zip']['dir']['files'];
$cc104= count( $carr104);
$ck104= array_keys( $carr104);
for( $ci104= 0; $ci104< $cc104; ++$ci104)
{ 
   $A['file'] = $carr104[ $ck104[ $ci104 ] ];
   
?><li ><?= $A['file']?></li><?
} // END: for loop

?></ul>
<?
} // END: for loop
} // END: if

?></td></tr>
</table>
<br  />
</td>
</tr>
<?
} // END: for loop

?></table>
<hr  />
<h2 >Raw Studio Tracks</h2>
<p >
	In addition to pre-cut loops you can cut your own samples from a selection out of the original studio tracks, including the 
  a cappellas.
</p>
<p >
		To download right click (control on Mac) on these buttons and select 'Save Target As...'
</p>
<div  class="dldiv"><a  id="cc_downloadbutton" href="http://ccmixter.org/media/curve/ccmixter.org_curvemusic_instr.zip"><span >guitar/percussion (29.4 MB) </span>
</a>
<p >(FLAC compressed WAV files in ZIP archive)</p></div>
<div  class="dldiv"><a  id="cc_downloadbutton" href="http://ccmixter.org/media/curve/ccmixter.org_curvemusic_vocals.zip"><span >vocals (41 MB) </span>
</a>
<p >(FLAC compressed WAV files in ZIP archive)</p></div>
  
<h2 >About Tamy</h2>
<div  style="margin: 1px;float:right;width:395px;"><img  src="<?= $T->URL('images/tamy.png'); ?>" /></div>
<p > Tamy's music is Bossa Nova with a 21st Century twist. Hailing from Vitoria, 
Brazil, the singer/songwriter mixes MPB (Musica Popular Brasileira) with electronic beats, croons 
to the swing of Rio's famous samba-funk and sings softly alongside Afro-Brazilian grooves. Add to the 
mix her melodic vocal arrangements and a danceable beat and you have something everyone can appreciate.</p>
<br  style="clear:right" />
<h2 >About Zone</h2>
<div  style="">
<div  style="margin-right: 5px;float:left">
<img  src="<?= $T->URL('images/zone.png'); ?>" />
</div>
<div  style="margin-left: 7px;float:right">
<img  src="<?= $T->URL('images/manola.png'); ?>" />
</div>
<p > 
    Enzo Torregrossa AKA ZONE, is among the very best modern jazz players in the world. And, lucky for us, the Italian bass 
    player, who has performed alongside masters Dizzie Gillespie and Kenneth Jackson, is back in action. 
  </p>
<p > 
On MADRUGADA, ZONE explores his newly found identity on technology mixing it with trademark live arrangements, cleverly mounding contemporary jazz into dance culture. Breaking new grounds in modern Latin music via songs like PENSO EM MIM (feat. singer/songwriter - MANOLA MICALIZZI), ZONE places himself high next to the peers that inspired him in the first place.</p>
<p  style="clear:both"> "Zone has meticulously created an intriguing fusion of jazz and dance music to yield a unique melody the jazz scene hasn’t yet tasted. The Italian bass player has made comrades of two very different genres of music, and collided what most would consider polar opposites to combine into musical eloquence. Zone will most notably be recognized for a risky marriage of two seemingly distant musical expressions and making one great sound." 
    <i >The Inside Connection Magazine - USA</i>
</p>
</div>
<br  style="clear:right" />
<h2 >From (&copy;urve)music&trade;</h2>
<table  style="vertical-align: center;">
<tr ><td >
<img  style="margin-right:11px;" src="<?= $T->URL('images/curve_logo.png'); ?>" alt="[ (&copy;urve)music&trade; ]" />
</td><td >
<p > "It is with great pleasure we announce this remix contest alongside ccMixter. We hope with your help to continue drawing the (&copy;urve) around new ideas for the delivery of digital content; contribute to the debate of sharing knowledge to enhance awareness of unknown artists, while also offering our token of appreciation and belief of the work carried out by the Creative Commons and similar organizations." </p>
<p  style="text-align: right; font-style: italic">Afonso Marcondes, (&copy;urve)music&trade;</p>
</td></tr>
</table>
<br  style="clear:left;margin-bottom:14px;" />
</div>
