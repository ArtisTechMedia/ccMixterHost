<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_magnatune_winners_init($T,&$targs) {
    
}
?><style >
p { font-size: 12px; }
h2,h3 { margin: 2px; }
.uploadcredits { margin-bottom: 13px; }

.contestback 
{
  float:left;
  background-color:#eee;
  border:1px solid #ddd;
  width:100%;
}
</style>
<h1 >Creative Commons Magnatune Lisa DeBenedictis Remix Contest Winners</h1>
<p >Thirteen people who uploaded 15 tracks won <a  href="/magnatune/view/contest/about">the Magnatune Lisa DeBenedictis Remix Contest</a>. Due to the
high-quality remixes received, Magnatune increased the winning entries from 10 to 15.</p>
<p >Winners are signed to a Magnatune standard "non-evil" recording 
contract. Also, the winning entries are included on a forthcoming Lisa DeBenedictis remix album to be sold commercially where the artists' share (50% from the first sale) will be split between the winners and (of course) the artists they sample.</p>
<p ><i >Again, because of the high quality of submissions, Magnatune will be  
signing 15 more songs for another Magnatune-released CD in the future, 
independent of this contest.</i></p>
<div  style="width:135px;padding:0px;">
<div  class="cc_podcast_link"><a  href="<?= $A['home-url']?>podcast/page?tags=winner,magnatune">PODCAST Winners</a></div>
<div  class="cc_stream_page_link"><a  href="<?= $A['home-url']?>stream/page?tags=winner,magnatune">STREAM Winners</a></div>
</div>
<br  clear="both" />
<div  style="float:left; width:45%;">
<div  style="padding-right:10px;">
<h2  style="font-size:18px;margin:0px;font-weight:normal;">The Magnatune Lisa DeBenedictis Remix Contest Winners</h2>
<br  />
<?
$A['wrecords'] = CC_tag_query('winner,magnatune', 'all' );

$carr101 = $A['wrecords'];
$cc101= count( $carr101);
$ck101= array_keys( $carr101);
for( $ci101= 0; $ci101< $cc101; ++$ci101)
{ 
   $A['w'] = $carr101[ $ck101[ $ci101 ] ];
   
?><h3 ><a  class="cc_file_link" href="<?= $A['w']['file_page_url']?>"><?= $A['w']['upload_name']?></a></h3>
<div  class="uploadcredits">by <a class="cc_user_link" href="<?= $A['w']['user_page_url']?>"><?= $A['w']['user_real_name']?></a></div>
<?
} // END: for loop

?></div>
</div>
<div  class="contestbox">
<div  class="contestback" style="width:370px;float:right;margin:8px;">
<div  style="padding:10px 20px 10px 10px;">
<a  href="/contests/magnatune"><img  src="/mixter-files/magnatune_logo.gif" alt="Magnatune" style="float:right;margin-left:10px;margin-bottom:6px;border:1px solid #ccc;" /></a>
<a  href="http://magnatune.com/info"><h2  style="font-size:18px;margin:0px;font-weight:normal;">What's Magnatune?</h2></a>
<br  clear="right" />
<p >"Musicians need to be in control and enjoy the process of having their music released. The systematic destruction of musician's lives is unacceptable: musicians are very close to staging a revolution (and some already have)."</p>
<p ><i >John Buckman, CEO Magnatune</i></p>
<p ><a  href="http://magnatune.com/info">Read more about this innovative music label.</a></p>
</div>
</div>
</div>