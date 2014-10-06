<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

//------------------------------------- 
function _t_sample_browser_browser_page($T,&$A) {
  
?><h1>Samples Browser</h1>
<script type="text/javascript">
  //<!--
  var tags = new Array();
  var tag_filter = 'any';
  var bpm_tag = '-';
  var lic_filter = '-';

  function tag_checked(cb)
  {
     var namespan = $( 'name_' + cb.name );
     if( cb.checked )
     {
       namespan.style.fontWeight = 'bold';
       tags.push( cb.name );
       var clear_btn = $('btn_clear');
       clear_btn.disabled = false;
     }
     else
     {
       namespan.style.fontWeight = 'normal';

       var tagarr = new Array();
       var i;
       var n = 0;
       for( i = 0; i < tags.length; i++ )
       {
          if( tags[i] != cb.name )
            tagarr[n++] = tags[i];
       }
       tags = tagarr;
     }

     do_get_data();
  }

  function bpm_picker(selobj)
  {
     bpm_tag = selobj.options[ selobj.selectedIndex ].value;
     if( tags.length > 0 )
       do_get_data();
  }

  function do_get_data()
  {
     var tagstr = tags.join( ',' );
     var url = home_url + 'samples/list' + q + "tags=" + tagstr 
                                         + '&tf=' + tag_filter 
                                         + '&bpm=' + bpm_tag
                                         + '&lic=' + lic_filter;
     $('sample_browser').innerHTML = '<span class="sb_msg">Hold on... looking up samples...</span>';
     new Ajax.Updater('sample_browser',url,{ method: 'get' });
  }

  function new_tag_sort(selobj)
  {
    var newval = selobj.options[ selobj.selectedIndex ].value;
    var loc = get_base_url();
    window.location.href = loc + '?ts=' + newval;
  }

  function new_tag_limiter(selobj)
  {
    var newval = selobj.options[ selobj.selectedIndex ].value;
    var loc = get_base_url();
    window.location.href = loc + '?sa=' + newval;
  }

  function new_tag_filter(selobj)
  {
    var newval = selobj.options[ selobj.selectedIndex ].value;
    tag_filter = newval;
    do_get_data();
  }

  function new_lic_filter(selobj)
  {
    var newval = selobj.options[ selobj.selectedIndex ].value;
    lic_filter = newval;
    do_get_data();
  }

  function get_base_url()
  {
    var loc = '' + window.location.href;
    var regex = new RegExp(/\?..=[0-9a-z]+/);
    if( regex.exec( loc ) )
       loc = loc.replace( regex, '' );
    return loc;
  }

  function clear_all(btn)
  {
      do_clear_all(btn);
      do_get_data();
  }

  function do_clear_all(btn)
  {
      btn.disabled = true;
      for( i = 0; i < tags.length; i++ )
      {
        var checkbox = $(tags[i]);
        checkbox.checked = false;
        var namespan = $( 'name_' + tags[i] );
        namespan.style.fontWeight = 'normal';
      }
      tags = new Array();
  }

  function do_search()
  {
     var btn = new $('btn_clear');
     do_clear_all(btn);

     var input = $('sampsearch');
     var str   = input.value;
    
     var url = home_url + 'samples/search' + q + "term=" + str;  /* + '&bpm=' + bpm_tag */
     $('sample_browser').innerHTML = '<span class="sb_msg">Hold on... looking up samples...</span>';
     new Ajax.Updater('sample_browser',url,{method:'get'});
  }

  function toggle_zipper(link_id,id)
  {
     var div = $(id);
     var link = $(link_id);
     if( div.style.display == 'none' )
     {
        link.src = '<?=$A['root-url']?>ccskins/shared/images/close.gif';
        div.style.display = 'block';
     }
     else
     {
        link.src = '<?=$A['root-url']?>ccskins/shared/images/open.gif';
        div.style.display = 'none';
     }
  }
  // -->
</script>
<style >

  /* --- Two main areas: ---- */

  #controls {
    width: 220px;
    margin-left: 10px;
    background-color: #DDD;
    border: 2px solid #999;
    padding-bottom: 4px;
    float: right;
  }

  #sample_browser {
    float: left;
    width: 580px;
    border: 2px solid #999;
    height: 480px;
    background-color: #EEE;
    padding: 8px;
  }

  #browser_container {
      width:   842px;
      white-space: nowrap;
  }
  /* ------------------------ */

  #results_container {
     overflow: scroll;
     width: 100%;
     height: 420px;
  }

  #results_header {
    height: 30px;
    padding: 4px;
    font-weight: bold;
    background: #999;
  }

  .sb_msg {
    font-family: arial;
    font-size: 14px;
    font-style: italic;
    display: block;
    margin: 10px;
  }

  .down_button, .hear_button {
    display: block;
    width: 17px;
    height: 17px;
    text-decoration: none;
  }
  .down_button {
    background: url('<?= $T->URL('images/down-button-fg.gif'); ?>');
  }
  .down_button:hover {
    background: url('<?= $T->URL('images/down-button-bg.gif'); ?>');
  }
  .hear_button {
    background: url('<?= $T->URL('images/player/hear-button-fg.gif'); ?>');
  }
  .hear_button:hover {
    background: url('<?= $T->URL('images/player/hear-button-bg.gif'); ?>');
  }

  #sampsearch, #btn_search, #dd_tag_sorter, 
  #dd_tag_filter, #btn_clear, #dd_tag_limiter,
  #dd_bpm_picker, #dd_lic_filter {
     font-family: Verdana;
     font-size: 10px;
  }

  #dd_lic_filter {
    width: 170px;
  }

  #search_ctrls {
    padding: 20px 8px 8px 8px;
    margin-top: 6px;
    border-top: 2px solid #999;
    background-color: #DDD;
  }

  #bpm_ctrls {
  }

  #bpm_caption {
    font-weight: bold;
    margin: 0px 0px 10px 0px;
  }

  #search_caption {
    display: block;
    font-weight: bold;
    margin: 0px 0px 10px 0px;
  }

  #sampsearch {
    margin: 0px;
    width: 200px;
  }

  #btn_search {
    margin: 5px 0px 2px 150px;
    display: block;
  }

  #dd_tag_sorter {
  }

  #tag_filter {
  }

  #dd_tag_filter {
  }

  #cpanel {
     margin: 3px 0px 10px 13px;
  }

  #cpanel td {
    padding: 3px;
  }

  #btn_clear {
     float: left;
  }

  #picker_caption {
     margin: 8px 4px 8px 4px;
     display: block;
     font-weight: bold;
  }

  #tags_container {
     height: 320px;
     overflow: scroll;
     background-color: white;
     margin: 1px 4px 8px 4px;
  }
  
  #taglist {
  }

  .tagcheck {
  }

  .tb {
      font-size: 11px;
  }

  .tbc {
  }

  #results_caption {
  }

  #term_caption span {
    font-weight: bold;
    font-style: italic;
    color: white;
  }

  #results_table {
    width: 100%;
  }

  #results_table td, table.files td {
    vertical-align: top;
  }

  #results_table a {
    font-size: 11px;
  }

  .row_even {
  }

  .row_odd {
    background: #ddF;
  }

  td.filelink {
    padding-left: 8px;
  }

  td.userlink {
  }

  table.files {
  }

  td.dbutton, td.sbutton {
    width: 18px;
  }

  td.nicname {
    /* width: 40px; */
    white-space: nowrap;
    font-size: 10px;
  }

  td.bpm {
    width: 20px;
    font-size: 10px;
    color: #677;
 }
  .zip_files {
      color: green;
  }
  </style>
