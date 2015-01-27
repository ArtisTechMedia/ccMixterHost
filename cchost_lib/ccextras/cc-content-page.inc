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
* $Id: cc-content-page.inc 12909 2009-07-13 18:24:11Z fourstones $
*
*/

/**
* @package cchost
* @subpackage extras
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/ccextras/cc-topics-forms.inc');

define('CC_FAR_IN_THE_FUTURE', '2030');

class CCContentPostForm extends CCTopicForm
{
    function CCContentPostForm()
    {
        $this->CCTopicForm(_('Content Entry'),_('Submit Content'),CC_TTF_EDIT,false);
        $this->EnableUploads();

        $types = CCDatabase::QueryItems('SELECT DISTINCT topic_type FROM cc_tbl_topics');

        $new_types[] = _('home');
        $new_types[] = _('sidebar_blurb');        
        $types = array_filter(array_unique(array_merge($types,$new_types)));
        $types = array_combine( array_values($types), $types );
        ksort($types);
        $fields['topic_type'] =
                        array(  'label'      => _('Content Type'),
                               'form_tip'    => '',
                               'formatter'   => 'select',
                                'options'    => $types,
                               'flags'       => CCFF_POPULATE );

        $fields['new_topic_type'] =
                        array(  'label'      => _('New Content Type'),
                               'form_tip'    => '',
                               'formatter'   => 'textedit',
                               'flags'       => CCFF_POPULATE );

        $fields['topic_date'] =
                        array(  'label'      => _('Publish Date'),
                               'form_tip'    => _('Check "Sticky" to have this topic stick as the first topic of its type. It will ' .
                   'be skipped by feed generators.'),
                               'formatter'   => 'sticky_date',
                               'value'       => date('Y-m-d H:i:s'),
                               'year_begin'  => date('Y') - 2,
                               'flags'       => CCFF_POPULATE );

        $options =
            array( CC_TOPIC_FORMAT_BB    => _('[b] formatting codes'),
                   CC_TOPIC_FORMAT_HTML  => _('X/HTML markup'),
                   CC_TOPIC_FORMAT_PLAIN => _('Plain text (no formatting)'),
                );

        $fields['topic_format'] =
                        array( 'label'      => _('Text Format'),
                               'form_tip'    => _('Select the text format you are using for this topic'),
                               'formatter'   => 'select',
                               'options'     => $options,
                               'flags'       => CCFF_POPULATE );

        $this->SetFormFieldItem('topic_text', 'show_img', true);
        $this->InsertFormFields( $fields, 'top' );
        $this->RemoveFormField('user_confirm');
        $this->RemoveFormField('user_mask');

        global $CC_GLOBALS;

        $imgurl = ccd($CC_GLOBALS['image-upload-dir']);
        $txt = sprintf( _('Images uploaded here will be accessable from %s Use [img=full_url][/img] or [skinimg=filename.ext][/skinimg] in the topic to display.'),
                                                  '<p><b>'. $imgurl.'</b></p>' 
                                                  );
        $images['img_help'] = array( 'label' => _('Image Uploads'),
                                     'form_tip' => '',
                                     'formatter' => 'statictext',
                                     'flags' => CCFF_STATIC | CCFF_NOUPDATE,
                                     'value' => $txt );
        for( $i = 1; $i < 5; $i++ )
        {
            $images['images_' . $i] = 
                array( 'label' => _('Image' ) . ' ' . $i,
                        'formatter' => 'upload',
                        'upload_dir' => $CC_GLOBALS['image-upload-dir'],
                        'flags' => CCFF_NONE );
        }

        $this->AddFormFields($images);
        $this->SetHiddenField('topic_user',CCUser::CurrentUser());
        $this->SetHiddenField('topic_nsfw',0);
    }

    function generator_sticky_date($varname,$value='',$class='')
    {
        $html = $this->generator_date($varname,$value,$class);
        $y = strftime('%Y',strtotime($value));
        if( intval($y) >= intval(CC_FAR_IN_THE_FUTURE) )
        {
            $value = date('Y-m-d H:i:s');
            $html = $this->generator_date($varname,$value,$class);
            $html = str_replace( '<select', '<select disabled=\'disabled\'',$html);
            $val = 'checked="checked"';
        }
        else
        {
            $val = '';
        }
        $html .= ' <input '.$val.' type="checkbox" id="sticky_check_'.$varname.
                 '" onclick="cc_date_stick(\''.$varname.'\');" /> ' . _('Sticky');
                 
        return $html;
    }

    function validator_sticky_date($fieldname)
    {
        $v = $this->GetFormValue($fieldname);
        if( empty($v) )
        {
            $v = array( 'm' => _('January'), 'd' => 1, 'y' => CC_FAR_IN_THE_FUTURE, 'h' => 12, 'i' => '00', 'a' => 'am' );
            $this->SetFormValue($fieldname,$v);
        }        
        return $this->validator_date($fieldname);
    }
}

/**
* Form for configuration the file format verification module
* !!incomplete!!
* @package cchost
* @subpackage admin
*
*/
class CCContentPageForm extends CCForm
{
    /**
    * Constructor
    *
    */
    function CCContentPageForm($old_name=false)
    {
        $this->CCForm();

        $fields['title'] =
                        array(  'label'      => _('Page Title'),
                               'form_tip'    => '',
                               'formatter'   => 'textedit',
                               'flags'       => CCFF_POPULATE | CCFF_REQUIRED );

        if( $old_name )
        {
            $old_url = ccl($old_name);
            $tip = sprintf(_('Check this to keep the URL as %s even if you edit the title above.'),'<b>'.$old_url.'</b>');
            $fields['keep_name'] = array( 'label' => _('URL Follows Title'),
                              'form_tip' => $tip,
                              'formatter' => 'checkbox',
                              'value' => 'on',
                              'flags' => CCFF_NONE );
        }

        $types = CCDatabase::QueryItems('SELECT DISTINCT topic_type FROM cc_tbl_topics ORDER BY topic_type');
        $types = array_filter($types);
        $types = empty($types) ? array( 'home' => 'home' ) : array_combine( array_values($types), $types );

        if( !empty($types) )
        {
            $fields['type'] =
                            array(  'label'      => _('Content Type'),
                                   'form_tip'    => _('You can specify a topic type and sort by date or another field...'),
                                    'value'       => 'home',
                                   'formatter'   => 'select',
                                    'options'    => $types,
                                   'flags'       => CCFF_POPULATE );
        }

        require_once('cchost_lib/cc-template.inc');
        $formats = CCTemplateAdmin::GetContentTemplates();
        $fields['t'] =
                        array(  'label'      => _('Layout'),
                               'form_tip'    => '',
                               'formatter'   => 'select',
                               'options'     => $formats,
                               'flags'       => CCFF_POPULATE );


        $options =
            array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' );

        $fields['content_page_columns'] =
                        array(  'label'      => _('Number of columns'),
                               'form_tip'    => _('Some layouts will ignore this.'),
                               'formatter'   => 'select',
                               'options'     => $options,
                               'flags'       => CCFF_POPULATE );

        $options =
            array( '' => _('Default page maximum'), '1' => '1', '4' => '4', '6' => '6', '10' => '10', '25' => '25', '40' => '40', );

        $fields['limit'] =
                        array(  'label'      => _('Limit number of topics'),
                               'form_tip'    => _('Some layouts will ignore this.'),
                               'formatter'   => 'select',
                               'options'     => $options,
                               'flags'       => CCFF_POPULATE );

        $fields['content_page_width'] =
                        array(  'label'      => _('Page Width'),
                               'form_tip'    => _('ex: 80% or 730px (Some layouts ignore this)'),
                               'formatter'   => 'textedit',
                               'flags'       => CCFF_POPULATE );

        $options =
            array( 'format' => _('Always display HTML'),
                   'text'  =>  _('Always display plain text'),
                    'raw' =>   _('Display the topic "raw" (for debugging)'),
                    );

        $fields['content_page_textformat'] =
                        array(  'label'      => _('Text Format'),
                               'form_tip'    => _('Some layouts will ignore this.'),
                               'formatter'   => 'select',
                               'options'     => $options,
                               'flags'       => CCFF_POPULATE );

        
        $toc_templates = array_merge( array('0' => _('(No table of contents)')), CCTemplateAdmin::GetFormats('page_content_toc') );
        $fields['content_toc'] =
                        array(  'label'      => _('Table of Contents Links'),
                               'form_tip'    => _('Show a list of links to other topics'),
                               'formatter'   => 'select',
                               'options'     => $toc_templates,
                               'flags'       => CCFF_POPULATE );

        $fields['paging'] =
                        array(  'label'      => _('Next/Prev Links'),
                               'form_tip'    => _('Allowing paging with next/prev links'),
                               'formatter'   => 'select',
                               'options'     => array( 'on' => _('Turn ON paging links'),
                                                       'off' => _('Turn OFF paging links'),
                                                     ),
                               'flags'       => CCFF_POPULATE );

        $fields['content_page_box'] =
                        array(  'label'      => _('Boxes'),
                               'form_tip'    => _('Use boxes around content'),
                               'formatter'   => 'checkbox',
                               'flags'       => CCFF_POPULATE );

        $options = 
            array( 'date' => _('Date') );

        $fields['sort'] =
                        array(  'label'      => _('Sort by'),
                               'form_tip'    => '',
                               'formatter'   => 'select',
                               'options'     => $options,
                               'flags'       => CCFF_POPULATE );

        $options = 
            array( 'desc' => _('Newest first'), 'asc' => _('Oldest first'), );

        $fields['ord'] =
                        array(  'label'      => _('Order'),
                               'form_tip'    => '',
                               'formatter'   => 'select',
                               'options'     => $options,
                               'flags'       => CCFF_POPULATE );

        $fields['content_feed'] =
                        array(  'label'      => _('Feed Links'),
                               'form_tip'    => _('Check this to display a feed link on these pages'),
                               'formatter'   => 'checkbox',
                               'flags'       => CCFF_POPULATE );

        $fields['show_bread_crumbs'] =
                        array(  'label'      => _('Show Bread Crumbs'),
                               'form_tip'    => _('Check this to display a bread crumbs'),
                               'formatter'   => 'checkbox',
                               'flags'       => CCFF_POPULATE );

        $this->AddFormFields( $fields );
    }
}

