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
* $Id: cc-reviews.php 10356 2008-07-01 22:38:12Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-extras-events.php');

define('NUM_REVIEWS_PER_PAGE', 20);

/**
*/
CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU,        array( 'CCReviewsHV',  'OnUploadMenu')       );
CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE,array( 'CCReviewsHV',  'OnFilterUserProfile')      );
CCEvents::AddHandler(CC_EVENT_USER_PROFILE_TABS,  array( 'CCReviewsHV',  'OnUserProfileTabs')      );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCReview',  'OnMapUrls')         , 'cchost_lib/ccextras/cc-reviews.inc' );
CCEvents::AddHandler(CC_EVENT_GET_CONFIG_FIELDS,  array( 'CCReview' , 'OnGetConfigFields') , 'cchost_lib/ccextras/cc-reviews.inc' );
CCEvents::AddHandler(CC_EVENT_DELETE_UPLOAD,      array( 'CCReview',  'OnUploadDelete')    , 'cchost_lib/ccextras/cc-reviews.inc' );
CCEvents::AddHandler(CC_EVENT_TOPIC_DELETE,       array( 'CCReview' , 'OnTopicDelete')     , 'cchost_lib/ccextras/cc-reviews.inc' );
CCEvents::AddHandler(CC_EVENT_SEARCH_META,          array( 'CCReviewsHV',  'OnSearchMeta'));

CCEvents::AddHandler(CC_EVENT_FILTER_MACROS,            array( 'CCReviewsHV',  'OnFilterMacros') );
CCEvents::AddHandler(CC_EVENT_FITLER_REVIEWERS_UNIQUE,  array( 'CCReviewsHV',  'OnFilterReviewersUnique') );

class CCReviewsHV
{
    function OnSearchMeta(&$search_meta)
    {
        $search_meta[] = 
            array(
                'template'   => 'search_reviews',
                'title'      => 'str_search_reviews',
                'datasource' => 'topics',
                'group'      => 'review',
                'match'      => 'topic_name,topic_text',
            );
    }

