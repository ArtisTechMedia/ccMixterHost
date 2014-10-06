/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: sharesites.js 12966 2009-07-16 19:03:57Z fourstones $
*
*/
//<!--
/*
    If you want to modify this file, COPY it first (using
    the same name as this file) to your local_files/viewfiles 
    directory and work on that version.
*/
var ccShareSites = 
[
    [
        [
            'facebook',                                                 // unique ID
            'facebook',                                                 // display text 
            'http://www.facebook.com/share.php?u=%url%&t=%title%',     // bookmark URL
            root_url + '/ccskins/shared/images/shareicons/facebook.png' // icon
        ], 
        [
            'twitter' ,
            'twitter',
            'http://twitter.com/home?status=%title%+%url%',
            root_url + '/ccskins/shared/images/shareicons/twitter.png'
        ],
        [
            'delicious',                                        // unique ID
            'del.icio.us',                                      // display text 
            'http://del.icio.us/post?url=%url%&title=%title%',  // bookmark URL
            root_url + '/ccskins/shared/images/shareicons/delicious.gif'     // icon
        ], 
        [
            'digg' ,
            'Digg',
            'http://digg.com/submit?phase=2&url=%url%&title=%title%',
            root_url + '/ccskins/shared/images/shareicons/digg.gif'
        ],
    ],
    [
        [
            'yahoo_myweb',
            'Yahoo! My Web',
            'http://myweb2.search.yahoo.com/myresults/bookmarklet?u=%url%&t=%title%',
            root_url + '/ccskins/shared/images/shareicons/yahoo_myweb.gif'
        ],
        [
            'stumbleupon',
            'StumbleUpon', 
            'http://www.stumbleupon.com/submit?url=%url%&title=%title%',
            root_url + '/ccskins/shared/images/shareicons/stumbleupon.gif'
        ], 
        [
            'google_bmarks',
            'Boooookmarks', 
            'http://www.google.com/bookmarks/mark?op=edit&bkmk=%url%&title=%title%',
            root_url + '/ccskins/shared/images/shareicons/google_bmarks.gif'
        ], 
        [
            'technorati',
            'Technorati', 
            'http://www.technorati.com/faves?add=%url%',
            root_url + '/ccskins/shared/images/shareicons/technorati.gif'
        ]
    ],
    [
        /*
        [
            'blinklist',
            'BlinkList',
            'http://blinklist.com/index.php?Action=Blink/addblink.php&Url=%url%&Title=%title%',
            root_url + '/ccskins/shared/images/shareicons/blinklist.gif'
        ], 
        [
            'newsvine',
            'Newsvine', 
            'http://www.newsvine.com/_wine/save?u=%url%&h=%title%',
            root_url + '/ccskins/shared/images/shareicons/newsvine.gif'
        ],
        [
            'netscape',
            'Netscape',
            'http://www.netscape.com/submit/?U=%url%&T=%title%',
            root_url + '/ccskins/shared/images/shareicons/netscape.gif'
        ],
        */
        [
            'ping_fm',
            'Ping FM',
            'http://ping.fm/ref?link=%url%&title=%title%&body=%site_title%',
            root_url + '/ccskins/shared/images/shareicons/ping_fm.png'
        ],
        [
            'furl' ,
            'Furl',
            'http://furl.net/storeIt.jsp?u=%url%&t=%title%',
            root_url + '/ccskins/shared/images/shareicons/furl.gif'
        ],
        [
            'magnolia',
            'ma.gnolia',
            'http://ma.gnolia.com/bookmarklet/add?url=%url%&title=%title%',
            root_url + '/ccskins/shared/images/shareicons/magnolia.gif'
        ], 
        [
            'reddit',
            'reddit',
            'http://reddit.com/submit?url=%url%&title=%title%',
            root_url + '/ccskins/shared/images/shareicons/reddit.gif'
        ]
    ],
    [
        [
            'windows_live',
            'Windows Live', 
            'https://favorites.live.com/quickadd.aspx?marklet=1&mkt=en-us&url=%url%&title=%title%&top=1',
            'https://favorites.live.com/favicon.ico'
        ],
        [
            'tailrank',
            'Tailrank', 
            'http://tailrank.com/share/?link_href=%url%&title=%title%',
            root_url + '/ccskins/shared/images/shareicons/tailrank.gif'
        ],
        [
            'bloglines',
            'Bloglines', 
            'http://www.bloglines.com/sub/%url%',
            'http://www.bloglines.com/favicon.ico'
        ],
        [
            'slashdot',
            'Slashdot', 
            'http://slashdot.org/bookmark.pl?url=%url%&title=%title%',
            'http://images.slashdot.org/favicon.gif'
        ]
    ]
];
//-->
