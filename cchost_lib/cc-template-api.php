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
* $Id: cc-template-api.php 13108 2009-07-27 18:59:51Z fourstones $
*
*/
/**
*   CUSTOM TEMPLATE API 
*
*    Methods here were designed to be called from templates:
*
* @package cchost
* @subpackage admin
*/


if( !defined('IN_CC_HOST') )
    die('Welcome to CC Host');


/**
* Chop a string and append ellipse if over a given length.
*
* The third parameter '$dochop' allows for runtime decisions about
* whether to chop or not. This is <i>much</i> faster than making the
* branch descision in phpTAL.
*
* @param string $str String to potentially chop
* @param integer $maxlen Maximum number of characters before adding ellipse
* @param boolen $dochop If false then maxlen is ignored and string is returned
* @return string $string Chopped string
*/
function cc_strchop($str,$maxlen,$dochop = true)
{
    if( empty($maxlen) || empty($dochop) || (strlen($str) <= $maxlen) )
        return($str);
    return( substr($str,0,$maxlen) . '...' );
}

/**
* Format a date
* 
* Maps to: date(<i>fmt</i>,strtotime(<i>date</i>))
*
* @param string $date A string representation of a date
* @param string $fmt A PHP date() formatter
* @return string $datestring A formatted date string
*/
function cc_datefmt($date,$fmt)
{
    if( empty($date) )
        return '';
    return( date($fmt,strtotime($date)) );
}


/**
* Return the values current stored in the configs tables
*
* @param string $configName Name of settings (e.g. 'chart', 'licenses')
* @return mixed Raw config value as stored in db
*/
function cc_get_config($configName,$format='php')
{
    $configs =& CCConfigs::GetTable();
    $v = $configs->GetConfig($configName);
    if( $format == 'php' )
        return $v;
    if( $format == 'json' )
        return cc_php_to_json($v);
}


function cc_get_config_roots()
{
    $configs = CCConfigs::GetTable();
    $roots = $configs->GetConfigRoots();
    $keys = array_keys($roots);
    foreach( $keys as $k )
        $roots[$k]['url'] = ccc($roots[$k]['config_scope']);
    return $roots;
}

function cc_get_submit_types($allow_remix=false,$default_title='')
{
    if( empty($default_title) )
        $default_title = _('(Default)');

    require_once('cchost_lib/cc-submit.php');
    $sapi = new CCSubmit();
    $types = $sapi->GetSubmitTypes();
    foreach( $types as $typekey => $typeinfo )
    {
        if( empty($types['quota_reached']) && ($allow_remix || !$typeinfo['isremix']) )
            $submit_types[$typekey] = $typeinfo['submit_type'];
    }
    $alternates = $sapi->GetAlternates();
    return array_merge(    array( '' => $default_title ),
                        $submit_types, $alternates );
}

function cc_query_default_args($required_args=array())
{
    require_once('cchost_lib/cc-query.php');
    require_once('cchost_lib/zend/json-encoder.php');
    $query = new CCQuery();
    $args = array_merge( $query->ProcessUriArgs(), $required_args );
    $json_args = CCZend_Json_Encoder::encode($args);
    return array( $args, $json_args );
}

function cc_query_fmt($qstring,$debug=0)
{
    if( empty($qstring) )
        return array();

    $qstring = CCUtil::Strip($qstring);    
    parse_str($qstring,$args);
    require_once('cchost_lib/cc-query.php');
    $query = new CCQuery();
    if( empty($args['format']) )
        $args['format'] = 'php';
    $args = $query->ProcessAdminArgs($args);
    list( $results ) = $query->Query($args);
    return $results;
}

