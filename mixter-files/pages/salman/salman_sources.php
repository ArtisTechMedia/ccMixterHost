<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_salman_sources_init($T,&$targs) {
    
}
?>﻿
<style >
    h2 a {
      margin-top: 25px;
	  font-size: 22px;
	  border-top: 1px solid #999;
	  padding: 4px 3px 4px 20px;
	  background-color: #DDF;
    }
    
	h3 span {
	  font-size: 11px;
	  font-weight: normal;
	}
	.caveats li {
		margin-bottom: 16px;
	}
	.intro {
		padding: 18px;
	}
	.dldiv {
        margin-bottom: 25px;
    }
	.dldiv p {
	  margin: 8px;
	  font-style: italic;
	  padding-top: 12px;
	}
	.dldiv #cc_downloadbutton {
		 display: block;
		 margin: 5px;
		 width: 220px;
	}
  table.looptable td {
    vertical-align: top;
  }
  b.uname {
    font-size: 14px;
    color: brown;
  }
  </style>
<H2 >
  CREATIVE COMMONS &amp; MAGNATUNE PRESENT:
</H2>
<h1  id="ccttite">Salman Ahmad "Natchoongi" Remix Contest</h1>
<div  style="margin: 17px; float: left;">
<img  src="<?= $T->URL('salman-small.jpg'); ?>" align="left" alt="[ Salman ]" />
</div>
<p >

The audio source files Salman's <i >Natchoongi</i> are
available here under a <a  href="http://creativecommons.org/licenses/by-nc/2.5">Creative Commons Attribution-NonCommercial</a> license, so that 
producers worldwide can use the sounds in remixes and new compositions. The sources are available both as a selection of 
looped or, if you want to cut your own samples, some of the original solo tracks from the recording session are available as well.
</p>
<p >
  For reference, hear the original fully mixed track "<a  href="http://ccmixter.org/media/files/salman/10757">Natchoongi</a>."
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
$A['srcs'] = cc_query_fmt('tags=salman,contest_sample');

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
<td >[<a  class="cc_file_link" href="<?= $A['R']['file_page_url']?>">details</a>]</td>
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
<div  class="dldiv" style="display:none">
<a  id="cc_downloadbutton" href="http://ccmixter.org/media/salman/Salman_ccmixter_VOCALS-snippets.zip">
<span >Vocal Snippets (6.9 MB) </span>
</a>
<p >(FLAC compressed WAV files in ZIP archive)</p>
</div>
<div  class="dldiv">
<a  id="cc_downloadbutton" href="http://ccmixter.org/media/salman/Salman_ccmixter_VOCALS.zip">
<span >Main Vocals (60.7 MB) </span>
</a>
<p >(FLAC compressed WAV files in ZIP archive)</p>
</div>
<div  class="dldiv">
<a  id="cc_downloadbutton" href="http://ccmixter.org/media/salman/salman_ccmixter_guitars.zip">
<span >Guitars (50.5 MB) </span>
</a>
<p >(FLAC compressed WAV files in ZIP archive)</p>
</div>
<div  class="dldiv">
<a  id="cc_downloadbutton" href="http://ccmixter.org/media/salman/salman_ccmixter_perc.zip">
<span >Percussion (34.5 MB) </span>
</a>
<p >(FLAC compressed WAV files in ZIP archive)</p>
</div>