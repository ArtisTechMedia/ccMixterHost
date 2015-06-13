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
* $Id: cc-debug.php 12559 2009-05-06 19:54:43Z fourstones $
*
*/

/**
* Module for debugging ccHost 
*
* For examples and tutorials see {@tutorial cchost.pkg#debug Inspecting variables}, 
* {@tutorial cchost.pkg#stacktrace Dumping a stacktrace} and {@tutorial cchost.pkg#testbed Create a test bed}
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
define( 'CC_QUIET_LEVEL', 0); 

define( 'CC_LOG_FILE', 'cc-log.txt');
define( 'CC_ERROR_FILE', 'cc-errors.txt');
define( 'CC_ERROR_MSG_FILE', 'cc-error-msg.txt');

/**
* Helper API for debugging the app
*
* Error handling state machine:
*
*
*     
*/
class CCDebug
{
    /**
    * Set current state of debugging functionality
    *
    * Debug::Enable(true)
    *     Logging of all errors/warning to cc-errors.txt
    *     Enables debugging functions like Log, LogVar, PrintVar and StackTrace
    *
    * Debug::Enable(false)
    *     No error logging
    *     Disables debugging functions like Log, LogVar, PrintVar and StackTrace
    *
    * @param bool $bool true enables 
    * @return bool $previous_state Returns previous state of debug enabled flag
    */
    public static function Enable($bool)
    {
        $states =& CCDebug::_states();
        $prev = !empty($states['enabled']);
        $states['enabled'] = $bool;
        return($prev);
    }

    /**
    * Installs ccHost custom error handler
    * 
    * Using the ccHost error hanlder can be too strict when including third
    * party libraries (like getID3) and interfacing with outside applications
    * (like phpBB2). It might also cause conflict with other code that wants
    * to set the error handler. Use the QuietErrors/RestoreErrors methods
    * to wrap third party includes and calls.
    *
    * @see Enable
    * @see QuietErrors
    * @param bool $bool true means install ccHost custom error handler
    * @returns bool $prev_state true means error hanlder was already installed, false means no handler was installed
    */
    public static function InstallErrorHandler($bool)
    {
        $states =& CCDebug::_states();
        $prev_state = empty($states['error_handler']) ? false : $states['error_handler'];
        if( $bool )
        {
            // Don't set the handler more than once
            if( empty($states['error_handler']) )
            {
                set_error_handler('cc_error_handler');
                $states['error_handler'] = true;
            }
        }
        else
        {
            if( !empty($states['error_handler']) )
            {
                restore_error_handler();
                $states['error_handler'] = false;
            }
        }
        return($prev_state);
    }

    /**
    * Get current state of debugging flag
    *
    * @see Enable
    * @return bool $bool true means enabled
    */
    public static function IsEnabled()
    {
        $states =& CCDebug::_states();
        return( isset($states['enabled']) && ($states['enabled'] === true) );
    }

    /**
    * Tones down the error reporting noise.
    * 
    * Use this to wrap calls to lax 3rd party libraries 
    * 
    * @see InstallErrorHandler
    * @see RestoreErrors
    * @param integer $new_level Set the new error_reporting level (default is 0!)
    */
    public static function QuietErrors($new_level=CC_QUIET_LEVEL)
    {
        $states =& CCDebug::_states();
        $states['old_err'] = error_reporting($new_level);
    }

    /**
    * Use this to wrap calls to lax 3rd party libraries 
    *
    * @see QuietErrors
    */
    public static function RestoreErrors()
    {
        $states =& CCDebug::_states();
        error_reporting($states['old_err']);
    }

    /**
    * Displays a stack track from where ever this a call to this is placed
    *
    * This method EXITS THE SESSION (!!)
    * 
    * This method is only enabled when CCDebug::Enable is frst called with 'true'
    * 
    * @see Enable
    * @param bool $template_safe true means you are NOT debugging code that displays HTML
    */
    public static function StackTrace($template_safe=false,$full=false)
    {
        if( !CCDebug::IsEnabled() )
            return;

        if (function_exists("debug_backtrace")) 
        {
            $st = debug_backtrace();
        }
        else
        {
            $st = _("No stack trace in this vesion of php");
        }

        if( !$full )
        {
            $c = count($st);
            for( $i = 0; $i < $c; $i++ )
            {
                if( !empty($st[$i]['args']) )
                    unset($st[$i]['args']);
                if( !empty($st[$i]['object']) )
                    unset($st[$i]['object']);
            }
        }
        CCDebug::PrintVar($st,$template_safe);
    }

