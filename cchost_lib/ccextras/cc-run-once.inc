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
* $Id: cc-run-once.inc 12618 2009-05-14 04:30:46Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

class CCRunOnce
{
    function RunOnce()
    {
        global $CC_GLOBALS,$CC_CFG_ROOT;

        if( empty($CC_GLOBALS['run_once']) || !isset($CC_GLOBALS['user_extra']) )
        {
            return;
        }
        
        require_once('cchost_lib/cc-tags.php');
        $run_pages = CCTag::TagSplit($CC_GLOBALS['run_once']);
        $key = 'run7';

        foreach( $run_pages as $run_page )
        {
            if( empty($CC_GLOBALS['user_extra'][$key][$run_page]) )
            {
                $CC_GLOBALS['user_extra'][$key][$run_page] = true;
                $up['user_extra'] = serialize($CC_GLOBALS['user_extra']);
                $up['user_id'] = $CC_GLOBALS['user_id'];
                $users =& CCUsers::GetTable();
                $users->Update($up);
                $url = url_args( ccl($run_page), 'run_once=1' );
                $num_users = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_user');
                if( $num_users > 1 )
                {
                    CCUtil::SendBrowserTo( $url );
                }
                else
                {
                    // this is the admin in the middle of the install,
                    // don't mess it up...
                }
            }
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
            $fields['run_once'] =
               array(  'label'      => _('Run Once'),
                       'form_tip'   => _('Comma separated list of content pages that registered users will see once.'),
                       'value'      => '',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE );
        }
    }

}

?>
