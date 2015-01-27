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
* $Id: cc-events.php 10358 2008-07-01 23:09:12Z fourstones $
*
*/

/**
* Implements core eventing system
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Internal class used for event triggering
*/
class CCAction
{
    function _dummy() {}
}

/**
* Invoking and registering system wide events. 
*
* For a tutorial on using this method see {@tutorial cchost.pkg#url Create an URL and Bind it to a Method}
*
* You can register for an event ({@link CCEvents::AddHandler}) so that when some code, 
* somewhere triggers the event your code will be called.
*  
* You can also define an event and then invoke it ({@link CCEvents::Invoke}) and get
* results back.
*  
* Using this system allows for modules to come and go, extending the system without 
* disturbing or rewriting the core code. Typical events are for when a menu is being
* constructed, when a row is fetched from a database, when a file is done being uploaded,
* etc. etc.
*  
* Events that are mapped to URLs are handled separately via {@link CCEvents::MapUrl}.
*
* @see AddHandler
* @see Invoke
* @see MapUrl
*/
class CCEvents
{
    /**
    * Call this to register (wait) for an event.
    *  
    * This is typically done outside of any function or class at the top of a source file
    * before the application is really going. This is the only way to ensure that you
    * don't actually miss the firing of the event.
    *  
    * <code>
    * CCEvents::AddHandler(CC_EVENT_MAIN_MENU, array( 'CCID3Tagger', 'OnBuildMenu') );
    * </code>
    *  
    * The <b>$eventname</b> parameter is typically a descriptively named define(). By
    * convention event defines all start with <b>CC_EVENT_</b>. 
    *  
    * The <b>$callback</b> parameter is an extended version of PHP's callback. It either be
    * a string (function name) or an array that has an instance of that class and the 
    * string method name.
    *  
    * As an extension you can make the first element in the callback array a string,
    * the name of the class with the callback. The class will only instantiated if
    * and when the event is triggered. (The class must have no constructor or an
    * empty parameter constructor.) 
    *  
    * The signature of the callback is different for every event and can not have
    * additional parameters (although it can have less). Every event publisher/invoker
    * must specify what it expects to pass along to the event listeners.
    *  
    * Event triggering is synchronous (which means each callback blocks any other callback).
    * It is therefore recommended that if the callback is a class that needs to be instantiated,
    * then the class should as little creation overhead as possible since there might be many, 
    * many others waiting for the same event (like when building the main menu). 
    *  
    * The <b>$includefile</b> paramater indicates what module to load before calling the
    * handler. The recommended usage is:
    *
    * 1. Put the CCEvent::AddHandler call in a .php file in the Plugins Path
    * 2. For "high volume" events (e.g. {@link CC_EVENT_FILTER_UPLOAD_LIST}) put the handler 
    *    into the same file, in which case <b>$includefile</b> does not need to be 
    *    specified since all .php modules are already in memory
    * 3. For "lo volume" events (e.g. {@link CC_EVENT_MAP_URLS}) put the handler in
    *    a .ini file and specify the location (relative to the ccHost root install)
    *    
    *  
    * @param string $eventname Unique system-wide name for the event
    * @param mixed  $callback Either string method name or extended callback array
    * @param string $includefile Location of module with event handler, relative to ccHost install
    * @see Invoke
    */
    public static function AddHandler($eventname, $callback, $includefile='')
    {
        $events =& CCEvents::_events();
        $events[$eventname][] = array( $callback, $includefile );
    }

