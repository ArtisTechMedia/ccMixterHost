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
* $Id: cc-cart-table.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Cart (as in shopping cart) table keeping lists of uploads
*
*/
class CCCart extends CCTable
{
    var $_type_limit;
    var $_type_sublimit;

    function CCCart($do_joins=false)
    {
        global $CC_SQL_DATE;

        $this->CCTable('cc_tbl_cart','cart_id');
        $this->AddExtraColumn("DATE_FORMAT(cart_date, '$CC_SQL_DATE') as cart_date_format");

        if( $do_joins )
        {
            $juser = $this->AddJoin( new CCUsers(),    'cart_user');
            // magyar pulleeeeze
            //$this->AddExtraColumn("DATE_FORMAT($juser.user_registered, '$CC_SQL_DATE') as user_date_format");
        }
    }

    function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCCart();
        return $_table;
    }

    function & GetRecordFromRow(&$row) 
    {

        if( !empty($row['user_id']) )
        {
            $users =& CCUsers::GetTable();
            return $users->GetRecordFromRow($row);
        }

        return $row;
    }

    function LimitType($type, $subtype='')
    {
        $this->_type_limit = $type;
        $this->_type_sublimit = $subtype;
    }

    // overwrite parent's version to add type limit
    function _get_select($where,$columns='*')
    {
        if( !empty($this->_type_limit) )
        {
            $w = "cart_type = '" . $this->_type_limit . "'";

            if( !empty($type->_type_sublimit) )
            {
                $w = "$w AND cart_subtype = '{$this->_type_sublimit}'";
            }

            if( empty($where) )
            {
                $where = $w;
            }
            else
            {
                $where = $this->_where_to_string($where);
                $where .= " AND ($w)";
            }
        }

        return parent::_get_select($where,$columns);
    }
}

/**
* Items that go into a cart
*/
class CCCartItems extends CCTable
{
    function CCCartItems()
    {
        $this->CCTable( 'cc_tbl_cart_items', 'cart_item_id');
    }

    function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCCartItems();
        return $_table;
    }

}

/**
* Specialized version of cart for playlists
*/
class CCPlaylist extends CCCart
{
    function CCPlaylist($do_joins=false)
    {
        $this->CCCart($do_joins);
        $this->LimitType('playlist');
    }

    function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCPlaylist();
        return $_table;
    }
}

/**
* Specialized version of cart items for playlists
*/
class CCPlaylistItems extends CCCartItems 
{
    var $_has_cart_join;

    function CCPlaylistItems()
    {
        $this->CCCartItems();
        $this->_has_cart_join = false;
    }

    function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCPlaylistItems();
        return $_table;
    }

    function AddJoin($other_cctable, $joinfield, $jointype = 'LEFT OUTER' )
    {
        $join = parent::AddJoin( $other_cctable, $joinfield, $jointype  );
        $other_class = strtolower(get_class($other_cctable));
        if(  $other_class == 'cccart' || is_subclass_of($other_cctable, 'CCCart') )
            $this->_cart_join = $join;

        return $join;
    }

    // overwrite parent's version to add type limit
    function _get_select($where,$columns='*')
    {
        if( !empty($this->_cart_join) )
        {
            $w = "cart_type = 'playlist'";

            if( empty($where) )
            {
                $where = $w;
            }
            else
            {
                $where = $this->_where_to_string($where);
                $where .= " AND ($w)";
            }
        }

        if( !empty($this->_upload_filter) )
        {
            $where = $this->_where_to_string($where);
            $where .= " AND ({$this->_upload_filter})";
        }

        return parent::_get_select($where,$columns);
    }
}


?>