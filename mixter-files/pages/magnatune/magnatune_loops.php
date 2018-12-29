<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

?><div>
<style type="text/css">
#inner_content {
    width: 80%;
    margin: 0px auto;
}

.lp_results_head {
  font-style: italic;
  color: #555;
  padding: 8px;
  line-height: 17px;
  background-color: #EEF;
}

.lp_results_head img {
  vertical-align: bottom;
}

td.sbutton {
  width: 18px;
}

.down_button, .hear_button {
  display: block;
  width: 17px;
  height: 17px;
}
.down_button, .hear_button,
.down_button:hover, .hear_button:hover {
  text-decoration:none;
}

.down_button {
  background: url(<?= $T->URL('images/down-button-fg.gif') ?>);
}
.down_button:hover {
  background: url(<?= $T->URL('images/down-button-bg.gif') ?>);
}
.hear_button {
  background: url(<?= $T->URL('images/player/hear-button-fg.gif') ?>);
}
.hear_button:hover {
  background: url(<?= $T->URL('images/player/hear-button-bg.gif') ?>);
}
</style>

<script type="text/javascript">
function on_get_albumlist(listbox)
{
   var opt    = listbox.options[listbox.selectedIndex];
   if( opt.value != "[d]" )
   {
     var opt = listbox.options[listbox.selectedIndex];
     var url = home_url + "library/albumlist/" + opt.value;
     $('album_preview').innerHTML = 'getting album data...';
     new Ajax.Updater( 'album_preview', url, { method: 'get' } );
   }
}

function on_get_loopset(listbox)
{
    var opt = listbox.options[listbox.selectedIndex];
    var url = home_url + "library/looplist/" + opt.value;
    $('loop_preview').innerHTML = 'getting loop data...';
    new Ajax.Updater( 'loop_preview', url, { method: 'get' } );
}

function on_pick_genre()
{
   var glistbox = $('magnatunegenres');
   var opt = glistbox.options[glistbox.selectedIndex];
   var loc = get_mbase_url();
   window.location.href = loc + '?genre=' + opt.value + '#catalog';
}

function get_mbase_url()
{
  var loc = '' + window.location.href;
  return loc.split('?')[0];
}

</script>
<style type="text/css">
.genre_head {
  margin: 5px;
}
.genre_head span {
  font-weight: bold;
}
.msgs {
  float: right;
  width: 32%;
}
.msgs td div {
  padding: 10px;
  background-color:#eee;
  border:1px solid #ddd;
  margin: 6px;
}
.msgs td a {
  font-weight: bold;
}

</style>
<h1>Magnatune Samples Library</h1>
<table  class="msgs" cellpadding="0" cellspacing="0">
<tr>
<td><div><a  href="http://creativecommons.org/licenses/by-nc-sa/1.0" style="float:right; margin: 3px;"><img  src="http://creativecommons.org/images/public/somerights20.gif" /></a> All music and samples pointed to on this page are covered under a
  Creative Commons <a  href="http://creativecommons.org/licenses/by-nc-sa/2.0">Attribution NonCommercial ShareAlike</a> license. 
  Please note that any work derived from this music <i>must</i> be released under the same license.</div></td>
</tr>
</table>
<h2>Studio Tracks</h2>
<p>Several Magnatune recording have made the individual studio tracks available for remixing. 
(These tracks are under the same Attribution-Noncommericial-ShareAlike license as every thing else in the 
Magnatune catalog.). These are in WAV format (archived in ZIP files) and taken directly from the 
pre-mixed recording sessions:</p>
<p><a  href="http://magnatune.com/extra/remix/tracks/thornside.zip">Tracks to "Lambourgini" and "Can I Be A Star" by Burnsee Thornside</a></p>
<p><a  href="http://magnatune.com/extra/remix/tracks/clayne.zip">Tracks to "The King" by c. layne</a></p>
<p><a  href="http://magnatune.com/extra/remix/tracks/brad_sucks.zip">Tracks to "Sick as a Dog" by Brad Sucks</a>. <span  style="font-style:italic;">(More Brad stuff 
<a  href="<?= $A['root-url']?>media/people/bradsucks"><b>here</b></a>.)</span></p>
<p><a  href="http://magnatune.com/extra/remix/tracks/drop_trio.zip">Tracks to "Lefty's Alone" by Drop Trio</a></p>
<p><a  href="http://magnatune.com/extra/remix/tracks/debenedictis.zip">A cappellas for "Below" and "Cuckoo" by Lisa DeBenedictis</a></p>
<hr  />
<a  name="loops"></a>
<h2>Pre-Cut Loop Libraries</h2>
<p>In order to jump start your remix ccMixter has prepared a set of loop libraries culled from the 
Magnatune library. There are over 400 loops in the total set with every imaginable instrumentation
and genre. All loops are have been pre-cut (where appropriate) and are each labelled with 
key and tempo (BPM). In addition the loop files are all ACIDized which means applications like ACID,
FL Studio, SONAR, Garageband and many others will be able to correctly detect the key and tempo
for each of the loops.</p>
<table  border="0" cellpadding="3" cellspacing="0">
<tr>
<td  valign="top">
<select  name="loop_artist" id="loop_artist" size="9" onchange="on_get_loopset(this)">
<?

