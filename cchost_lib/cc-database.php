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
* $Id: cc-database.php 13098 2009-07-25 05:50:43Z fourstones $
*
*/

/**
* Implements the core mySQL wrapper
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

$CC_SQL_DATE = '%W, %M %e, %Y @ %l:%i %p';

/**
* Wrapper class for mySQL, however only CCTable should be calling it directly.
*
* @see CCTable::CCTable()
*/
class CCDatabase
{
    /**
    * Close a connection to the database
    * 
    * Useful when interacting with 3rd party software (like blogs or forums)
    * that have their own connection management.
    *
    */
    public static function DBClose()
    {
        $link =& CCDatabase::_link();
        if( $link )
        {
            @mysql_close($link);
            $link = null;
        }
    }

    /**
    * Static call to ensure connection to daemon. Can be called multiple times safely.
    */
    public static function DBConnect()
    {
        $config_db = CCDatabase::_config_db();
        include($config_db);
        $config = $CC_DB_CONFIG;

        $link = @mysql_connect( $config['db-server'], 
                                $config['db-user'], 
                                $config['db-password']) or die( mysql_error() );
        
        @mysql_select_db( $config['db-name'], $link ) or die( mysql_error() );

        return( $link );
    }

    /**
    * Performs one (or more) mySQL queries.
    *
    * @param mixed $sql single mySQL query or array of them
    */
    public static function Query( $sql )
    {
        if( is_array($sql) )
        {
            $retvals = array();
            foreach( $sql as $s )
                $retvals[] = CCDatabase::Query($s);
            return( $retvals );
        }

        $link =& CCDatabase::_link();
        if( empty($link) )
            $link = CCDatabase::DBConnect();

        global $_sql_time;
        static $_sql_t;

        //CCDebug::Chronometer($_sql_t);
        $last_statement =& CCDatabase::_last_sql_statement();
        $last_statement = $sql;
        $qr = mysql_query($sql,$link);
        if( !$qr ) {
            $mysqlerr = mysql_error();
        }
        //$_sql_time = CCDebug::Chronometer($_sql_t);

        if( preg_match('/^insert\s/i',trim($sql)) )
        {
            $insert_id =& CCDatabase::_last_insert_id();
            $insert_id = mysql_insert_id();
        }

        global $CC_GLOBALS;

        if( function_exists('cc_set_if_modified') )
        {
            static $cc_in_table_lock = false;

            if( preg_match( '/^(\s+)?lock/i',$sql) )
            {
                $cc_in_table_lock = true;
            }
            elseif( !$cc_in_table_lock 
                && empty($CC_GLOBALS['in_if_modified']) 
                && preg_match( '/^(\s+)?(insert|delete|update)/i',$sql) 
                && !preg_match( '/(cc_tbl_keys|cc_tbl_activity|cc_tbl_notifications)/i',$sql)
                )
            {
                cc_set_if_modified();
                $cc_in_table_lock = false;
            }
        }

        if( !$qr )
        {
            if( CCDebug::IsEnabled() )
            {
                print( "<pre>$sql<br /><hr />MYSQL ERROR:\n" . $mysqlerr . "</pre>");
                CCDebug::StackTrace(false,true);
            }
            else
            {
                print( "<pre>$sql<br /><hr /></pre>");
                print mysql_error();
                //st();
                trigger_error(_("Internal error, contact the admin"));
            }
        }
        return( $qr );
    }

    /**
    * Retrieves a single row. Use with SELECT statment.
    *
    * @param string $sql single mySQL SELECT statement
    * @param bool   $assoc TRUE means fetch_assoc, FALSE means fetch_row
    * @return array $row Row from database or null if results count greater or less than one.
    */
    public static function QueryRow( $sql, $assoc = true )
    {
        $qr = CCDatabase::Query($sql);

        if( mysql_num_rows($qr) != 1 )
            return( null );
        
        if( $assoc )
            return( mysql_fetch_assoc( $qr ) );
        else
            return( mysql_fetch_row( $qr ) );

    }

