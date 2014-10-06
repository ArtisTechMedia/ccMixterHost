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
* $Id: cc-recommends.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Shows user recommendations
*
* @package cchost
* @subpackage feature
*/

CCEvents::AddHandler(CC_EVENT_USER_PROFILE_TABS,  array( 'CCRecommends',  'OnUserProfileTabs')      );

class CCRecommends
{
    function OnUserProfileTabs( &$tabs, &$record )
    {
        if( empty($record['user_id']) )
        {
            $tabs['recommends'] = 'Recommends';
            return;
        }

		$count = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_ratings WHERE ratings_user = '.$record['user_id']);
		if( !$count )
			return;

        $tabs['recommends'] = array(
                    'text' => 'Recommends',
                    'help' => 'Recommendation for and by this person',
                    'tags' => 'recommends',
                    'access' => CC_DONT_CARE_LOGGED_IN,
                    'function' => 'url' ,
                    'user_cb' => array( 'CCRecommends', 'User' ),
            );
    }

    function User($user)
    {
        $users =& CCUsers::GetTable();
        $w['user_name'] = $user;
        $fullname = $users->QueryItem('user_real_name',$w);
        $args['ruser'] = $user;
        $args['fullname'] = $fullname;
        CCPage::PageArg('get',$args);
        CCPage::ViewFile('recommends');
    }

}

?>