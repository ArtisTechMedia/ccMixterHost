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
* $Id: cc-language.php 12595 2009-05-12 00:37:13Z fourstones $
*
*/

/**
* @package cchost
* @subpackage lang
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
 * This is a class for dealing with standard gettext-based localization
 * (i18n) in php. It should be studied and used for sites that
 * want this type of support for their sites.
 *
 * LINUX/OTHER NIXES: locale setting is very sensitive based on your system,
 * installed locales, and how your .po and .mo files are formatted.
 * If your locale doesn't work on a *nix (linux, etc) system, try to do
 * a 'locale -a' from the commandline to see what locale's are installed
 * on your system. Your installed locales are usually in /usr/lib/locale and
 * are often generated from /etc/locale.gen with the locale-gen command.
 * Please consult your system to see what type of locale system is installed 
 * on your system. 
 *
 * WINDOWS: On windows machines, the 
 * locale codes are not used and the full english name of languages is used.
 * This has not been fully tested on windows systems and possibly might 
 * break. There should be a workaround in an upcoming system
 *
 */
class CCLanguage
{
    /**
     * @access private
     * @var array
     * This holds an array that points to all locale prefs (folders) and the
     * languages within these folders for access later.
     */
    var $_all_languages;
   
    /**
     * @access private
     * @var string
     * This is the currently selected language.
     */
    var $_language;
    
    /**
     * @access private
     * @var string
     * The current locale preference folder selected (default is default)
     */
    var $_locale_pref;
    
    /**
     * @access private
     * @var string
     * The current domain to access strings in inside the locale .po files.
     */
    var $_domain;
    
    /**
     * @access private
     * @var string
     * The current web browsers' default autodetected language.
     */
    var $_browser_default_language;

    /**
     * @access private
     * @var integer
     * Error state of this object === 0 ok, otherwise bail
     */
     var $_error_state;

    
    /**
     * Constructor
     * 
     * This method sets up the default language, preferences, etc for dealing
     * with languages for the entire app.
     *
     * TODO: Note that the defaults are in the file cc-defines.php at present
     * and will be moved to defaults that can be set where applicable in a 
     * user's interface.
     * 
     * @param string $language The default language
     * @param string $locale_dir The default locale master folder
     * @param string $locale The default locale preference folder
     * @param string $domain The domain to access strings with from .po files
     */
    function CCLanguage ( $language   = CC_LANG,
                          $locale_dir = CC_LANG_LOCALE, 
                          $locale     = CC_LANG_LOCALE_PREF,
                          $domain     = CC_LANG_LOCALE_DOMAIN )
    {
        global $CC_GLOBALS;
        // CCDebug::StackTrace();
        // CCDebug::PrintVar($CC_GLOBALS);
    
        $this->_all_languages = array();
        $this->_domain = $domain;
        $this->LoadBrowserDefaultLanguage();
        $this->LoadLanguages( $locale_dir );
   
        if ( isset($CC_GLOBALS['lang_locale_pref']) )
            $this->SetLocalePref( $CC_GLOBALS['lang_locale_pref'] );
        else
            $this->SetLocalePref( $locale );

        /* Sets the current language depending upon the user and
           global setting for languages with a couple of presets */

        // The code is an example of how to do things once the
        // user_language field is sure to work...
        // it tries to set the global users language with top priority
        /*
        if ( 'default' == $CC_GLOBALS['user_language'] &&
             'default' == $CC_GLOBALS['lang'] ) {
                $this->SetLanguage( $this->_browser_default_language );
        } else if ( 'default' == $CC_GLOBALS['user_language'] ) {
            $this->SetLanguage( $CC_GLOBALS['lang'] );
            } else if ( !empty($CC_GLOBALS['user_language']) ) {
                $this->SetLanguage( $CC_GLOBALS['user_language'] );
        // next, tries to set the global selected for everyone
            } else if ( !empty($CC_GLOBALS['lang']) ) {
                $this->SetLanguage( $CC_GLOBALS['lang'] );
        // the next attempts language detection from the browser
        } else if ( !empty($this->_browser_default_language ) ) {
            // echo $browser_language;
                $this->SetLanguage( $this->_browser_default_language );
        } else {
                $this->SetLanguage( $language );
        }
        */

        if ( !empty($CC_GLOBALS['lang']) ) 
        {
            if ( 'default' == $CC_GLOBALS['lang'] ) 
                $this->SetLanguage( $this->_browser_default_language );
            else
                $this->SetLanguage( $CC_GLOBALS['lang'] );
        
        } 
        else if ( !empty($this->_browser_default_language ) ) 
        {
            $this->SetLanguage( $this->_browser_default_language );
        } 
        else 
        {
            $this->SetLanguage( $language );
        }

    }
    

