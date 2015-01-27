%%
[meta]
    type = template_component
    desc = _('dump of tags')
    dataview = tags
[/meta]
%%

%loop(records,R)%
%(#R/tags_tag)% - %(#R/tags_count)% - %(#R/tag_category)%<br />
%end_loop%
