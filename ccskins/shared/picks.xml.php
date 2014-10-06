<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?><!-- template picks -->
<h1><?= $T->String('str_edpicks_hot_tracks'); ?></h1>

<link rel="stylesheet" type="text/css" title="Default Style" href="<?= $T->URL('css/picks.css') ?>" />
<table  class="pickspage">
<tr >
<td  rowspan="2" style="padding-right: 25px;">
<?
    $A['ed_pick'] = 1;
    $A['pick_title'] = $T->String('str_editors_picks'); 
    $A['qstring'] = 'tags=editorial_pick&sort=date&dir=DESC&limit=22';
    $T->Call('picks.xml/picks');
?>
</td>
<td >
<?
    $A['settings'] = CC_get_config('chart');
    $A['pick_title'] = $T->String('str_editorial_whats_hot');
    $A['ed_pick'] = 0;
    $A['qstring'] = 'tags=remix,-digital_distortion&sort=rank&sinced=' . $A['settings']['cut-off'] . '&limit=12';
    $T->Call('picks.xml/picks');
?>
</td>
</tr>
<tr >
<td >
<?
    $A['pick_title'] = $T->String('str_editorial_all_time');
    $A['ed_pick'] = 0;
    $A['qstring'] = 'tags=remix&sort=rank&limit=10';
    $T->Call('picks.xml/picks');
?></td>
</tr>
</table>

<script type="text/javascript">
//<!--
function pickwinplay(qstring)
{
  var url = query_url + 'popup=1&t=playable_list&' + qstring;
        var dim = "height=400,width=650";
  var win = window.open( url, 'cchostplayerwin', "status=1,toolbar=0,location=0,menubar=0,directories=0," +
                "resizable=1,scrollbars=1," + dim );
}
//-->
</script>


<?
function _t_picks_picks_links(&$T,&$A) 
{
    if( $GLOBALS['strings-profile'] == 'audio' )
    {
?>
        <div class="pickslinks">
        <a id="mi_podcast_page" href="<?= $A['query-url']?>f=rss&<?= $A['qstring']?>"><span ><?= $T->String('str_podcast')?></span></a>
        <a id="mi_stream_page" href="<?= url_args( ccl('api','query','stream.m3u'), $A['qstring'] . '&f=m3u' ) ?>">
            <span><?= $T->String('str_stream')?></span></a>
        <a id="mi_play_page" href="javascript://play win" onclick="pickwinplay('<?= $A['qstring']?>');">
            <span><?= $T->String('str_play')?></span></a>
            <br class="pickslinks_break" />
        </div>
<?
    }
}

function _t_picks_picks(&$T,&$A) {

  print "<h3>{$A['pick_title']}</h3>\n";
      
  $A['chart'] = cc_query_fmt($A['qstring'] . '&dataview=links_extra' );
  if ( !empty($A['chart'])) 
  {
        $T->Call('picks_links');

        foreach( $A['chart'] as $item )
        {    
            ?><div ><a href="<?= $item['file_page_url'] ?>" class="cc_file_link"><?= $item['upload_name'] ?></a> <?
            if( !empty($item['upload_num_playlists']) )
            {
                ?> <a style="font-weight:normal;font-size:0.85em;color:#797;" href="<?= url_args(ccl('playlist','browse'),'upload='.$item['upload_id'])?>">(<?=$item['upload_num_playlists']?>)</a><?
            }
                ?>
                  <br  /><span ><?= $T->String('str_by') ?> <a class="cc_user_link" href="<?= $item['artist_page_url'] ?>"><?= $item['user_real_name'] ?></a></span>
            <?
            if ( !empty($A['ed_pick'])) 
            {
                $edkeys = array_keys($item['upload_extra']['edpicks']);
                $editorial = $item['upload_extra']['edpicks'][$edkeys[0]];
                $_t_ = CC_strchop($editorial['review'],40);
                ?><p><i><?= $_t_ ?> <a class="cc_file_link" href="<?= $item['file_page_url'] ?>">(<?= $T->String('str_more') ?>) </a></i></p><?
            }
            print '</div>';
        }
    }
    else
    {
        ?><div ><?= $T->String('str_editorial_no_chart') ?></div><?
    }
}
