<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

/*%%
[meta]
    type = template_component
    desc = _('Playlist Reorder')
    dataview = playlist_reorder
[/meta]
%%*/?>
<!-- template playlist_reorder -->
<style type="text/css">
ul.ddex    { list-style: none; padding: 0px; width: 50%; margin:0px auto;}
ul.ddex li { padding: 1px; margin: 2px; border: 1px solid #999}
div.fn {
    font-size: 0.8em;
    margin-bottom: 3px;
    cursor: move;  
    overflow: hidden;
}
.cmd_link {
    width: 12em;
    text-align: center;
}
</style>
<div class="cc_form_about box" style="width:30%;" ><?= $T->String('str_pl_drag_to_reorder') ?></div>
<ul id="fo" class="ddex">
    %loop(records,R)%
    <li class="file_desc dark_border light_bg dark_color" id="fo_%(#i_R)%">
    <div class="fn"><b>%(#R/upload_name)%</b> by %(#R/username)%</div>
    </li>
    %end_loop%
</ul>

<div class="cmd_link">
    <a id="submit_file_order" class="cc_gen_button" href="javascript://playlist record">%text(str_pl_save_order)%</a>
</div>

<script type="text/javascript">
function on_reorder_click()
{
    var _file_order = Sortable.serialize('fo');
    var url = home_url + 'playlist/editorder/%(playlist_id)%/cmd' + q + _file_order;
    window.location.href = url;
    return false;
}
Event.observe('submit_file_order','click',on_reorder_click);
Sortable.create("fo",{constraint:false});
</script>
