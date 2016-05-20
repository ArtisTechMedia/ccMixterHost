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
* $Id: cc-user.php 12611 2009-05-13 19:24:00Z fourstones $
*
*/

/**
* @package cchost
* @subpackage user
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


class CCUser
{
    public static function IsLoggedIn()
    {
        global $CC_GLOBALS;

        return( !empty($CC_GLOBALS['user_name']) );
    }

    public static function IsSuper($name='')
    {
        if( !CCUtil::IsHTTP() )
            return true;

        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['supers']) )
            return false; // err...

        if( empty($name) )
            $name = CCUser::CurrentUserName();
        $ok = !empty($name) && (preg_match( "/(^|\W|,)$name(\W|,|$)/i",$CC_GLOBALS['supers']) > 0);

        return $ok;
    }

    public static function IsAdmin($name='')
    {
        static $checked;

        if( empty($name) && isset($checked) )
        {
            return $checked;
        }
        else
        {
            if( empty($name) && (!CCUtil::IsHTTP() || CCUser::IsSuper($name)) )
            {
                $checked = true;
                return true;
            }

            $configs =& CCConfigs::GetTable();
            $settings = $configs->GetConfig('settings');
            $_admins = $settings['admins'];

            if( empty($name) )
                $name = CCUser::CurrentUserName();

            $checked = !empty($name) && (preg_match( "/(^|\W|,)$name(\W|,|$)/i",$_admins) > 0);

            return $checked;
        }
    }

    public static function CurrentUser()
    {
        global $CC_GLOBALS;

        return( CCUser::IsLoggedIn() ? intval($CC_GLOBALS['user_id']) : -1 );
    }


    public static function CurrentUserName()
    {
        global $CC_GLOBALS;

        return( CCUser::IsLoggedIn() ? $CC_GLOBALS['user_name'] : '' );
    }

    public static function CurrentUserField($field)
    {
        global $CC_GLOBALS;

        return( CCUser::IsLoggedIn() ? $CC_GLOBALS[$field] : '' );
    }

    public static function GetUserName($userid)
    {
        if( $userid == CCUser::CurrentUser() )
            return( CCUser::CurrentUserName() );

        $users =& CCUsers::GetTable();
        return( $users->QueryItemFromKey('user_name',$userid) );
    }

    public static function CheckCredentials($usernameorid)
    {
        $id     = CCUser::CurrentUser();
        $argid  = intval($usernameorid);
        $name   = CCUser::CurrentUserName();
        $bad = !$id || (($id !== $argid) && (strcmp($name,$usernameorid) != 0)) ;
        if( $bad )
        {
           CCUtil::AccessError();
        }
    }

    // N.B. we don't validate if the argument is valid if it's already
    // numeric (and I'm too afraid to change this behavoir at this point)
    public static function IDForName($username_or_id)
    {        
        if( (int)$username_or_id > 0 ) {
            return $username_or_id;
        }
        return CCDatabase::QueryItem(
                  'SELECT user_id FROM cc_tbl_user WHERE user_name = \'' .
                  strtolower($username_or_id) . '\'' );
    }

    public static function NameForID($username_or_id)
    {
        if( empty($username_or_id) ) {
            return null;
        }
        if( (int)$username_or_id > 0 ) {
            $sql = 'SELECT user_name FROM cc_tbl_user WHERE user_id = ' . $username_or_id;
        } else {
            $sql = 'SELECT user_name FROM cc_tbl_user WHERE LOWER(user_name) = \'' .
                 strtolower($username_or_id) . '\'';
        }

        return CCDatabase::QueryItem($sql);
    }

    public static function IDFromName($username_or_id) {
        return CCUser::IDForName($username_or_id);
    }
    public static function NameFromID($username_or_id) {
        return CCUser::NameForID($username_or_id);
    }

    
    /**
    * Digs around the cookies looking for an auto-login. If succeeds, populate CC_GLOBALS with user data
    */
    public static function InitCurrentUser()
    {
        global $CC_GLOBALS;

        if( !empty($_COOKIE[CC_USER_COOKIE]) )
        {
            $users =& CCUsers::GetTable();
            $val = $_COOKIE[CC_USER_COOKIE];
            if( is_string($val) )
            {
                $val = unserialize(stripslashes($val));
                $record = CCDatabase::QueryRow( 'SELECT * FROM cc_tbl_user WHERE user_name = \'' . $val[0]  . '\'' );
                if( !empty($record) )
                {
                    $record['user_extra'] = unserialize($record['user_extra']);
                    if( !empty( $record ) && ($record['user_password'] == $val[1]) )
                    {
                        $CC_GLOBALS = array_merge($CC_GLOBALS,$record);
                        $users->SaveKnownIP();
                    }
                }
            }
        }
    }

    public static function GetPeopleDir()
    {
        global $CC_GLOBALS;
        return( empty($CC_GLOBALS['user-upload-root']) ? 'content' : 
                            $CC_GLOBALS['user-upload-root'] );
    }

    public static function GetUploadDir($name_or_row)
    {
        if( is_array($name_or_row) )
            $name_or_row = $name_or_row['user_name'];

        return( CCUser::GetPeopleDir() . '/' . $name_or_row );
    }

    /**
    * Event handler for {@link CC_EVENT_PATCH_MENU}
    * 
    */
    function OnPatchMenu(&$menu)
    {
        $page =& CCPage::GetPage();
        
        $current_user_name = $this->CurrentUserName();

        // technically this isn't supposed to happen

        if( empty($menu['artist']['action']) )
        {
            $page->Prompt(_('Attention: Menus have been corrupted'));
            return;  
        }

        // fwiw, this whole thing is a heck, what really
        // should happen is that admins should be able
        // to access *any* CC_GLOBAL variable in any menu
        // item.

        $keys = array_keys($menu);
        $count = count($keys);
        for( $i = 0; $i < $count; $i++ )
        {
            $M =& $menu[$keys[$i]];
            $M['action'] = str_replace('%login_name%',$current_user_name,$M['action']);
        }
    }

    public static function AddUserBreadCrumbs($text,$more=array(),$userrec=array())
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        global $CC_GLOBALS;
        if( empty($userrec) )
            $userrec =& $CC_GLOBALS;
        $bc = array();
        if( empty($userrec['user_name']) )
        {
            $bc[] = array( 'url' => '', 'text' => 'invalid user info in breadcrumbs...' );
        }
        else
        {
            $bc[] = array( 'url' => ccl(), 'text' => 'str_home' );
            $bc[] = array( 'url' => ccl('people'), 'text' => 'str_people' );
            $url = empty($text) ? '' : ccl('people',$userrec['user_name'] );
            $bc[] = array( 'url' => $url, 'text' => $userrec['user_real_name'] );
            if( !empty($more) )
            {
                foreach( $more as $M )
                    $bc[] = $M;
            }
            if( !empty($text) )
                $bc[] = array( 'url' => '', 'text' => $text );
        }
        $page->AddBreadCrumbs($bc);
    } 
    
}

