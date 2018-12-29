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
* $Id: update_v_5_2_mixup_confirm.inc 13831 2009-12-25 12:35:42Z fourstones $
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
class v_5_2_mixup_confirm extends CCUpdate
{
    function Update()
    {
        if( !$this->_table_exists('cc_tbl_mixup_confirm') )
        {
            $sql =<<<EOF
                CREATE TABLE cc_tbl_mixup_confirm (
                  mixup_confirm_mixup        INT(9) unsigned NOT NULL,
                  mixup_confirm_user      INT(9) unsigned NOT NULL,
                  mixup_confirm_code      varchar(4) NOT NULL default '',
                  KEY mixup_confirm_key (mixup_confirm_mixup,mixup_confirm_user)
                ) DEFAULT CHARSET=latin1;
EOF;
            CCDatabase::Query($sql);
            
        }
        $page =& CCPage::GetPage(); $page->Prompt( _('Mixup confirmation table') );
    }
}

?>
