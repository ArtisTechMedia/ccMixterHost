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
* $Id: update_v_5_2_mixup.inc 13831 2009-12-25 12:35:42Z fourstones $
*
*/

/**
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
class v_5_2_mixup extends CCUpdate
{
    function Update()
    {
        if( !$this->_table_exists('cc_tbl_mixups') )
        {
            $sql =<<<EOF
                CREATE TABLE cc_tbl_mixups (
                  mixup_id        INT(9) unsigned NOT NULL auto_increment,
                  mixup_name      varchar(100) NOT NULL default '',
                  mixup_tag       varchar(100) NOT NULL default '',
                  mixup_display   varchar(255) NOT NULL default '',
                  mixup_desc      mediumtext NOT NULL,
                  mixup_date      datetime NOT NULL default '0000-00-00 00:00:00',
                  mixup_mode      int(4) unsigned NOT NULL default '0',
                  mixup_admin     int(9) unsigned NOT NULL,
                  mixup_thread    int(9) unsigned NOT NULL,
                  mixup_playlist  int(9) unsigned NOT NULL,
                  PRIMARY KEY mixup_id (mixup_id),
                  KEY mixup_name (mixup_name)
                ) DEFAULT CHARSET=latin1;
EOF;
            CCDatabase::Query($sql);
            
        }
        if( !$this->_table_exists('cc_tbl_mixup_user') )
        {
            $sql =<<<EOF
                CREATE TABLE cc_tbl_mixup_user (
                  mixup_user_id        INT(9) unsigned NOT NULL auto_increment,
                  mixup_user_mixup INT(9) unsigned NOT NULL default '0',
                  mixup_user_user INT(9) unsigned NOT NULL default '0',
                  mixup_user_other INT(9) unsigned NOT NULL default '0',
                  mixup_user_upload INT(9) unsigned NOT NULL default '0',
                  mixup_user_date datetime NOT NULL default '0000-00-00 00:00:00',
                  PRIMARY KEY mixup_id (mixup_id)
                  KEY user_mixup (mixup_user_mixup,mixup_user_user)
                ) DEFAULT CHARSET=latin1;
EOF;
            CCDatabase::Query($sql);
            
        }
        if( !$this->_table_exists('cc_tbl_mixup_mode') )
        {
            $sql =<<<EOF
                CREATE TABLE cc_tbl_mixup_mode (
                  mixup_mode_id INT(9) unsigned NOT NULL auto_increment,
                  mixup_mode_type INT(4) unsigned NOT NULL default '0',
                  mixup_mode_mixup INT(9) unsigned NOT NULL,
                  mixup_mode_name varchar(100) NOT NULL default '',
                  mixup_mode_date datetime NOT NULL default '0000-00-00 00:00:00',
                  mixup_mode_date_offset varchar(100) NOT NULL default '',
                  mixup_mode_desc mediumtext NOT NULL,
                  mixup_mode_mail mediumtext NOT NULL,
                  PRIMARY KEY mixup_mode_id (mixup_mode_id)
                ) DEFAULT CHARSET=latin1;
EOF;
            CCDatabase::Query($sql);
            
        }
        $page =& CCPage::GetPage(); $page->Prompt( _('Mixup') );
    }
}

?>