    /**
    * The most useful function in the entire codbase. Display any variable for debugging
    *
    * Use this method with any variable (include globals like $_REQUEST)
    *
    * This method EXITS THE SESSION (!!)
    * 
    * This method is only enabled when CCDebug::Enable is frst called with 'true'
    * 
    * @see Enable
    * @param mixed $var Reference to variable to dump to screen
    * @param bool $template_safe true means you are NOT debugging code that displays HTML
    */
    public static function PrintVar(&$var, $template_safe = false)
    {
        if( !CCDebug::IsEnabled() )
            return;

        $t =& CCDebug::_textize($var);

        $html = '<pre style="font-size: 10pt;text-align:left;">' .
                htmlspecialchars($t) .
                '</pre>';

        if( $template_safe )
        {
            require_once('cchost_lib/cc-page.php');
            $page =& CCPage::GetPage();            
            $page->PrintPage( $html );
        }
        else
        {
            print("<html><body>$html</body></html>");
        }
        exit;
    }

    public static function PrintV(&$var, $template_safe = false)
    {
        CCDebug::Enable(true);
        CCDebug::PrintVar($var,$template_safe);
    }

    /**
    * Log errors according the severity
    * 
    * This method uses the same contansts as PHP's 
    * error_reporting to determine whether to write
    * out errors to a log file.
    * 
    * @param integer $error_mask Same as php's error_reporting()
    */
    public static function LogErrors($error_mask)
    {
        $states =& CCDebug::_states();
        $old = $states['log_errors'];
        $states['log_errors'] = $error_mask;
        return($old);
    }
    /**
    * The second most useful function in the entire codbase. Display any variable for debugging
    *
    * Dumps the results to /cc-log.txt in the main directory of the site. 
    * Use this method with any variable (include globals like $_REQUEST).
    *
    * This method is only enabled when CCDebug::Enable is frst called with 'true'
    * 
    * @see Enable
    * @param string $msg Use this string to identify what your actually dumping into the log
    * @param $var Reference to variable to dump to screen
    */
    public static function LogVar($msg, &$var)
    {
        if( !CCDebug::IsEnabled() )
            return;

        $t =& CCDebug::_textize($var);

        CCDebug::Log('[' . $msg . '] ' . $t);
    }

    /**
    * Write stuff to a log
    *
    * Dumps the results to /cc-log.txt in ccHost directory
    *
    * This method is only enabled when CCDebug::Enable is frst called with 'true'
    * 
    * @see Enable
    * @param string $msg Use this string to identify what your actually dumping into the log
    */
    public static function Log($msg)
    {
        //print('hello ' . $msg . '<br />');

        if( !CCDebug::IsEnabled() )
            return;
        
        $ip   = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cmdline';
        $msg = '[' . $ip . ' - ' . date("Y-m-d h:i a") . '] ' . $msg . "\n";
        global $CC_GLOBALS;
        static $deferred;
        if( empty($CC_GLOBALS['logfile-dir']) )
        {
            $deferred[] = $msg;
        }
        else
        {
            if( !empty($deferred) )
            {
                error_log("<<<<<<< BEGIN DEFERRED >>>>>>>>>>>>\n",3,$CC_GLOBALS['logfile-dir'] . CC_LOG_FILE);
                foreach( $deferred as $dmsg )
                    error_log($dmsg,3,$CC_GLOBALS['logfile-dir'] . CC_LOG_FILE);
                error_log("<<<<<<< END DEFERRED >>>>>>>>>>>>\n",3,$CC_GLOBALS['logfile-dir'] . CC_LOG_FILE);
             
                $deferred = array();
            }
            error_log($msg,3,$CC_GLOBALS['logfile-dir'] . CC_LOG_FILE);
        }
    }