/**
*
*
*/
class CCContentPage
{
    function TDelete($topic_id='')
    {
        $page =& CCPage::GetPage();
        $this->_build_bread_crumb_trail(_('Delete a Content Entry'));
        $page->SetTitle(_('Delete a Content Entry'));
        if( empty($topic_id) )
            CCUtil::Send404();
        $form = new CCGenericForm();
        if( empty($_POST['generic']) )
        {
            $name = CCDatabase::QueryItem('SELECT topic_name FROM cc_tbl_topics WHERE topic_id='.$topic_id);
            $form->SetFormHelp(_('This is permanantly erase this content. There is <b>NO UNDO</b>'));
            $fname = basename($name);
            $form->SetSubmitText(sprintf("Delete '%s' Now",$name));
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            require_once('cchost_lib/ccextras/cc-topics.inc');
            $topics = new CCTopics();
            $topics->DeleteKeyRow($topic_id);
            CCUtil::SendBrowserTo( ccl('admin','content','manage') );
        }
    }

    function Edit($topic_id)
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->_build_bread_crumb_trail(_('Edit Content'));
        $page->SetTitle(_('Edit Content'));
        $form = new CCContentPostForm();
        if( empty($_POST['contentpost']) )
        {
            $row = CCDatabase::QueryRow('SELECT * FROM cc_tbl_topics WHERE topic_id='.$topic_id);
            $form->PopulateValues($row);
        }
        elseif( $form->ValidateFields() )
        {
            global $CC_GLOBALS;
            $form->GetFormValues($values);
            for($i = 1; $i < 5; $i++ )
            {
                $name = 'images_' . $i;
                if( !empty($values[$name]['name']) )
                {
                    move_uploaded_file($values[$name]['tmp_name'],$CC_GLOBALS['image-upload-dir'] . $values[$name]['name']);
                }
                unset($values[$name]);
            }
            if( !empty($values['new_topic_type']) )
                $values['topic_type'] = $values['new_topic_type'];
            unset($values['new_topic_type']);
            $topics = new CCTopics();
            $values['topic_id'] = $topic_id;
            $topics->Update($values);
            $url = ccl('admin','content','manage');
            if( empty($_GET['offset']) )
                $offset = '';
            else
                $offset = '&offset='.$_GET['offset'];
            $url = url_args( $url, 'topic_type=' . $values['topic_type'] . $offset );
            CCUtil::SendBrowserTo($url);
        }

