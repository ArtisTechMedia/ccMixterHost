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
* $Id: cc-custom.php 12595 2009-05-12 00:37:13Z fourstones $
*
*/
/**
*    This file is fully deprecated
*    Please see cc-template-api.php instead
*
* @package cchost
*/


if( !defined('IN_CC_HOST') )
    die('Welcome to CC Host');

/**#@+
* @access private
* @deprecated
*/

function CC_pending_pool_remix()
{
    return( null );
}


function list_all_users()
{
   $users = new CCUsers();
   $users->SetOrder('user_name');
   $records = $users->GetRecords('');
   return $records;
}

function CC_badcall($to)
{
    print( sprintf(_('A bad call happened to "%s" in a template.'), $to) );
    exit;
}

function CC_split_tags($tagstr)
{
    $a = explode(',',$tagstr);
    return($a);
}

function CC_lang($string)
{
    return(_($string));
}

function CC_count($obj)
{
    if( isset($obj) && is_array($obj) )
        return( count($obj) );
    return( 0 );
}

function CC_test($obj)
{
    return( isset($obj) && !empty($obj) && !PEAR::IsError($obj) );
}

function CC_query($tablename,$func,$module='')
{
    if( !empty($module) )
        require_once($module);
    if( substr($tablename,0,2) != 'CC' )
        return(array());
    $table = new $tablename;
    return( $table->$func() );
}

function CC_ids_for_records(&$records)
{
    $count = count($records);
    $ids = '';
    for( $i = 0; $i < $count; $i++ )
    {
        $ids .= $records[$i]['upload_id'];
        if( $i != $count - 1 )
            $ids .= ';';
    }
    return($ids);
}

function CC_debug_dump($obj)
{
    $isenabled = CCDebug::IsEnabled();
    CCDebug::Enable(true);
    CCDebug::PrintVar($obj,false);
    CCDebug::Enable($isenabled);
}

function CC_log_dump($name,$obj)
{
    $isenabled = CCDebug::IsEnabled();
    CCDebug::Enable(true);
    CCDebug::LogVar($name,$obj);
    CCDebug::Enable($isenabled);
}

function & CC_cache_query( 
        $tags, $search_type='all', $sort_on='', $order='',
        $limit='', $with_menus=false, $with_remixes=false)
{
    global $CC_GLOBALS, $CC_CFG_ROOT;
    $cname = cc_temp_dir() . '/_ccc_' . $tags . '.txt';
    if( file_exists($cname) )
    {
        include($cname);
        return $rows;
    }
    
    $rows =& CC_tag_query($tags,$search_type,$sort_on,$order,$limit,$with_menus,$with_remixes);

    // this is hack fix to prevent random vroots from
    // showing up in the cache for these cached lists
    // hardly a permanent solution but we need to stop the
    // bleeding on mixter for now and keep all song pages
    // in the 'media' vroot

    if( $CC_CFG_ROOT == CC_GLOBAL_SCOPE )
    {
        $data = serialize($rows);
        $data = str_replace("'","\\'",$data);
        $text = '<? /* This is a temporary file created by ccHost. It is safe to delete. */ ' .
                 "\n" . '$rows = unserialize(\'' . $data . '\'); ?>';
        $f = fopen($cname,'w+');
        fwrite($f,$text);
        fclose($f);
        chmod($cname,cc_default_file_perms());
        $configs =& CCConfigs::GetTable();
        $tcache = $configs->GetConfig('tcache',CC_GLOBAL_SCOPE);
        if( !in_array($cname,$tcache) )
        {
            $tcache[] = $cname;
            $configs->SaveConfig('tcache',$tcache,CC_GLOBAL_SCOPE);
        }
    }
    return $rows;
}

function & CC_tag_query( 
   $tags,$search_type='all',$sort_on='',$order='',$limit='',$with_menus=false,$with_remixes=false)
{
    $q = "f=php&dataview=info_avatar&tags=$tags&type=$search_type&sort=$sort_on&order=$order&limit=$limit";
    require_once('cchost_lib/cc-query.php');
    $query = new CCQuery();
    $args = $query->ProcessAdminArgs($q);
    list( $records ) = $query->Query($args);
    require_once('cchost_lib/cc-dataview.php');
    $dv = new CCDataView();
    $info = array();
    if( $with_menus )
        $info['e'][] = CC_EVENT_FILTER_UPLOAD_MENU;
    if( $with_remixes )
        $info['e'][] = CC_EVENT_FILTER_REMIXES_SHORT;
    if( !empty($info) )
        $dv->FilterRecords($records,$info);
    return $records;
}


/**#@-*/

?>
