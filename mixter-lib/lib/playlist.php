<?
/**
* Implements playlist feature
*
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('mixter-lib/lib/status.inc');

define('PLAYLIST_TEST_UPLOAD',    1);
define('PLAYLIST_TEST_PLAYLIST',  2);
define('PLAYLIST_TEST_OWNER',     4);
define('PLAYLIST_TEST_ALL',       PLAYLIST_TEST_UPLOAD | PLAYLIST_TEST_PLAYLIST | PLAYLIST_TEST_OWNER);

define('PLAYLIST_NOT_FOUND',    'playlist not found');
define('PLAYLIST_EMPTY_QUERY',  'empty query string');
define('PLAYLIST_NOT_ALLOWED',  'wrong permissions');

require_once('cchost_lib/ccextras/cc-cart-table.inc');

class CCLibPlaylists
{

    function RemoveUploadFromAllPlaylists($upload_id)
    {
        $sql =<<<EOF
            SELECT cart_item_cart
            FROM cc_tbl_cart_items
            WHERE cart_item_upload = {$upload_id}
EOF;
        $carts = CCDatabase::QueryItems($sql);
        if( !empty($carts) )
        {
            $cart_set = join(',',$carts);
            $sql =<<<EOF
                UPDATE cc_tbl_cart 
                SET cart_num_items = cart_num_items-1
                WHERE cart_id IN ({$cart_set})
EOF;
            CCDatabase::Query($sql);
            $sql =<<<EOF
                DELETE FROM cc_tbl_cart_items
                WHERE cart_item_upload = {$upload_id}
EOF;
            CCDatabase::Query($sql);
        }
        return _make_ok_status();
    }

    function PlaylistForUser( $user_id, $type, $auto_create, $name )
    {
        $sql = "SELECT cart_id FROM cc_tbl_cart WHERE cart_user = '{$user_id}' and cart_subtype = '{$type}'";
        $cart_id = CCDatabase::QueryItem($sql);
        if( empty($cart_id) && $auto_create)
        {
            $api = new CCPlaylists();
            $status = $this->CreatePlaylistSimple($user_id, $upload_id, $name, '');
            if( $status->ok() ) {
                return _make_ok_status( $status->data['cart_id'] );
            } else {
                return $status;
            }
        }
        return _make_ok_status($cart_id);
    }

    function UpdateDynamic($user_id, $playlist_id, $args) 
    {
        if( !$this->_verifyPlaylist($user_id,0,$playlist_id, PLAYLIST_TEST_PLAYLIST | PLAYLIST_TEST_OWNER) ) {
            return _make_err_status(PLAYLIST_NOT_FOUND);
        }

        $status = $this->SerializeQueryArgs($args);

        if( $status->ok() ) {
            $string = $status->data;
            $carts  =& CCPlaylist::GetTable();
            $values = array( 'cart_id' => $playlist_id, 
                             'cart_dynamic' => $string );
            $carts->Update($values);
            $status = _make_ok_status();
        }
        return $status;
    }

    function UpdateProperties($user_id,$playlist_id,$name,$tags,$desc,$featured)
    {
        if( !$this->_verifyPlaylist($user_id,0,$playlist_id, PLAYLIST_TEST_PLAYLIST | PLAYLIST_TEST_OWNER) ) {
            return _make_err_status(PLAYLIST_NOT_FOUND);
        }

        $carts =& CCPlaylist::GetTable();

        $values = array( 'cart_id' => $playlist_id );      

        if( !is_null($name) ) {
            $values['cart_name'] = substr($name,0,60);
        }
        if( !is_null($tags) ) {
            $values['cart_tags'] = $tags;
        }
        if( !is_null($desc) ) {
            $values['cart_desc'] = $desc;
        }
        if( !is_null($featured) ) {
            if( !CCUser::IsAdmin() ) {
                return _make_err_status(PLAYLIST_NOT_ALLOWED);
            }
            $values['cart_subtype'] = $featured ? 'featured' : '';
        }

        $carts->Update($values);

        return _make_ok_status();
    }

    function Feature($playlist_id) 
    {
        if( $this->_verifyPlaylist(0,0,$playlist_id,PLAYLIST_TEST_PLAYLIST) ) {
            $carts =& CCPlaylist::GetTable();
            $sub_type = $carts->QueryItemFromKey('cart_subtype', $playlist_id); 
            $w['cart_id'] = $playlist_id;
            if( $sub_type == 'featured') {
                $w['cart_subtype'] = '';
            } else {
                $w['cart_subtype'] = 'featured';
            }
            $carts->Update($w);
            return _make_ok_status();
        }
        return _make_err_status(PLAYLIST_NOT_FOUND);
    }

    function DeletePlaylist($user_id,$playlist_id)
    {
        if( !$this->_verifyPlaylist( $user_id, 0, $playlist_id, PLAYLIST_TEST_PLAYLIST | PLAYLIST_TEST_OWNER ) ) {
            return _make_err_status(PLAYLIST_NOT_FOUND);
        }

        $cartitems =& CCPlaylistItems::GetTable();
        $where['cart_item_cart'] = $playlist_id;

        // update counts for uploads in this list
        $uploads = $cartitems->QueryItems('cart_item_upload',$where);
        foreach ($uploads as $id) {
            $this->_doRemove($id,$playlist_id);
            $this->SyncUpload($id);
        }

        // delete the item references
        $cartitems->DeleteWhere($where);

        // delete the playlist meta
        $carts =& CCPlaylist::GetTable();
        $carts->DeleteKey($playlist_id);

        return _make_ok_status();
    }

    function CreatePlaylist($sub_type='',$title_str = '',$desc ='',$user_id=0,$queryArgs=null,$tags='')
    {
        global $CC_GLOBALS;

        if( !empty($queryArgs) ) {
            $status = $this->SerializeQueryArgs($queryArgs);
            if( !$status->ok() ) {
                return $status;
            }
            $query = $status->data;
        } else {
            $query = '';
        }

        $carts =& CCPlaylist::GetTable();
        $record['cart_id']      = $carts->NextID();
        $record['cart_user']    = $user_id;
        $record['cart_type']    = 'playlist';
        $record['cart_subtype'] = $sub_type;
        $record['cart_name']    = $title_str;
        $record['cart_desc']    = $desc;
        $record['cart_date']    = date('Y-m-d H:i:s');
        $record['cart_dynamic'] = $query;
        $record['cart_tags']    = $tags;
        $carts->Insert($record);
        return _make_ok_status($record);
    }

    function CreatePlaylistSimple($user_id, $user_name, $upload_id, $name, $desc)
    {
        if( empty($name) )
            $name = $this->GenerateNameForNewPlaylist($user_id,$user_name);

        $status = $this->CreatePlaylist('',$name, $desc,$user_id);
        if( !$status->ok() ) {
            return $status;
        }
        $record = $status->data;
        $playlist_id = $record['cart_id'];

        if( !empty($upload_id) )
        {
            if( !$this->_verifyPlaylist($user_id,$upload_id,0,PLAYLIST_TEST_UPLOAD) ) {
                return _make_err_status(PLAYLIST_NOT_FOUND);
            }

            $status = $this->AddTrackToPlaylist($user_id,$upload_id,$playlist_id);
            if( !$status->ok() ) {
                return $status;
            }
        }
        return _make_ok_status($record);
    }


    // copied from cc-playlists-forms
    function EmptyPlaylist($user_id,$playlist_id)
    {
        if( !$this->_verifyPlaylist( $user_id, 0, $playlist_id, PLAYLIST_TEST_PLAYLIST | PLAYLIST_TEST_OWNER ) ) {
            return _make_err_status(PLAYLIST_NOT_FOUND);
        }
        $cartitems =& CCPlaylistItems::GetTable();
        $w['cart_item_cart'] = $playlist_id;
        $cartitems->DeleteWhere($w);
        $carts =& CCPlaylist::GetTable();
        $wx['cart_num_items'] = 0;
        $wx['cart_id'] = $playlist_id;
        $carts->Update($wx);
        return _make_ok_status();
    }

    function SerializeQueryArgs($args) 
    {
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        if( !empty($args['title']) ) { unset($args['title']); }
        if( !empty($args['limit']) ) { unset($args['limit']); }
        if( !empty($args['u'] ) ) { 
            $args['user'] = $args['u'];
        }
        $qstring = $query->SerializeArgs($args);
        if( empty($qstring) )
            return _make_err_status(PLAYLIST_EMPTY_QUERY);

        return _make_ok_status($qstring);
    }

    function Reorder( $user_id, $playlist_id, $order_spec )
    {
        if( !$this->_verifyPlaylist( $user_id, 0, $playlist_id, PLAYLIST_TEST_PLAYLIST | PLAYLIST_TEST_OWNER ) ) {
            return _make_err_status(PLAYLIST_NOT_FOUND);
        }

        // there are much more efficient ways of doing this, but by using
        // the exact same dataview as the edit screen, we are assured
        // fidelity between the two (e.g. skipping moderated and hidden tracks)
        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        
        $args = $query->ProcessAdminArgs('dataview=playlist_reorder&f=php&playlist='.$playlist_id);
        list( $recs, $m ) = $query->Query($args);
        $ids = array();
        foreach( $recs as $rec )
            $ids[] = $rec['cart_item_id'];
            
        $ups = array();
        foreach( $order_spec as $new_pos => $old_pos )
        {
            // $old_pos is '1' indexed
            $id = $ids[$old_pos-1];
            $sql = "UPDATE cc_tbl_cart_items SET cart_item_order = $new_pos WHERE cart_item_id = $id";
            CCDatabase::Query($sql);
        }
        return _make_ok_status();
    }

    function GenerateNameForNewPlaylist( $user_id, $user_name )
    {
        global $CC_GLOBALS;

        $carts =& CCPlaylist::GetTable();
        $wuser['cart_user'] = $user_id;
        $num = $carts->CountRows($wuser); // $CC_GLOBALS['user_real_name']
        return sprintf( _("%s's Collection (%d) "),$user_name, $num + 1);
    }

    function _verifyPlaylist( $user_id, $upload_id, $playlist_id, $flags )
    {
        $ok = true;
        if( $flags & PLAYLIST_TEST_UPLOAD )
        {
            $upload_id = sprintf('%0d',CCUtil::Strip($upload_id));
            $ok = !empty($upload_id) && ($upload_id > 0);
        }
        if( $ok && ($flags & PLAYLIST_TEST_PLAYLIST) )
        {
            $playlist_id = sprintf('%0d',CCUtil::Strip($playlist_id));
            $ok = !empty($playlist_id) && ($playlist_id > 0);
            if( $ok )
                $ok = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_cart WHERE cart_id='.$playlist_id);
        }
        if( $ok && ($flags & PLAYLIST_TEST_OWNER) )
        {
            $owner = CCDatabase::QueryItem('SELECT cart_user FROM cc_tbl_cart WHERE cart_id='.$playlist_id);
            $ok = !empty($owner) && ($owner == $user_id);
        }
        return $ok;
    }

    function AddTrackToPlaylist($user_id, $upload_id, $playlist_id)
    {
        if( !$this->_verifyPlaylist( $user_id, $upload_id, $playlist_id, PLAYLIST_TEST_ALL ) ) {
            return _return_status(PLAYLIST_NOT_FOUND);
        }

        $cart_items =& CCPlaylistItems::GetTable();
        $check['cart_item_upload'] = $upload_id;
        $check['cart_item_cart']   = $playlist_id;
        $check_row = $cart_items->CountRows($check);
        if( $check_row )
        {
            return _make_ok_status();
        }

        $carts =& CCPlaylist::GetTable();
        $carts->Inc('cart_num_items',$playlist_id);

        $cart_items =& CCPlaylistItems::GetTable();
        $record['cart_item_cart'] = $playlist_id;
        $count = $cart_items->CountRows($record);
        $record['cart_item_upload'] = $upload_id;
        $record['cart_item_order'] = $count;
        $cart_items->Insert($record);

        return $this->SyncUpload($upload_id);
    }

    function RemoveTrackFromPlaylist($user_id,$upload_id,$playlist_id)
    {
        if( !$this->_verifyPlaylist($user_id,$upload_id,$playlist_id, PLAYLIST_TEST_ALL) ) {
            return _make_err_status(PLAYLIST_NOT_FOUND);
        }
        $this->_doRemove($upload_id,$playlist_id);
        return $this->SyncUpload($upload_id);        
    }

    function _doRemove($upload_id,$playlist_id) {
        $cart_items =& CCPlaylistItems::GetTable();
        $w['cart_item_upload'] = $upload_id;
        $w['cart_item_cart'] = $playlist_id;
        $cart_items->DeleteWhere($w);
        $carts =& CCPlaylist::GetTable();
        $carts->Dec('cart_num_items',$playlist_id);        
    }

    function SyncUpload($upload_id)
    {
        $sql =<<<EOF
        UPDATE cc_tbl_uploads SET upload_num_playlists = (
            SELECT COUNT( * )
                FROM cc_tbl_cart_items
                WHERE cart_item_upload = upload_id
            )
        WHERE upload_id = {$upload_id}
EOF;
        CCDatabase::Query($sql);
        return _make_ok_status();
    }

}

?>