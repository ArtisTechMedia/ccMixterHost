<?/*
[meta]
    type = template_component
    desc = _('Topic flat list')
    datasource = topics
    dataview = topic_flat_list
[/meta]
*/
?>
<!-- template topic_flat_list (tpl)-->

%if_null(records)%
    <div>There's no topics here!</div>
    %return%
%end_if%

<script type="text/javascript">
var cc_show_xlat = function( orig_id, xlat_id, is_native, lang_name )
{
    var url = query_url + 't=topic&f=html&ids=' + xlat_id;
    new Ajax.Updater( $('topic_text_' + orig_id), url, { method: 'get' } );

    var edit = $('edit_link_' + orig_id);

    if( edit )
    {
        if( orig_id == xlat_id ) // this is native topic
        {
            edit.style.display = 'none';
        }
        else
        {
            edit.innerHTML = '<span>' + str_topic_edit + ': ' + lang_name + '</span>';
            edit.href = home_url + 'topics/edit/' + xlat_id;
            edit.style.display = '';
        }

    }
}

</script>

%map(show_topic_name,'1')%
%call('topic_list.tpl')%

<script type="text/javascript">
if( window.user_name && userHookup )
{
    new userHookup('topic_cmds','noreply=1&xlat=1&ids=<?= join(',',$A['thread_ids']) ?>');
}
</script>