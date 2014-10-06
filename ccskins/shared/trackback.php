<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_component
    desc = _('Trackback feature')
    dataview = trackback
    embedded = 1
[/meta]
[dataview]
function trackback_dataview()
{
    $sql =<<<EOF
        SELECT upload_id, upload_name, user_real_name, user_name
            FROM cc_tbl_uploads
            JOIN cc_tbl_user ON upload_user=user_id
            %where%
EOF;
    
    return array( 'sql' => $sql,
                   'e'  => array() );
}
[/dataview]
*/

$ttype = $_GET['ttype'];
$R     =& $A['records'][0];
$text  = $T->String( array( 'str_trackback_' .$ttype, '<span>'.$R['upload_name'].'</span>', 
                    '<span>'.$R['user_real_name'].'</span>' ) );
$title = $T->String('str_trackback_title_' .$ttype);
?>
<!-- template trackback -->

<div id="trackback_response">
</div>
<form id="trackback_form" name="trackback_form" style="z-index:200;display:block;">
    <div id="trackback_help" name="trackback_help">
        <h2><?= $title ?></h2>
        <?= $text ?>
    </div>
    <input type="hidden" name="trackback_name" />

    <div class="f"><?= $T->String('str_trackback_artist_' . $ttype); ?>
    <input id="trackback_artist" name="trackback_artist" /></div>

    <div class="f"><?= $T->String('str_trackback_link_' . $ttype); ?>
    <input id="trackback_link" name="trackback_link" /></div>

<? if( $ttype != 'video' ) { ?>

    <input type="hidden" name="trackback_media" />

<? } else { ?>

    <div class="f"><?= $T->String('str_trackback_media_video'); ?>
    <textarea id="trackback_media" name="trackback_media"></textarea></div>

<? } ?>

    <div class="f"><?= $T->String('str_trackback_your_name'); ?>
    <input id="trackback_your_name" name="trackback_your_name" 
        <?= empty($A['user_real_name']) ? '' : "value=\"" . $A['user_real_name'] . '"'; ?> /></div>

    <div class="f"><?= $T->String('str_trackback_email'); ?>
    <input id="trackback_email" name="trackback_email" 
        <?= empty($A['user_email']) ? '' : "value=\"" . $A['user_email'] . '"'; ?> /></div>

    <div class="f">
        <a id="trackback_submit" class="cc_gen_button" href="javascript://submit track"><span><?= $T->String('str_trackback_submit'); ?></span></a>
    </div>
</form>
<? if( ($ttype == 'remix') && !empty($A['logged_in_as']) && ($R['user_name'] != $A['logged_in_as']) ) { ?>
   <div style="clear:both">&nbsp;</div>
   <a class="remix_up_link" href="<?= ccl('submit','remix',$A['logged_in_as'], $R['upload_id']) ?>">
        <?= $T->String(array('str_trackback_remix_upload','',$R['upload_name'],'')) ?>
    </a>
<? } ?>

<script type="text/javascript">

function on_track(resp)
{
    if( resp.responseText != 'ok' )
    {
        $('trackback_response').innerHTML = str_trackback_error + '<br />' + resp.responseText;        
    }
    else
    {
        var vars = [ '', eval('str_trackback_type_<?= $ttype ?>'), 
                     '<?= addslashes($R['upload_name']) ?>', 
                     '<?= $R['user_real_name'] ?>' ];

        var text = new Template( str_trackback_response ).evaluate( vars );
        Modalbox.alert(text);
    }   
}
 

function submit_tb()
{
    var params = Form.serialize('trackback_form');
    var p = params.parseQuery();
    if( !p.trackback_email.length )
    {
        alert( str_trackback_no_email );
        $('trackback_email').focus();
        return false;
    }
    if( !p.trackback_link.length )
    {
        alert( str_trackback_no_link );
        $('trackback_link').focus();
        return false;
    }

    $('trackback_response').innerHTML = str_thinking;
    var cd = new Date();
    var url = home_url + 'track/<?= $ttype ?>/' + <?= $R['upload_id'] ?> + q + 'cd=' + cd.getTime();;
    new Ajax.Request( url, { onComplete: on_track, parameters: p } );
    return false;
}
Event.observe('trackback_submit','click',submit_tb);
</script>

