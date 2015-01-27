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
* $Id: cc-blog-ping.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* incomplete
* @package cchost
* @subpackage extras
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

/**
* Form for configuration the file format verification module
* !!incomplete!!
* @package cchost
* @subpackage admin
*
*/
class CCAdminBlogPingForm extends CCEditConfigForm
{
    /**
    * Constructor
    *
    */
    function CCAdminBlogPingForm()
    {
        $this->CCEditConfigForm('blog-ping');

        $fields = array(
                'pingurl' =>
                        array(  'label'      => "Ping URL:",
                               'form_tip'    => 'Starts with http://',
                               'formatter'   => 'textedit',
                               'flags'       => CCFF_POPULATE )
            );

        $this->AddFormFields( $fields );
        $this->SetModule( ccs(__FILE__) );
    }
}

/**
*
*
*/
class CCBlogPing
{

    function Admin()
    {
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Administer Blog Notification'));
        $form = new CCAdminBlogPingForm();
        $page->AddForm( $form->GenerateForm() );
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        return; // not implemented yet.

        if( $scope != CC_GLOBAL_SCOPE )
        {
            $items += array(
                'blog-ping'   => array( 
                                 'menu_text'    => _('Blog ping'),
                                 'menu_group'   => 'configure',
                                 'help'         => _('Ping your blog on new uploads'),
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 40,
                                 'action' =>  ccl('admin','ping')
                                 ),
                );
        }
    }

    /**
    * Event handler for {@link CC_EVENT_UPLOAD_DONE}
    * 
    * @param integer $upload_id ID of upload row
    * @param string $op One of {@link CC_UF_NEW_UPLOAD}, {@link CC_UF_FILE_REPLACE}, {@link CC_UF_FILE_ADD}, {@link CC_UF_PROPERTIES_EDIT'} 
    * @param array &$parents Array of remix sources
    */
    function OnUploadDone($upload_id, $op)
    {
        if( $op != CC_UF_NEW_UPLOAD )
            return;

        $configs =& CCConfigs::GetTable();
        $ping_settings = $configs->GetConfig('blog-ping');
        if( empty( $ping_settings ) || empty( $ping_settings['pingurl'] ) )
            return;

        $uploads =& CCUploads::GetTable();
        $record = $uploads->GetRecordFromKey($upload_id);

        if( empty($record) )
            return;

        $URL_to_ping = $ping_settings['pingurl'];

        // At this point the $record holds all the information
        // for the newly uploaded item, use 
        
        // CCDebug::PrintVar($record);

        // to dump the contents

        // also see http://beta.cmixter.org/cctools/apidoc/record_dump.html

        global $CC_GLOBALS;

        /*
        $tfile = CCTemplate:GetTemplate( 'blog_ping.xml' );
        $template = new CCTemplate( $tfile , false ); // false means XML
        $args = array( 'something' => 'value' );
        $xml = $template->SetAllAndParse($args);
        */

        // I use Snoopy because I was told not to trust curl (not
        // to mention curl is not compiled into many php installations)

        require_once('cchost_lib/snoopy/Snoopy.class.php');
        $snoopy = new Snoopy();
        
        // This does a GET:

        // $ok = $snoopy->fetch($URL_to_ping);

        // This does a POST:

        // $post_vars = array( 'data' => $xml );
        // $ok = $snoopy->Submit($URL_to_ping, $post_vars);

        $page =& CCPage::GetPage();
        $page->Prompt("Blog ($URL_to_ping) pinged for {$record['upload_name']}");
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','ping'),  array( 'CCBlogPing', 'Admin'), 
                          CC_ADMIN_ONLY, ccs(__FILE__) );
    }

}



?>
