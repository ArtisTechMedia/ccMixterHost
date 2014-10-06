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
* $Id: cc-flag.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-extras-events.php'); // for EVENT_TOPIC stuff

/**
*/

CCEvents::AddHandler(CC_EVENT_FILTER_TOPICS,      array( 'CCFlagHV', 'OnFilterTopics'));
CCEvents::AddHandler(CC_EVENT_FILTER_REVIEWS,     array( 'CCFlagHV', 'OnFilterTopics'));
CCEvents::AddHandler(CC_EVENT_FILTER_UPLOAD_PAGE,         array( 'CCFlagHV', 'OnFilterUploads'));

CCEvents::AddHandler(CC_EVENT_MAP_URLS,       array( 'CCFlag' ,   'OnMapUrls'),    'cchost_lib/ccextras/cc-flag.inc' );
CCEvents::AddHandler(CC_EVENT_FORM_FIELDS,    array( 'CCFlag',    'OnFormFields'), 'cchost_lib/ccextras/cc-flag.inc' );

class CCFlagHV
{
    function _is_flagging_on()
    {
        global $CC_GLOBALS;
        return( !empty($CC_GLOBALS['flagging']) );
    }

    function OnFilterTopics(&$rows)
    {
        if( $this->_is_flagging_on() )
        {
            foreach($rows as $K => $row)
                $rows[$K]['flag_url'] = ccl('flag','topic',$row['topic_id']);
        }
    }

    function OnFilterUploads(&$rows)
    {
        if( $this->_is_flagging_on() )
        {
            foreach($rows as $K => $row)
                $rows[$K]['flag_url'] = ccl('flag','upload',$row['upload_id']);
        }
    }
}

?>