function cc_query_get_optset( $optset_name = 'default', $fmt = 'php' )
{
    $optsets = cc_get_config('query-browser-opts');
    if( empty($optsets[$optset_name]) )
    {
        $optset = array( 'template' => 'reccby',
                         'css'      => 'css/qbrowser_wide.css',
                         'limit'    => 25,
                         'reqtags'  => '*',
                         'license'  => 1,
                         'user'     => 1,
                        );
    }
    else
    {
        $optset = $optsets[$optset_name];
    }
    $reqtags_all = cc_get_config('query-browser-reqtags');
    if( empty($optset['types_key']) || empty($reqtags_all[$optset['types_key']]) )
    {
        $reqtags = array( 
                        array( 'tags' => '*',
                               'text' => 'str_filter_all' )
                         );
    }
    else
    {
        $reqtags = $reqtags_all[$optset['types_key']];
    }

    $optset['types'] = $reqtags;

    if( $fmt != 'json' )
        return $optset;

    require_once('cchost_lib/zend/json-encoder.php');
    return CCZend_Json_Encoder::encode($optset);
}

if( !function_exists('http_build_query') )
{
    function http_build_query($args)
    {
        $qargs = array();
        foreach( $args as $K => $V )
            $qargs[] = $K . '=' . $V;
        return join( '&', $qargs );
    }
}

function cc_get_value($arr,$key)
{
    if( is_array($arr) && array_key_exists($key,$arr) )
        return $arr[$key];
    return null;
}


function cc_get_user_role($user_name)
{
    return CCUser::IsAdmin($user_name) ? 'admin' : '';
}

function cc_get_topic_name_slug($col='topic_name')
{
    return "LOWER(REPLACE(REPLACE({$col},' ','-'),\"'\",''))";
}

function cc_get_license_logo_sql($size='big',$col='license_logo_url')
{
    return "license_img_{$size} as {$col}";
}

function cc_get_user_avatar_sql($table='',$colname='user_avatar_url')
{
    global $CC_GLOBALS;

    if( !empty($table) && substr($table,-1) != '.' )
        $table .= '.';
        
    if( empty($CC_GLOBALS['avatar-dir']) )
    {
        $aurl = ccd($CC_GLOBALS['user-upload-root']) . '/';
        $aavtr = "{$table}user_name,  '/', " ;
    }
    else
    {
        $aurl = ccd($CC_GLOBALS['avatar-dir']) . '/';
        $aavtr = '';
    }
    if( !empty($CC_GLOBALS['default_user_image']) )
    {
        $davurl = ccd($CC_GLOBALS['default_user_image']);
    }
    else
    {
        $davurl = '';
    }
 
    return "IF( LENGTH({$table}user_image) > 0, CONCAT( '$aurl', {$aavtr} {$table}user_image ), '$davurl' ) as {$colname}";
    //return "'$davurl' as user_avatar_url";
}

function cc_get_user_avatar(&$R)
{
    global $CC_GLOBALS;

    if( empty($R['user_image']) )
    {
        return ccd($CC_GLOBALS['image-upload-dir'],$CC_GLOBALS['default_user_image']);
    }

    if( empty($CC_GLOBALS['avatar-dir']) )
    {
        return ccd($CC_GLOBALS['user-upload-root'], $R['user_name'], $R['user_image'] );
    }

    return ccd($CC_GLOBALS['avatar-dir'], $R['user_image'] );
}

function cc_php_to_json(&$obj)
{
    require_once('cchost_lib/zend/json-encoder.php');
    return CCZend_Json_Encoder::encode($obj);
}

function cc_wrap_user_tags(&$tag)
{
    $tag = '<p class="cc_autocomp_line" id="_ac_'.$tag.'">'.$tag.'</p>';
}

function cc_content_feed($query,$title,$datasource='uploads',$id='')
{
    $page =& CCPage::GetPage();
    $title = $page->String($title);
    $page->AddFeedLink($query, $title, $title, $id, $datasource);
}

function cc_fancy_user_sql($colname='fancy_user_name',$table='')
{
    if( !empty($table) && substr($table,-1) != '.' )
        $table .= '.';

    $sql =<<<EOF
        IF( {$table}user_name = REPLACE({$table}user_real_name,' ','_'), 
            {$table}user_real_name, 
            CONCAT( {$table}user_real_name, ' (', {$table}user_name, ')' ) ) as {$colname}
EOF;
    
    return $sql;
}

