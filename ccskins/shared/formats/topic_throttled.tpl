<?/*
[meta]
    type = topic_format
    desc = _('Insert for post abuse')
    dataview = topics
[/meta]
*/?>
%if_not_null(topic_throttled)%
<!-- topic_throttled.tpl -->
<link rel="stylesheet" type="text/css" title="Default Style" href="<?= $T->URL('css/topics.css'); ?>" />
<div class="topic_throttled_msg">
%if_null(topic-throttled-msg)%
You have been restricted from posting. If you feel you have received this message by mistake, please contact the admins
%else%
%(topic-throttled-msg)%
%end_if%
</div>
%end_if%