$carr101 = $A['mt']['artists'];
$cc101= count( $carr101);
$ck101= array_keys( $carr101);
for( $ci101= 0; $ci101< $cc101; ++$ci101)
{ 
   $art = $carr101[ $ck101[ $ci101 ] ];
   ?><option  value="<?= $art['loop_artist']?>"><?= $art['artist']?> - <?= $art['artistdesc']?></option><?
} // END: for loop

?></select>
</td>
<td  style="vertical-align:top;padding-left:5px;">
    <div  id="loop_preview"></div>
</td>
</tr>
</table>
<hr  />
<a  name="catalog"></a>
<h2>Cut Your Own from the Magnatune Catalog</h2>
<p>You can also use samples from the entire Magnatune catalog. Here you can browse, stream and 
download individual tracks in high quality MP3:</p>
<br  />
<table  border="0" cellpadding="3" cellspacing="0">
<tr>
<td  valign="top">
<style type="text/css"> optgroup { font-weight: bold; font-family: verdana; font-style: normal;} </style>
<div>
<table><tr><td>
<select  name="magnatunegenres" id="magnatunegenres" style="font-size: 11px;font-family: Verdana">
<option  value="all">See all genres</option>
<?

$carr102 = $A['mt']['genres'];
$cc102= count( $carr102);
$ck102= array_keys( $carr102);
for( $ci102= 0; $ci102< $cc102; ++$ci102)
{ 
   $A['genre'] = $carr102[ $ck102[ $ci102 ] ];
   
?><option  value="<?= $A['genre']?>"><?= $A['genre']?></option>
<?
} // END: for loop

?></select></td><td  style="width:120px"><a  class="cc_gen_button" href="#catalog" onclick="on_pick_genre();"><span>View genre</span></a></td></tr></table>
<?

if ( !empty($A['mt']['genre_filter_on'])) {
    ?><div  class="genre_head">Currently viewing genre: <span><?= $A['mt']['current_genre']?></span></div><?
} // END: if

?><select  name="album_pick" size="19" onchange="on_get_albumlist(this)" style="font-size: 11px" id="album_pick">
<?

$carr103 = $A['mt']['albums_by_artist'];
$cc103= count( $carr103);
$ck103= array_keys( $carr103);
for( $ci103= 0; $ci103< $cc103; ++$ci103)
{ 
    $art = $carr103[ $ck103[ $ci103 ] ];
   
    ?><optgroup  label="<?= $art['artist']?>">
    <option  value="[d]" style="color:#777"><?= CC_strchop($art['artistdesc'],50);?></option><?

    $carr104 = $art['albums'];
    $cc104= count( $carr104);
    $ck104= array_keys( $carr104);
    for( $ci104= 0; $ci104< $cc104; ++$ci104)
    { 
       $alb = $carr104[ $ck104[ $ci104 ] ];
        ?><option  value="<?= $alb['albumsku']?>">"<?= $alb['albumname']?>"</option><?
    } // END: for loop

    ?></optgroup><?
} // END: for loop

?></select>
</div>
</td>
<td  style="vertical-align:top;padding-left:5px;">
    <div id="album_preview"></div>
</td>
</tr></table>
<?

if ( !empty($A['mt']['genre_filter_on'])) {

?><script type="text/javascript">
//<!--
function sel_genre()
{
   var glistbox = $('magnatunegenres');
   var curgenre = '<?= $A['mt']['current_genre']?>';
   var opts     = glistbox.options;
   var i=0;
   for( i=0; i < opts.length; i++ )
   {
      if( opts[i].value == curgenre )
      {
        glistbox.selectedIndex = i;
        break;
      }
    }
 }

 sel_genre();
//-->
</script><?
} // END: if

?></div>