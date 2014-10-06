<?/*
[meta]
    type = extras
    desc = _('Hightest rated [2 weeks]')
    allow_user = 1
[/meta]
*/?>

<p>%text(str_highest_rated)%</p>
<? $charts = cc_query_fmt('sort=rank&sinced=2 weeks ago&dataview=links&limit=6'); ?>
<ul>
%loop(#charts,CI)%
  <li><a class="cc_file_link" href="%(#CI/file_page_url)%">%chop(#CI/upload_name,10)%</a></li>
%end_loop%
%if_null(#charts)%
  <li>%text(str_no_chart)%</li>
%end_if%
</ul>
