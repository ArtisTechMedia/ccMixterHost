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
* $Id: cc-page.php 13108 2009-07-27 18:59:51Z fourstones $
*
*/

/**
* Main page display module
*
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-template.php');
require_once('cchost_lib/cc-menu.php');
require_once('cchost_lib/cc-navigator.php');

/**
* Page template administration API
*
* Handles events and basic event routing for the page template used throughout the site
* @package cchost
* @subpackage admin
*
*/
class CCPageAdmin
{
    /**
    * Event handler for mapping urls to methods
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrl()
    {
        CCEvents::MapUrl( 'viewfile', array( 'CCPageAdmin', 'ViewFile' ),  
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{docfilename}', _('Displays template from "pages" dir'), CC_AG_VIEWFILE  );
        CCEvents::MapUrl( 'docs',     array( 'CCPageAdmin', 'ViewFile' ),  
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{docfilename}', _('(alias for viewfile)'), CC_AG_VIEWFILE );
    }


    function OnApiQuerySetup( &$args, &$queryObj, $requiresValidation )
    {
        extract($args);

        $original_limit = $limit;
        if( ($limit == 'page') || ($format == 'page')  )
        {
            $page =& CCPage::GetPage();
            $max_listing = $page->GetPageQueryLimit();
            $queryObj->ValidateLimit(null,$max_listing);
        }

        if( $format != 'page' )
            return;

        if( empty($template) )
        {
            if( !empty($datasource) && ($datasource == 'pool_items') )
                $args['template'] = 'pool_listing';
            else
                $args['template'] = 'list_files';
        }

        $queryObj->GetSourcesFromTemplate($args['template']);

        if( $queryObj->args['datasource'] == 'topics' )
        {
            $args['limit'] = !empty($original_limit) && is_numeric($original_limit) ? $original_limit : 1000;
        }
        
        // why is this needed again?
        if( !empty($_GET['offset']) )
            $args['offset'] = sprintf('%0d',$_GET['offset']);

        if( !empty($args['dataview']) && ($args['dataview'] == 'passthru') )
            return;

    }

    function OnApiQueryFormat( &$records, $args, &$result, &$result_mime )
    {
        //CCDebug::PrintVar($args);
        if( strtolower($args['format']) != 'page' )
            return;

        extract($args);

        $page =& CCPage::GetPage();
        
        if( !empty($title) )
            $page->SetTitle($title);

        $page->_add_template_bread_crumbs($queryObj->templateProps);
        $dochop = isset($chop) && $chop > 0;
        $chop   = isset($chop) ? $chop : 25;
        $page->PageArg('chop',$chop);
        $page->PageArg('dochop',$dochop);

        // this needs to be done through CCDataview:

        if( !empty($records) && (count($records) > 1) && (empty($paging) || ($paging == 'on') || ($paging=='default')) )
        {
            $page->AddPagingLinks($queryObj->dataview,'',empty($limit)?'':$limit);
        }

        if( empty($template) )
            $template = 'list_files';
        $page->PageArg( 'records', $records, $template );

        if( !isset( $qstring ) )
            $qstring = $queryObj->SerializeArgs($args);

        $page->PageArg('qstring',$qstring );
        $page->PageArg('page_datasource',$datasource);
        $result = true;
    }

    /**
    * Display a file in the client area of the page (wrapper)
    *
    * @see CCPage::ViewFile()
    */
    public static function ViewFile($template='')
    {
        $page =& CCPage::GetPage();
        $page->ViewFile($template);
    }

    /**
    * Event handler for {@link CC_EVENT_GET_CONFIG_FIELDS}
    *
    * Add global settings settings to config editing form
    * 
    * @param string $scope Either CC_GLOBAL_SCOPE or CC_LOCAL_SCOPE
    * @param array  $fields Array of form fields to add fields to.
    */
    function OnGetConfigFields($scope,&$fields)
    {
        if( $scope != CC_GLOBAL_SCOPE )
        {
            $fields['homepage'] =
                array(  'label'      => _('Homepage'),
                        'form_tip'   => sprintf(_('For example a file: docs/home %sor a navigation tab: view/media/home'), '<br />'),
                       'value'       => '',
                       'formatter'   => 'textedit',
                       'flags'       => CCFF_POPULATE);
            $fields['default-feed-query'] =
                array( 'label'       => _('Default Feed Query'),
                       'form_tip'    => _('Query to use for pages that do not specify a feed') . ' ' 
                                        . _('Leave blank for no default feed.'),
                               'formatter'  => 'textedit',
                               'flags'      => CCFF_POPULATE);
        }

    }