        $page->AddForm( $form->GenerateForm() );
    }

    function Post()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $this->_build_bread_crumb_trail(_('Post Content'));
        $page->SetTitle(_('Post Content'));
        $form = new CCContentPostForm();
        if( empty($_POST['contentpost']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            global $CC_GLOBALS;
            $form->GetFormValues($values);
            for($i = 1; $i < 5; $i++ )
            {
                $name = 'images_' . $i;
                if( !empty($values[$name]['name']) )
                {
                    CCUtil::MakeSubDirs($CC_GLOBALS['image-upload-dir']);
                    move_uploaded_file($values[$name]['tmp_name'],$CC_GLOBALS['image-upload-dir'] . $values[$name]['name']);
                }
                unset($values[$name]);
            }
            if( !empty($values['new_topic_type']) )
                $values['topic_type'] = $values['new_topic_type'];
            unset($values['new_topic_type']);
            $topics = new CCTopics();
            $values['topic_id'] = $topics->NextID();
            $values['topic_upload'] = 0;
            $values['topic_thread'] = 0;
            if( empty($values['topic_date']) )
                $values['topic_date'] = date('Y-m-d H:i:s',time());
            
            $topics->InsertNewTopic($values,0);

            CCUtil::SendBrowserTo( ccl('admin','content','manage') );
        }
    }

    function PageDelete()
    {
        $page =& CCPage::GetPage();
        $this->_build_bread_crumb_trail(_('Delete a Content Page'));
        $page->SetTitle(_('Delete a Content Page'));
        $pagePath = CCUtil::Strip($_GET['page']);
        if( empty($pagePath) )
            CCUtil::Send404();
        $form = new CCGenericForm();
        if( empty($_POST['generic']) )
        {
            $form->SetFormHelp(_('This is permanantly erase this page. There is <b>NO UNDO</b>'));
            $fname = basename($pagePath);
            $form->SetSubmitText(sprintf("Delete '%s' Now",$fname));
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            unlink($pagePath);
            CCUtil::SendBrowserTo( ccl('admin','content','manage') );
        }
    }

    function PageEdit()
    {
        $page =& CCPage::GetPage();
        $this->_build_bread_crumb_trail(_('Edit a Content Page'));
        $page->SetTitle(_('Edit a Content Page'));
        $pagePath = CCUtil::Strip($_GET['page']);
        if( empty($pagePath) )
            CCUtil::Send404();
        require_once('cchost_lib/cc-file-props.php');
        $fp = new CCFileProps();
        $props = $fp->GetFileProps($pagePath);
        $props['type'] = $props['topic_type'];
        $props['title'] = $props['desc'];
        $short_page = str_replace('.php','',basename($pagePath));
        $form = new CCContentPageForm($short_page);
        if( empty($_POST['contentpage']) )
        {
            $form->PopulateValues($props);                
        }
        elseif( $form->ValidateFields() && $this->_valid_target_dir($form,$target) )
        {
            $this->_save_page($form,false,$target,$short_page);
            $url = url_args( ccl('admin','content','manage'), 'topic_type=' . $props['type'] );
            CCUtil::SendBrowserTo($url);
        }

        $page->AddForm( $form->GenerateForm() );
    }

    function Page($cmd='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        if( $cmd == 'edit' )
            return $this->PageEdit();
        if( $cmd == 'delete' )
            return $this->PageDelete();

        $this->_build_bread_crumb_trail(_('Create a Content Page'));
        $page->SetTitle(_('Create a Content Page'));
        $form = new CCContentPageForm();
        if( empty($_POST['contentpage']) || !$form->ValidateFields() || !$this->_valid_target_dir($form,$target) )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $this->_save_page($form,true,$target);
            $url = ccl( 'admin','content' );
            CCUtil::SendBrowserTo($url);
        }
    }

    function _valid_target_dir(&$form,&$target_dir) 
    {
        global $CC_GLOBALS;
        list( $target_dir ) = array_filter(cc_split( ';', $CC_GLOBALS['files-root'] ));
        if( !empty($target_dir) )
            $target_dir = trim($target_dir);
        if( empty($target_dir) || !file_exists($target_dir) )
        {
            $form->SetHelpText(sprintf(_("Can't find a suitable place to write the new 'docs' content. Fix this in %sPath Configure%"),
                            '<a href="' . ccl('admin','paths') . '">', '</a>' ) );
            return false;
        }
        return true;
    }

    function _save_page(&$form,$new,$target_dir,$old_name='')
    {
        $form->GetFormValues($values);
        if( empty($values['keep_name']) || empty($old_name) )
        {
            $fname = trim(preg_replace('/[^0-9a-z_-]+/', '-', strtolower($values['title'])),'-');
            if( empty($fname) )
                $fname = 'topicpage';
        }
        else
        {
            $fname = $old_name;
        }
        $path = $target_dir . '/' . $fname . '.php';
        if( $new )
        {
            CCUtil::MakeSubdirs($target_dir);
            $i = 0;
            $root = $fname;
            while( file_exists($path) )
            {
                $fname = $root . '_' . ++$i;
                $path = $target_dir . '/' . $fname . '.php';
            }
        }

        $values['title'] = addslashes($values['title']);

        $meta = '';
        ksort($values);
        foreach( $values as $K => $V )
        {
            if( ($K == 'title') || ($K == 'type') || ($K == 'keep_name')  )
                continue;
            $meta .= '      ' . $K . ' = ' . $V . "\n";
        }

        $meta .= '      topic_type = ' . $values['type'] . "\n";

        if( !empty($values['show_bread_crumbs']) )
        {
            $meta .= "      breadcrumbs = home,page_title,topic\n";
        }

        $q = 'f=embed';
        foreach( array('t','sort','ord','type','limit','paging',) as $K )
        {
            $q .= '&' . $K . '=' . $values[$K];
        }

        $vars = '';
        foreach( array( 'content_page_box', 'content_page_width',
                        'content_page_textformat', 'content_page_columns', ) as $K  )
        {
            if( isset($values[$K]) )
                $vars .= "\$A['{$K}'] = '" . $values[$K] . "';\n";
        }

        
        $feed_goo = empty($values['content_feed']) ? '// ' : 
                          "cc_content_feed('datasource=topics&type={$values['type']}&page={$fname}','{$values['title']}','topics');";

        if( $values['paging'] == 'on' )
        {
            $paging_goo = '';

            //if( $values['limit'] == 1 )
            {
                // let's special case topic to topic paging because we don't use 'offset', we
                // use the topic slug (URL safe version of the topic_name) 
                $paging_goo = "cc_add_content_paging_links(\$A,'{$values['type']}',\$topic,'{$values['ord']}','{$fname}', '{$values['limit']}');\n";
            }

            $paging_goo .= "\$A['macro_names'][] = 'prev_next_links';\n";
        }
        else
        {
            $paging_goo = "// no paging \n";
        }


        if( empty($values['content_toc']) )
        {
            $toc = '';
        }
        else
        {
            $toc_query = 'f=html&limit=query&&noexit=1&nomime=1&' .
                          "t={$values['content_toc']}&page={$fname}&sort={$values['sort']}&ord={$values['ord']}";

            $toc = "cc_query_fmt('{$toc_query}');\n";
        }

        $text = "<?/*\n   This file was generated by ccHost Content Manager\n\n[meta]\n";
        $text .=<<<EOF
     type = dynamic_content_page
     desc = _('{$values['title']}')
     page_title = _('{$values['title']}')
{$meta}
[/meta]
*/
{$vars}
\$topic = empty(\$_GET['topic']) ? '' : \$_GET['topic'];
{$toc}cc_query_fmt('{$q}&topic=' . \$topic );
{$paging_goo}{$feed_goo} 

EOF;

        $f = fopen($path,'w');
        fwrite($f,$text . '?' . ">\n\n");
        fclose($f);
        @chmod($path,cc_default_file_perms());
        return $fname;
    }

    function Manage()
    {
        require_once('cchost_lib/cc-page.php');
        $this->_build_bread_crumb_trail(_('Manage Page Content'));
        $page =& CCPage::GetPage();
        $page->SetTitle(_('Manage Page Content'));

        require_once('cchost_lib/cc-query.php');
        $query = new CCQuery();
        if( empty($_GET['topic_type']) )
        {
            // the list was always defaulting to 'review' (which is kind of lame)
            // see if there's a home topics
            $home_count = CCDatabase::QueryItem('SELECT COUNT(*) FROM cc_tbl_topics WHERE topic_type=\'home\'');
            if( !empty($home_count) )
            {
                $type = 'home';
            }
            else
            {
                // well, anything but 'review' or 'reply'
                $type = CCDatabase::QueryItem(
                  "SELECT topic_type FROM cc_tbl_topics LIMIT 1 WHERE topic_type NOT IN ('review','reply')");
                  
                if( empty($type) )
                {
                    // ok, there's probably nothing there, just grab whatever is first
                    $type = CCDatabase::QueryItem("SELECT topic_type FROM cc_tbl_topics LIMIT 1");
                    if( empty($type) )
                    {
                        // oh, there are no topics, then it doesn't matter what this
                        $type = 'home';
                    }
                }
            }
            $_GET['topic_type'] = $type;
        }
        $args = $query->ProcessAdminArgs('t=content_manage&type='.$_GET['topic_type']);
        $query->Query($args);
    }

    /*
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
                'admin_content' => array( 
                                 'menu_text'  => _('Manage Page Content'),
                                 'menu_group' => 'configure',
                                 'help' => 'Manage special pages and topics ',
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 3,
                                 'action' =>  ccl('admin','content')
                                 ),
                );
        }
    }


    /**
    * @access private
    */
    function _build_bread_crumb_trail($cmd='')
    {
        $trail[] = array( 'url' => ccl(), 'text' => _('Home') );
        
        $trail[] = array( 'url' => ccl('admin','site','global'), 
                          'text' => _('Settings') );

        if( $cmd )
        {
            $trail[] = array( 'url' => ccl('admin','content'), 'text' => _('Content') );
            $trail[] = array( 'url' => '', 'text' => $cmd );
        }
        else
        {
            $trail[] = array( 'url' => '', 'text' => _('Content') );
        }

        $page =& CCPage::GetPage();
        $page->AddBreadCrumbs($trail);
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','content','page'),   array( 'CCContentPage', 'Page'),       
                CC_ADMIN_ONLY, ccs(__FILE__), '', _('Create a content page'), CC_AG_CONTENT_MAN );
        CCEvents::MapUrl( ccp('admin','content','post'),   array( 'CCContentPage', 'Post'),       
                CC_ADMIN_ONLY, ccs(__FILE__), '', _('Post content topic'), CC_AG_CONTENT_MAN );
        CCEvents::MapUrl( ccp('admin','content','edit'),   array( 'CCContentPage', 'Edit'),       
                CC_ADMIN_ONLY, ccs(__FILE__), '', _('Edit a content topic'), CC_AG_CONTENT_MAN );
        CCEvents::MapUrl( ccp('admin','content','delete'), array( 'CCContentPage', 'TDelete'),    
                CC_ADMIN_ONLY, ccs(__FILE__), '', _('Delete a content topic'), CC_AG_CONTENT_MAN );
        CCEvents::MapUrl( ccp('admin','content'),          array( 'CCContentPage', 'Manage'),
                CC_ADMIN_ONLY, ccs(__FILE__), '', _('Content manager'), CC_AG_CONTENT_MAN );
    }

}


?>
