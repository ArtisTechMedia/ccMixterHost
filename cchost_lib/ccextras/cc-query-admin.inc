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
* $Id: cc-query-admin.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-tag-editor.inc');
require_once('cchost_lib/cc-admin.php');


class CCAdminQueryTypesForm extends CCTagEditorForm
{
    function CCAdminQueryTypesForm($typekeys_name)
    {
        $this->CCTagEditorForm(array('query-browser-reqtags',$typekeys_name),_('Add Tag'));
    }
}

class CCQueryAdminForm extends CCGridForm
{
    function CCQueryAdminForm($opts)
    {
        $this->CCGridForm();
        $heads = array( 
                        _('Delete'),
                        _('Name'),
                        _('Template'), 
                        _('Style'),
                        _('Def. limit'),
                        _('Playlist but.'),
                        _('Req. tags'),
                        '', // types_key
                        _('Types'),

                        '<span style="font-weight:normal">' . _('Filter on:') . '</span>',
                        _('User'),  
                        _('License'), 


                      );
        $this->SetColumnHeader($heads);
        if( empty($opts) )
        {
            $opts['default'] = array( 'name' => 'default',
                                      'template' => 'ccskins/shared/formats/reccby.tpl',
                                      'css' => 'ccskins/shared/css/qbrowser_wide.css',
                                      'user' => 1,
                                      'license' => 1,
                                      'reqtags' => 'remix',
                                      'limit'   => 25,
                                      'types_key' => rand()+1,
                                    );
        }

        require_once('cchost_lib/cc-template.inc');
        $templates = CCTemplateAdmin::GetFormats('query_browser_template');

        $css = CCTemplateAdmin::GetStyles('query_browser_styles');

        $keys = array_keys($opts);
        $count = count($keys);
        for( $i = 0; $i < $count; $i++ )
        {
            $name =& $keys[$i];
            $opt =& $opts[$name];
            
            $type_edit_url = ccl('admin','browse',$opt['types_key'],'tags');
            $type_edit_id  = 'type_edit_' . $i;
            $type_edit_link = '<a href="'.$type_edit_url.'" id="' . $type_edit_id .'">' .
                                _('Edit types') . '</a>' .
                              '<script> new popupHook([ "' . $type_edit_id . '"], { width:650, height:300 } );</script>';
            $a = array(  

                 array(
                    'element_name'  => "mi[$name][delete]",
                    'value'      => '',
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_NONE ),

                array(
                    'element_name'  => "mi[$name][name]",
                    'value'      => $name,
                    'class'      => 'cc_form_input_short',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED),

                
                array(
                    'element_name'  => "mi[$name][template]",
                    'value'      => $opt['template'],
                    'formatter'  => 'raw_select',
                    'options'    => $templates,
                    'flags'      => CCFF_REQUIRED),

                array(
                    'element_name'  => "mi[$name][css]",
                    'value'      => $opt['css'],
                    'formatter'  => 'raw_select',
                    'options'    => $css,
                    'flags'      => CCFF_REQUIRED),

                array(
                    'element_name'  => "mi[$name][limit]",
                    'value'      => $opt['limit'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE),

                 array(
                    'element_name'  => "mi[$name][playlist_button]",
                    'value'      => '',
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_NONE ),

                array(
                    'element_name'  => "mi[$name][reqtags]",
                    'value'      => $opt['reqtags'],
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE),

                array(
                    'element_name'  => "mi[$name][types_key]",
                    'value'      => $opt['types_key'],
                    'class'      => 'hide',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE),

                array(
                    'element_name'  => "stat_" . $i,
                    'value'      => $type_edit_link,
                    'formatter'  => 'statictext',
                    'flags'      => CCFF_STATIC | CCFF_NOUPDATE),

                array(
                    'element_name'  => "statx_" . $i,
                    'value'      => '',
                    'formatter'  => 'statictext',
                    'flags'      => CCFF_STATIC | CCFF_NOUPDATE),
                array(
                    'element_name'  => "mi[$name][user]",
                    'value'      => $opt['user'],
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_POPULATE),
                array(
                    'element_name'  => "mi[$name][license]",
                    'value'      => $opt['license'],
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_POPULATE),

                );
            $this->AddGridRow($name,$a);

        }

        $S = 'new[%i%]';

        $a = array(

                array(
                    'element_name'  => $S . "[delete]",
                    'value'      => '',
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_NONE ),

                array(
                    'element_name'  => $S . "[name]",
                    'value'      => 'replace_me_' . $i,
                    'class'      => 'cc_form_input_short',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_REQUIRED),
                array(
                    'element_name'  => $S . "[template]",
                    'value'      => 'ccskins/shared/formats/reccby.tpl',
                    'formatter'  => 'raw_select',
                    'options'    => $templates,
                    'flags'      => CCFF_REQUIRED),

                array(
                    'element_name'  => $S . "[css]",
                    'value'      => 'ccskins/shared/css/qbrowser_wide.css',
                    'formatter'  => 'raw_select',
                    'options'    => $css,
                    'flags'      => CCFF_REQUIRED),

                array(
                    'element_name'  => $S . "[limit]",
                    'value'      => '',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE),

                 array(
                    'element_name'  => $S . "[playlist_button]",
                    'value'      => '',
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_NONE ),

                array(
                    'element_name'  => $S . "[reqtags]",
                    'value'      => '',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE),

                array(
                    'element_name'  => $S . "[types_key]",
                    'value'      => '',
                    'class'      => 'hide',
                    'formatter'  => 'textedit',
                    'flags'      => CCFF_POPULATE),

                array(
                    'element_name'  => "stat_%i%",
                    'value'      => '<a href="" id="edit_tags_link_%i%">' . _('Edit types') . '</a>',
                    'formatter'  => 'statictext',
                    'flags'      => CCFF_STATIC | CCFF_NOUPDATE),


                array(
                    'element_name'  => "statx_%i%",
                    'value'      => '',
                    'formatter'  => 'statictext',
                    'flags'      => CCFF_STATIC | CCFF_NOUPDATE),
                array(
                    'element_name'  => $S . "[user]",
                    'value'      => 1,
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_POPULATE),
                array(
                    'element_name'  => $S . "[license]",
                    'value'      => 1,
                    'formatter'  => 'checkbox',
                    'flags'      => CCFF_POPULATE),

            );

        $type_edit_url = ccl('admin','browse','%types_key%','tags');

        $script =<<<EOF
function() {
    var tagkey = Math.floor(Math.random()*0x7000) + 1;
    var link_id = 'edit_tags_link_' + this.num_rows;
    $('new[' + this.num_rows + '][types_key]').value = tagkey; 
    $(link_id).href = '{$type_edit_url}'.replace(/%types_key%/,tagkey);
    new popupHook([ link_id ], { width:650, height:300 } );
    }
EOF;
        $this->AddMetaRow($a, _('Add option set'), $script );
    }

}

class CCQueryAdmin
{
    function Admin($optsname='',$dotags='')
    {
        if( !empty($dotags) && $dotags == 'tags' )
        {
            $this->AdminQueryTypes($optsname);
            return;
        }
        require_once('cchost_lib/cc-page.php');
        require_once('cchost_lib/cc-admin.php');
        $title = _('Admin Query Options Sets');
        CCAdmin::BreadCrumbs(true,array('url'=>'','text'=>$title));
        CCPage::SetTitle($title);

        $configs = CCConfigs::GetTable();
        $query_opts = $configs->GetConfig('query-browser-opts');
        $configs =& CCConfigs::GetTable();
        $opts = $configs->GetConfig('query-browser-opts');
        $form = new CCQueryAdminForm($opts);
        if( empty($_POST['queryadmin']) || !$form->ValidateFields() )
        {
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            $new_opts = array();
            $names = array();
            $counter = 1;
            $this->_proc_qopts($_POST['mi'],$new_opts,$names,$counter);
            if( !empty($_POST['new']) )
                $this->_proc_qopts($_POST['new'],$new_opts,$names,$counter);
            $configs->SaveConfig('query-browser-opts',$new_opts,CC_GLOBAL_SCOPE,false);
            CCPage::SetArg('query-browser-opts',$new_opts,'qbrowser_edit_aftermath');
            CCPage::Prompt(_('Query options sets saved.'));
        }
    }