<div id="browser_container">
<div  id="sample_browser">
  <?= $A['default_msg']?>
</div>

<div  id="controls">
<span  id="picker_caption"><?= $A['picker_caption']?></span>
<div  id="tags_container">
<table  cellpadding="0" cellspacing="0" id="taglist">
<?

$carr101 = $A['tags_tags'];
$cc101= count( $carr101);
$ck101= array_keys( $carr101);
for( $ci101= 0; $ci101< $cc101; ++$ci101)
{ 
   $A['tagrec'] = $carr101[ $ck101[ $ci101 ] ];
   
?><tr>
    <td class="tagcheck">
        <input  onclick="tag_checked(this)" type="checkbox" id="<?= $A['tagrec']['tags_tag']?>" name="<?= $A['tagrec']['tags_tag']?>"></input>
    </td>
<td class="tb"><label  for="<?= $A['tagrec']['tags_tag']?>" id="name_<?= $A['tagrec']['tags_tag']?>"><?= $A['tagrec']['tags_tag']?></label>
<span  class="tbc">(<?= $A['tagrec']['tags_count']?>)</span></td>
</tr>
<?
} // END: for loop

?></table>
</div>
<table  cellspacing="0" cellpadding="0" id="cpanel">
<tr >
<td >
<div  id="bpm_ctrls">
<span  id="bpm_caption"><?= $A['bpm_caption']?></span>
<select  onchange="bpm_picker(this)" id="dd_bpm_picker">
<?

$carr102 = $A['bpms'];
$cc102= count( $carr102);
$ck102= array_keys( $carr102);
for( $ci102= 0; $ci102< $cc102; ++$ci102)
{ 
   $A['bpm'] = $carr102[ $ck102[ $ci102 ] ];
   
if ( !empty($A['bpm']['selected'])) {

?><option  selected="selected" value="<?= $A['bpm']['bpm']?>"><?= $A['bpm']['bpm']?></option>
<?
} // END: if

if ( !($A['bpm']['selected']) ) {

?><option  value="<?= $A['bpm']['bpm']?>"><?= $A['bpm']['bpm']?></option>
<?
} // END: if
} // END: for loop

?></select>
</div>
</td>
</tr>
<tr >
<td >

</td>
</tr>
<tr >
<td >
<div  id="license_filter">
<select  onchange="new_lic_filter(this)" id="dd_lic_filter" class="toggler">
<?

