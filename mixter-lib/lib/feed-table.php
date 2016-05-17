<?
/**
* Module for managing personal feeds
*
* @package cchost
* @subpackage feature
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

define('FEED_TYPE_FOLLOWER_UPLOAD', 'fol'); // upload_id
define('FEED_TYPE_FOLLOWER_UPDATE', 'fup'); // upload_id
define('FEED_TYPE_REVIEW',          'rev'); // topic_id
define('FEED_TYPE_RECOMMEND',       'rec'); // ratings_id
define('FEED_TYPE_REMIXED',         'rmx'); // upload_id
define('FEED_TYPE_REPLY',           'rpy'); // topic_id
define('FEED_TYPE_ADMIN_MSG',       'adm'); // topic_id
define('FEED_TYPE_MSG',             'msg'); // topic_id
define('FEED_TYPE_EDPICK',          'edp'); // upload_id

/*
  CREATE TABLE cc_tbl_feed (
    feed_id         int(11) NOT NULL auto_increment,
    feed_user       int(11) NOT NULL default '0',
    feed_type       varchar(3) NOT NULL default '',
    feed_date       datetime default NULL,
    feed_key        int(11) NOT NULL default '0',
    feed_seen       int(1) NOT NULL default '0',
    feed_sticky     int(1) NOT NULL default '0',
    PRIMARY KEY  (feed_id)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

*/

class CCFeedTable extends CCTable
{
    function CCFeedTable() {
        global $CC_SQL_DATE;        
        $this->CCTable('cc_tbl_feed','feed_id');
        $this->AddExtraColumn("DATE_FORMAT(feed_date, '$CC_SQL_DATE') as feed_date_format");
    }

    public static function & GetTable() {
        static $_table;
        if( !isset($_table) )
            $_table = new CCFeedTable();
        return( $_table );
    }

    function AddItem($user_id,$type,$foreign_key,$date='') {
        if( empty($date) ) {
            $date = date( 'Y-m-d H:i:s' );
        }
        $id = $this->NextID();
        $where = array(
                'feed_id'   => $id,
                'feed_user' => (int)$user_id,
                'feed_type' => $type,
                'feed_key'  => (int)$foreign_key,
                'feed_date' => $date
            );
        $this->Insert($where);
        return $id;
    }

    function HasItem($user_id,$type,$foreign_key) {
        $where = array(
                'feed_user' => (int)$user_id,
                'feed_type' => $type,
                'feed_key'  => (int)$foreign_key,
            );
        return (int)$this->CountRows($where) > 0;
    }

    function MarkAsSticky($feed_id,$sticky=true) {
        $flds =array( 'feed_id' => $feed_id, 'feed_sticky' => $sticky ? 1 : 0 );
        $this->Update($flds);        
    }

    function MarkAsSeen($feed_id) {
        $flds =array( 'feed_id' => $feed_id, 'feed_seen' => 1 );
        $this->Update($flds);
    }

}


?>