function cc_get_content_page_type($pagename)
{
    $page_path = CCPage::GetViewFile($pagename);
    if( empty($page_path) )
    {
        // searching the page view path failed,
        // have some mercy and see if it's wandered
        // off to the template directories
        require_once('cchost_lib/cc-template.php');
        $skinmac = new CCSkinMacro($pagename);
        $props = $skinmac->GetProps();
    }
    else
    {
        require_once('cchost_lib/cc-file-props.php');
        $fp = new CCFileProps();
        $props = $fp->GetFileProps($page_path);
    }
    if( empty($props['topic_type']) )
        die("Content Page '{$page}' does not have 'topic_type' property");
    return $props['topic_type'];
}

function cc_add_content_paging_links(&$A,$type,$topic_slug,$ord,$page_slug,$limit=1)
{
    $slug_sql = cc_get_topic_name_slug();

    if( $topic_slug && ($limit==1) )
    {
        // get date of current topic
        $sql =<<<EOF
            SELECT topic_date FROM cc_tbl_topics WHERE topic_type = '{$type}' AND  {$slug_sql} = '{$topic_slug}'
EOF;
        $date = CCDatabase::QueryItem($sql);
        $ord = strtolower($ord);
        if( $ord == 'asc' )
        {
            $prev_op = '<';
            $next_op = '>';
            $prev_ord = 'desc';
            $next_ord = 'asc';
        }
        else
        {
            $prev_op = '>';
            $next_op = '<';
            $prev_ord = 'asc';
            $next_ord = 'desc';
        }

        // query to get prev
        $sql =<<<EOF
            SELECT topic_name, {$slug_sql} FROM cc_tbl_topics 
            WHERE topic_type = '{$type}' AND topic_date {$prev_op} '{$date}' 
            ORDER BY topic_date {$prev_ord}
            LIMIT 1
EOF;
        _make_topic_np_link( $A, array('prev_link','back_text'), $page_slug,'&lt;&lt;&lt; %s',$sql);

        // query to get next
        $sql =<<<EOF
            SELECT topic_name, {$slug_sql} FROM cc_tbl_topics 
            WHERE topic_type = '{$type}' AND topic_date {$next_op} '{$date}' 
            ORDER BY topic_date {$next_ord}
            LIMIT 1
EOF;
        _make_topic_np_link( $A, array('next_link','more_text'), $page_slug,'%s &gt;&gt;&gt;',$sql);

    }
    else
    {
        if( $limit == 1 )
        {
            // query to get second topic
            $sql =<<<EOF
                SELECT topic_name, {$slug_sql} 
                FROM cc_tbl_topics 
                WHERE topic_type = '{$type}' 
                ORDER BY topic_date {$ord} 
                LIMIT 1 OFFSET 1
EOF;
            _make_topic_np_link( $A, array('next_link','more_text'), $page_slug,'%s &gt;&gt;&gt;',$sql);
        }
        else
        {
            $table = new CCTable('cc_tbl_topics','topic_id');
            $sql_where = "topic_type = '{$type}'";
            $page =& CCPage::GetPage();
            $page->AddPagingLinks($table,$sql_where,$limit);
        }
    }
}

function _make_topic_np_link(&$A,$keys,$page_slug,$sptext,$sql)
{
    list( $name, $slug) = CCDatabase::QueryRow($sql,false);

    if( !empty($slug) )
    {
        $A[$keys[0]] = url_args( ccl( $page_slug ), 'topic='.$slug);
        $A[$keys[1]] = cc_strchop(sprintf($sptext,$name),40);
    }
}

function cc_get_topic_tranlations($topic_id)
{
    $xlat_rows = CCDatabase::QueryRows('SELECT * FROM cc_tbl_topic_i18n WHERE topic_i18n_topic = '.$topic_id);
    return $xlat_rows;
}

?>
