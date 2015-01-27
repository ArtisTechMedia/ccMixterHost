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
* $Id: cc-contest-table.inc 8961 2008-02-11 22:17:33Z fourstones $
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


/**
* Wrapper for database Contest table
*
*/
class CCContests extends CCTable
{
    var $_publish_filter;

    /**
    * Constructor -- don't use new, use GetTable() instead
    *
    * @see GetTable
    */
    function CCContests()
    {
        $this->CCTable('cc_tbl_contests','contest_id');
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
        static $_table;
        if( !isset($_table) )
            $_table = new CCContests();
        return($_table);
    }

    /**
    * Populate a database row for a contest with specific state flags
    *
    * Upon return there will be several boolean flags regarding the
    * current state of this contest. 
    *
    * <code>
    * $a['contest_taking_submissions']  // true if NOW is before deadline
    * $a['contest_can_browse_entries']  // true if browing is always allowed or NOW is after contest deadline
    * $a['contest_over']                // true is NOW after all submissions and voting
    *  </code>
    *
    * @param array $row Reference to contest database row
    */
    function GetOpenStatus(&$row)
    {
        $row['contest_taking_submissions'] = false;
        $row['contest_can_browse_entries'] = false;
        $row['contest_over']               = false;
        if( $row['contest_publish'] > 0 )
        {
            $open     = strtotime($row['contest_open']);
            $entries  = strtotime($row['contest_entries_accept']);
            $deadline = strtotime($row['contest_deadline']);
            $now      = time();
            if( ($now > $open) && ($now < $deadline) )
            {
                if( $now > $entries )
                {
                    $row['contest_taking_submissions'] = true;

                    if( $row['contest_auto_publish'] )
                        $row['contest_can_browse_entries'] = true;
                }
            }
            else
            {
                if( $now > $open )
                {
                    if( $now > $entries )
                        $row['contest_can_browse_entries'] = true;

                    if( $now > $deadline )
                        $row['contest_over'] = true;
                }
                else
                {
                    //$row['contest_over'] = true;
                }
            }
        }
    }

    /**
    *  Converts a raw database row to a semantically rich (display ready) record
    *
    * @param array $row Reference to database row
    * @param bool  $expand true if you want to include local menu commands for each record
    */
    function & GetRecordFromRow(&$row,$expand = true)
    {
        die( 'contest get record called' );
    }

    /**
    * Overwrites base class to add specific publishing and other filters
    * 
    * @param mixed $where string or array representing WHERE clause
    * @param string $columns SELECT will be limited to these columns
    * @return string $select Fully formed SELECT statement
    */
    function _get_select($where,$columns='*')
    {
        $where = $this->_where_to_string($where);

        if( !empty($this->_publish_filter) )
        {
            if( empty($where) )
                $where = $this->_publish_filter;
            else
                $where = '($where) AND ({$this->_publish_filter})';
        }
        $sql = parent::_get_select($where,$columns);
        return($sql);

    }
}

?>