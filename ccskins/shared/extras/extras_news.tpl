<?/*
[meta]
    type = extras
    desc = _('News')
    allow_user = 1
[/meta]
*/?>
<style>
.topic_dump_name {
    display: block;
    font-weight: bold;
}
.topic_dump div {
    margin-bottom: 0.5em;
}

</style>
<p>%text(str_news)%</p>
<ul>
%query('f=embed&t=topic_dump&type=news&limit=5')%
</ul>
