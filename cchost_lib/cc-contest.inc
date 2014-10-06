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
* $Id: cc-contest.inc 12624 2009-05-18 15:47:40Z fourstones $
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

require_once('cchost_lib/cc-contest-table.inc');
require_once('cchost_lib/cc-contest.php');


//-------------------------------------------------------------------

/**
* Contest API and event callbacks
*
*/
class CCContest
{
    /**
    * Event handler for {@link CC_EVENT_GET_MACROS}
    *
    * @param array &$record Upload record we're getting macros for (if null returns documentation)
    * @param array &$file File record we're getting macros for
    * @param array &$patterns Substituion pattern to be used when renaming/tagging
    * @param array &$mask Actual mask to use (based on admin specifications)
    */
    function OnGetMacros( &$record, &$file, &$patterns, &$mask )
    {
        if( empty($record) )
        {
            $patterns['%contest%']            = 'Contest (Internal Name)';
            $patterns['%contest_fullname%']   = 'Contest (Full Name)';
            $patterns['%url%']                = 'Download URL';
            $patterns['%song_page%']          = 'File page URL';
            $patterns['%unique_id%']     = 'Guaranteed to be unique number';
            $mask['contest']        = 'Pattern to use for contest entries';
            $mask['contest-source'] = 'Pattern to use for contest sources';
            return;
        }

        $isentry  = CCUploads::InTags( CCUD_CONTEST_ENTRY, $record );
        $issource = !$isentry && CCUploads::InTags( CCUD_CONTEST_ALL_SOURCES, $record );

        if( !($isentry || $issource)  )
            return;

        $configs =& CCConfigs::GetTable();
        $mask_configs = $configs->GetConfig('name-masks');

        if( $isentry )
        {
            if( array_key_exists('contest',$mask_configs) )
                $mask = $mask_configs['contest'];
        }
        elseif( $issource )
        {
            if( array_key_exists('contest-source',$mask_configs) )
                $mask = $mask_configs['contest-source'];
        }

        if( !empty($record['download_url']) )
            $patterns['%url%']              = $record['download_url'];

        if( !empty($file['file_id']) )
            $patterns['%unique_id%'] = $file['file_id'];

        if( empty($record['upload_contest']) )
        {
            if( !empty($file['file_upload']) )
            {
                $uploads =& CCUploads::GetTable();
                $contest_id = $uploads->QueryItemFromKey('upload_contest',$file['file_upload']);
            }
        }
        else
        {
            $contest_id = $record['upload_contest'];
        }

        if( empty($contest_id) )
        {
            // things are pretty horked here...
            return;
        }


        $contests =& CCContests::GetTable();
        $contest = $contests->QueryKeyRow($contest_id);

        if( !empty($record['upload_id']) )
        {
            if( empty($record['user_name']) )
            {
                $user_name = CCDatabase::QueryItem('SELECT user_name FROM cc_tbl_user WHERE user_id='.$record['upload_user']);
            }
            else
            {
                $user_name = $record['user_name'];
            }
            $patterns['%song_page%'] = ccl('files',$user_name,$record['upload_id']);
        }

        $patterns['%contest%']          = $contest['contest_short_name'];
        $patterns['%contest_fullname%'] = $contest['contest_friendly_name'];
    }


    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        // contests are deprecated

    }

}


?>
