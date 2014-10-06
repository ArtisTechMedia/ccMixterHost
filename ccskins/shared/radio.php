<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template radio -->
<link rel="stylesheet" type="text/css" href="<?= $T->URL('css/radio.css'); ?>" />

<div id="radio_container">
    <form  id="channel_form">
        <div  id="djback" class="box">
            <h2><?= $T->String('str_radio_station') ;?></h2>
            <div id="channel_content">
                <div  id="channel_intro"><?= $T->String( array('str_radio_create',$A['site-title']) ) ;?></div>

                <table  id="channels" cellspacing="0" cellpadding="0">
<?

$channels = CC_get_config('channels');

$rows = array_chunk($channels,5);
$cls = 'starter med_bg';
foreach( $rows as $R )
{ 
    ?><tr><?

    foreach( $R as $C )
    {
        $tag = str_replace(',','+',$C['tags']);

        ?><td><div class="cbutton <?=$cls?>" rel="<?= $tag ?>"><?= $C['text']?></div></td><?

        $cls = '';
    }

    ?></tr><?
} // END: for loop

?>
                </table>

                <div class="radio_opt">
                    <span  class="opt_label"><?= $T->String('str_filter_since') ?>:</span>
                    <select  id="sinceu" name="sinceu">
                    <option  value="<?= strtotime('1 day ago')?>"><?= $T->String('str_filter_yesterday')?></option>
                    <option  value="<?= strtotime('1 week ago')?>"><?= $T->String('str_filter_last_week')?></option>
                    <option  value="<?= strtotime('2 weeks ago')?>"><?= $T->String('str_filter_2_weeks_ago')?></option>
                    <option  value="<?= strtotime('1 month ago')?>"><?= $T->String('str_filter_last_month')?></option>
                    <option  selected="selected" value="<?= strtotime('3 months ago')?>" ><?= $T->String('str_filter_3_months_ago')?></option>
                    <option  value="<?= strtotime('1 year ago')?>"><?= $T->String('str_filter_last_year')?></option>
                    <option  value="0"><?= $T->String('str_filter_all_time')?></option>
                    </select>
                </div>
                <div class="radio_opt">
                    <span  class="opt_label"><?= $T->String('str_filter_this_many')?>:</span>
                    <select  id="limit" name="limit">
                    <option  value="10">10</option>
                    <option  value="25" selected="selected">25</option>
                    <option  value="50">50</option>
                    <option  value="100">100</option>
                    <option  value="200">200</option>
                    </select>
                </div>
<?
$chart = cc_get_config('chart');
if( !empty($chart['ratings']) )
{
    if( empty($chart['thumbs_up']) )
    {?>
                <div class="radio_opt">
                    <span  class="opt_label"><?= $T->String('str_ratings')?>:</span>
                    <select  id="score" name="score">
                    <option  value="500">5</option>
                    <option  value="450"><?= $T->String( array('str_filter_d_or_above', '4.5') ) ?></option>
                    <option  value="400" selected="selected"><?= $T->String( array('str_filter_d_or_above', '4') ) ;?></option>
                    <option  value="350"><?= $T->String( array('str_filter_d_or_above', '3.5') ) ?></option>
                    <option  value="300"><?= $T->String( array('str_filter_d_or_above', '3') ) ;?></option>
                    <option  value="0"><?= $T->String('str_filter_all') ;?></option>
                    </select>
                </div>
    <? } else { ?>
                <div class="radio_opt">
                    <span  class="opt_label"><?= $T->String('str_recommends') ?>:</span>
                    <select  id="score" name="score">
                    <option  value="20"><?= $T->String( array('str_filter_d_or_above', '20') ) ?></option>
                    <option  value="10"><?= $T->String( array('str_filter_d_or_above', '10') ) ?></option>
                    <option  value="5" selected="selected"><?= $T->String( array('str_filter_d_or_above', '5') ) ;?></option>
                    <option  value="0"><?= $T->String('str_filter_all') ;?></option>
                    </select>
                </div>
<?  } 
} // ratings enabled 
?>
                <div class="radio_opt">
                    <div  id="countresults"></div>
                </div>

                <div  id="gobuttons" >
                    <a  href="javascript://radio play" id="mi_play_page"><span><?= $T->String('str_play')?></span></a>
                    <a  href="" id="mi_stream_page"><span><?= $T->String('str_stream')?></span></a>
                    <a  href="" id="mi_podcast_page"><span><?= $T->String('str_podcast')?></span></a>
                </div>

            </div> <!-- channel_content aka when in doubt thrown a div around it -->
        </div> <!-- djback/box -->
    </form>
</div><!-- radio container -->

<script type="text/javascript">
stream_url = '<?= ccl('api','query','stream.m3u') . $A['q'] ?>';
sitePromoTag = '<?= empty($A['site_promo_tag']) ? 'site_promo' : $A['site_promo_tag'] ?>';
</script>
<script  src="<?= $T->URL('js/radio.js'); ?>"></script>
