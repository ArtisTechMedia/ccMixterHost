<?/*
[meta]
    type = extras
    desc = _('Feed links')
[/meta]
*/?>

%if_not_null(feed_links)%
    <p>%text(str_feeds)%</p>
    <ul>
    %loop(feed_links,feed)%
        <li><a rel="%(#feed/rel)%" type="%(#feed/type)%" href="%(#feed/href)%" title="%(#feed/title)%">%(#feed/link_text)%</a></li>
    %end_loop%
    </ul>
%end_if%