    /**
     * Static function that returns boolean true if the
     * PHP get_text module is compiled into this installation
     *
     * @return bool true if installation is get_text enabled
     */
    public static function IsEnabled()
    {
        static $_enabled;
        if( !isset($_enabled) )
            $_enabled = function_exists('gettext');
        return $_enabled;
    }

    /**
     * Loads all locale preference folders and languages into the 
     * $_all_languages array for use during runtime.
     *
     * @param string $locale_dir The master locale directory.
     * @param string $po_fn The master name of catalogs.
     * @return bool <code>true</code> if loads, <code>false</code> otherwise
     */
    function LoadLanguages ($locale_dir = CC_LANG_LOCALE, 
                            $po_fn = CC_LANG_PO_FN) 
    {
        // try to head off any type of malicious search
        if ( empty($locale_dir) || $locale_dir == '/' )
            return false;

        // read in each locale preference folder
        $locale_dirs = glob( $locale_dir . '/*', GLOB_ONLYDIR ); 

        if ( empty($locale_dirs)  )
            return false;
    
        foreach ( $locale_dirs as $dir ) {
            // Read in each folder (language) for consideration
            $lang_dirs = glob( "$dir/*", GLOB_ONLYDIR );

            // if the locale pref. folder has no languages, then don't load it
            if ( empty($lang_dirs) )
                continue;
            $locale_pref = basename($dir);
            $this->_all_languages['locale'][$locale_pref] = 
            array('path' => $dir);
    
            foreach ( $lang_dirs as $lang_dir ) {
                $lang_name = basename($lang_dir);
                // if there is no readable mo file, then get the hell out
                if ( is_readable( "$lang_dir/LC_MESSAGES/$po_fn" ) ) {
                    $this->
            _all_languages['locale'][$locale_pref]['language'][$lang_name] =
            array('path' => $lang_dir); 
                }
            }
        }
        // TODO: Need one more check here of the array and if there is nothing
        // usable, then should return a false here and remove that bad shit from
        // the global array.
    
        return true;
    }
    
    /* MUTATORS */
    
    /**
     * This is where the default locale is set upon startup
     * of the app and where one can set the locale pref at anytime.
     * 
     * @param string $locale_pref The default locale preference directory
     * @return bool <code>true</code> if sets, <code>false</code> otherwise
     */
    function SetLocalePref ($locale_pref = CC_LANG_LOCALE_PREF)
    {
        // conditions for not attempting anything
        if ( $locale_pref == $this->_locale_pref )
            return true;
    
        // these are the various possible settings ranked in order for
        // setting this directory.
        $locale_tests = array(&$locale_pref, 
                              &$_SERVER['HTTP_HOST'], 
                              CC_PROJECT_NAME);
    
        // test to see if we can set to some default in order of the array
        foreach ( $locale_tests as $test )
        {
            if ( isset($this->_all_languages['locale'][$test]) ) {
                $this->_locale_pref = $test;
                return true;
            }
        }
    
        // NOTE: I have gone back and forth on whether or not to set this
        // I think it is wisest to set as last precaution to the default
        // and ideally also make some note in the error log stating what is up
        $this->_locale_pref = CC_LANG_LOCALE_PREF;
        return false;
    }
   
    /**
     * This method sets the current language and also the default if no
     * parameter is provided.
     *
     * @param string $lang_pref This is the language pref as 2 or 4 length code.
     * @return bool <code>true</code> if sets, <code>false</code> otherwise
     */
    function SetLanguage ($lang_pref = CC_LANG)
    {
    if ( $this->_language == $lang_pref )
        return true;
    
        $lang_possible = 
            &$this->_all_languages['locale'][$this->_locale_pref]['language'];
   
        // Yet again, the conditions to test for default language
        // in order
        $lang_tests = array(&$lang_pref, 
                            $lang_pref . "_" . strtoupper($lang_pref));
    
        // test to see if we can set to some default in order of the array
        foreach ( $lang_tests as $test )
        {
            if ( isset($lang_possible[$test]) ) {
                $this->_language = $test;
                return true;
            }
        } 
        // if all else fails set it to the default
        $this->_language = CC_LANG;
        return false;
    }
   
