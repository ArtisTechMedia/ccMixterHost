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
* $Id: cc-playlist-forms.inc 12599 2009-05-12 14:04:34Z fourstones $
*
*/

/**
* Implements playlist feature
*
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*
*/
require_once('cchost_lib/ccextras/cc-cart-table.inc');
require_once('cchost_lib/ccextras/cc-playlist.inc');
require_once('cchost_lib/cc-form.php');
require_once('cchost_lib/cc-admin.php');
require_once('cchost_lib/cc-page.php');

class CCPlaylistForm extends CCForm
{
    function CCPlaylistForm($dynamic,$playlist_id)
    {
        $this->CCForm();
        $fields = array(
            'cart_name' => 
                        array( 'label'      => _('Name'),
                               'formatter'  => 'textedit',
                               'form_tip'   => _(''),
                               'flags'      => CCFF_POPULATE | CCFF_REQUIRED),
            'cart_tags' => 
                        array( 'label'      => _('Tags'),
                               'formatter'  => 'tagsedit',
                               'form_tip'   => _('(e.g. chill, high_energy, guitar_stuff)'),
                               'flags'      => CCFF_POPULATE),
            'cart_desc' =>
                        array( 'label'      => _('Description'),
                               'formatter'  => 'textarea',
                               'want_formatting' => true,
                               'form_tip'   => _(''),
                               'flags'      => CCFF_POPULATE),
            );

        if( $dynamic )
        {
            $url = ccl('playlist','editquery',$playlist_id);
            $fields += array(
                '_dynlink' => 
                            array( 'label'      => _('Dynamic Query'),
                                   'url'        => $url,
                                   'value'      => _('Edit dynamic query'),
                                   'formatter'  => 'button',
                                   'form_tip'   => _('Edit the dynamic query for this playlist'),
                                   'flags'      => CCFF_NOUPDATE | CCFF_STATIC ),
                );
        }
        else
        {
            $url = ccl('playlist','editorder',$playlist_id);
            $fields += array(
                '_dynlink' => 
                            array( 'label'      => _('Track Order'),
                                   'url'        => $url,
                                   'value'      => 'str_pl_edit_track_order',
                                   'formatter'  => 'button',
                                   'form_tip'   => _('Edit the order in which the tracks play'),
                                   'flags'      => CCFF_NOUPDATE | CCFF_STATIC ),
                );
        }

        $this->AddFormFields($fields);
        $this->SetFormHelp('str_pl_tags_are_fun');
    }

    function SetSuggestedTags($tags)
    {
        $fields['suggested_tags'] = 
                    array( 'label'      => 'str_suggested_tags',
                           'target'     => 'cart_tags',
                           'tags'       => $tags,
                           'formatter'  => 'metalmacro',
                           'macro'      => 'popular_tags',
                           'form_tip'   => 'str_pl_click_on_these',
                           'flags'      => CCFF_STATIC | CCFF_NOUPDATE );

        $this->InsertFormFields( $fields, 'after', 'cart_tags' );

    }
}

class CCConfirmPlaylistDeleteForm extends CCForm
{
    function CCConfirmPlaylistDeleteForm()
    {
        $this->CCForm();
        $this->SetHelpText(_('This action can not be reversed...'));
        $this->SetSubmitText(_("Are you sure you want to delete this playlist?"));
    }
}

class CCConfirmPlaylistEmptyForm extends CCForm
{
    function CCConfirmPlaylistEmptyForm()
    {
        $this->CCForm();
        $this->SetHelpText(_('This action can not be reversed...'));
        $this->SetSubmitText(_("Are you sure you want to empty all contents this playlist?"));
    }
}

class CCAdminPlaylistsForm extends CCEditConfigForm
{
    function CCAdminPlaylistsForm()
    {
        $this->CCEditConfigForm('config',CC_GLOBAL_SCOPE);
        $this->SetModule(ccs(__FILE__));

        require_once('cchost_lib/cc-template.inc');
        $players = CCTemplateAdmin::GetPropType( 'players','embedded_player');
        
        $fields = array(

            'enable_playlists' =>
               array(  'label'      => 'str_pl_enable',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE ),

            'playlist_promo_tag' =>
               array(  'label'      => _('Promomotion tag for playlist'),
                       'form_tip'   => _('This tag will be mixed in with dynamic playlists (audio sites only)'),
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE ),
                       
            'embedded_player' =>
                array( 'label'       => _('Embedded Audio Player'),
                       'form_tip'    => _('(audio sites only)'),
                       'formatter'   => 'select',
                       'options'     => $players,
                       'flags'       => CCFF_POPULATE ),

                );


        $this->AddFormFields($fields);
    }
}

