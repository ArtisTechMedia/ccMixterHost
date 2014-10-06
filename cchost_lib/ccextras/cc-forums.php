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
* $Id: cc-forums.php 12624 2009-05-18 15:47:40Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-extras-events.php');

define('CC_MAX_USER_TOPICS', 30 );


CCEvents::AddHandler(CC_EVENT_SEARCH_META,          array( 'CCForums',  'OnSearchMeta') );

CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE,array( 'CCForums',  'OnFilterUserProfile') );
CCEvents::AddHandler(CC_EVENT_USER_PROFILE_TABS,  array( 'CCForums',  'OnUserProfileTabs') );

CCEvents::AddHandler(CC_EVENT_TOPIC_DELETE,       array( 'CCForumAPI' , 'OnTopicDelete'), 'cchost_lib/ccextras/cc-forums.inc' );
CCEvents::AddHandler(CC_EVENT_POST_TOPIC_DELETE,  array( 'CCForumAPI' , 'OnPostTopicDelete'), 'cchost_lib/ccextras/cc-forums.inc' );
CCEvents::AddHandler(CC_EVENT_TOPIC_REPLY,        array( 'CCForumAPI',  'OnTopicReply'),  'cchost_lib/ccextras/cc-forums.inc'  );

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCForumsAdmin',  'OnMapUrls'),    'cchost_lib/ccextras/cc-forums-admin.inc');
CCEvents::AddHandler(CC_EVENT_ADMIN_MENU,         array( 'CCForumsAdmin',  'OnAdminMenu'), 'cchost_lib/ccextras/cc-forums-admin.inc');


/**
* Forums API
*
* A delegator class that catches events and forwards the requests
* to the code that does the real work. @see CCForumsAPI
*/
class CCForums
{
    function OnSearchMeta(&$search_meta)
    {
        $count = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_forum_threads');
        if( empty($count) )
            return;
        $search_meta[] = 
            array(
                'template'   => 'search_forums',
                'title'      => 'str_search_forums',
                'datasource' => 'topics',
                'group'      => 'forum',
                'match'      => 'topic_name,topic_text',
            );
    }

    function OnUserProfileTabs( &$tabs, &$record )
    {
        if( empty($record['user_id']) )
        {
            $tabs['topics'] = 'Topics';
            return;
        }

        if( empty($record['user_num_posts']) )
            return;

        $tabs['topics'] = array(
                    'text' => 'Topics',
                    'help' => 'Forums Topics',
                    'tags' => 'topics',
                    'access' => 4,
                    'function' => 'url',
                    'user_cb' => array( 'CCForumAPI', 'User' ),
                    'user_cb_mod' => 'cchost_lib/ccextras/cc-forums.inc',
            );
    }

    function OnFilterUserProfile(&$records)
    {
        $record =& $records[0];
        if( $record['user_num_posts'] == 0 )
            return;

        $name = $record['user_real_name'];

        $url = ccl('people',$record['user_name'],'topics');

        $link1 = "<a href=\"$url\">";
        $link2 = '</a>';
        
        $text = sprintf( _("%s has posted %s%d forum messages%s"), 
                           $name, $link1,  
                                      $record['user_num_posts'], $link2 );

        $record['user_fields'][] = array( 'label' => 'str_user_forum_posts', 
                                          'value' => $text,
                                          'id' => 'user_post_stats' );
    }


}

?>
