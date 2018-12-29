<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_vieux_sources_init($T,&$targs) {
    
}
?><style >
    h1 {
      color: #000 !important;
    }
    
    h2 a {
      margin-top: 25px;
	  font-size: 22px;
	  border-top: 1px solid #999;
	  padding: 4px 3px 4px 20px;
	  background-color: #DDF;
    }
    
    ._photo {
      width: 300px;

    }
    ._pic {
      float: right;
      margin: 0 0 10px 10px;      
    }

    ._photo small {
      color:  #777;
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
  </style>
<h1 >The Vieux Farka Touré / "Ana" Remix Contest Sources</h1>
<div  class="intro">
<p >
<div  class="_pic _photo">
<img  src="<?= $T->URL('vieux.jpg'); ?>" align="right" width="300" height="200" alt="[ Vieux Farka Touré ]" /><br  /><small >&copy; Amidou Touré. All rights reserved. Used with permission.</small>
</div>
	Vieux Farka Touré is offering the audio source files from the song “Ana” online under a 
	  <a  href="http://creativecommons.org/licenses/by-nc/2.5">Creative Commons Attribution-NonCommercial</a> 
	license, so that producers 
	worldwide can use the sounds in remixes and new compositions. The sources are available both as a selection of looped and tagged 
	files (ACIDized with key and BPM tempo information embedded in them) or, if you want to cut your own samples, the original solo 
	tracks from the entire recording session are available as well.
</p>
<p >
	The song is in the key of <b >B minor</b> at a tempo of <b >114 BPM</b>
</p>
<p >
	In order to use these files you need an 'unzip' utility. (For Windows we suggest the fine and free <a  href="http://www.7-zip.org/">Z-zip</a>.)
</p>
</div>
<br  clear="right" />
<hr  />
<h2 >Ready for Remixing</h2>
<h3 ><a  href="<?= $A['home-url']?>files/vieux/8639">Go to "Ana" a cappellas download page</a>
<span >(Hi quality VBR MP3)</span></h3>
<h3 ><a  href="<?= $A['home-url']?>files/vieux/8638">Go to pre-cut instrumental loops page</a>
<span >(Over 50 WAV loops (ACIDized))</span></h3>
<hr  />
<h2 >Raw Studio Tracks</h2>
<p >
	If the pre-cut loops aren't doing it for you can cut your own samples out of the original studio tracks. 
</p>
<p >
    Please note the following:
</p>
<ul  class="caveats">
<li >All tracks are aligned for 114 BPM. </li>
<li >These tracks are compressed using the FLAC lossless compression utility. 
		(<a  href="http://flac.sourceforge.net/download.html">download FLAC encoder here</a>)
	</li>
<li >
		In order to save on bandwidth and space only the kick mic tracks are separated in <i >drums.zip</i>,
		the rest of the kit's mic tracks are merged.
</li>
<li >
		To download right click (control on Mac) on these buttons and select 'Save Target As...'
	</li>
</ul>
<div  class="dldiv"><a  id="cc_downloadbutton" href="http://ccmixter.org/media/vieux/ccmixter_vieux_gtr_bass.zip"><span >gtr_bass.zip (41.1 MB)</span></a>
<p >(FLAC compressed WAV files in ZIP archive)</p></div>
<div  class="dldiv"><a  id="cc_downloadbutton" href="http://ccmixter.org/media/vieux/ccmixter_vieux_horns.zip"><span >horns.zip (4.9 MB)</span></a>
<p >(FLAC compressed WAV files in ZIP archive)</p></div>
<div  class="dldiv"><a  id="cc_downloadbutton" href="http://ccmixter.org/media/vieux/ccmixter_vieux_keys.zip"><span >keys.zip (4.1 MB)</span></a>
<p >(FLAC compressed WAV files in ZIP archive)</p></div>
<div  class="dldiv"><a  id="cc_downloadbutton" href="http://ccmixter.org/media/vieux/ccmixter_vieux_vocals.zip"><span >vocals.zip (22.7 MB)</span></a>
<p >(FLAC compressed WAV files in ZIP archive)</p></div>
<div  class="dldiv"><a  id="cc_downloadbutton" href="http://ccmixter.org/media/vieux/ccmixter_vieux_drums.zip"><span >drums.zip (29.4 MB)</span></a>
<p >(FLAC compressed WAV files in ZIP archive)</p></div>