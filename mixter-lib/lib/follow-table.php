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
CREATE TABLE cc_tbl_follow (     
    follow_user         int(11) NOT NULL default '0',     
    follow_follows      int(11) NOT NULL default '0',     
    INDEX(follow_user)   
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;  
*/


class CCFollowTable extends CCTable
{
    function CCFollowTable() {
      $this->CCTable('cc_tbl_follow','follow_user');
    }

    public static function & GetTable() {
      static $_table;
      if( !isset($_table) )
          $_table = new CCFollowTable();
      return( $_table );
    }

    function Follow($user,$follows) {
      $where['follow_user'] = CCUser::IDFromName($user);
      $where['follow_follows'] = CCUser::IDFromName($follows);
      $this->Insert($where);
    }

    function UnFollow($user,$follows) {
      $where['follow_user'] = CCUser::IDFromName($user);
      $where['follow_follows'] = CCUser::IDFromName($follows);
      $this->DeleteWhere($where);
    }

    function IsFollowing($user,$follows) {
      $where['follow_user'] = CCUser::IDFromName($user);
      $where['follow_follows'] = CCUser::IDFromName($follows);
      return (int)$this->CountRows($where) === 1;
    }

    function RemoveUser($user) {
      $user = CCUser::IDFromName($user);
      $where = "follow_user = {$user} OR follow_follows = {$user}";
      $this->DeleteWhere($where);
    }

    function Following($user) {
      $user_id = CCUser::IDFromName($user);
      $join = $this->AddJoin( new CCUsers(), 'follow_follows');
      $where['follow_user'] = $user_id;
      $rows = $this->QueryRows($where,'user_name,user_real_name');
      $this->RemoveJoin($join);
      return $rows;
    }

    function Followers($user) {
      $user_id = CCUser::IDFromName($user);
      $join = $this->AddJoin( new CCUsers(), 'follow_user');
      $where['follow_follows'] = $user_id;
      $rows = $this->QueryRows($where,'user_name,user_real_name');
      $this->RemoveJoin($join);
      return $rows;
    }

}

?>
