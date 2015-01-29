<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_ghostly_sources_init($T,&$targs) {
    
}
?><div >
<style >

h1 {
  color:  #000 !important;
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
</style>
<h1 >Christopher Willits  / "Colors Shifting" Remix Contest Sources</h1>
<?
$A['before'] = date('Y-m-d H:i') < '2006-11-15 10:40';

if ( !empty($A['before'])) {

?><p >Sources will be available at 9AM PST</p>
<?
} // END: if

if ( !($A['before']) ) {

?><img  src="<?= $T->URL('Willits_1.jpg'); ?>" style="float:right; margin: 10px" />
<p >Ghostly Records and Christopher Willits have made the studio tracks available
for "Colors Shifting" under a <a  href="http://creativecommons.org/licenses/by-nc/2.5">Creative Commons Attribution Non-Commercial (2.5) License</a>.</p>
<p >Some of the sources are compressed using an free Open Source lossless compressor utility
called FLAC (you can <a  href="http://flac.sourceforge.net">download it from here</a>).</p>
<p >The vocals tracks are also available here as high quality VBR MP3s.</p>
<p >The song is in the key of C, at a tempo of 60 BPM.</p>
<p >You can download the original studio tracks as FLAC or start your remix with samples
packs made up of pre-cut WAV loops (ACIDized with key and tempo) or ReCyle format REX files.</p>
<h4 >Source Downloads </h4>
<h3 ><a  href="<?= $A['home-url']?>files/cwillits/7781">Go to the vocal track's download page (VBR MP3, FLAC)</a></h3>
<h3 ><a  href="<?= $A['home-url']?>files/cwillits/7780">Go to the sample packs download page (ACIDized WAVs, REX)</a></h3>
<div  class="dldiv">
<a  id="cc_downloadbutton" type="application/octet-stream" href="http://ccmixter.org/media/ghostly/ColorShifting-Tracks.zip"><span >Download solo tracks</span></a>
<p >Download uncut studio solo tracks (ZIP of WAVs 72.8 MB)</p>
</div>
<hr  />
<h4 >Extras</h4>
<p >For completeness' sake, you can also get the vocal tracks in WAV, several mixes featuring isolated
frequency ranges of the instrumental tracks mixed together, and the original distortion tracks.</p>
<div  class="dldiv">
<a  id="cc_downloadbutton" type="application/octet-stream" href="http://ccmixter.org/media/ghostly/Colors%20Shifting-vocals.wav"><span >Download Vocals WAV</span></a>
<p >Download vocals in WAV format (11.4 MB)</p>
</div>
<div  class="dldiv">
<a  id="cc_downloadbutton" type="application/octet-stream" href="http://ccmixter.org/media/ghostly/MIXES-FLAC.zip"><span >Download mixed tracks</span></a>
<p >Download frequency range mixed tracks (ZIP of FLACS 28.8 MB)</p>
</div>
<p >
<a  href="<?= $A['home-url']?>files/cwillits/7779">Go to the distortion tracks download page (FLAC)</a>
</p>
<?
} // END: if

?></div>