    /**
    * Invokes a system wide event, optionally with parameters.
    *  
    * Most events are triggered at extensibility points, this way
    * the calling code can remain 'clean' without knowlegde of 
    * who is implementing the event handler.
    *  
    * <code>
    *     // file is uploaded, database record, let add-in modules
    *     // have a go at the file and record. 
    *     
    *     CCEvents::Invoke( CC_EVENT_FINALIZE_UPLOAD, array( &$record ) );
    * </code>
    *  
    *  
    * Other times they are used to gather information from disparate
    * places and multiple providers.
    *  
    * <code>
    *  
    *     // Only proceed if 'image' is a valid media type
    *  
    *     $types = array();
    *     CCEvents::Invoke( CC_EVENT_VALID_MEDIA_TYPES, array(&$types) );
    *     if( in_array('image',$types) )
    *     {
    *         //...
    *     }
    *</code>
    *  
    * Arguments are passed in an array because that is the only way to 
    * ensure that references are kept throughout the invocation. 
    *  
    * @param string $eventname Unique system-wide name for the event.
    * @param array  $args Array of parameters to pass along to event listeners.
    * @see AddHandler
    */
    public static function Invoke($eventname,$args=array())
    {
        //CCDebug::Log("Invoking: $eventname");

        $hook_list =& CCEvents::_hooks();
        if( !empty($hook_list) )
        {
            foreach( $hook_list as $hook_handler )
            {
                if( is_array($hook_handler) && is_string($hook_handler[0]))
                {
                    $class = $hook_handler[0];
                    $method = $hook_handler[1];
                    $obj = new $class;
                    $hook_handler = array( $obj, $method );
                }
                $hargs = array( $eventname, $args );
                if( call_user_func($hook_handler,$hargs) === false )
                    return;
            }
        }

        $events  =& CCEvents::_events();
        $results = array();

            if( CCEvents::_watch() )
            {
                $x[] = $eventname;
                $x[] = $events[$eventname];
                CCDebug::PrintVar($x,false);
            }


        if( array_key_exists($eventname,$events) )
        {
            foreach( $events[$eventname] as $handler )
            {
                $callback = $handler[0];
                if( is_array($callback) )
                {
                    if( is_string($callback[0]) )
                    {
                        $class = $callback[0];
                        if( !class_exists($class) && !class_exists(strtolower($class)) )
                        {
                            if( !CCEvents::_load_event_handler($handler) )
                                return;
                        }
                        if( !class_exists($class) && !class_exists(strtolower($class)) )
                        {
                            $x[] = 'Bad handler mapping';
                            $x[] = $handler;
                            CCDebug::PrintVar($x);
                        }
                        $obj = new $class;
                    }
                    
                    $callback = array( $obj, $callback[1] );
                }
                else
                {
                    if( !function_exists($callback) && !function_exists(strtolower($callback)) )
                    {
                        if( !CCEvents::_load_event_handler($handler) )
                            return;
                    }

                }
                $results[] = call_user_func_array($callback,$args);
            }
        }

        return($results);
    }

    /**
    * @access private
    */

    static function & _watch()
    {
        static $watch = false;
        return $watch;
    }

    public static function SetWatch($bool)
    {
        $w =& CCEvents::_watch();
        $w = $bool;
    }

    static function & _hooks()
    {
        static $_hook_list;
        return $_hook_list;
    }

    /**
    * Adds an event hook into the system
    *
    * All events will be sent to the '$func' argument
    *
    * @param string $func Name of function callback
    */
    public static function AddHook( $func )
    {
        $hook_list =& CCEvents::_hooks();
        $hook_list[] = $func;
    }

