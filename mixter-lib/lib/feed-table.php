<?
/**
* Module for managing personal feeds
*
* @package cchost
* @subpackage feature
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/*
    VERBS
    ----------
    new upload
    upload update
    review
    recommend
    topic reply 
    site annouce
    ed pick

    OBJECT TYPES
    ---------------
    upload
    review
    forum post

    REASONS (why you care)
    ---------------------
    you were remixed 
    you were reviewed
    you are on a thread with replies
    you are ed picked
    you follow this person 
*/
/*

  CREATE TABLE cc_tbl_feed_action (
    action_id           int(11) NOT NULL auto_increment,
    action_actor        int(11) NOT NULL default '0',
    action_verb         int(3) NOT NULL default 0,
    action_object       int(11) NOT NULL default '0',
    action_object_type  int(3) NOT NULL default 0,
    action_date         datetime default NULL,
    action_sticky       int(1) NOT NULL default 0,
    PRIMARY KEY  (action_id)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

  

  CREATE TABLE cc_tbl_feed (
    feed_id           int(11) NOT NULL auto_increment,
    feed_user         int(11) NOT NULL default '0',
    feed_action       int(11) NOT NULL default '0',
    feed_reason       int(11) NOT NULL default '0',
    PRIMARY KEY  (feed_id)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


*/


class CCFeedTable extends CCTable
{
    function CCFeedTable() {
        global $CC_SQL_DATE;        
        $this->CCTable('cc_tbl_feed','feed_id');
    }

    public static function & GetTable() {
        static $_table;
        if( !isset($_table) )
            $_table = new CCFeedTable();
        return( $_table );
    }

    function AddItem($where) {
        $where['feed_id'] = $this->NextID();
        $this->Insert($where);
        return $where['feed_id'];
    }

    function MarkAsSticky($feed_id,$sticky=true) {
        $flds =array( 'feed_id' => $feed_id, 'feed_sticky' => $sticky ? 1 : 0 );
        $this->Update($flds);        
    }

}

class CCFeedActionTable extends CCTable
{
    function CCFeedActionTable() {
        global $CC_SQL_DATE;        
        $this->CCTable('cc_tbl_feed_action','action_id');
    }

    public static function & GetTable() {
        static $_table;
        if( !isset($_table) )
            $_table = new CCFeedActionTable();
        return( $_table );
    }

    function AddAction($where) {
        if( empty($where['action_date']) ) {
            $where['action_date'] = date( 'Y-m-d H:i:s' );
        }
        $where['action_id'] = $this->NextID();
        $this->Insert($where);
        return $where['action_id'];
    }

    function IDsForObject($object_id,$object_types) {
        if( !is_array($object_types) ) {
            $object_types = array( $object_types );
        }
        $types = implode(',', $object_types);
        $where = "action_object = {$object_id} AND action_object_type IN ({$types})";
        return $this->QueryItems('action_id',$where);
    }
}


?>