    function OnLogout($username)
    {
        $page =& CCPage::GetPage();
        $page->UnSetArg('logged_in_as');
        $page->UnSetArg('logout_url');
        $page->SetArg('is_logged_in',0);
        $page->SetArg('is_admin',0);
        $page->SetArg('not_admin',1);
        CCMenu::Reset();
    }
}


/**
* Page template for the entire site
*
* This class is designed as a singleton instance. Calling GetPage() will 
* return the one instance for this session or (even better) simply call 
* any method using the CCPage::<i>method</i> static syntax.
*
* For less specialized use, you should use the base class instead.
*
* @see GetPage
* @see CCSkin::CCSkin()
*/
class CCPage extends CCSkin
{
    var $_body_template;
    var $_have_forms;

    /**
    * Constructor
    *
    * Do not call this for the main page's output. Use GetPage function instead to get 
    * the global singleton instance.
    *
    * @see GetPage
    */
    function CCPage()
    {
        global $CC_GLOBALS;
        static $c = 0;

        $this->instance = ++$c;
        $this->CCSkin( $CC_GLOBALS['skin-file'] );
        $this->vars['auto_execute'][] = 'page.tpl';

        $this->vars['show_body_header'] = true;
        $this->vars['show_body_footer'] = true;
        $this->vars['chop'] = 20;
        $this->vars['dochop'] = true;
        $this->vars['bread_crumbs'] = array();
        $this->vars['crumb_seperator'] = ' &raquo; ';
        $this->_have_forms = false;
    }


    /**
    * Returns the a singleton instance of the page that will be displayed
    * 
    */
    public static function & GetPage($force = false)
    {
        static $_page;
        if( empty($_page) || $force )
            $_page = new CCPage();
        return($_page);
    }

    public static function GetPageQueryLimit()
    {
        $configs =& CCConfigs::GetTable();
        $settings = $configs->GetConfig('skin-settings');
        return empty($settings['max-listing']) ? 12 : $settings['max-listing'];
    }


    /**
    * Displays the contents of an XHTML file in the main client area of the page
    *
    * The file to be displayed must be in the ccfiles directory. It will be
    * parsed through the template engine so it has to be valid XML. All page
    * variables are available to the file so, for example, macro substitions
    * will work.
    * 
    * @param string $template Name of file (in the 'viewfile' directory) to parse and display
    */
    function ViewFile($template='')
    {
        if( empty($template) )
            CCUtil::SendBrowserTo(ccl());

        if( !($file = $this->GetViewFile($template)) && !preg_match('/\.xml$/',$template) )
        {
            $file = $this->GetViewFile($template . '.xml');
        }

        if( empty($file) )
        {
            $this->Prompt( sprintf(_("Can't find %s template"),$template) );
            CCUtil::Send404(false);
        }
        else
        {
            require_once('cchost_lib/cc-file-props.php');
            $fp = new CCFileProps();
            $props = $fp->GetFileProps($file);
            if( empty($props['page_title']) )
            {
                // see notes in CCPage::Show for why this is important
                if( empty($this->vars['page-title']) )
                {
                    $contents = file_get_contents($file);
                    $this->_check_for_title($contents);
                }
            }
            else
            {
                $this->SetTitle( $props['page_title'] );
            }

            $this->_add_template_bread_crumbs($props);
            
            $this->_body_template = $file;
        }
    }

    /**
    * Make a variable available to the page when rendering
    *
    * @param string $name The name of the variable as will be seen in the template
    * @param mixed  $value The value that will be substituted for the 'name'
    * @param string $macroname The name of a specific macro to invoke during template generation
    */
    function PageArg($name, $value='', $macroname='')
    {
        $this->SetArg($name,$value,$macroname);
    }

    function AddMacro($macroname)
    {
        $this->_add_macro($macroname);
    }

    function _add_macro($macroname)
    {
        parent::AddMacro($macroname);
    }

    /**
    * Get a variable available to the template parser
    *
    * @param string $name The name of the variable as will be seen in the template
    * @returns mixed $value Value of template variable
    */
    function GetPageArg($name)
    {
        if( isset($this->vars[$name]) )
            return $this->vars[$name];

        return '';
    }

    /**
    * Sets the the title for the page
    *
    * @param string $title The title.
    */
    function SetTitle( $title )
    {
        $arg = func_num_args() == 1 ? $title : func_get_args();
        $this->SetArg('page-title', $arg );
        $this->SetArg('page-caption', $arg );
    }

