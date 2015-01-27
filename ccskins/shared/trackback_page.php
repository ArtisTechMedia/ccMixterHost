<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_component
    desc = _('Trackback page')
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

/* video, podcast, remix, album */

$R     =& $A['records'][0];
$title = $T->String('str_trackback_title');
?>
<!-- template trackback page-->
<link rel="stylesheet" type="text/css" title="Default Style" href="<?= $T->URL('css/trackback.css') ?>" />

<script type="text/javascript">

var tb_help = {    
    <?
        $comma = '';
        foreach( array('video','podcast','remix','album') as $htype )
        {
            $text  = $T->String( array( 'str_trackback_' .$htype, '<span>'.$R['upload_name'].'</span>', 
                    '<span>'.$R['user_real_name'].'</span>' ) );
            
            $text = addslashes($text);
            
            print "{$comma}\n    {$htype}: '{$text}'";
            
            $comma = ',';
        }
    ?>

}

var ret_url  = '<?= empty($_GET['returl']) ? 'null' : urldecode($_GET['returl']) ?>';
var ret_text = '<?= empty($_GET['rett']) ? 'previous page' : ($_GET['rett']) ?>';

function on_type_change()
{
    var cb = $('ttype');
    var ttype = cb.options[cb.selectedIndex].value;
    
    var type_text = $('type_text');
    type_text.innerHTML = tb_help[ttype];
    
    var fields = gen_tb_fields(ttype);
    var fields_div = $('tb_fields');
    fields_div.innerHTML =fields;
    
    <?
        if( !empty($A['logged_in_as']) && ($R['user_name'] != $A['logged_in_as']) )
        {
            ?>
            
    if( ttype == 'remix' )
    {
        html = '<div style="clear:both">&nbsp;</div>'
            +   '<a class="remix_up_link" href="<?= ccl('submit','remix', $A['logged_in_as'] , $R['upload_id']) ?>">'
            +       '<?= $T->String(array('str_trackback_remix_upload','',$R['upload_name'],'')) ?>'
            +   '</a>';

        $('remix_note').innerHTML = html;
    }
    else
    {
        $('remix_note').innerHTML = '';
    }
            <?
        }
    ?>
}

function gen_tb_fields(ttype)
{
    var artist_str = eval('str_trackback_artist_' + ttype);
    var link_str   = eval('str_trackback_link_' + ttype);
    
    var html =    '<div class="f">'+artist_str+'<input id="trackback_artist" name="trackback_artist" /></div>'
                + '<div class="f">'+link_str+'<input id="trackback_link" name="trackback_link" /></div>';

    if( ttype != 'video' )
    {
        html += '<input type="hidden" name="trackback_media" />';
    }
    else
    {

        html += '<div class="f">'+str_trackback_media_video+'<textarea id="trackback_media" name="trackback_media"></textarea></div>';
    }

    return html;    
}

function on_track(ttype,resp)
{
    if( resp.responseText != 'ok' )
    {
        $('trackback_response').innerHTML = str_trackback_error + '<br />' + resp.responseText;        
    }
    else
    {
        msg = "Thanks for the trackback!";
        if( ret_url )
        {
            msg += " Go back to <a href=\""+ret_url+"\">"+ret_text+"</a>";            
        }
        
        $('trackback_response').innerHTML = msg;
        
        var vars = [ '', eval('str_trackback_type_' + ttype), 
                     '<?= addslashes($R['upload_name']) ?>', 
                     '<?= $R['user_real_name'] ?>' ];

        var text = new Template( str_trackback_response ).evaluate( vars );
        Modalbox.alert(text);
    }   
}
 

function submit_tb()
{
    var cb = $('ttype');
    var ttype = cb.options[cb.selectedIndex].value;

    
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
    var url = home_url + 'track/'+ttype+'/' + <?= $R['upload_id'] ?> + q + 'cd=' + cd.getTime();;
    new Ajax.Request( url, { onComplete: on_track.bind(this,ttype), parameters: p } );
    $('trackback_form').hide();
    $('remix_note').hide();
    return false;
}
</script>


<div id="trackback_response">
</div>
<form id="trackback_form" name="trackback_form" style="z-index:200;display:block;margin:0px auto;width:80%;">
    <input type="hidden" name="trackback_name" />
    
    <div>
        Trackback type: <select onchange='on_type_change()' id='ttype'>
                            <option value="video">Video</option>
                            <option value="podcast">Podcast</option>
                            <option value="remix">Remix</option>
                            <option value="album">Album</option></select>
    </div>
    <div id="trackback_help" name="trackback_help">
        <div id="type_text" class="cc_form_help box" style="width:60%" ></div>
    </div>
    <div id="tb_fields">
        
    </div>
    
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
    <div id="remix_note">
    </div>
    
    

<script type="text/javascript">
Event.observe('trackback_submit','click',submit_tb);
on_type_change();
</script>