    /**
    *  Works exactly like a stop watch, ie. starts if stopped and stops if started
    *
    * Based on {@link http://us2.php.net/microtime#50277}
    *
    * Call the function a first time to start the chronometer. The next call to the function will return the number of
    * milliseconds elapsed since the chronometer was started (rounded to three decimal places). The next call will start 
    * the chronometer again from where it finished. Multiple timers can be used by creating multiple $timer variables.
    *
    * <code>
    * CCDebug::Chronometer($timer1);
    * // DO STUFF HERE
    * CCDebug::Log('timer1: ' . CCDebug::Chronometer($timer1));
    *
    * CCDebug::Chronometer($timer2);
    * CCDebug::Chronometer($timer3);
    * 
    * // DO SOMETHING
    * CCDebug::Log('timer3: ' . CCDebug::Chronometer($timer3));
    * // DO SOMETHING
    * CCDebug::Log('timer2: ' . CCDebug::Chronometer($timer2));
    * </code>
    *
    * The $CHRONO_STARTTIME reference paramater does not need to be declared or initialized before use.
    *
    * @param mixed $CHRONO_STARTTIME Reference to timer var
    * @returns float $result Void if starting timer, string (in seconds) formatted
    */
    public static function Chronometer(&$CHRONO_STARTTIME)
    {
       global $total_sql;

       $now = (float) array_sum( explode(' ', microtime()) );
      
       if(isset($CHRONO_STARTTIME['running']))
       {
           if($CHRONO_STARTTIME['running'])
           {
               /* Stop the chronometer : return the amount of time since it was started,
               in ms with a precision of 4 decimal places.
               We could factor the multiplication by 1000 (which converts seconds
               into milliseconds) to save memory, but considering that floats can
               reach e+308 but only carry 14 decimals, this is certainly more precise */
              
               $CHRONO_STARTTIME['elapsed'] += round($now - $CHRONO_STARTTIME['temp'], 4);
               $CHRONO_STARTTIME['running'] = false;
               if( !empty($total_sql) )
                   $CHRONO_STARTTIME['sql'] = $total_sql - $CHRONO_STARTTIME['sql'];

              
               return number_format($CHRONO_STARTTIME['elapsed'],4);
           }
           else
           {
               $CHRONO_STARTTIME['running'] = true;
               $CHRONO_STARTTIME['temp'] = $now;
           }
       }
       else
       {
           // Start the chronometer : save the starting time
          
           
           $CHRONO_STARTTIME = array();
           $CHRONO_STARTTIME['running'] = true;
           $CHRONO_STARTTIME['elapsed'] = 0;
           $CHRONO_STARTTIME['temp'] = $now;
           $CHRONO_STARTTIME['temp'] = $now;
           if( !empty($total_sql) )
                $CHRONO_STARTTIME['sql'] = $total_sql;

       }
    }
    /**
    * Internal buddy
    */
    static function & _textize(&$var)
    {
        ob_start();
        if( is_array($var) || is_object($var) || is_resource($var) )
            print_r($var);
        else
            var_dump($var);
        $t = ob_get_contents();
        ob_end_clean();

        $r =& $t;
        return($r);
    }

    /**
    * Internal buddy
    */
    static function & _states()
    {
        static $_error_states;
        if( !isset($_error_states) )
        {
            $_error_states['enabled'] = false;
            $_error_states['log_errors'] = 0;
        }
        return( $_error_states );
    }

}

$CC_ERROR_STRING = '';

//
// Parsing errors (missing ';') and other fatalities are handled by PHP.
//
// ALL other errors, warnings and whines end up here
//
function cc_error_handler($errno, $errstr='', $errfile='', $errline='', $errcontext=null)
{
    global $CC_GLOBALS;

    // these libraries just spew too much stuff
    // especially warning for php >4.1
    if( strpos($errfile,'phptal') !== false )
        return;
    if( strpos($errfile,'getid3') !== false )
        return;
    // same goes for PEAR in php 5
    if( strpos($errfile,'PEAR') !== false )
        return;
    
    // errno will be 0 when caller uses the '@' prefix
    // comment these two lines if want these errors logged
    // anyway
    if( !$errno ) // || ($errno == 2048) ) // E_STRICT, sorry, we don't care about 
    {                                 // deprecated stuff
        return;
    }

    // just return if system error is below threshold
    if( ($errno & error_reporting()) == 0 )
    {
        global $CC_ERROR_STRING;
        $CC_ERROR_STRING = $errstr;
        return;
    }

    $states =& CCDebug::_states();

    if( ($errno == 2) && preg_match('/Missing argument/',$errstr) ) // missing argument, bots end up here a lot (yes, English only, I know)
    {
        if( $states['enabled'] !== true )
            CCUtil::Send404();
    }


    //
    // Format error message
    //
    $date = date("Y-m-d H:i a");
    if( isset($_SERVER['REMOTE_ADDR']) )
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $url = preg_replace("#http://[^/]*/(.*)#","\1",$_SERVER['REQUEST_URI']);
    }
    else
    {
        $ip = '';
        $url = 'cmdline';
    }
    $err  = "\"$errfile\"($errline): $errstr [$date][$ip][$url]\n";

    //
    // If within logging threshold, send out to some log file
    //
    if( ($states['log_errors'] & $errno) != 0 )
    {
        $logdir = empty($CC_GLOBALS['logfile-dir']) ? './' : $CC_GLOBALS['logfile-dir'];
        error_log($err, 3, $logdir. CC_ERROR_FILE);
    }

    if( $states['enabled'] === true )
    {
        die($err);
    }
    else
    { 
        print( "ERROR($errno) " . $err);
        //
        // If debugging is NOT on then we want to show users a happy
        // friendly lie, er, message
        //
        $txtmsg = empty( $CC_GLOBALS['error-txt'] ) ? CC_ERROR_MSG_FILE : $CC_GLOBALS['error-txt'];
        readfile($txtmsg);
        exit;
    }
}


?>
