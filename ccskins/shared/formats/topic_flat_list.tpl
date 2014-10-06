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


%call('topic_list.tpl')%