    /**
    * Gets the the title for the page
    *
    * @return string The title or null if not set yet
    */
    function GetTitle()
    {
        $arg = $this->GetArg('page-title');
        if( empty($arg) )
            $arg = $this->SetArg('page-caption' );
        if( !empty($arg) )
            $arg = $this->String($arg);
        return $arg;
    }

    /**
    * Force the display (HTML output to client) of the current page
    *
    * @param string $body Specific HTML for the client area of the page
    */
    function PrintPage( & $body )
    {
        $this->AddContent($body);
        $this->Show();
    }

    /**
    * Add a stylesheet link to the header of the page
    *
    * @param string $css Name of the css file (inlcuding relative path)
    * @param string $title Title of link
    */
    function SetStyleSheet( $css, $title = '' )
    {
        $this->vars['style_sheets'][] = $css;
    }

    /**
    * Show or hide the banner, menus and footers on the page
    *
    * @param bool $show_header true means show banner and menus (this is default)
    * @param bool $show_footer true means show footer (this is default)
    */
    function ShowHeaderFooter( $show_header, $show_footer )
    {
        $this->vars['show_body_header'] = $show_header;
        $this->vars['show_body_footer'] = $show_footer;
    }

    /**
    * Output the page to the client
    *
    */
    function Show($print=true)
    {
        global $CC_GLOBALS;

        CCDebug::Enable(true);
        
        if( !CCUtil::IsHTTP() )
            return;

        $this->vars['menu_groups'] = CCMenu::GetMenu();

        if( !empty($this->_body_template) )
        {
            $this->AddMacro($this->_body_template);
        }

        if( empty($CC_GLOBALS['hide_sticky_tabs']) && 
            empty($this->vars['tab_info']) )
        {
            $naviator_api = new CCNavigator();
            $naviator_api->ShowTabs($this);
        }

        /*
            Google puts a lot of emphasis on <title> tag so yes, we
            go to great lengths to make sure there is something 
            relevant there.
        */
        if( empty($this->vars['page-caption']) )
        {
            if( empty($this->vars['page-title']) )
            {
                if( !empty($this->vars['html_content']) )
                {
                    foreach( $this->vars['html_content'] as $contents )
                    {
                        $this->_check_for_title($contents);
                        if( !empty($this->vars['page-caption']) )
                            break;
                    }
                }
            }
            else
            {
                $this->vars['page-caption'] = $this->vars['page-title'];
            }
        }

        CCEvents::Invoke(CC_EVENT_RENDER_PAGE, array( &$this ) );

        // did anyone add a feed to page 'manually'?
        if( empty($this->vars['feed_links']) )
        {
            // no, is there a defaul query that ran to fill the page?
            if( empty($this->vars['qstring']) )
            {
                // no, is there an admin set 'default feed query'?
                $config =& CCConfigs::GetTable();
                $settings = $config->GetConfig('settings');
                if( !empty($settings['default-feed-query']) )
                    $defq = $settings['default-feed-query'];
            }
            else
            {
                $defq = $this->vars['qstring'];
            }

            if( !empty($defq) )
            {
                parse_str($defq,$defq_args);
                if( empty($defq_args['title']) )
                {
                    if( empty($defq_args['tags']) )
                    {
                        $title = _('Feed');
                    }
                    else
                    {
                        $title = _('Tags: ') . preg_replace('/[,\+ ]+/',' ',$defq_args['tags']);
                    }
                }
                else
                {
                    $title = $this->String($defq_args['title']);
                }
                $this->AddFeedLink($defq, $title, $title);
                // Set this flag incase a template adds a feed link midway through render
                $this->_using_default_feeds = true;
            }
        }

        if( !empty($_REQUEST['dump_page']) && CCUser::IsAdmin() )
             CCDebug::PrintVar($this->vars,false);

        if( !empty($CC_GLOBALS['no-cache']) )
            cc_send_no_cache_headers();

        if( $print )
            $this->SetAllAndPrint(array());
        else
            return $this->SetAllAndParse(array());

    }

    public static function GetViewFile($filename,$real_path=true)
    {
        global $CC_GLOBALS;
        $files = CCSkin::GetFilenameGuesses($filename);
        return CCUtil::SearchPath( $files, $CC_GLOBALS['files-root'], 'ccskins/shared', $real_path, true );
    }

