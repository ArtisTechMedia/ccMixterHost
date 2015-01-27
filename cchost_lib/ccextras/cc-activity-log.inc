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
* $Id: cc-activity-log.inc 12896 2009-07-09 08:11:30Z fourstones $
*
*/

/**
* Manages storing and display of various events for analysis by admins
* 
* @package cchost
* @subpackage admin
*/


if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

class CCAdminLoggingForm extends CCForm
{
    var $_default_ip_url = 'http://tools.whois.net/index.php?fuseaction=whois.whoisbyipresults&host=%IP%';
    
    function CCAdminLoggingForm()
    {
        $this->CCForm();

        $files = array( 'cchost_lib/cc-defines-events.php', 'cchost_lib/ccextras/cc-extras-events.php' );
        $defines = array();
        foreach( $files as $file )
        {
            $names = $m = array();
            $contents = file_get_contents($file);
            preg_match_all('/Notification [Ee]vent.*define\(\'CC_EVENT_([^\`]+)\'[\s,]+[\'"]([^"\']+)[\'"]/Us',$contents,$m);
            foreach( $m[1] as $name )
                $names[] = ucwords( strtolower(str_replace('_', ' ', $name )) );
            $defines = array_merge( $defines, array_combine($m[2],$names) );
        }
            
        $fields = array();
        foreach( $defines as $K => $D )
        {
            $fields[ 'events[' . $K . ']' ] = array(
                'label' => $D,
                'formatter' => 'checkbox',
                'flags' => CCFF_NONE );
        }
        
        $fields['ip_lookup_url'] = array(
            'label' => _('IP Lookup URL'),
            'form_tip' => _('This is the address of the WhoIS utility to use for looking up IP addresses.'),
            'value' => $this->_default_ip_url,
            'formatter' => 'textedit',
            'flags' => CCFF_POPULATE,
        );
        $this->AddFormFields($fields);
        $help = _('Select the events you want to show in the activity log.' );
        $this->SetHelpText($help);
    }

    /**
    * Overwrite CCForm's function to handle special case of logging checkboxes
    */
    function PopulateValues($values) 
    {
        // please don't do this anywhere else, it's a paste-on hack
        // while we prepare 5.1 - Reaching into CC_GLOBALS at this
        // point is very nasty.
        global $CC_GLOBALS;
        if( !empty($CC_GLOBALS['ip_lookup_url'])) {
            $this->SetFormValue('ip_lookup_url',$CC_GLOBALS['ip_lookup_url']);
        }
        else {
            $this->SetFormValue('ip_lookup_url',$this->_default_ip_url);            
        }
        
        // at least this is ok...
        foreach( $values as $V )
            $this->SetFormValue( 'events[' . $V . ']', true );
    }
}

/**
* Table for storing various events for analysis by admins
*
*/
class CCActivityLog extends CCTable
{
    /**
    * Standard constructor
    *
    * @see GetTable
    */
    function CCActivityLog()
    {
        $this->CCTable('cc_tbl_activity_log','activity_log_id');
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCActivityLog();
        return($_table);
    }
}

/**
* Table for storing various events for analysis by admins
*
*/
class CCActivityLogTemp extends CCTable
{
    /**
    * Standard constructor
    *
    * @see GetTable
    */
    function CCActivityLogTemp()
    {
        $name = $this->_tname();
        $this->CCTable($name,'activity_log_id');
    }

    function _tname() 
    {
        return 'cc_tbl_activity_log_' . CCUser::CurrentUserName();
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    public static function & GetTable()
    {
        $tables = CCDatabase::ShowTables();
        $name = CCActivityLogTemp::_tname();
        if( !in_array( $name, $tables ) )
        {

            $sql =<<<EOF
CREATE TABLE `$name` (
  `activity_log_id` int(11) unsigned NOT NULL auto_increment,
  `activity_log_event` varchar(255) NOT NULL default '',
  `activity_log_date` datetime default NULL,
  `activity_log_user_name` varchar(255) NOT NULL default '',
  `activity_log_ip` varchar(255) NOT NULL default '',
  `activity_log_param_1` varchar(255) NOT NULL default '',
  `activity_log_param_2` varchar(255) NOT NULL default '',
  `activity_log_param_3` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`activity_log_id`)
)
EOF;
            CCDatabase::Query($sql);
        }

        static $_table;
        if( !isset($_table) )
            $_table = new CCActivityLogTemp();
        return($_table);
    }
}