    function _proc_qopts(&$opts,&$new_opts,&$names,&$counter)
    {
        $configs =& CCConfigs::GetTable();
        $qtags = $configs->GetConfig('query-browser-reqtags');
        $got_delete = false;

        foreach( $opts as $new_opt )
        {
            $name = $new_opt['name'];
            if( !empty($new_opt['delete']) )
            {
                if( array_key_exists( $new_opt['types_key'], $qtags ) )
                {
                    unset( $qtags[ $new_opt['types_key'] ] );
                    $got_delete = true;
                }
                continue;
            }

            if( in_array( $name, $names ) )
            {
                $name .= '_' . $counter++;
            }
            $new_opts[ $name ] = array(
                'template'   => $new_opt['template'],
                'css'        => $new_opt['css'],
                'types_key'  => $new_opt['types_key'],
                'reqtags'    => $new_opt['reqtags'],
                'limit'      => $new_opt['limit'],
                'playlist_button' => empty($new_opt['playlist_button']) ? 0 : 1,
                'license'         => empty($new_opt['license']) ? 0 : 1,
                'user'            => empty($new_opt['user']) ? 0 : 1,
                );
        }

        if( $got_delete )
        {
            $configs->SaveConfig('query-browser-reqtags',$qtags,CC_GLOBAL_SCOPE,true);
        }
    }

