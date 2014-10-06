<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
    [file_id] => 12744
    [file_upload] => 12735
    [file_name] => admin_-_2fingersGlow.png
    [file_nicname] => png
    [file_format_info] => Array
        (
            [media-type] => image
            [format-name] => image-png-png
            [default-ext] => png
            [mime_type] => image/png
            [dim] => Array
                (
                    [0] => 87
                    [1] => 91
                )
      )
    [file_filesize] =>  (6KB)
    [file_order] => 0
    [file_extra] => Array
        (
            [ccud] => sample   // this is optional
        }
*/


$submit_types = cc_get_submit_types();

?>
<!-- template edit_files_links -->
<style type="text/css">
ul.ddex    { list-style: none; padding: 0px; width: 70%; margin:0px auto;}
ul.ddex li { padding: 4px; margin: 5px; border: 1px solid #999}
div.drag_handle { cursor: move;  }
div.fn {
    font-size: 1.2em;
    margin-bottom: 6px;
}
li.file_desc div.c {
    float: left;
    margin: 0px 0px 0px 20px;
    padding: 3px 2px 7px 2px;
}

.drag_handle {
    border: 1px solid;
}

li.file_desc div.c a {
    padding: 2px;
}

div.cmd_link{
    clear: both;
    margin: 1.0em;
}
div.cmd_link a {
    float: left;
    margin: 1.3em;
}
div.addtype {
    float: right;
}
</style>

<ul id="file_order" class="ddex">
%loop(field/files,F)%
    <li class="file_desc dark_border light_bg dark_color" id="file_order_%(#F/file_id)%_%(#i_F)%">
        <div class="fn">
            <div class="addtype" id="type_cmd_%(#F/file_id)%">
                %text(str_files_type)%: 
                %if_not_null(#F/file_extra/type)%
                    %map(ccudtype,#F/file_extra/type)%
                %else%
                    %map(ccudtype,'')%
                %end_if%
                <select id="type_pick_%(#F/file_id)%" class="type_picker">
                %loop(#submit_types,st)%
                    <? $selected = ($k_st == $A['ccudtype']) ? 'selected="selected"' : ''; ?>
                    <option value="%(#k_st)%" %(#selected)%>
                        %text(#st)%
                    </option>
                %end_loop%
                </select>
            </div>%(#F/file_name)%
        </div>
        <div style="clear:both;font-size:3px;height:3px;">&nbsp;</div>
        <div style="margin: 6px;clear:both;">
            <? if( $c_F > 1 ) { ?>
                <div class="c light_border drag_handle">%text(str_file_drag_this)%</a></div>
            <? } ?>
            <div class="c light_border">
                <a class="small_button" 
                    href="%(field/urls/upload_nicname_url)%/%(#F/file_id)%"><span>%text(str_file_nicname_this)%</span></a> %(#F/file_nicname)%
            </div>
            <div class="c light_border"><a class="small_button" 
                href="%(field/urls/upload_replace_url)%/%(#F/file_id)%"><span>%text(str_file_replace_this)% </span></a></div>
            <div class="c light_border" id="del_cmd_%(#i_F)%" <? if( $i_F == 1 ) { ?>style="display:none"<?}?>>
                <a class="small_button" href="%(field/urls/upload_delete_url)%/%(#F/file_id)%"><span>%text(str_file_delete_this)%</span></a></div>
            <div style="clear:both;font-size:3px;height:3px;">&nbsp;</div>
        </div>
    </li>
%end_loop%
</ul>
<div class="cmd_link">
    <a href="%(field/urls/upload_new_url)%" class="cc_gen_button"><span>%text(str_file_add_new)%</span></a>
</div>
<script type="text/javascript">
    function on_change_type(id)
    {
        var sel  = $('type_pick_' + id);
        var type = sel.options[ sel.selectedIndex ].value;
        if( !type )
            type = '-';
        var url  = '%(field/urls/file_change_type_url)%' + '/' + id + '/' + type;
        new Ajax.Request( url, { method: 'get' } );
    }

    $$('.type_picker').each( function(e) {
        var id = e.id.match(/[0-9]+$/);
        Event.observe( e,'change', on_change_type.bind(this,id) );
    });

</script>
<? if( count($A['field']['files']) > 1 ) { ?>
    <div class="cmd_link" id="submit_order_link" style="display:none">
        <a class="cc_gen_button" id="submit_file_order" href="javascript://submit order"><span>%text(str_file_submit_order)%</span></a>
    </div>
    <script type="text/javascript">
    var _first_file = 1;
    var upload_id = %(field/upload_id)%;

    function on_reorder_click()
    {
        var _file_order = Sortable.serialize('file_order');
        var url = '%(field/urls/upload_jockey_url)%' + q + _file_order;
        new Ajax.Request( url, { method:'get' } );
    }

    Event.observe('submit_file_order','click',on_reorder_click);

    function on_file_drop(a)
    {
        $('submit_order_link').style.display = 'block';

        try {
            if( _first_file != a.childNodes[0].id )
            {
                var id_old = _first_file;
                var id = a.childNodes[0].id;
                var id_new = id.match(/([0-9]+)$/)[1];
                $('del_cmd_' + id_old).style.display = 'block';
                $('del_cmd_' + id_new).style.display = 'none';
                _first_file = id_new;
            }
        } catch(e) {
            alert(e);
        }
    }

    Sortable.create("file_order",{handle:'drag_handle',constraint:false,onUpdate: on_file_drop});

    </script>
<? } ?>

<!--[if lt IE 7.]> 
<script>
    $$('.file_desc').each( function(e) {
        e.style.backgroundColor = 'transparent';
    });
</script>
<![endif]-->