class CCPlaylistManage
{
    function Admin()
    {
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $page =& CCPage::GetPage();
        $title = 'str_pl_manage';
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        $page->SetTitle($title);
        $form = new CCAdminPlaylistsForm();
        $page->AddForm($form->GenerateForm());
    }

    /**
    * Event handler for {@link CC_EVENT_ADMIN_MENU}
    *
    * @param array &$items Menu items go here
    * @param string $scope One of: CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    */
    function OnAdminMenu(&$items,$scope)
    {
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $items += array( 
                'playlists'   => array( 'menu_text'  => 'str_playlists',
                                 'menu_group' => 'configure',
                                 'help'      => 'str_pl_manage',
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 700,
                                 'action' =>  ccl('admin','playlists')
                                 ),
                );
        }
    }

    function Edit($playlist_id='')
    {
        $url = 'http://beta.ccmixter.org/playlist/browse/' . $playlist_id;
        CCUtil::SendBrowserTo($url);
        /*

        $api = new CCPlaylists();
        $api->_test_pl('',$playlist_id, /* test upload: * / false, 
                                        /* test_playlist * / true,
                                        /* test_owner * / true);

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $api->_build_bread_crumb_trail($playlist_id,'str_pl_edit_properties');
        $page->SetTitle('str_pl_edit_properties');
        $carts =& CCPlaylist::GetTable();
        $row = $carts->QueryKeyRow($playlist_id);
        $form = new CCPlaylistForm($row['cart_dynamic'],$playlist_id,array());
        $show = false;
        if( empty($_POST['playlist']) )
        {
            $form->PopulateValues($row);
            $show = true;
        }
        else
        {
            $show = !$form->ValidateFields();
        }
        if( $show  )
        {
            $tags = $this->_get_suggested_tags($row);
            if( !empty($tags) )
                $form->SetSuggestedTags($tags);
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $values['cart_name'] = substr($values['cart_name'],0,60);
            $values['cart_id'] = $playlist_id;
            $carts->Update($values);
            $url = '';
            $refer = $form->GetFormValue('http_referer');
            if( empty($refer) )
            {
                if( !empty($_POST['http_referer']) )
                    $refer = $_POST['http_referer'];
            }
            if( !empty($refer) )
            {
                if( strpos(urldecode($refer),'playlist/save') !== false )
                {
                    $url = ccl('playlist','browse',$playlist_id);
                }
            }
            CCUtil::SendBrowserTo($url);
        }
        */
    }


    function _get_suggested_tags($row)
    {

        $playlist_id = $row['cart_id'];
        $query = $row['cart_dynamic'];

        if( empty($query) )
        {
            $sql =<<<EOF
            SELECT upload_tags
            FROM cc_tbl_uploads
            JOIN cc_tbl_cart_items ON upload_id=cart_item_upload
            JOIN cc_tbl_cart ON cart_item_cart=cart_id
            WHERE cart_id = $playlist_id
EOF;

            $all_tags = CCDatabase::QueryItems($sql);
            if( empty($all_tags) )
                return;
        }
        else
        {
            $all_tags = array();
            parse_str($query,$args);
            if( !empty($args['reqtags']) )
                $all_tags[] = ','.$args['reqtags'].',';
            if( !empty($args['tags']) )
                $all_tags[] = ','.$args['tags'].',';
            if( empty($all_tags) )
                return;
        }
        
        $all_tags = array_unique(preg_split('/,/',join('',$all_tags),0,PREG_SPLIT_NO_EMPTY));
        if( !empty($row['cart_tags']) )
        {
            $cart_tags = cc_split(',',$row['cart_tags']);
            $all_tags = array_diff($all_tags,$cart_tags);
            if( empty($all_tags) )
                return;
        }

        $tagstr = join("', '", $all_tags);

        $sql =<<<EOF
            SELECT tags_tag
            FROM cc_tbl_tags
            WHERE (tags_count > 22) AND (tags_type = 4) && (tags_tag IN ('{$tagstr}'))
EOF;
        $tags = CCDatabase::QueryItems($sql);
        return $tags;
    }


    function _send_prompt($msg)
    {
        $url = ccl('people', CCUser::CurrentUserName(), 'playlists' );
        $link = "<a href=\"$url\">";
        $link2 = '</a>';
        $page =& CCPage::GetPage();
        $page->Prompt($msg . ' ' . sprintf(_('Return to %syour playlists%s'),$link,$link2));
    }

    function EmptyPL($playlist_id='')
    {
        $api = new CCPlaylists();
        $api->_test_pl('',$playlist_id, /* test upload: */ false, 
                                        /* test_playlist */ true,
                                        /* test_owner */ true);
        

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $api->_build_bread_crumb_trail($playlist_id,'str_pl_empty_playlist');
        $page->SetTitle('str_pl_empty_playlist');
        if( empty($_POST['confirmplaylistempty']) )
        {
            require_once('cchost_lib/ccextras/cc-playlist-forms.inc');
            $form = new CCConfirmPlaylistEmptyForm();
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $this->_empty_playlist($playlist_id);
            $this->_send_prompt(_('Playlist is now empty.'));
        }
    }

    function _empty_playlist($playlist_id)
    {
        $cartitems =& CCPlaylistItems::GetTable();
        $w['cart_item_cart'] = $playlist_id;
        $cartitems->DeleteWhere($w);
        $carts =& CCPlaylist::GetTable();
        $wx['cart_num_items'] = 0;
        $wx['cart_id'] = $playlist_id;
        $carts->Update($wx);
    }

    function DeletePL($playlist_id='')
    {
        $api = new CCPlaylists();
        $api->_test_pl('',$playlist_id, /* test upload: */ false, 
                                        /* test_playlist */ true,
                                        /* test_owner */ true);
        

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $api->_build_bread_crumb_trail($playlist_id,'str_pl_delete_playist');
        $page->SetTitle('str_pl_delete_playist');
        if( empty($_POST['confirmplaylistdelete']) )
        {
            require_once('cchost_lib/ccextras/cc-playlist-forms.inc');
            $form = new CCConfirmPlaylistDeleteForm();
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $this->_empty_playlist($playlist_id);
            $carts =& CCPlaylist::GetTable();
            $carts->DeleteKey($playlist_id);
            $this->_send_prompt(_('Playlist has been deleted.'));
        }
    }

    function Recalc()
    {
        global $CC_GLOBALS;
        require_once('cchost_lib/ccextras/cc-playlist-rank.inc');
        $msg = calc_plays();
        $page =& CCPage::GetPage();
        if( empty($msg) )
           $page->Prompt(_('Playlists rankings recalcuated'));
        else
           $page->Prompt($msg);
    }
    
    function _wipe_carts($dead_carts_arr)
    {
        if( empty($dead_carts_arr) )
        {
            $count = '0';
        }
        else
        {
            $count = count($dead_carts_arr);
            $dead_carts = join(',',$dead_carts_arr);
            $sql2 = "DELETE FROM cc_tbl_cart_items where cart_item_cart in ({$dead_carts})";
            CCDatabase::Query($sql2);
            $sql3 = "DELETE FROM cc_tbl_cart WHERE cart_id in ({$dead_carts})";
            CCDatabase::Query($sql3);
        }
        return $count;
    }
    
    function OnUserDelete($user_id)
    {
        $sql = "select cart_id from cc_tbl_cart where cart_user = {$user_id}";
        $dead_carts_arr = CCDatabase::QueryItems($sql);
        $this->_wipe_carts($dead_carts_arr);
    }
    
    function CleanUp()
    {
        $sql = "select cart_id from cc_tbl_cart left outer join cc_tbl_user on cart_user = user_id where isnull(user_id)";
        $dead_carts_arr = CCDatabase::QueryItems($sql);
        $count = $this->_wipe_carts($dead_carts_arr);
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Playlist Cleanup'));
        $page->Prompt("Playlist: cleaned up {$count} records");
    }

}

?>
