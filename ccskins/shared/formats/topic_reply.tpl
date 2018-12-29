<?/*
[meta]
    type = topic_format
    desc = _('Topic text for reply form [ids = topic]')
    dataview = topics
    required_args = ids
[/meta]
*/?>
<link rel="stylesheet" type="text/css" title="Default Style" href="<?= $T->URL('css/topics.css'); ?>" />

<div class="topic_reply_head light_bg">
<h3 class="dark_bg light_color">%text(str_topic_reply_head)%</h3>
<div class="topic_reply_text">
%(records/0/topic_text_html)%
</div>
</div>