    /**
    * Maps access to commands and incoming urls to functions/methods
    *
    * For a tutorial on using this method see {@tutorial cchost.pkg#url Create an URL and Bind it to a Method}
    *
    * You call this method in your event handler for {@link CC_EVENT_MAP_URLS}
    * It will tell the system what method to call in repsone to incoming URLs
    *
    * The more specific mapping
    * is always respected first. If no handler is found for a specific url
    * the trailing part of the url is assumed to be arguments to the method
    * that handles the base url.
    * 
    * For example:
    * <code>
    *            
    * // Given:
    *
    * CCEvents::AddHandler(CC_EVENT_MAP_URLS,      array( 'MyClass' , 'OnMapUrls'));
    * 
    * class MyClass
    * {
    *     function OnMapUrls()
    *     {
    *         CCEvents::MapUrl( 'foo',      
    *                           array( 'MyClass', 'HandleFoo'),    
    *                           CC_DONT_CARE_LOGGED_IN );
    *
    *         CCEvents::MapUrl( 'foo/bar',  
    *                           array( 'MyClass', 'HandleFooBar'), 
    *                           CC_MUST_BE_LOGGED_IN );
    *     }
    *  }
    * 
    *  // Here is what the mapping looks like:
    *  //
    *  //    URL                             Method called
    *  //  ------                            ----------------
    *  //  http://cchost.org/media/foo       $this->HandleFoo()
    *  //  http://cchost.org/media/foo/bar   $this->HandleFooBar()
    *  //  http://cchost.org/media/foo/BAZ   $this->HandleFoo('BAZ')
    *  //
    * </code>
    * 
    * Functions and methods mapped to URLs can be loaded 'on demand',
    * that is, the file the handler is in does not be included before
    * the URL is process. You specify the name of the file in the 
    * 'module' parameter. The path should be either fully qualified
    * or relative to the cchost document root. 
    *
    * Functions mapped to URLs are 'self documenting', that is: the
    * documentation for public URLs are stored with the mapping so
    * it is always (theoretically) always up to date. The documentation
    * gleaned from here is mainly targeted at site admins. URLs used as
    * 'commands' (something an admin should or could put into a 
    * menu or nav tab) REALLY should be documented, otherwise there
    * is a good chance the admin won't know about it or how to use
    * it. 
    *
    * URLs that used for 'internal' purposes like form POST or part
    * of a stateful AJAX protocol should not be documented since they
    * are unlikely to be useful to admins.
    *
    * Also note: ONLY documented URLs are used in admin/access, the
    *            form that allows super admins to change the access
    *            rights.
    *
    * For the 'doc_param' argument you should specify URL parameters
    * you allow or require. The notation is: curly braces {} used
    * for required parameters and square brackets [] used for the
    * optional one.
    * 
    *   '{user_id}/[upload_id]'
    *
    * In the example above, user_id is required, upload_id is optional.
    *
    * 'doc_summary' is a short description of what the URL is used
    * for. If the URL displays a form, say which one. If it is an AJAX
    * call, say that.
    *
    * 'doc_group' is used to group URLs into functionality categories. 
    * Stock ccHost groups names can be found in cc-defines-access.php
    * But you can use any string to group your custom URL commands,
    * like your company or organization's name.
    *
    * 
    * @param string $url What the incoming url looks like stripped of domain and vroot
    * @param mixed $callback Method to be called 
    * @param integer $permissions CC_* flags to mask off unauthorized users
    * @param string $module Name of the file to load before attempting to call handler
    * @param string $doc_param Documentation of params allowed and required
    * @param string $doc_summary Brief descriptions of the functionality of the handler
    * @param string $doc_group Documenation category to use for this handler
    */
    public static function MapCommand( $params=array( 'url'         => '',
                                        'callback'    => '',
                                        'module'      => '',
                                        'permissions' => '',
                                        'roles'       => '',
                                        'menu_text'   => '',
                                        'doc_param'   => '',
                                        'doc_summary' => '',
                                        'doc_group'   => '' ) )
    {
        extract($params);

        $action     = new CCAction();
        $action->cb = $callback;
        $action->pm = $permissions;
        $action->md = $module;
        $action->dp = $doc_param;
        $action->ds = $doc_summary;
        $action->dg = $doc_group;
        $action->url = $url;
        $paths =& CCEvents::_paths();
        $paths[$url] = $action;
    }

    public static 
    function MapUrl( $url, $callback, $permissions, $module='', $doc_param='',
                                                                $doc_summary='',
                                                                $doc_group = '' )
    {
        CCEvents::MapCommand( array( 'url'         => $url,
                                    'callback'    => $callback,
                                    'module'      => $module,
                                    'permissions' => $permissions,
                                    'doc_param'   => $doc_param,
                                    'doc_summary' => $doc_summary,
                                    'doc_group'   => $doc_group ) );
    }

