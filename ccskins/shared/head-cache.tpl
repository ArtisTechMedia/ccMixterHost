%%
[meta]
    type = head
    desc = _('Cache common scripts and styles (experimental)')
[/meta]
%%
<head> 

%if_empty(page-caption)%
  <title>%text(site-title)% - %text(site-description)%</title>
%else%
  <title>%text(site-title)% - <?= $T->String($A['page-caption']) ?></title>
%end_if%
 
%if_not_empty(site-meta-keywords)%
    <meta name="keywords" content="%(site-meta-keywords)%" />
%end_if%
%if_not_empty(site-meta-description)%
    <meta name="description" content="%(site-meta-description)%" />
%end_if%

%if_not_empty(extra-meta)%
    %(extra-meta)%
%end_if%

<meta name="robots" content="index, follow" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
//<!--
var home_url  = '%(home-url)%';
var root_url  = '%(root-url)%';
var query_url = '%(query-url)%';
var q         = '%(q)%';
var user_name = %if_not_null(logged_in_as)% '%(logged_in_as)%'; %else% null; %end_if%

//-->
</script>

%loop(feed_links,feed)%
    <link rel="%(#feed/rel)%" type="%(#feed/type)%" href="%(#feed/href)%" title="%(#feed/title)%"/>
%end_loop%

%loop(head_links,head)%
    <link rel="%(#head/rel)%" type="%(#head/type)%" href="%(#head/href)%" title="%(#head/title)%"/>
%end_loop%

<? $T->CachedHead(); ?>

</head>