    /**
    * Retrieves a single item from a single row. Use with SELECT statment.
    *  
    * <code>
    *    $username = CCDatabase::QueryItem("SELECT username FROM users WHERE id = '9'");
    * </code>
    *  
    * @param string $sql mySQL SELECT statement with a single column
    * @return string $item First column results from SELECT statement
    */
    public static function QueryItem( $sql )
    {
        $row = CCDatabase::QueryRow($sql,false);
        return( $row[0] );
    }

    /**
    * Retrieves an array of a single column. Use with SELECT statment.
    *  
    * <code>
    * $usernames = CCDatabase::QueryItem("SELECT username FROM users");
    * foreach( $usernames as $username )
    * {
    *     //...
    * }
    * </code>
    *  
    * @param string $sql mySQL SELECT statement with a single column
    * @return array $rows Array of sql rows
    */
    public static function QueryItems( $sql )
    {
        $qr = CCDatabase::Query($sql);
        $results = array();
        while( $row = mysql_fetch_row($qr) )
            $results[] = $row[0];
        return( $results );
    }

    /* pre port to mysqli */
   public static function QueryCached($sql) {
        return CCDatabase::Query($sql);
    }

    public static function FetchCachedRow($qr) {
        return mysql_fetch_row($qr);
    }

    public static function FetchCachedRowArray($qr) {
        return mysql_fetch_array($qr);
    }

    public static function FetchCachedRowAssoc($qr) {
        return mysql_fetch_assoc($qr);
    }

    public static function NumCachedRows($qr) {
        return mysql_num_rows($qr);
    }

    /**
    * Retrieves multiple rows. Use with SELECT statment.
    *  
    * <code>
    * $rows =& CCDatabase::QueryRows("SELECT username, age FROM users WHERE age < 27");
    * foreach( $rows as $row )
    * {
    *     // ....
    * }
    * </code>
    *
    * @param string $sql mySQL SELECT statement 
    * @param bool   $assoc TRUE means fetch_assoc, FALSE means fetch_row
    * @return array $rows Array with database rows inside
    */
    public static function & QueryRows( $sql, $assoc = true )
    {
        $qr = CCDatabase::Query($sql);
        $rows = array();
        if( $assoc )
        {
            while( $row = mysql_fetch_assoc($qr) )
                $rows[] = $row;
        }
        else
        {
            while( $row = mysql_fetch_row($qr) )
                $rows[] = $row;
        }

        return $rows;
    }

    /**
    * Returns the tables in the current database
    * 
    */
    public static function ShowTables()
    {
        $qr = CCDatabase::Query("SHOW TABLES");
        $rows = array();
        while( $row = mysql_fetch_row($qr) )
            $rows[] = $row[0];

        return( $rows );
    }

    /**
    * Public interface to get the last insert id
    *
    */
    public static function LastInsertID()
    {
        return CCDatabase::_last_insert_id();
    }
    
    /**
    * Internal:  Returns the path to the current database config file
    *
    * @param string $file Name of config file to load (default is 'cc-config-db.php')
    *
    * @access private
    **/
    static function _config_db($file = '')
    {
        static $CC_DB_INFO_FILE;
        if( !empty($file) )
            $CC_DB_INFO_FILE = $file;
        if( empty($CC_DB_INFO_FILE) )
            return( 'cc-host-db.php' );
        return( $CC_DB_INFO_FILE );
    }

    /**
    * Internal:  Returns the link to the current connection
    *
    * @access private
    **/
    static function & _link()
    {
        static $_link;
        return $_link;
    }
    
    /**
    * Internal:  Returns the latest INSERT id
    *
    * @access private
    **/
    static function & _last_insert_id()
    {
        static $_id;
        return $_id;
    }
    
    static function & _last_sql_statement()
    {
        static $_statement;
        return $_statement;
    }
    
}

?>