    /**
    * Creates ant straigh-across mapping between two urls
    *
    * @param string $this_url Incoming URL 
    * @param string $becomes_this Outgoing aliases URL
    */
    public static function AddAlias( $this_url, $becomes_this )
    {
        $aliases =& CCEvents::_aliases();
        $aliases[$this_url] = $becomes_this;
    }

    /**
    * Grabs the current incoming URL and calls the approproate method mapped to it
    *
    * @param object $action a CCAction object (empty means perform the current url)
    */
    public static function PerformAction($action = null )
    {
        if( !isset($action) )
        {
            $action = CCEvents::ResolveUrl();
            if( !isset($action) )
            {
                $_REQUEST['ccm'] = preg_replace('#^/([^/]+)/#','/\1/docs/',$_REQUEST['ccm']);
                $action = CCEvents::ResolveUrl();
            }
        }

        $method = false;

        if( isset($action) )
        {
            require_once('cchost_lib/cc-menu.php');

            $pm = CCEvents::_get_action_perms($action);

            if( ($pm & CCMenu::GetAccessMask() ) == 0 )
                $action = CCEvents::ResolveUrl('homepage'); // gets the home page

            if( is_string($action->cb) )
            {
                $method = $action->cb;
                if( !function_exists($method) && !function_exists(strtolower($method)) )
                    CCEvents::_load_action($action);
            }
            else
            {
                if( is_string($action->cb[0]) )
                {
                    $cname = $action->cb[0];
                    if( !class_exists($cname) && !class_exists(strtolower($cname)) )
                        CCEvents::_load_action($action);
                    $obj = new $cname;
                    $method = array( &$obj, $action->cb[1] );
                }
                else
                {
                    $method = $action->cb;
                    if( !method_exists($method) && !method_exists(strtolower($method)) )
                        CCEvents::load_action($action);
                }
            }
        }

        if( $method )
        {
            if( !isset($action->args) )
                $action->args = array();

            call_user_func_array($method,$action->args);
        }
        else
        {
            CCUtil::Send404(true);
            //CCPage::SystemError("Invalid path");
        }
    }

    static function _load_event_handler($handler)
    {
        if( empty($handler[1]) )
        {
            //CCDebug::PrintVar($handler,false);
            $hx = serialize($handler);
            print(_("Can't find module") . ' ' . $hx);
            CCUtil::Send404(true);
            return false;
        }
        else
        {
            require_once($handler[1]);
        }

        return true;
    }

    static function _load_action($action)
    {
        if( empty($action->md) )
        {
            CCUtil::Send404(false);
            /*
            CCDebug::Enable(true);
            $x['error'] = "Can't find module";
            $x['action'] = $action;
            CCDebug::PrintVar($x);
            */
        }
        else
        {
            require_once($action->md);
        }
    }

    public static function CheckAccess($url)
    {
        static $_accmap;
        static $_urlmap;
        static $_mask;
        static $_ccl;

        if( !isset($_accmap) )
        {
            $configs =& CCConfigs::GetTable();
            $_accmap = $configs->GetConfig('accmap');
            $_mask   = CCMenu::GetAccessMask();
            $_ccl    = ccl();
            $_urlmap = CCEvents::GetUrlMap();
        }
        if( strpos($url,'viewfile') !== false )
            return true;
        $url = str_replace( $_ccl, '', $url);
        if( !empty($_accmap[$url]) )
            return ($_accmap[$url] & $_mask) != 0;
        // viewfile commands? arguments? ah!!
        return empty($_urlmap[$url]) || ($_urlmap[$url]->pm & $_mask) != 0;
    }