class CCUsers extends CCTable
{
    function CCUsers()
    {
        global $CC_SQL_DATE;

        $this->CCTable( 'cc_tbl_user','user_id');
        $this->AddExtraColumn("DATE_FORMAT(user_registered, '$CC_SQL_DATE') as user_date_format");
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCUsers();
        return $_table;
    }

    function SetExtraField($user_id,$name,$data)
    {
        $extra = $this->QueryItemFromKey('user_extra',$user_id);
        $args['user_extra'] = unserialize($extra);
        $args['user_extra'][$name] = $data;
        $args['user_extra'] = serialize($args['user_extra']);
        $args['user_id'] = $user_id;
        $this->Update($args);
    }

    function GetExtraField($user_id,$name)
    {
        $extra = $this->QueryItemFromKey('user_extra',$user_id);
        $extra = unserialize($extra);
        return empty($extra[$name]) ? null : $extra[$name];
    }

    function UnsetExtraField($user_id,$name)
    {
        $extra = $this->QueryItemFromKey('user_extra',$user_id);
        $extra = unserialize($extra);
        if( !isset($extra[$name]) )
            return;
        unset($extra[$name]);
        $args['user_extra'] = serialize($extra);
        $args['user_id'] = $user_id;
        $this->Update($args);
    }

    // depricated (will break your site)
    function & GetRecordFromID($userid)
    {
        $row = $this->QueryKeyRow($userid);
        if( empty($row) )
        {
            // this is a pretty bad state of affairs
            // the user account was deleted and the 
            // caller doesn't know it
            $a = array();
            return $a;
        }
        $r =& $this->GetRecordFromRow($row);
        return $r;
    }

    function SaveKnownIP()
    {
        global $CC_GLOBALS;
    
        // we don't care about anon users
        if( empty($CC_GLOBALS['user_id']) )
            return;

        $ip    = CCUtil::EncodeIP($_SERVER['REMOTE_ADDR']);
        $dbip  = substr($CC_GLOBALS['user_last_known_ip'],0,8);
     
        if( empty($dbip) || ($ip != $dbip) )
        {
            $where['user_id'] = $CC_GLOBALS['user_id'];
            $where['user_last_known_ip'] = $ip . date('YmdHis');
            $this->Update($where);
        }
    }
}

?>