    function OnFilterReviewersUnique(&$records)
    {
        $c = count($records);
        $k = array_keys($records);
        $reviewers = array();
        $found = 0;
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[ $k[$i] ];
            if(  count($reviewers) > 6 || in_array($R['topic_user'],$reviewers) )
            {
                unset($records[ $k[$i] ]);
                continue;
            }
            $reviewers[] = $R['topic_user'];
        }
    }


    function OnFilterUserProfile(&$records)
    {
        $record =& $records[0];

        $name        = $record['user_real_name'];
        $num_reviews = $record['user_num_reviews'];
        $num_reved   = $record['user_num_reviewed'];
        $url         = ccl('people',$record['user_name'],'reviews');
        $byurl       = url_args($url,'qtype=leftby');
        $forurl      = url_args($url,'qtype=leftfor');
        $linkby      = "<a href=\"$byurl\">";
        $linkfor     = "<a href=\"$forurl\">";
        $link_close  = "</a>";

        if( $num_reviews == 0 )
        {
            switch( $num_reved )
            {
                case 0:
                {
                    return;
                }

                case 1:
                {
                    // _('%s has not left any reviews and has been reviewed %sonce%s')
                    $text = array( 'str_reviews_stats_1', $name, $linkfor, $link_close );
                    break;
                }
                default:
                {   
                    // _('%s has not left any reviews and has been reviewed %s%d times%s')
                    $text = array( 'str_reviews_stats_2', $name, $linkfor, $num_reved, $link_close );
                    break;
                }
            }
        }
        else
        {
            if( $num_reviews == 1 )
            {
                switch( $num_reved )
                {
                    case 0:
                    {
                        // _('%s has left %s1 review% and has not been reviewed')
                        $text = array('str_reviews_stats_3',$name,$linkby,$link_close);
                        break;
                    }
                    case 1:
                        // _('%s has left %s1 review% and has been %sreviewed once%')
                        $text = array('str_reviews_stats_4', $name,$linkby,$link_close,$linkfor,$link_close);
                        break;
                    default:
                        // _('%s has left %s1 review% and has been reviewed %s%d times%')
                        $text = array('str_reviews_stats_5', $name,$linkby,$link_close,$linkfor,$num_reved,$link_close);
                        break;
                }
            }
            else
            {
                switch( $num_reved )
                {
                    case 0:
                    {
                        // _('%s has left %s%d reviews% and has not been reviewed')
                        $text = array('str_reviews_stats_6',$name,$linkby,$num_reviews,$link_close);
                        break;
                    }
                    case 1:
                    {
                        // _('%s has left %s%d reviews% and has been %sreviewed once%')
                        $text = array('str_reviews_stats_7', $name,$linkby,$num_reviews,$link_close,$linkfor,$link_close);
                        break;
                    }
                    default:
                    {
                        // _('%s has left %s%d reviews%s and has been reviewed %s%d times%')
                        $text = array('str_reviews_stats_8', $name,$linkby,$num_reviews,$link_close,$linkfor,$num_reved,$link_close);
                        break;
                    }
                }
            }
        }
        $record['user_fields'][] = array( 'label'   => 'str_review_stats', 
                                          'value'   => $text,
                                          'id'      => 'user_review_stats' );

    }

    function OnFilterMacros( &$records )
    {
        $k = array_keys($records);
        $c = count($k);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$k[$i]];
            $R['file_macros'][] = 'comment_thread';
            $R['comment_thread_url'] = ccl( 'reviews', 'thread', $R['upload_id'] );
        }
    }

    function OnUserProfileTabs( &$tabs, &$record )
    {
        if( empty($record['user_id']) )
        {
            $tabs['reviews'] = 'Reviews';
            return;
        }

        if( empty($record['user_num_reviews']) && empty($record['user_num_reviewed']))
            return;

        $tabs['reviews'] = array(
                    'text' => 'Reviews',
                    'help' => 'Reviews',
                    'tags' => 'reviews',
                    'access' => 4,
                    'function' => 'url',
                    'user_cb' => array( 'CCReview', 'Reviews' ),
                    'user_cb_mod' => 'cchost_lib/ccextras/cc-reviews.inc',
            );
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_MENU}
    * 
    * The handler is called when a menu is being displayed with
    * a specific record. All dynamic changes are made here
    * 
    * @param array $menu The menu being displayed
    * @param array $record The database record the menu is for
    */
    function OnUploadMenu(&$menu,&$record)
    {
        global $CC_GLOBALS;

        $menu['comments'] = 
                 array(  'menu_text'  => 'str_review',
                         'weight'     => 95,
                         'group_name' => 'comment',
                         'id'         => 'commentcommand',
                         'access'     => CC_MUST_BE_LOGGED_IN );

        if( empty($CC_GLOBALS['reviews_enabled']) || !CCReviewsHV::_can_review($record) )
        {
            $menu['comments']['access'] = CC_DISABLED_MENU_ITEM;
        }
        else
        {
            $menu['comments']['action'] = ccl('reviews','post', $record['upload_id']  )  . '#edit';
        }
    }


    public static function _can_review($row_or_id)
    {
        if( CCUser::IsLoggedIn() )
        {
            if( is_array($row_or_id) )
            {
                $user_id   = $row_or_id['upload_user'];
                $upload_id = $row_or_id['upload_id'];
            }
            else
            {
                $uploads   =& CCUploads::GetTable();
                $user_id   = $uploads->QueryItemFromKey('upload_user',$row_or_id);
                $upload_id = $row_or_id;
            }

            $current_user = CCUser::CurrentUser();
            if( $user_id != $current_user )
            {
                require_once('cchost_lib/ccextras/cc-reviews.inc');
                $reviews =& CCReviews::GetTable();
                $where['topic_upload'] = $upload_id;
                $where['topic_user'] = $current_user;
                $count = $reviews->CountRows($where);
                return !$count;
            }
        }

        return false;
    }
}


?>
