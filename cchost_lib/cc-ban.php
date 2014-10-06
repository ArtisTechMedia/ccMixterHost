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
* $Id: cc-ban.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/** 
* Admin user interface for banning users
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Ban API used by admins to moderate uploads
*
*/
class CCBanHV
{

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_MENU}
    * 
    * The handler is called when a menu is being displayed with
    * a specific record. All dynamic changes are made here
    * 
    * @param array $menu The menu being displayed
    * @param array $record The database record the menu is for
    */
    function OnUploadMenu(&$menu,&$record)
    {
        $isowner = CCUser::CurrentUser() == $record['user_id'];

        if( CCUser::IsAdmin() )
        {
            $menu['ban'] = 
                         array(  'menu_text'  => 'Ban',
                                 'weight'     => 1001,
                                 'group_name' => 'admin',
                                 'id'         => 'bancommand',
                                 'access'     => CC_ADMIN_ONLY );

            if( $record['upload_banned'] > 0 )
                $menu['ban']['menu_text'] = 'UnBan';

            $menu['ban']['action']  = ccl('admin','ban', $record['upload_id']);
        }
    }
    
}

?>