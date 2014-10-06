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
* $Id: cc-reviews.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/

require_once('cchost_lib/ccextras/cc-reviews-table.inc');


/**
* Review API
*
*/
class CCReview
{
    function Reviews( $user_name='', $upload_id='' )
    {
        global $CC_GLOBALS;

        // 
        // These args will be passed to the template
        //
        $args = array();

        //
        // Setup the page and initialize useful things
        //

        require_once('cchost_lib/cc-page.php');

        $page =& CCPage::GetPage();
        $users =& CCUsers::GetTable();
        $upload_id = sprintf("%0d",$upload_id);

        if( !empty($user_name) )
        {
            $qkarg['user_name'] = $user_name;
            $user_id = $users->QueryKey($qkarg);
            if( empty($user_id) )
            {
                CCPage::Prompt(_('Cannot find that user'));
                CCUtil::Send404(false);
                return;
            }

            if( !empty($upload_id) )
            {
                $uploads =& CCUploads::GetTable();
                if( !$uploads->KeyExists($upload_id) )
                {
                    CCPage::Prompt(_('Cannot find that upload. It may have been removed by the owner.'));
                    CCUtil::Send404(false);
                    return;
                }
            }

            $user_real_name = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_name = \'' . $user_name . '\'');
        }
        else
        {
            $user_real_name = '';
        }

        //
        // OK, figure out what the template should be
        //
        if( empty($upload_id) )
        {
            if( empty($user_name) )
            {
                //
                // This is just a 'recent review' listing for the whole site
                //
                CCPage::SetTitle('str_recent_reviews');
                CCPage::ViewFile('reviews');
            }
            else
            {
                //
                // This is request for listing a users' reviews
                //

                $template = 'reviews_user';
                $qtype = CCUtil::StripText($_REQUEST['qtype']);
                if( empty($qtype) || ($qtype == 'leftby') )
                {
                    $where = 'reviewer.user_name=';
                    $tstr = 'str_reviews_left_by';
                    $otstr = 'str_reviews_left_for_button';
                    $qtype = 'leftfor';
                }
                else
                {
                    $where = 'reviewee.user_name=';
                    $tstr = 'str_reviews_left_for';
                    $otstr = 'str_reviews_left_by_button';
                    $qtype = 'leftby';
                }
                require_once('cchost_lib/cc-query.php');
                $query = new CCQuery();
                $args = $query->ProcessAdminArgs('sort=date&ord=desc&t=reviews_browse&datasource=topics&limit=30',array(),false);
                $args['title'] = array( $tstr, $user_real_name );
                $sqlargs['where'] = $where . "'$user_name'";
                $query->QuerySQL($args,$sqlargs);

                $leftby_link = array( 'text' => array( $otstr, $user_real_name ),
                                      'url' =>  cc_current_url() . '?qtype=' . $qtype );

                CCPage::PageArg('leftby_link',$leftby_link);
            }
        }
        else
        {
            $upload_name = CCDatabase::QueryItem('SELECT upload_name FROM cc_tbl_uploads WHERE upload_id = '.$upload_id);
            $this->_build_bread_crumb_trail(array($user_name,$user_real_name),array($upload_id,$upload_name));
            require_once('cchost_lib/cc-query.php');
            $query = new CCQuery();
            $title = $page->String(array( 'str_reviews_for_s', $upload_name));
            $args = $query->ProcessAdminArgs('limit=0&paging=off&t=reviews_upload&match='.$upload_id.'&title='.$title);
            $query->Query($args);

            // this is so we can display the upload record on the page:
            CCPage::PageArg('topic_upload',$upload_id);
        }