/**
* Manages storing and display of various events for analysis by admins
* 
*/
class CCActivityLogAPI
{
    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('activity'), array('CCActivityLogAPI','ViewLog'),
                         CC_ADMIN_ONLY, ccs(__FILE__) , '[clear|admin|export|import]',
                         _('Show activity log'), CC_AG_MISC_ADMIN );
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
                'remote_files' => array( 
                                 'menu_text'  => _('Activity Log'),
                                 'menu_group' => 'configure',
                                 'help' => 'Configure the administrator\'s activity log',
                                 'access' => CC_ADMIN_ONLY,
                                 'weight' => 40,
                                 'action' =>  ccl('activity', 'admin')
                                 ),
                );
        }
    }

    function _do_admin()
    {
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetTitle( _('Activity Log Admin Options') );
        $form = new CCAdminLoggingForm();
        if( empty($_POST['adminlogging']) )
        {
            $form->PopulateValues( empty($CC_GLOBALS['logging']) ? array() : $CC_GLOBALS['logging'] );
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $logging = empty($_POST['events']) ? array() : array_keys($_POST['events']);
            $configs =& CCConfigs::GetTable();
            // 'logging' is not stored in 'config' but stuffed there in AppInit()
            $configs->SaveConfig('logging', $logging, CC_GLOBAL_SCOPE, false );
            // on the other hand, ip_lookup_url is a global variable
            $cfg['ip_lookup_url'] = empty($_POST['ip_lookup_url']) ? $form->_default_ip_url : $_POST['ip_lookup_url'];
            $configs->SaveConfig('config',$cfg,CC_GLOBAL_SCOPE,true);
            $page->Prompt( _("Logging options saved.") );
        }

    }

    /**
    * Display the current activity log
    *
    * Maps from /activity[?user=<i>user_name</i> | ?ip=<i>ip_number</i>]
    *
    * @param string $arg clear: will delete the log
    */
    function ViewLog($arg='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        global $CC_GLOBALS;
        
        $searchterm = '';
        $menuopts['full'] = true;
 
        if( $arg == 'admin' )
        {
            $this->_do_admin();
            return;
        }

        $page->PageArg('activity_menu',$menuopts,'admin.php/show_activity_menu'); 
        $page->SetTitle(_('View Activity Log'));

        $logs =& CCActivityLog::GetTable();
        $logdir = empty($CC_GLOBALS['logfile-dir']) ? './' : $CC_GLOBALS['logfile-dir'];

        if( ($arg == 'clear') || ($arg == 'export') )
        {
            $since = empty($CC_GLOBALS['last-log-save']) ? '2000' : $CC_GLOBALS['last-log-save'];
            if( $this->_do_export($logs,$since) )
            {
                $page->Prompt(_("Activity log exported succesfully"));
                $cargs['last-log-save'] = date('Y-m-d H:i:s');
                $configs =& CCConfigs::GetTable();
                $configs->SaveConfig('config',$cargs,CC_GLOBAL_SCOPE,true);
                if( $arg == 'clear' )
                {
                    $logs->DeleteWhere('1');
                    $page->Prompt(_('Log Archived and Cleared'));
                }
            }
            else
            {
                $page->Prompt(_("Error opening export file") );
            }
            return;
        }

        $where = array();

        if( $arg == 'archive' )
        {
            if( empty($_GET['file']) )
            {
                //
                // view directory of archive files
                //
                $page->SetTitle(_("Activity Log Archive"));
        
                $files = glob( $logdir . 'activity-log*.*' );
                $html = '<table id="activity_log_archive">';
                if( $files !== false )
                {
                    foreach( $files as $file )
                    {
                        $name = basename($file,'.csv');
                        $url = url_args( ccl('activity','archive'), 'file=' . $name );
                        $html .= "<tr><td><a href=\"$url\">$name</a></td></tr>";
                    }
                }
                $html .= '</table>';
                $page->AddContent($html);
                return;
            }
            else
            {
                //
                // View a specific archive file
                // Use a temp activity table
                //
                $filename = $_GET['file'];
                $fname = $logdir . $filename . '.csv';
                $page->SetTitle(sprintf(_("Activity Log Archive: %s"),$filename));
                if( !file_exists($fname) )
                {
                    $page->Prompt(sprintf(_("Can't find activity archive %s"),$fname));
                    return;
                }
                $f = fopen($fname,'r');
                if( !$f )
                {
                    $page->Prompt(sprintf(_("Can't open activity archive %s"),$fname));
                    return;
                }
                $logs = CCActivityLogTemp::GetTable();
                $this->_do_import($fname,$logs);
                //
                // FALL THRU to viewer
            }
        }
        else
        {
            if( !empty($_GET['ip']) )
            {
                $ip = CCUtil::StripText($_GET['ip']);
                $searchterm = $ip;
            }
            else if( !empty($_GET['user']) )
            {
                $name = CCUtil::StripText($_GET['user']);
                $searchterm = $name;
            }
        }

        if( !empty($searchterm) )
        {
            $logs = CCActivityLogTemp::GetTable();
            if( !isset($_GET['offset']) )
                $this->_do_archive_search($logs,$logdir,$searchterm);
        }

        $page->AddPagingLinks($logs,'',50);
        $logs->SetOrder('activity_log_date','DESC');
        $rows = $logs->QueryRows('');
        $page->PageArg( 'act-searchterm', $searchterm );
        $page->PageArg( 'activity-url', ccl( 'activity' ) );
        $page->PageArg('activity_log',$rows,'admin.php/show_activity_log');
    }

    function _do_import($fname,$logs)
    {
        $lines = file ($fname); 
        $c = count($lines);
        if( empty($_GET['offset']) )
        {
            $logs->DeleteWhere('1');
            $start = 1;
            $end = min(101,$c);
        }
        else
        {
            $row_count = $logs->CountRows();
            if( $row_count > $_GET['offset'] + 50 )
                return;
            $start = $_GET['offset'] + 50;
            $end = min( $_GET['offset'] + 101, $c );
        }
        $this->_do_import_text($lines,$logs,$start,$end);
    }

    function _do_archive_search($logs,$logdir,$term)
    {
        $logs->DeleteWhere('1');

        $term = strtolower(trim($term));

        $sql =<<<EOF
INSERT INTO {$logs->_table_name}
 SELECT * FROM `cc_tbl_activity_log` WHERE 
    LOWER(CONCAT(activity_log_user_name,activity_log_ip,`activity_log_param_1`,`activity_log_param_2`,`activity_log_param_3`))
    LIKE "%$term%"
EOF;
        CCDatabase::Query($sql);
    
        $files = glob( $logdir . 'activity-log*.*' );
        $lines = array();
        if( $files !== false )
        {
            foreach( $files as $file )
            {
                $contents = file_get_contents($file);
                preg_match_all( "/^(.*$term.*)$/Uim", $contents, $m );
                if( !empty($m[1]) )
                    $lines = array_merge($lines,$m[1]);
            }
        }
        $count = min(151,count($lines));
        $this->_do_import_text($lines,$logs,0,$count);
    }

    function _do_import_text(&$lines,$logs,$start,$end)
    {
        $table = $logs->_table_name;
        $id = $logs->NextID();
        if( !$id )
            $id = 1;
        CCDatabase::Query("LOCK TABLES $table WRITE");
        for( $i = $start; $i < $end; $i++ )
        {
            $line = ($lines[$i]); 
            if( empty($line) )
                continue;
            $arr = preg_match_all('/((?:[^,#]|#,)*),/', $line,$m); 
            $f = array( $id++ );
            for( $n = 1; $n < 8; $n++ )
            {
                if( empty($m[1][$n]) )
                {
                    $f[] = "''";
                    continue;
                }
                $t = addslashes(str_replace('#,', ',', trim($m[1][$n])));
                $f[] = "'$t'";
            }
            $sql = "INSERT INTO {$table} VALUES (" . implode(',', $f) .")"; 
            CCDatabase::Query($sql);
        } 
        CCDatabase::Query("UNLOCK TABLES");
    }

    function _do_export($logs,$since='')
    {
        global $CC_GLOBALS;
        
        // don't bother if there aren't any new log entries
        //
        $where = empty($since) ? '' : "WHERE activity_log_date > '$since'";
        $qr = CCDatabase::Query( "SELECT * FROM {$logs->_table_name} $where ORDER BY activity_log_date DESC" );
        if( !mysql_num_rows($qr) )
        {
            $page =& CCPage::GetPage();
            $page->Prompt(_("There are no new records in the activity log"));
            return true;
        }

        // build log name

        $logdir = empty($CC_GLOBALS['logfile-dir']) ? './' : $CC_GLOBALS['logfile-dir'];
        $fname = 'activity-log-' . date( 'YmdHi');
        $fnum = 0;
        while( file_exists( $logdir . $fname . '-' . $fnum . '.csv' ) )
            ++$fnum;
        $fname .= '-' . $fnum . '.csv' ;
        $fullpath = $logdir . $fname;

        // Write log

        $f = fopen($fullpath,'w');
        if( !$f )
        {
            return false;
        }
        fwrite($f,"event,date,user,ip,param1,param2,param3\n");
        $keys = array();
        while( $row = mysql_fetch_row($qr) )
        {
            if( empty($keys) )
                $keys = array_keys($row);
            $fields = array();
            foreach( $keys as $key )
                $fields[] = preg_replace('/[^#],/','#,',$row[$key]);
            fwrite( $f,  join( ',', $fields ) . " \n");
        }
        fclose($f);
        return true;
    }


    /**
    * Inner implementation a event hook
    * 
    * @param mixed $args Event dependent 
    * @see CCEvents::AddHook()
    */
    function InnerHook($args,$event_name)
    {
        $logs =& CCActivityLog::GetTable();
        $uargs['activity_log_event']     = $event_name;
        $uargs['activity_log_ip']        = $_SERVER['REMOTE_ADDR'];
        $uargs['activity_log_date']      = date( 'Y-m-d H:i:s' );
        $uargs['activity_log_user_name'] = CCUser::CurrentUserName();

        $ufs[CC_UF_NEW_UPLOAD]      = 'NEW UPLOAD';
        $ufs[CC_UF_FILE_REPLACE]    = 'FILE REPLACE';
        $ufs[CC_UF_FILE_ADD]        = 'FILE ADD';
        $ufs[CC_UF_PROPERTIES_EDIT] = 'PROPERTIES EDIT';

        switch( $event_name )
        {
            case CC_EVENT_UPLOAD_DONE:
                $uargs['activity_log_param_2'] = $ufs[$args[1]];
                $upload_id = $args[0];
                break;

            case CC_EVENT_FILE_DONE:
                $uargs['activity_log_param_1'] = $args[0]['file_name'];
                break;

            case CC_EVENT_DELETE_UPLOAD:
                $uargs['activity_log_param_1'] = $args[0]['upload_name'];
                break;

            case CC_EVENT_DELETE_FILE:
                $files =& CCFiles::GetTable();
                $upload_id = $files->QueryItem('file_upload','file_id = ' . $args[0]);
                break;

            case CC_EVENT_USER_REGISTERED:
                $uargs['activity_log_user_name'] = $args[0]['user_name'];
                $uargs['activity_log_param_1'] = $args[0]['user_name'];
                $uargs['activity_log_param_2'] = $args[0]['user_email'];
                break;

            case CC_EVENT_UPLOAD_MODERATED:
                $uargs['activity_log_user_name'] = $args[0]['user_name'];
                $uargs['activity_log_param_1'] = $args[0]['upload_name'];
                $uargs['activity_log_param_2'] = $args[1];
                break;

            case CC_EVENT_USER_IP_BANNED:
                $uargs['activity_log_user_name'] = $args[0]['user_name'];
                $uargs['activity_log_param_1'] = $args[1];
                break;

            default:
                for( $i = 0; $i < 2; $i++ )
                {
                    if( empty($args[$i]) )
                        continue;
                    $v =& $args[$i];
                    if( is_array($v) )
                    {
                        $k = array_keys($v);
                        $str = '';
                        if( is_string($v[$k[0]]) )
                            $str = CC_strchop($v[$k[0]],25);
                        if( !empty($k[1]) && !empty($v[$k[1]]) && is_string($v[$k[1]]) )
                            $str .= ' ' . CC_strchop($v[$k[1]],25);
                    }
                    else
                    {   
                        if( !is_object($v) )
                            $str = $v;
                    }
                    $uargs['activity_log_param_' . ($i+1) ] = $str;
                }

        }

        if( !empty($upload_id) )
        {
            $uploads =& CCUploads::GetTable();
            $uargs['activity_log_param_1'] = $uploads->QueryItem('upload_name','upload_id = ' . $upload_id);
        }

        $logs->Insert($uargs);

    }

}

?>