    public static function GetViewFilePath()
    {
        global $CC_GLOBALS;
        return CCUtil::SplitPaths( $CC_GLOBALS['files-root'], 'ccskins/shared/' );
    }
    
    /**
    * Output a div with the class 'php_error_message'
    *
    * @param string $err_msg Contents of message
    */
    function PhpError($err_msg)
    {
        $this->AddPrompt('php_error_message',$err_msg);
    }

    /**
    * Output a div with the class 'system_error_message'
    *
    * @param string $err_msg Contents of message
    */
    function SystemError($err_msg)
    {
        if( !CCUtil::IsHTTP() )
        {
            print($err_msg);
        }
        else
        {
            $this->AddPrompt('system_error_message',$err_msg);
        }
    }

    /**
    * Output a div with the class 'system_prompt'
    *
    * @param string $prompt Contents of message 
    */
    function Prompt($prompt)
    {
        $prompt = func_num_args() == 1 ? $prompt : func_get_args();
        $this->AddPrompt('system_prompt', $prompt );
    }


    /**
    * Add a form to the page's template variables
    *
    * Use this method to add a form to the page.
    * @see CCForm::GenerateForm()
    * @param object $form The CCForm object to add.
    */
    function AddForm($form)
    {
        $this->vars['forms'][] = array(
                                    $form->GetTemplateMacro(),
                                    $form->GetTemplateVars() );
        if( !$this->_have_forms )
        {
            $this->vars['macro_names'][] = 'print_forms';
            $this->_have_forms = true;
        }
    }

     /**
    * Add a html content into the body of the page
    *
    * @param string $html_text The text to add
    */
    function AddContent($html_text)
    {
        $this->vars['html_content'][] = $html_text;

        if( empty($this->vars['macro_names']) || !in_array( 'print_html_content', $this->vars['macro_names'] ) )
            $this->vars['macro_names'][] = 'print_html_content';
    }

    /**
    * Generates a call out to a client script in the template
    * 
    * How to use this method: 
    <ol><li>Create some client script in a template</li>
    <li>Put it into a named metal-macro block , say 'hover_script'</li>
    <li>Back in PHP call this method with a reference to the script (e.g. 'hover_script')</li></ol>
    * 
    * @param string $script_macro_name The macro with file reference
    * @param bool $place_at_end Set to true if script block requires to be at the end of the page
    */
    function AddScriptBlock($script_macro_name,$place_at_end = false)
    {
        $group = $place_at_end ? 'end_script_blocks' : 'script_blocks';

        if( empty($this->vars[$group]) || !in_array($script_macro_name,$this->vars[$group]) )
            $this->vars[$group][] = $script_macro_name;
    }

    /**
    * Include a script link in the head of the page
    * 
    * 
    * @param string $script_url Path to .js file
    */
    function AddScriptLink($script_url,$top=true)
    {
        $arr = array();
        $arr_name = $top ? 'script_links' : 'end_script_links';
        if( !empty($this->vars[$arr_name]) )
            $arr = $this->vars[$arr_name];
        $arr[] = $script_url;
        $this->vars[$arr_name] = array_unique($arr);
    }

    /**
    * Add a navigation tab set to the top of the page
    *
    * @param array &$tab_info Array of meta data for tabs
    * @param string $macro Name of macro to invoke
    */
    function AddTabNavigator(&$tab_info,$macro)
    {
        $this->vars['tab_info'] = $tab_info;
        $this->vars['page_tabs'] = $macro;
    }


    /**
    * Include a trail of bread crumb urls at the top of the page
    * 
    * $trail arg is an array:
    * <code>
    *   $trail = array( 
    *      array( 'url' => '/',      'text' => 'home' ),
    *      array( 'url' => '/people' 'text' => 'people' ),
    *      array( 'url' => '/people/' . $user, 'text' => $user )
    *    );
    * </code>         
    * @param array $trail Links to display at top of page
    */
    function AddBreadCrumbs($trail,$overwrite=false)
    {
        if( empty($this->vars['bread_crumbs']) || $overwrite )
            $this->vars['bread_crumbs'] = $trail;
    }

    /**
    * Return a trail of bread crumb urls at the top of the page
    *
    * @return array Links to display at top of page
    */
    function GetBreadCrumbs()
    {
        return $this->GetArg('bread_crumbs');
    }
    
