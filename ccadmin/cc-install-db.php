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
* $Id: cc-install-db.php 12641 2009-05-23 17:14:26Z fourstones $
*
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

if( !defined('CC_MAIL_THROTTLED') )
    define('CC_MAIL_THROTTLED', 8); // see ccextras/cc-mail.php

function d(&$obj)
{
    print '<pre>';
    print_r($obj);
    print '</pre>';
    exit;
}

function cc_install_tables(&$vars,&$msg,$local_base_dir)
{
    $new_tables_text = file_get_contents( dirname(__FILE__) . '/cchost_tables.sql');

    preg_match_all( '/CREATE TABLE ([^\s]+) \((.*\))\s+\) ENGINE[^;]+;/msU', $new_tables_text, $m );

    /* DROP PREVIOUS TABLES */

    $tables = CCDatabase::ShowTables();

    if( !empty($tables) )
    {
        foreach( $m[1] as $drop_table )
        {
            if( in_array($drop_table,$tables) )
            {
                mysql_query( "DROP TABLE $drop_table" );
                $msg = mysql_error();
                if( $msg )
                   return(false);
            }
        }
    }


    /* INSTALL TABLES */
    
    foreach( $m[0] as $s )
    {
       mysql_query($s);
       $msg = mysql_error();
       if( $msg )
           return(false);
    }


    $configs =& CCConfigs::GetTable();

    require_once('cc-install-settings.php');

    foreach( $install_settings as $S )
    {
        $configs->SaveConfig($S['config_type'],$S['config_data'],$S['config_scope']);
    }

    // ----------------- default forums and forum groups ----------------------------

      $sql = array(
"INSERT INTO `cc_tbl_forums` VALUES(1, 8, 4, 1, 'str_forum_announcements', 'str_forum_messages_admins', 1);",
"INSERT INTO `cc_tbl_forums` VALUES(2, 1, 4, 2, 'Help', 'get aid', 2);",
"INSERT INTO `cc_tbl_forums` VALUES(3, 1, 4, 3, 'The Big OT', 'off topic stuff', 3);",
"INSERT INTO `cc_tbl_forums` VALUES(4, 1, 4, 4, 'Bugs', 'Report bugs here', 1);",
"INSERT INTO `cc_tbl_forum_groups` VALUES(1, 'str_forum_the_site', 1);",
"INSERT INTO `cc_tbl_forum_groups` VALUES(2, 'str_forum_the_content', 2);",
"INSERT INTO `cc_tbl_forum_groups` VALUES(3, 'str_forum_off_beats', 10);",
);
    CCDatabase::Query($sql);

    require_once( dirname(__FILE__) . '/cc-content-topics.php' );
    inject_content_topics();

    return(true);
}

?>