    function AdminQueryTypes($typekeys_name)
    {
        require_once('cchost_lib/cc-page.php');
        CCPage::SetTitle(_('Admin Query Types'));

        $form = new CCAdminQueryTypesForm($typekeys_name);
        if( empty($_POST['adminquerytypes']) || !$form->ValidateFields() )
        {
            CCPage::AddForm( $form->GenerateForm() );
        }
        else
        {
            $channels = array();
            if( !empty($_POST['S']) )
                $this->_inject($channels,$_POST['S']);
            if( !empty($_POST['new']) )
                $this->_inject($channels,$_POST['new']);
            $configs =& CCConfigs::GetTable();
            $all_tagkeys = $configs->GetConfig('query-browser-reqtags');
            $all_tagkeys[$typekeys_name] = $channels;
            $configs->SaveConfig('query-browser-reqtags',$all_tagkeys,CC_GLOBAL_SCOPE,false);
            if( empty($_GET['popup']) )
            {
                CCUtil::SendBrowserTo();
            }
            else
            {
                CCPage::Prompt(_('Query browser types saved') . '<br /><br /><a class="small_button button_small" href="javascript:window.close()">' . _('Close this window') . '</a>');
            }
        }

    }

    function _inject(&$results,$arr)
    {
        CCUtil::StripSlash($arr);
        foreach( $arr as $channel )
        {
            if( empty($channel['delete']) )
                $results[] = $channel;
        }
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','browse'), 
                          array( 'CCQueryAdmin', 'Admin'),  CC_ADMIN_ONLY,
                          ccs(__FILE__), '', 
                          _('Configure browse interface'), 
                          CC_AG_QUERY );
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
                'qbrowse' => array( 
                                 'menu_text'  => _('Query Browser'),
                                 'menu_group' => 'configure',
                                 'help' => 'Configure query browser',
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 1340,
                                 'action' =>  ccl('admin','browse')
                                 ),
                );
        }
    }

}

?>
