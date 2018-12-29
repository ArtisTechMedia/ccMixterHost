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
* $Id: update_v_5_openid.inc 12467 2009-04-29 05:09:20Z fourstones $
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
class v_5_openid extends CCUpdate
{
    function Update()
    {
        if( !$this->_table_exists('cc_tbl_openids') )
        {
            $sql =<<<EOF
                CREATE TABLE cc_tbl_openids (
                  openid      varchar(255) default NULL,
                  openid_user int(11) NOT NULL default '0',
                  openid_show int(1)  NOT NULL default '0',
                  PRIMARY KEY openid (openid)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
EOF;
            CCDatabase::Query($sql);
        }
        /*
         *  old version:
         * 
              openid_confirmed int(1) NOT NULL default '0',
              openid_session int(11) NOT NULL default '0',
              openid_extra mediumtext,
         * 
         */        
       $page =& CCPage::GetPage(); $page->Prompt( _('OpenID extensions installed') );

    }
}

?>
