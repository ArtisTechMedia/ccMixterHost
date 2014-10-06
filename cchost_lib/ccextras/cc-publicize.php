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
* $Id: cc-publicize.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE,array( 'CCPublicizeHV',  'OnFilterUserProfile') );
CCEvents::AddHandler(CC_EVENT_UPLOAD_MENU,        array( 'CCPublicizeHV',  'OnUploadMenu') );
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'CCPublicize',  'OnMapUrls'),   'cchost_lib/ccextras/cc-publicize.inc');

/**
*/
class CCPublicizeHV
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
        require_once('cchost_lib/cc-template.php');
        $rurl = CCTemplate::Search('images/shareicons') . '/';
        $menu['share_link'] = 
                     array(  'menu_text'  => 'str_share',
                             'weight'     => 10,
                             'id'         => 'sharecommand',
                             'group_name' => 'share',
                             'tip'        => _('Bookmark, share, embed...'),
                             'access'     => CC_DONT_CARE_LOGGED_IN,
                        );

        $url = ccl('share', $record['upload_id'] );
        $jscript = "window.open( '$url', 'cchostsharewin', 'status=1,toolbar=0,location=0,menubar=0,directories=0,resizable=1,scrollbars=1,height=480,width=550');";

        $menu['share_link']['class']   = "cc_share_button";
        $menu['share_link']['action']  = $url;
        $menu['share_link']['onclick'] = '';
    }


    function OnFilterUserProfile(&$rows)
    {
        $row =& $rows[0];
        $itsme = CCUser::CurrentUser() == $row['user_id'];

        if( $this->_pub_wizard_allowd($itsme) )
        {
            $url = ccl('publicize',$row['user_name'] );
            $text = $itsme ? array( _('str_publicize_yourself_s'), "<a href=\"$url\">", '</a>' )
                           : array( _('str_publicize_s'), "<a href=\"$url\">{$row['user_real_name']}</a>" );
                
            $row['user_fields'][] = array( 'label' => 'str_publicize', 
                                       'value' =>  $text );
        }
    }

    function _pub_wizard_allowd($itsme)
    {
        global $CC_GLOBALS;

        return !empty($CC_GLOBALS['pubwiz']) &&
               (
                    $CC_GLOBALS['pubwiz'] == CC_DONT_CARE_LOGGED_IN ||
                    (
                        ($CC_GLOBALS['pubwiz'] == CC_MUST_BE_LOGGED_IN) && $itsme
                    )
               );
    }

} // end of class CCQueryFormats


?>
