<?
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
* $Id: cc-defines-access.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* Core defines for the system
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**#@+
* Access flags
*/
define('CC_MUST_BE_LOGGED_IN',   1 );
define('CC_ONLY_NOT_LOGGED_IN',  2 );
define('CC_DONT_CARE_LOGGED_IN', 4 );
define('CC_ADMIN_ONLY',          8 );
define('CC_OWNER_ONLY',          0x10 );
define('CC_SUPER_ONLY',          0x1000 );

define('CC_DISABLED_MENU_ITEM', 0x20 );
define('CC_DYNAMIC_MENU_ITEM',  0x40 );
/**#@-*/


/**#@+
* @access private
*
* These for intended for documenation only
*/
define('CC_AG_ADMIN_MISC', '_mad' );
define('CC_AG_API', '_api' );
define('CC_AG_COLLAB', 'clb' );
define('CC_AG_CONFIG', '_cfg' );
define('CC_AG_CONTENT_MAN', '_cntm' );
define('CC_AG_CONTESTS', '_cnt' );
define('CC_AG_deprecated', 'dpt' );
define('CC_AG_ED_PICKS', '_edp' );
define('CC_AG_EDPICK',CC_AG_ED_PICKS ); 
define('CC_AG_FEEDS', '_fds' );
define('CC_AG_FEED', CC_AG_FEEDS );
define('CC_AG_FILE_API', '_fapi' );
define('CC_AG_FORUMS', '_fum' );
define('CC_AG_FORUM', CC_AG_FORUMS ); 
define('CC_AG_HIDI', '_hdi' );
define('CC_AG_MISC_ADMIN', CC_AG_ADMIN_MISC );
define('CC_AG_NAVTABS', '_nav');
define('CC_AG_PLAYLIST', '_play' );
define('CC_AG_QUERY', '_agq' );
define('CC_AG_RATINGS', '_rww' );
define('CC_AG_REVIEWS', CC_AG_RATINGS );
define('CC_AG_SAMPLE_POOLS', '_pls' );
define('CC_AG_SAMPLE_POOL', CC_AG_SAMPLE_POOLS );
define('CC_AG_RENDER', CC_AG_deprecated );
define('CC_AG_SEARCH', '_src' );
define('CC_AG_SKINS', '_skin' );
define('CC_AG_SKIN', CC_AG_SKINS );
define('CC_AG_SUBMIT_FORMS', '_frm' );
define('CC_AG_SUBMIT_FORM', CC_AG_SUBMIT_FORMS );
define('CC_AG_TAGS', '_tag' );
define('CC_AG_UPLOADS', '_ups' );
define('CC_AG_UPLOAD', CC_AG_UPLOADS );
define('CC_AG_USER', '_usr' );
define('CC_AG_VIEWFILE', '_vwf' );

 
function cc_get_access_groups()
{
    return array(  
        CC_AG_API          => _('API')   ,
        CC_AG_COLLAB       => _('Collaboration') ,
        CC_AG_CONFIG       => _('Site Admin'),
        CC_AG_CONTENT_MAN  => _('Content Manager')   ,
        CC_AG_CONTESTS     => _('Contests')   ,
        CC_AG_deprecated   => _('deprecated') ,
        CC_AG_ED_PICKS     => _('Ed Picks')   ,
        CC_AG_FEEDS        => _('Feeds')   ,
        CC_AG_FILE_API     => _('File API')   ,
        CC_AG_FORUMS       => _('Forums')   ,
        CC_AG_HIDI         => _('How I Did It')   ,
        CC_AG_NAVTABS      => _('Navigator Tabs')   ,
        CC_AG_PLAYLIST     => _('Playlists')   ,
        CC_AG_MISC_ADMIN   => _('Misc. Admin Commands')   ,
        CC_AG_QUERY        => _('Query')   ,
        CC_AG_REVIEWS      => _('Ratings/Reviews')   ,
        CC_AG_SAMPLE_POOLS => _('Sample Pools')   ,
        CC_AG_SKINS        => _('Skins'),
        CC_AG_SEARCH       => _('Search')   ,
        CC_AG_SUBMIT_FORMS => _('Submit Forms')   ,
        CC_AG_TAGS         => _('Folksonomy Tags')   ,
        CC_AG_UPLOADS      => _('Uploads')   ,        
        CC_AG_USER         => _('User')   ,
        CC_AG_VIEWFILE     => _('Viewfile')   ,
        );
}

function cc_get_roles()
{
    return array(
            CC_MUST_BE_LOGGED_IN   => _('Registered users'),
            CC_ONLY_NOT_LOGGED_IN  => _('Anonymous users only'),
            CC_DONT_CARE_LOGGED_IN => _('Everybody'),
            CC_ADMIN_ONLY          => _('Admin/Moderators'),
            CC_SUPER_ONLY          => _('Super admins')
            );
}

/**#@-*/

?>