        //
        // Attach feed buttons
        //
        if( $user_name )
        {
            if( $upload_id )
            {
                $q = '&upload=' . sprintf("%0d",$upload_id);
                $title= sprintf( _('Reviews left for "%s"'), CC_strchop($upload_name,16) );
            }
            else
            {
                if( $qtype == 'leftfor' ) // note: this was flipped above
                {
                    $q = '&user=' . $user_name;
                    $title = sprintf( _('Reviews left by %s'), CC_strchop($user_real_name,16) );
                }
                else
                {
                    $q = '&reviewee='.$user_name;
                    $title = sprintf( _('Reviews left for "%s"'), CC_strchop($user_real_name,16) );
                }
            }
        }
        else
        {
            $q = '';
            $title = $page->String('str_reviews');
        }
        $query = 'datasource=topics&type=review' . $q .'&title=' . urlencode($title);
        $page->AddFeedLink($query, $title, $title, 'feed_reviews', 'topics' );
    }

    function _build_bread_crumb_trail($user=array(),$upload=array(),$cmd_url='',$cmd_text='')
    {
        $trail = array();
        $trail[] = array( 'url' => ccl(), 'text' => 'str_home');

        if( empty($user) )
        {
            $trail[] = array( 'url' => ccl('reviews'), 'text' => 'str_reviews' );
        }
        else
        {
            $trail[] = array( 'url' => ccl('people'), 'text' => 'str_people' );

            $trail[] = array( 'url' => ccl('people',$user[0]), 'text' => $user[1] );

            if( empty($upload) )
            {
                $trail[] = array( 'url' => ccl('reviews',$user[0]), 'text' => 'str_reviews' );
            }
            else
            {
                $upload_name = '"' . $upload[1] . '"';
                $trail[] = array( 'url' => ccl('files',$user[0],$upload[0]),    'text' => $upload[1]   );
                $trail[] = array( 'url' => ccl('reviews',$user[0],$upload[0]), 'text' => 'str_reviews' );

                if( !empty($cmd_text) )
                    $trail[] = array( 'url' => $cmd_url, 'text' => $cmd_text );
            }
        }

        require_once('cchost_lib/cc-page.php');
        CCPage::AddBreadCrumbs($trail);
    }

    function OnTopicDelete($deltype,$topic_id)
    {
        $reviews =& CCReviews::GetTable();
        $row = $reviews->QueryKeyRow($topic_id);
        $upload_id = $row['topic_upload'];
        if( empty($upload_id) ) // never mind, it wasn't a review
            return;
        $reviewer = $row['topic_user'];
        $this->Sync($upload_id,$reviewer,-1);
    }

    function _can_review($row_or_id)
    {
        return CCReviewsHV::_can_review($row_or_id);
    }

    function Sync($upload_id,$reviewer,$add=0)
    {
        $count = CCDatabase::QueryItem("SELECT COUNT(*) FROM cc_tbl_topics WHERE topic_type='review' AND topic_upload=$upload_id");
        $count += $add;
        $uploads =& CCUploads::GetTable();
        $uploads->SetExtraField( $upload_id, 'num_reviews', $count );

        if( !$add )
            $add = 1;

        $reviewee = $uploads->QueryItemFromKey('upload_user',$upload_id);

        $users =& CCUsers::GetTable();
        $arg1['user_id'] = $reviewer;
        $count = $users->QueryItemFromKey('user_num_reviews',$reviewer) + $add;
        $arg1['user_num_reviews'] = $count;
        $users->Update($arg1);

        $arg2['user_id'] = $reviewee;
        $count = $users->QueryItemFromKey('user_num_reviewed',$reviewee) + $add;
        $arg2['user_num_reviewed'] = $count;
        $users->Update($arg2);
    }

    /**
    * Event hander for {@link CC_EVENT_DELETE_UPLOAD}
    * 
    * @param array $record Upload database record
    */
    function OnUploadDelete(&$record)
    {
        global $CC_GLOBALS;

        $reviews =& CCReviews::GetTable();
        $upload_id = $record['upload_id'];
        $w['topic_upload'] = $upload_id;
        $rows = $reviews->QueryRows($w,'topic_id,topic_user');
        
        $topic_api = new CCTopic();

        // q: should we be throwing an topic_delete event??
        foreach( $rows as $row)
        {
            $topic_id = $row['topic_id'];
            $reviewer = $row['topic_user'];
            $this->Sync($upload_id,$reviewer,-1);
            $reviews->DeleteKeyRow($topic_id);
        }
    }


    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields['reviews_enabled'] =
               array(  'label'      => _('Enable Reviews'),
                       'form_tip'   => _('Allow users to review uploads'),
                       'value'      => '1',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE );

            /*
            $fields['reviews_access'] =
                  array(
                    'label'      => 'Who Can Write Reviews',
                    'value'      => CC_MUST_BE_LOGGED_IN,
                    'formatter'  => 'select',
                    'options'    => array( CC_MUST_BE_LOGGED_IN   => 'Logged in users only',
                                           CC_DONT_CARE_LOGGED_IN => "Everyone",
                                           CC_ADMIN_ONLY          => "Administrators only"
                                        ),
                    'flags'      => CCFF_NONE );
            */

        }
    }


    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('reviews'),        array( 'CCReview', 'Reviews'),  
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '[user_name]/[upload_id]', 
            _('See reviews by a person or for a specific upload'), CC_AG_REVIEWS );

        CCEvents::MapUrl( ccp('reviews','post'), array( 'CCReviewFormAPI', 'PostReview'),  
            CC_MUST_BE_LOGGED_IN, 'cchost_lib/ccextras/cc-review-forms.inc', '[upload_id]', 
            _('Display a review form'), CC_AG_REVIEWS );

    }

}

?>
