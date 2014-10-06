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
* $Id: cc-seckeys.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage user
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Wrapper for cc_tbl_keys database table, used in register spam prevention
*/
class CCSecurityKeys extends CCTable
{
    /**
    * Constructor (use GetTable() to get an instance of this table)
    *
    * @see GetTable
    */
    function CCSecurityKeys()
    {
        $this->CCTable('cc_tbl_keys','keys_id');
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCSecurityKeys();
        return( $_table );
    }

    /**
    * Add a key record to the database and returns a key that should match later
    *
    * @returns integer $id ID of this key
    */
    function AddKey($key)
    {
        $this->CleanUp();
        $ip = $_SERVER["REMOTE_ADDR"];
        $dbargs['keys_key']  = $key;
        $dbargs['keys_ip']   = $ip;
        $dbargs['keys_time'] = date('Y-m-d H:i');
        $this->Insert($dbargs);
        $id = $this->QueryKey("keys_key = '$key' AND keys_ip = '$ip'");
        return($id);
    }

    /**
    * Clean up utility function, empties the database of record over an hour old.
    */
    function CleanUp()
    {
        $this->DeleteWhere('keys_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
    }

    /** 
    * Verify a key/id pair are a match
    */
    function IsMatch($key,$id)
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $real_id = $this->QueryKey("keys_key = '$key' AND keys_ip = '$ip'");
        return( $real_id === $id );
    }

    /**
    * Generate a fairly unique, kinda sorta unpredictable key that
    * doesn't use confusing characters like l1 oO0 8B zZ2 and 9g.
    */
    function GenKey()
    {
        $hash = md5(uniqid(rand(),true));
	    return( substr($hash,intval($hash[0],16),5) );
    }
}

?>
