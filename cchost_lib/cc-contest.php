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
* $Id: cc-contest.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* Implements the user interface and database management for contests
*
* @package cchost
* @subpackage contest
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

//-------------------------------------------------------------------

/**
* Contest High Volume API and event callbacks
*
*/
class CCContestHV
{
    /**
    * Callback for Navigation tab display
    *
    * @see CCNavigator::View()
    * @param array $page Array of tabs to be manipulated before display
    */
    function OnTabDisplay( &$page )
    {
        require_once('cchost_lib/cc-contest-table.inc');
        $contests =& CCContests::GetTable();
        $short_name = $page['handler']['args']['contest'];
        $contest = CCDatabase::QueryRow(
            'SELECT contest_id, contest_publish, contest_open, contest_entries_accept, contest_deadline FROM cc_tbl_contests ' .
            "WHERE contest_short_name = '{$short_name}'");
        $contests->GetOpenStatus($contest);


        if( !empty($page['winners']) )
        {
            if( $page['winners']['function'] != 'url' )
            {
                require_once('cchost_lib/cc-dataview.php');
                $dv = new CCDataView();
                $filter = $dv->MakeTagFilter('winner,' . $short_name,'all');
                $num_winners = CCDatabase::QueryItem("SELECT COUNT(*) from cc_tbl_uploads WHERE $filter");
                if( !$num_winners )
                {
                    unset($page['winners']);
                }
            }
        }

        if( !$contest['contest_taking_submissions'] )
        {
            unset($page['submit']);
        }

        if( !CCUser::IsAdmin() && !$contest['contest_can_browse_entries'] )
        {
            unset($page['entries']);
        }
    }

}
?>