    /**
    * Add a LINK tag into the output
    *
    * @param string $placement Either 'head_links' or 'feed_links' 
    * @param string $rel The value for REL attribute
    * @param string $type MIME type (e.g. text/css)
    * @param string $href Value for the HREF attribute
    * @param string $title Value for TITLE attribute
    * @param string $link_text Text for footer links (e.g. 'RSS 1.0')
    * @param string $id Set the link id so other elements on the page can access directly
    */
    function AddLink($placement, $rel, $type, $href, $title, $link_text = '', $id = '')
    {

        $this->vars[$placement][] = array(   'rel'       => $rel,
                                                   'type'      => $type,
                                                   'href'      => str_replace('&','&amp;',$href),
                                                   'title'     => str_replace('"',"'",$title),
                                                   'link_text' => $link_text,
                                                   'id'        => $id );
    }

    /**
    * Add a Feed LINK tag into the output (will be used by feed formatters at RenderPage)
    *
    * @param string $query Streamlined query (no format, etc.)
    * @param string $title Value for TITLE attribute
    * @param string $link_text Text for footer links (e.g. 'RSS 1.0')
    * @param string $link_help Text beside footer links (e.g. 'Remixes of pathchilla')
    * @param string $datasource Datasource for query
    */
    function AddFeedLink($query, $title, $link_text = '', $id = '', $datasource='uploads')
    {
        
        if( !empty($this->_using_default_feeds) )
        {
            // it seems we are already in the middle of a render
            // nuke the default feed links and let the caller
            // override all
            $this->var['feed_links'] = array();
            $this->_using_default_feeds = false;
        }

        $feed_info = array(  'query'      => $query, 
                           'title'     => $title,
                           'link_text' => $link_text,
                           'id'        => $id,
                           'datasource'=> $datasource,
                        );
        CCEvents::Invoke( CC_EVENT_ADD_PAGE_FEED, array( &$this, $feed_info ) );
    }

    /**
    * Add a prompt div to the page
    *
    * @param string $name Class name of prompt
    * @param string $value Content of prompt message
    */
    function AddPrompt($name,$value)
    {
        $this->vars['prompts'][] = array(  'name' => $name,
                                           'value' => $value );

        if( empty($this->vars['macro_names']) )
            $this->vars['macro_names'][] = 'print_prompts';
        elseif( !in_array( 'print_prompts', $this->vars['macro_names'] ) )
            array_unshift($this->vars['macro_names'],'print_prompts');
    }

    function _check_for_title($contents)
    {
        // um, bit of a hack but I can't figure out another
        // to have the <h1> tag in the file end up in the title 
        // of the browser (?),
        $r1 = '<h1[^>]+>%\(([^)]+)\)%'; // macro
        $r2 = "<h1[^>]*><\?=[^']+'([^']+)'"; // global string
        $r3 = '<h1[^>]*>(.*)</h1'; // normal h1
        if( preg_match("#(($r1)|($r2)|($r3))#Uis",$contents,$m) )
            $this->vars['page-caption'] = stripslashes($m[ count($m) - 1 ]);
    }
    /**
    * Calculate and add paging links ( next/prev ) for listings
    *
    * @param object $table A instance of the CCTable being queried
    * @param string $sql_where The SQL WHERE clause to limit queries
    * @param integer $limit Override system defaults for how many records in a page
    * @param object $template Template to set args into (current page is default)
    */
    function AddPagingLinks(&$table_or_dataview,$sql_where='',$limit ='',$template='')
    {
        global $CC_GLOBALS;

        $args = array();

        if( empty($limit) )
        {
            $configs =& CCConfigs::GetTable();
            $settings = $configs->GetConfig('skin-settings');
            $limit = empty($settings['max-listing']) ? 12 : $settings['max-listing'];
        }

        if( isset($_REQUEST['offset']) && (intval($_REQUEST['offset']) > 0) )
        {
            $got_offset = true;
            $offset = $_REQUEST['offset'];
        }
        else
        {
            $got_offset = false;
            $offset = 0;
        }

        if( empty($table_or_dataview->_key_field) )
        {
            $all_row_count = $table_or_dataview->GetCount();
        }
        else
        {
            $table_or_dataview->SetOffsetAndLimit(0,0);
            $all_row_count = $table_or_dataview->CountRows($sql_where);
            $table_or_dataview->SetOffsetAndLimit($offset,$limit);
        }

        $args['limit'] = $limit;                     // 3
        $args['all_row_count'] = $all_row_count;     // 8
        
        if( $limit < $all_row_count )
        {
            $current_url = cc_current_url(); // ccl(CCEvents::_current_action());
            if( count($_GET) > 1 )
            {
                foreach( $_GET as $key => $val )
                {
                    if( $key == 'offset' || $key == 'ccm' )
                        continue;
                    $val = urlencode(CCUtil::StripSlash($val));
                    $current_url = url_args( $current_url, "$key=$val" );
                }
            }

            $args['paging'] = 1;
            $args['current_url'] = $current_url;
            $args['num_pages'] =  intval($all_row_count / $limit);  // 2
            if( ($all_row_count % $limit) > 0 )
              ++$args['num_pages'];                                 // 3

            if( $offset )                                           // 6
            {
               $prev_num = $offset - $limit;                        // 3
               if( $prev_num < 0 )
                 $prev_num = 0;
               $args['prev_link'] = url_args( $current_url, 'offset=' . $prev_num );
               $args['prev_offs'] = $prev_num;
               $args['current_page'] = intval( $offset / $limit );  // 2
            }
            else
            {
               $args['current_page'] = 0;
            }
            
            if( $offset + $limit < $all_row_count )                 // (6 + 3) < 8
            {
               $next_offs = $offset + $limit;
               $args['next_link'] = url_args( $current_url, 'offset=' . $next_offs );
               $args['next_offs'] = $next_offs;
            }
        }
        else
        {
            $args['paging'] = 0;
        }

        if( empty($template) )        
            $this->PageArg('paging_stats',$args);
        else
            $template->SetArg('paging_stats',$args);

        if( !empty($template) )
        {
            // this is all deprecated since 5.1
            $this->PageArg('more_text', _('More') . ' >>>');
            $this->PageArg('back_text','<<< ' . _('Back'));
            if( !empty($args['prev_link']) )
              $this->PageArg('prev_link',$args['prev_link']);
            if( !empty($args['next_link']) )
              $this->PageArg('next_link',$args['next_link']);
        }
        
        return $args;
    }
    