    /**
     * Sets the domain for the .po files.
     * @param string $domain The domain for strings in .po files.
     */
    function SetDomain ($domain = DEFAULT_DOMAIN)
    {
        $this->_domain = $domain;
    }

    /* ACCESSORS */

    /** 
     * Gets all languages and locale prefs as an array.
     * @return array An array that looks like the one constructed by 
     * LoadLanguages()
     */
    function GetAllLanguages ()
    {
        return $this->_all_languages;
    }

    /**
     * Get the current locale preference (directory).
     * @return string The current locale preference directory
     */
    function GetLocalePref ()
    {
        return $this->_locale_pref;
    }

    /**
     * Get the current language.
     * @return string The current language
     */
    function GetLanguage()
    {
        return $this->_language;
    }

    /**
     * Get possible language as an array.
     * @param bool $inherits_parent <code>true</code> or <code>false</code>
     * @return array This is an array of possible language within the current
     * locale preference directory.
     */
    function GetPossibleLanguages()
    {
        $lang_list = 
        array_keys(
            $this->_all_languages['locale'][$this->_locale_pref]['language']);

        $possible_langs = array();

        foreach ( $lang_list as $item ) {
          $possible_langs[$item] = $item;
        }
    
    // This is dumb in that if it is selected for user preferences, it
    // inherits the master default for an installation.
    // If default is selected for the master setting, then this is 
    // set to the constant, CC_LANG, and if that setting is not available
    // or set to nothing, then the default is to use the strings in the
    // code
        $possible_langs['default'] = _('default');
        $possible_langs[CC_LANG] = CC_LANG;
        $possible_langs['autodetect'] = _('autodetect');
    
        return $possible_langs;
    }

    /**
     * Get possible locale prefs as an array.
     * @return array possible locale preferences
     */
    function GetPossibleLocalePrefs()
    {
        $locale_prefs_list = array_keys($this->_all_languages['locale']);
    // had to add this hack because array_combine() is only in php5
    $locale_prefs_list_combined = array();
    foreach ( $locale_prefs_list as $pref )
        $locale_prefs_list_combined[$pref] = $pref;
        return $locale_prefs_list_combined;
    
    }

    /**
     * Get the current domain for strings.
     * @return string The current domain
     */
    function GetDomain()
    {
        return $this->_domain;
    }

    /**
     * Loads Browser Default Language into local variable and returns if
     * already set.
     *
     * @return bool <code>true</code> if loads, <code>false</code> otherwise
     */
    function LoadBrowserDefaultLanguage()
    {
    // return true if this is set
        if ( !empty($this->_browser_default_language) )
            return true;

        if( !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) )
        {
            list($this->_browser_default_language) =  
                 explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'], 2);
        }