$carr103 = $A['licenses'];
$cc103= count( $carr103);
$ck103= array_keys( $carr103);
for( $ci103= 0; $ci103< $cc103; ++$ci103)
{ 
   $A['lic'] = $carr103[ $ck103[ $ci103 ] ];
   
if ( !empty($A['lic']['selected'])) {

?><option  selected="1" value="<?= $A['lic']['value']?>"><?= $A['lic']['text']?></option>
<?
} // END: if

if ( !($A['lic']['selected']) ) {

?><option  value="<?= $A['lic']['value']?>"><?= $A['lic']['text']?></option>
<?
} // END: if
} // END: for loop

?></select>
</div>
</td>
</tr>
<tr >
<td >
<div  id="tag_filter">
<select  onchange="new_tag_filter(this)" id="dd_tag_filter" class="toggler">
<option  value="any" select="1">Match any tags</option>
<option  value="all">Match all tags</option>
</select>
</div>
</td>
</tr>
<tr >
<td >
<select  onchange="new_tag_limiter(this)" id="dd_tag_limiter">
<?

$carr104 = $A['tag_limiters'];
$cc104= count( $carr104);
$ck104= array_keys( $carr104);
for( $ci104= 0; $ci104< $cc104; ++$ci104)
{ 
   $A['TL'] = $carr104[ $ck104[ $ci104 ] ];
   
if ( !empty($A['TL']['selected'])) {

?><option  selected="1" value="<?= $A['TL']['value']?>"><?= $A['TL']['text']?></option>
<?
} // END: if

if ( !($A['TL']['selected']) ) {

?><option  value="<?= $A['TL']['value']?>"><?= $A['TL']['text']?></option>
<?
} // END: if
} // END: for loop

?></select>
</td>
</tr>
<tr >
<td >
<input  type="button" value="Clear all" disabled="disabled" onclick="clear_all(this);" id="btn_clear"></input>
</td>
</tr>
</table>
<div  id="search_ctrls">
<span  id="search_caption"><?= $A['search_caption']?></span>
<input  id="sampsearch" type="text"></input>
<input  type="button" value="<?= $A['search_text']?>" onclick="do_search(this);" id="btn_search"></input>
</div>
</div>
<br style="clear:both" />
</div><!-- browser container -->
<?
} // END: function browser_page


//------------------------------------- 
function _t_sample_browser_browser_list($T,&$A) {
  
?><div  id="results_header">
<span  id="term_caption"><?= $A['term_caption']?>: <span ><?= $A['term']?></span></span>
<span  id="results_caption"><?= $A['results_caption']?></span>
</div>
<div  id="results_container">
<table  id="results_table" cellpadding="0" cellspacing="0">
<?
$ci105 = 0;
foreach( $A['records'] as $_rec )
{
   $A['R'] =& $_rec;
   
?><tr  class="row_<?= $ci105++ & 1 ? 'odd' : 'even' ?>">
<td  class="filelink"><a  href="<?= $A['R']['file_page_url']?>" class="cc_file_link"><span ><?= CC_strchop($A['R']['upload_name'],26);?></span></a></td>
<td  class="bpm">
<?

    if ( !empty($A['R']['upload_extra']['bpm'])) 
    {
        ?><span ><?= $A['R']['upload_extra']['bpm']?></span>bpm<?
    } // END: if

?></td>
<td  class="userlink"><a  class="cc_user_link" href="<?= $A['R']['artist_page_url']?>"><span ><?= CC_strchop($A['R']['user_real_name'],12);?></span></a></td>
<td >
<table  cellpadding="0" cellspacing="0" class="files">
<?
    $ci105_f = $ci105 * 100;
    foreach( $_rec['files'] as $F ) 
    { 
        
?><tr >
<td  class="dbutton"><a  class="down_button" href="<?= $F['download_url']?>">&nbsp;</a></td>
<td  class="sbutton"><?

        $is_zip = !empty($F['file_format_info']['zipdir']['files'] );

        $link_id = 'ziplink_' . $ci105_f++;
        $id = 'zipdir_' . $ci105_f;
        if ( !empty($F['file_format_info']['media-type']) )
        {
            if ( $F['file_format_info']['media-type'] == 'audio' ) 
            {
                ?><a  href="/samples/stream/<?= $F['file_id']?>.m3u" class="hear_button">&nbsp; </a><?
            } 
            // END: if
            if( $is_zip )
            {
                ?><a href="javascript://see zip contents" onclick="toggle_zipper('<?=$link_id?>','<?=$id?>'); return false" ><img src="<?= $A['root-url']?>ccskins/shared/images/open.gif" id="<?=$link_id?>" /></a><?
            }
        }
?></td><td  class="nicname"><?= $F['file_nicname']?> <span ><?= $F['file_filesize']?></span><?
        if( $is_zip )
        {   
            print '<div id="'. $id . '" class="zip_files" style="display:none";>';
            foreach( $F['file_format_info']['zipdir']['files'] as $zipfile )
            {
                print $zipfile . '<br />';
            }
        }
?></td>
</tr><?

    } // END: for loop

?></table>
</td>
</tr>
<?
} // END: for loop

?></table>
</div>
<?
} // END: function browser_list

?>