    function _add_template_bread_crumbs($props)
    {
        if( empty($props['breadcrumbs']) )
            return;
            
        $breadcrumbs = $props['breadcrumbs'];
        $bc = array();
        $args = cc_split(',',$breadcrumbs);
        foreach( $args as $arg )
        {
            $arg = trim($arg);
            
            if( $arg == 'home' )
            {
                $bc[] = array( 'url' => ccl(), 'text' => 'str_home' );
            }
            elseif( $arg == 'user_name' || $arg == 'user' || $arg == 'username')
            {
                global $CC_GLOBALS;
                if( empty($CC_GLOBALS['user_name']) )
                    CCUtil::Send404(); // this is bot or a-h
                    
                $bc[] = array( 'url' => ccl('people'), 'text' => 'str_people' );
                $bc[] = array( 'url' => ccl('people',$CC_GLOBALS['user_name'] ),
                                      'text' => $CC_GLOBALS['user_real_name'] );
            }
            elseif( $arg == 'global' || $arg == 'globalsettings' || $arg == 'global_settings' )
            {
                $bc[] = array( 'url' => ccl('admin','site','global'), 'text' => _('Global Settings') );
            }
            elseif( $arg == 'local' || $arg == 'localsettings' || $arg == 'local_settings' )
            {
                $bc[] = array( 'url' => ccl('admin','site','local'), 'text' => _('Manage Site') );
            }
            elseif( $arg == 'forum' )
            {
                $bc[] = array( 'url' => ccl('forum'), 'text' => 'str_forum' );
            }
            elseif( $arg == 'page_title' || $arg == 'page-title'|| $arg == 'title')
            {
                $title = $this->GetTitle();
                if( empty($title) )
                    return; // we're done
                $bc[] = array( 'url' => cc_current_url(), 'text' => $title );
            }
            elseif( $arg == 'topic_title' || $arg == 'topic-title'|| $arg == 'topic')
            {
                if( empty($props['topic_type']) || empty($_GET['topic']) )
                {
                    continue;
                }
                $tname = CCUtil::Strip($_GET['topic']);
                $res = cc_query_fmt("dataview=topic_info&f=php&type={$props['topic_type']}&topic={$tname}" );
                if( !empty($res[0]['topic_name']) )
                {
                    $bc[] = array( 'url' => '', 'text' => $res[0]['topic_name'] );
                }
            }
            else
            {
                if( preg_match( '/text\(([^\)]+)\)/', $arg, $m ) )
                {
                    $bc[] = array( 'url' => '', 'text' => $m[1] );
                }
            }
        }

        if( !empty($bc) )
        {
            $this->AddBreadCrumbs($bc);
        }
    }

}



?>
