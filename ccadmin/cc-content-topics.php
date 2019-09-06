<?

function inject_content_topics()
{
    $data = array(
        array( 'news',          '2008-07-14 17:12:00', 'ccHost is up and running!',
          "Congratulations on getting ccHost up and running. The news feature is part of ".
          "the new [url=/admin/content]Content Manager[/url]." ),
        array( 'news',          '2008-07-14 17:14:00', 'Radiohead releases video sources under CC',
           "Creative Commons is [url=http://creativecommons.org/weblog/entry/8476]reporting[/url] that ".
           "Radiohead has released the sources to their video \"House of Cards\" under a CC license." ),
        array( 'home',          '2009-05-07 13:00:00', 'Welcome to ccHost 5',
           "Welcome to ccHost [define=CC_HOST_VERSION][/define] and congratulations on your installation of ".
           "[b][var=site-title][/var][/b]\r\n\r\nHere are some helpful documentation links:\r\n\r\n[big]".
           "[url=http://wiki.creativecommons.org/Cchost/Documentation]ccHost Documentaton Wiki[/url]\r\n\r\n".
           "[url=http://wiki.creativecommons.org/Cchost/guide/Customize]Customizing your installation[/url]".
           "\r\n\r\n[url=http://wiki.creativecommons.org/Cchost/guide/Troubleshooting]Troubleshooting[/url]".
           "\r\n\r\n[url=http://wiki.creativecommons.org/Cchost#Contacting]Contact the team[/url][/big]" ),
        array( 'home',          '2009-05-07 21:40:00', 'New Skin Engine',
           "[right][skinimg=layouts/images/layout005.gif][/skinimg]\r\n[skinimg=layouts/images/layout023.gif]".
           "[/skinimg]\r\n[skinimg=layouts/images/layout036.gif][/skinimg][/right]\r\n\r\n[indent=15]The new ".
           "skin engine allows for easy customization for admins and web developers. Shipping in the box are ".
           "40 layouts, 3 string profiles (generic media sites, music sites and image sites), configurable tab ".
           "layouts, form layouts, etc. [b][cmd=admin/skins]Start here...[/cmd][/b]." ),
        array( 'home',          '2009-05-07 22:01:00', 'Content Manager',
            "Admins can create pages in the system without any knowledge of HTML or coding. This page and the ".
            "content on it was created using it. You can see how it was by [b][cmd=admin/content]poking around ".
            "here[/cmd][/b].\r\n\r\nSee the ccHost Wiki [b][url=http://wiki.creativecommons.org/Cchost/concepts/".
            "Content]documentation[/url][/b] for a [b][url=http://wiki.creativecommons.org/Cchost/admin/".
            "Content_Manager]step by step tutorial[/url][/b] on how to create content topics and a page to display ".
            "them (like this page).\r\n\r\nYou don''t have to use the content manager to create pages in the ".
            "system. Read about how to [url=http://wiki.creativecommons.org/Cchost/Static_HTML_Pages]add HTML/PHP ".
            "files directly[/url].\r\n\r\nFor a slightly more technical discussion of how all content (including ".
            "user uploads) is handled, see the general discussion on content at the wiki." ),
        array( 'welcome',       '2009-05-08 14:27:00', 'Welcome New Member',
            "(for admins: use the [cmd=admin/content]Content Manager[/cmd] to edit this welcome screen)\r\n\r\n".
            "Hello [b][var=user_real_name][/var][/b] and welcome to [b][var=site-title][/var][/b]!\r\n\r\nYour ".
            "profile address is: [cmdurl=people/[var=user_name][/var]][/cmdurl]\r\n\r\nHere''s some stuff you ".
            "might want to do:\r\n\r\n[big][cmd=people/profile]Edit your profile[/cmd][/big] to change your ".
            "password, upload an avatar, etc.\r\n\r\n[big][cmd=preferences]Customize the site[/cmd][/big] decide ".
            "what you see every time you log in.\r\n\r\n[big][cmd=people/notify/edit]Edit your notifications[/cmd]".
            "[/big] tells us when you want to be notified in email.\r\n\r\n[big][cmd=submit]Submit files[/cmd]".
            "[/big] Start uploading your content!" ),
        array( 'home',          '2009-05-07 17:00:00', 'Site News',
            "[query=t=news&type=news&limit=4][/query]"), 
        array( 'sidebar_blurb', '2009-05-13 17:51:00', 'a blurb',
            "A sidebar blurb is an excellent way to dispense site news. Edit blurbs using the ".
            "[cmd=admin/content]Content Manager[/cmd]. Change their placement (or remove them) with ".
            "[cmd=admin/extras]Sidebar Extras[/cmd]" ),
        array( 'news',          '2009-05-10 00:31:00', 'RiP: Movie Making the Rounds',
           "The movie [url=http://www.ripremix.com/]RiP: A Remix Manifesto[/url] is tearing up the festivals ".
           "and brings to light the dangers of treating creativity as property." ),
        );

    require_once('cchost_lib/ccextras/cc-topics.inc');
    $topics =& CCTopics::GetTable();
    foreach( $data as $D )
    {
        $args['topic_user'] = 1;
        $args['topic_type'] = $D[0];
        $args['topic_date'] = $D[1];
        $args['topic_name'] = $D[2];
        $args['topic_text'] = $D[3];
        $topics->Insert($args,0);
    }
}

?>