        return !empty($this->_browser_default_language);
        
    }

    /**
     * This is where the main guts of this code takes place. I'm actually
     * splitting this out from the constructor because I think that it is
     * a better option to call this after any objects are pulled from a 
     * session variable and/or after doing some checking on the current
     * run-time setup.
     * 
     * NOTE: Setting of the locale is dependent on what locales are installed
     * on one's system: http://us2.php.net/manual/en/function.setlocale.php
     * http://www.gentoo.org/doc/en/guide-localization.xml
     */
    function Init ()
    {
        if( !CCLanguage::IsEnabled() )
        {
            //language_log('Language is disabled, no init');
            return;
        }

        $err = 0;
        
        // set the LANGUAGE environmental variable
        // This one for some reason makes a difference FU@#$%^&*!CK
        // and when combined with bind_textdomain_codeset allows one
        // to set locale independent of server locale setup!!!

        // NOTE: if php's safe is set then putenv will fail,
        // hence the '@' prefix (which allows it it work)

        if ( false == @putenv("LANGUAGE=" . $this->_language ) )
        {
            language_log(sprintf("Could not set the ENV variable LANGUAGE = %s",
                             $this->_language));
            $err = 1;
        }                             

        // set the LANG environmental variable
        if ( ($err === 0) && false == @putenv("LANG=" . $this->_language ) )
        {
            language_log(sprintf("Could not set the ENV variable LANG = %s", 
                             $this->_language));
            $err = 2;
        }                             

        // if locales are not installed in locale folder, they will not
        // get set! This is usually in /usr/lib/locale
        // Also, the backup language should always be the default language
        // because of this...see the NOTE in the class description

        // Try first what we want but with the .utf8, which is what the locale
        // setting on most systems want (and is most compatible
        // Then just try the standard lang encoding asked for, and then if 
        // all else fails, just try the default language
        // LC_ALL is said to be used, but it has nasty usage in some languages
        // in swapping commas and periods! Thus try LC_MESSAGE if on one of
        // those systems.
        // It is supposedly not defined on WINDOWS, so am including it here
        // for possible uncommenting if a problem is shown
        //
        // if (!defined('LC_MESSAGES')) define('LC_MESSAGES', 6);
        // yes, setlocale is case-sensitive...arg
        if ($err === 0) 
        {
            $locale_set = setlocale(LC_ALL, $this->_language . ".utf8", 
                            $this->_language . ".UTF8",
                            $this->_language . ".utf-8",
                            $this->_language . ".UTF-8",
                            $this->_language, 
                            CC_LANG);
            // if we don't get the setting we want, make sure to complain!
            if ( ( $locale_set != $this->_language && CC_LANG == $locale_set) || 
                 empty($locale_set) )
            {
                language_log(
                    sprintf("Tried: setlocale to '%s', but could only set to '%s'.",                        
                            $this->_language, $locale_set) );
                $err = 3;                                                
            }
        }
        
        if( $err === 0 )
        {
            $bindtextdomain_set = bindtextdomain($this->_domain, 
                                      CC_LANG_LOCALE . "/" . $this->_locale_pref );
            if ( empty($bindtextdomain_set) )
            {
                language_log(
                    sprintf("Tried: bindtextdomain, '%s', to directory, '%s', " . 
                            "but received '%s'",
                            $this->_domain, CC_LANG_LOCALE . "/" . $this->_locale_pref,
                            $bindtextdomain_set) );
                $err = 4;                            
            }
        }
        
        // This is the magic key to not being bound by a system locale
        if ( ($err === 0) && "UTF-8" != bind_textdomain_codeset($this->_domain, "UTF-8") )
        {
            language_log(
                sprintf("Tried: bind_textdomain_codeset '%s' to 'UTF-8'",
                        $this->_domain));
            $err = 5;                        
        }

        if( $err === 0 )
        {
            $textdomain_set = textdomain($this->_domain);
            if ( empty($textdomain_set) )
            {
                language_log(sprintf("Tried: set textdomain to '%s', but got '%s'",
                                     $this->_domain, $textdomain_set));
                $err = 6;                                     
            }
            else
            {
                // language_logVar( 'lang', $this->_language );
            }
        }
        
        $this->_error_state = $err;
    
    } // end of method Init ()

    /**
     * Gets NIX system locales 
     * @returns array array of possible locales on a system
     */
    function GetSystemLocales()
    {
        exec('locale -a', $system_locales); /* if need -> $retval); */
        return $system_locales;
    }

    
    /**
     * This method is for generically testing what is happening inside
     * of this object.
     */
    function DebugLanguages ()
    {
        global $CC_GLOBALS;
        echo "<pre>";
        // print_r( $this->_all_languages );
        // print_r( $this );
        // print_r( $CC_GLOBALS );
        echo ( $this->_language );
        // get system locals and print them out
        print_r($this->GetSystemLocales());
        echo "</pre>";
    }

    /**
    * Event handler for {@link CC_EVENT_APP_INIT}
    *
    * @see CCEvents::AppInit()
    */
    function OnInitApp()
    {
        global $CC_GLOBALS;
        // Basically need to init all the language stuff here...
        $this->Init();
        // need the following for lang. encoding standards...arg
        $CC_GLOBALS['lang_xml'] = str_replace('_', '-', $this->_language); 
        $CC_GLOBALS['lang_locale_pref'] = &$this->_locale_pref;
        // TODO: should replace this with a singleton...
        $CC_GLOBALS['language'] = &$this;
        // $this->DebugLanguages();
    }
} // end of CCLanguage class

function language_log($s)
{
//    CCDebug::Log($s);
}
?>
