<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file COPYING.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-feed.php 12881 2009-07-08 03:34:45Z fourstones $
*
*/

/**
 * Utils for generating feeds 
 *
 * @package cchost
 * @subpackage api
 *
 */

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

function cc_feed_encode($str)
{
    $str = preg_replace('/&(?!amp;|#)/','&amp;',$str);
    return utf8_encode( preg_replace('`[^'."\n".'&a-zA-Z0-9()!@#$%^*-_=+\[\];:\'\"\\.,/?~ ]`','',$str ) );
}

function cc_feed_transcode($str)
{
    return preg_replace_callback('`[^&a-zA-Z0-9()!@#$%^*-_=+\[\];:\'\"\\.,/?~ ]`','_cc_feed_transcode',$str );
}

function _cc_feed_transcode($arr)
{
    return preg_replace('/[^&a-zA-Z0-9;]/','-',htmlentities($arr[0])); //  '-'; //  . ord($arr[0]);
}

function cc_feed_safe_urls($text)
{
    return preg_replace_callback("/(?:href|title|src)\s?=['\"][^'\"]+\?([^'\"]+)['\"]/U",'_cc_encode_feed_url', $text);
}

function _cc_encode_feed_url($m) 
{
    return str_replace($m[1],urlencode($m[1]),$m[0]);
}

function cc_feed_safe_html($text)
{
    $tr = array( ']' => '&#' . ord(']') . ';', 
                 '[' => '&#' . ord('[') . ';' );
    return cc_feed_encode(strtr( cc_feed_safe_urls($text), $tr ));
}

function cc_feed_description()
{
    $configs         =& CCConfigs::GetTable();
    $template_tags   = $configs->GetConfig('ttag');
    return cc_feed_encode($template_tags['site-description']);
}

function cc_feed_title($args,$skin)
{
    $configs         =& CCConfigs::GetTable();
    $template_tags   = $configs->GetConfig('ttag');
    return cc_feed_encode($template_tags['site-title']) . cc_feed_subtitle($args,$skin);
}

function cc_feed_subtitle($args,$skin)
{
    $subtitle = '';

    if( !empty($args['title']) )
    {
        $subtitle = $args['title'];
    }
    elseif( !empty($args['user']) )
    {
        $subtitle = CCDatabase::QueryItem('SELECT user_real_name FROM cc_tbl_user WHERE user_name = \'' . $args['user'] . '\'' );
    }
    elseif( !empty($args['remixesof']) )
    {
        $subtitle = $skin->String(array('str_remixes_of_s',$args['remixesof']) );
    }
    elseif( !empty($args['remixedby']) )
    {
        $subtitle = $skin->String(array('str_remixed_by_s',$args['remixedby'] ));
    }


    if( !empty($args['tags'] ) )
    {
        if( !empty($subtitle) )
            $subtitle .= ' - ';
        $subtitle .= $args['tags'];
    }

    if( !empty($subtitle) )
        return ' (' . cc_feed_encode($subtitle) . ')';

    return '';
}

function cc_feed_add_page_links(&$page,$feed_info,$icon,$version,$fmt,$id,$accepts=array('uploads'))
{
    $img = '<img src="' . ccd('ccskins','shared','images',$icon) . '" title="[ '.$version.' ]" />';
    if( $accepts && !in_array($feed_info['datasource'],$accepts) )
        return;
    $feed_url = url_args( ccl('api','query'), $feed_info['query'] . '&f=' . $fmt);
    $help = empty($feed_info['title']) ? '[ ' . $version . ' ]' : $feed_info['title'];
    $link_text = $img . ' ' . $help;
    if( empty($feed_info['id']) )
        $_id = $id;
    else
        $_id = $feed_info['id'];
    $page->AddLink( 'feed_links', 'alternate', 'application/atom+xml', $feed_url, $help . ' [ '. $version .' ]', $link_text, $_id);
}

/**
 * @package cchost
 * @subpackage api
 */
class CCFeed
{
    //---------------------------
    // EVENT HANDLERS 
    //---------------------------

    /**
     * Event hander for {@link CC_EVENT_DELETE_UPLOAD}
     *
     * @param array $record Upload database record
     */
    function OnUploadDelete($record)
    {
        $this->_clear_cache($record);
    }

    /**
     * Event hander to clear the feed cache
     *
     * @param integer $fileid Database ID of file
     */
    function OnFileDelete($fileid)
    {
        $this->_clear_cache($fileid);
    }

    /**
     * Event handler for {@link CC_EVENT_UPLOAD_DONE}
     * 
     * @param integer $upload_id ID of upload row
     * @param string $op One of {@link CC_UF_NEW_UPLOAD}, {@link CC_UF_FILE_REPLACE}, {@link CC_UF_FILE_ADD}, {@link CC_UF_PROPERTIES_EDIT'} 
     * @param array &$parents Array of remix sources
     */
    function OnUploadDone($upload_id,$op)
    {
        $this->_clear_cache($upload_id);
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
        if( $scope == CC_GLOBAL_SCOPE )
        {
            $fields['feed-cache-flag'] =
               array(  'label'      => _('Feed Caching'),
                       'form_tip'   =>
                          _('Feed caching can optimize replies for feed requests'),
                       'value'      => '',
                       'formatter'  => 'checkbox',
                       'flags'      => CCFF_POPULATE);
            $fields['max-feed'] =
               array(  'label'      => _('Number of feed items'),
                       'form_tip'   =>
                          _('Number of items to list in feeds (recommended: 15)'),
                       'value'      => '',
                       'class'      => 'cc_form_input_short',
                       'formatter'  => 'textedit',
                       'flags'      => CCFF_POPULATE);
        }
    }

    /**
     * Internal: Checks to see if the cache is on or off.
     *
     * @returns boolean <code>true</code> if on or <code>false</code> otherwise
     */
    function _is_caching_on()
    {
        global $CC_GLOBALS;

        return !empty($CC_GLOBALS['feed-cache-flag']) ;
    }

    /**
     * Internal: Cleans out the feed cache
     *
     * @param mixed $record_or_id Database record of ID of changed file (unused)
     */
    function _clear_cache($record_or_id)
    {
        if( $this->_is_caching_on() )
        {
            $cache = new CCTable('cc_tbl_feedcache','feedcache_id');
            $cache->DeleteWhere('1');
        }
    }

    /**
     * Internal: Cache a generic feed into the database
     *
     * @param string $xml Actual feed text
     * @param string $type Feed format
     * @param string $tagstr Tags represented by this feed.
     */
    function _cache(&$xml,$type,$tagstr)
    {
        if( $this->_is_caching_on() )
        {
            $args['feedcache_type'] = $type;
            $args['feedcache_tags'] = $tagstr;
            $args['feedcache_text'] = $xml;
            $cache = new CCTable('cc_tbl_feedcache','feedcache_id');
            $cache->Insert($args);
        }
    }

    /**
     * Internal: check the cache for a given type of feed for specific query
     *
     * @param string $type Feed format
     * @param string $tagstr Tag query
     */
    function _check_cache($type,$tagstr)
    {
        if( $this->_is_caching_on() )
        {
            $where['feedcache_type'] = $type;
            $where['feedcache_tags'] = $tagstr;
            $cache = new CCTable('cc_tbl_feedcache','feedcache_id');
            $row = $cache->QueryRow($where);

            if( !empty($row) )
            {
                header("Content-type: text/xml");
                print($row['feedcache_text']);
                exit;
            }
        }
    }
} 


?>
