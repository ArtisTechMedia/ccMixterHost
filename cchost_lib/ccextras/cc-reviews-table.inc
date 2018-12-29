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
* $Id: cc-reviews-table.inc 8961 2008-02-11 22:17:33Z fourstones $
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

require_once('cchost_lib/ccextras/cc-topics.inc');


class CCReviews extends CCTopics
{
    function CCReviews($doing_join=false)
    {
        $this->CCTopics();
        $this->LimitType('review');
        if( $doing_join )
            $this->_key_field = 'topic_upload';
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        static $table;
        if( !isset($table) )
            $table = new CCReviews();
        return $table;
    }

}

?>