    /**
    * @access private
    */
    static function _get_action_perms($action)
    {
        $configs =& CCConfigs::GetTable();
        $accmap = $configs->GetConfig('accmap');
        if( isset($action->url) )
        {
            if( empty($accmap[$action->url]) )
                return $action->pm;
            return $accmap[$action->url];
        }
        else
        {
            // emergency code while I work this out 
            // url map is corrupted
            $w['config_type'] = 'urlmap';
            $configs->DeleteWhere($w);
            trigger_error("URLMAP was deleted");
        }
    }

    /**
    * Convert an url into an internal action structure.
    * 
    * @param string $url Internal url to execute (empty means the currently calling URL)
    */
    public static function ResolveUrl($url='')
    {
        global $CC_GLOBALS;

        $regex = '%/([^/\?]+)%';

        if( $url == 'homepage' )
        {
            $url_pieces = array();
        }
        elseif( empty($url) )
        {
            preg_match_all($regex,CCUtil::StripText($_REQUEST['ccm']),$a);
            // peel off the cfgroot
            array_shift($a[1]);
            $url_pieces =& $a[1];
        }
        else
        {
             preg_match_all($regex,$url,$a);
             $url_pieces =& $a[1];
        }

        if( empty($url_pieces) )
        {
            $P = CCEvents::_get_home_page();
            preg_match_all($regex,'/'.$P,$a);
            $url_pieces =& $a[1];
        }
        else
        {
            $P = implode('/',$url_pieces);
        }

        $paths          =& CCEvents::GetUrlMap();
        $current_action =& CCEvents::_current_action();
        $aliases        =& CCEvents::_aliases();

        if( array_key_exists($P,$aliases) )
        {
             $P = $aliases[$P];
             preg_match_all($regex,$P,$a);
             $url_pieces =& $a[1];
        }

        $current_action = $P;
        $argcount  = 0;

        while( $P )
        {
            if( array_key_exists($P,$paths) )
            {
                $action = $paths[$P];
                $shiftby = count($url_pieces) - $argcount;
                for( $i = 0; $i < $shiftby; $i++ )
                    array_shift($url_pieces);
                $action->args = $url_pieces;
                return( $action );
            }
            $P = substr( $P, 0, strrpos($P,'/') );
            if( $P )
               $argcount++;
        }
    }

    static function _get_home_page()
    {
        global $CC_CFG_ROOT;

        $configs =& CCConfigs::GetTable();
        $settings = $configs->GetConfig('settings');
        return $settings['homepage'];
    }

    /**
    * Retruns the current url-to-method map
    *
    * @param bool $force false: use cached version if available; true: always generate a new map
    */
    public static function & GetUrlMap($force = false)
    {
        $paths =& CCEvents::_paths();
        $configs =& CCConfigs::GetTable();
        if( !$force && empty($paths) )
        {
            $w['config_type'] = 'urlmap';
            $rows = $configs->QueryRows($w);
            if( !empty($rows[0]) )
                $paths = unserialize($rows[0]['config_data']);
            // $paths = $configs->GetConfig('urlmap');
        }
        if( $force || empty($paths) )
        {
            CCEvents::Invoke(CC_EVENT_MAP_URLS);
            $configs->SaveConfig('urlmap',$paths,CC_GLOBAL_SCOPE);
        }
        return($paths);
    }

    /**
    * @access private
    */
    static function & _paths()
    {
        static $_paths;
        if( !isset($_paths) )
            $_paths = array();
        return( $_paths );
    }

    /**
    * @access private
    */
    static function & _events()
    {
        static $_events;
        if( !isset($_events) )
            $_events = array();
        return( $_events );
    }

    /**
    * @access private
    */
    static function & _current_action()
    {
        static $_current_action;
        return( $_current_action );
    }

    /**
    * @access private
    */
    static function & _aliases()
    {
        static $_aliases;
        if( !isset($_aliases) )
            $_aliases = array();
        return( $_aliases );
    }

}


?>
