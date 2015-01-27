<?
/*
 
* Creative Commons has made the contents of this file
* * available under a CC-GNU-GPL license:
* http://creativecommons.org/licenses/GPL/2.0/
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
*
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
* 
* $Id: update_v_5_2_tag_cats.inc 13831 2009-12-25 12:35:42Z fourstones $
*
* 
*/

/* @package cchost
* @subpackage admin
*
*/
if( !defined('IN_CC_HOST') )
    die('Welcome to CC Host');
    
    
/**
*/
class v_5_2_tag_cats extends CCUpdate
{
    function Update() {

        $this->_check_for_field('cc_tbl_tags','tags_category', 'varchar(40)');

        if( !$this->_table_exists('cc_tbl_tag_category') )
        {
            $sql =<<<EOF
                CREATE TABLE cc_tbl_tag_category (
                  tag_category_id varchar(40) not null default '',
                  tag_category varchar(255) not null default '',
                  PRIMARY KEY tag_category_id (tag_category_id)
                );
EOF;
            CCDatabase::Query($sql);

        }

        if( !$this->_table_exists('cc_tbl_tag_pair') )
        {
            $sql =<<<EOF
                CREATE TABLE cc_tbl_tag_pair (
                  tag_pair varchar(255) not null default '',
                  tag_pair_tag varchar(255) not null default '',
                  tag_pair_count INT(9) unsigned NOT NULL,
                  PRIMARY KEY tag_pair (tag_pair,tag_pair_tag)
                );
EOF;
            CCDatabase::Query($sql);

        }

        $page =& CCPage::GetPage();
        $page->Prompt(_('Tag categories installed'));

    }
}

?>