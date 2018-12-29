<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_ghostly_home_init($T,&$targs) {
    
}
?><div >
<style >
#inner_content {
    width: 80%;
    margin: 0px auto;
}

    h1 {
      color: #000 !important;
    }
    
    .willits_photo {
      width: 300px;
      float: right;
      margin: 0 0 10px 10px;
    }
  </style>
<h1 >Christopher Willits  / "Colors Shifting" Remix Contest</h1>
<div  style="margin:8px; padding:8px; border:1px solid #77F">
<?
$A['Rs'] = CC_cache_query('ghostly,winner');
$A['R'] = $A['Rs']['0'];

?><table ><tr ><td >
<img  src="<?= $A['R']['user_avatar_url']?>" style="margin: 6px;" />
</td><td >
<h2 >Congratulations to Our Winner!</h2>
<h3 ><a  class="cc_file_link" href="<?= $A['R']['file_page_url']?>"><?= $A['R']['upload_name']?></a> by <a class="cc_user_link" href="<?= $A['R']['artist_page_url']?>"><?= $A['R']['user_real_name']?></a></h3>
<p ><a  id="cc_streamfile" href="<?= $A['root-url']?>/media/files/stream/ghostly/<?= $A['R']['upload_id']?>.m3u"><span >Stream</span></a>
</p>
<p ><i >We received a bunch of submissions and a great handfull of notable stuff, but Patrick's remix definitely showed the best overall production of the group. He guided the samples into his own vision, and made it his own.</i><p  style="text-align: right; font-weight: bold;">Christopher Willits</p></p>
<p >Thanks to our sponsors Ghostly, XLR8R, Creative Commons and of course Christopher for a great contest!</p>
</td></tr></table>
</div>
<p >
<div  class="willits_photo"><img  src="<?= $T->URL('Willits_1.jpg'); ?>" alt="Christopher Willits" border="0" align="right" /><br  /><small >Photo &copy; <a  href="http://www.ghostly.com/press/">Ghostly International</a>, used with permission.</small></div>
<a  href="http://creativecommons.org/">Creative Commons</a>, <a  href="http://ghostly.com/">Ghostly International</a> and <a  href="http://xlr8r.com/">XLR8R Magazine</a> are pleased to present the Christopher Willits / "Colors Shifting" Remix Contest.  <a  href="http://www.christopherwillits.com/">Christopher Willits</a> &mdash; a Bay Area-based musician and multimedia artist &mdash; is offering the audio source files from the song "Colors Shifting" online under a Creative Commons Attribution-NonCommercial license, so that producers worldwide can use the sounds in remixes and new compositions.</p>
<h2 >How to Participate</h2>
<p  style="margin:15px; width:30%;padding:14px;border: 1px solid brown; color:brown;">This contest is closed to new entries.</p>
<h2 >Source Materials</h2>
<p >Download the separated audio elements of Christopher Willits "Colors Shifting" <a  href="/ghostly/view/contest/sources">here</a>.</p>
<h2 >Prize</h2>
<p >After all eligible entries have been received, Christopher Willits will select the best remix. The winning remix will be included on an XLR8R Incite CD compilation, which will be included in copies of a future issue of XLR8R Magazine.</p>
<h2 >Scoring</h2>
<p >Entries will be judged based on the following criteria:</p>
<ul >
<li >Creativity (70% of overall score)</li>
<li >Production quality (30% of overall score)</li>
</ul>
<h2 >About Christopher Willits</h2>
<p ><img  src="<?= $T->URL('surf-boundaries.jpg'); ?>" alt="Christopher Willits -- Surf Boundaries" style="margin: 0 0 10px 10px;" border="0" align="right" />
Christopher Willits is a musician and multimedia artist located in San Francisco, California. Striking a delicate balance between acoustic and electronic sounds and systems, Willits manages to defy genre distinctions while still defining a sound unto his own. His numerous solo releases cover a broad spectrum of musical styles, and include one main commonality: Willits' unique use of the guitar with custom-made signal processing. This home-brewed software, along with Willits’ 6-string prowess, generates a unique real-time mixture of improvised melody and rhythm. Guitar lines and harmonies fold into each other. Notes and phrases hook and weave, creating complex patterns of interlocking rhythm, melody, and texture. On his breakthrough LP, <a  href="http://www.ghostly.com/1.0/ghostly/gi54.shtml">Surf Boundaries</a>, Willits merges the patterns of his signature guitar sound with treated strings, brass and five-part vocal harmonies for a sonic vision that draws upon elements of shoegaze, jazz, ambient and noise.  The San Francisco Weekly named Willits "the center cell of a rather complex indie rock-avant-garde electronic art Venn diagram." </p>
<p >Willits has a vast range of collaborations he is involved in, which include bands, sound installations, and film/video projects.  The <a  href="http://www.ghostly.com/1.0/artists/nvso">North Valley Subconscious Orchestra</a> is the project of guitarists Brad Laner and Christopher Willits, traversing a series of guitar noise sculptures and heavily processed pop songs.</p>
<h2 >Quotes</h2>
<p >
<strong >Christopher Willits</strong>: "Creative commons is helping artists forge new paths through the grey areas of digital ownership, allowing us to find appropriate boundaries of sharing our work that does not exclude creative interpretation and replication. I'm honored to be included in the CC remix project and super excited to hear what everyone comes up with."</p>
<p  style="height: 90px;">
<img  src="<?= $T->URL('ghostw.png'); ?>" alt="Ghostly International" style="margin: 0 0 10px 10px;" border="0" align="right" />
<strong >Jeff Owens, Label Manager, Ghostly International</strong>: "Ghostly is excited to be a part of the remix contest.  Creative Commons brings fans even closer to the artist and the musical process." </p>
<p  style="height: 90px;">
<img  src="<?= $T->URL('xlr8r.png'); ?>" alt="XLR8R" style="margin: 0 0 10px 10px;" border="0" align="right" />
<strong >Ken Taylor, Managing Editor, XLR8R</strong>: "XLR8R is totally stoked to be a part of ccMixter's remix contest, as we share with Creative Commons the philosophy that artists–not companies–should maintain the greatest amount of control in how music is used–and what it might become."</p>
<h2 >Official Rules</h2>
<p >Read the <a  href="/ghostly/view/contest/rules">Official Rules</a>.</p>
</div>