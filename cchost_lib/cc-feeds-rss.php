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
* $Id: cc-feeds-rss.php 12881 2009-07-08 03:34:45Z fourstones $
*
*/

/**
* RSS Module feed generator
*
* @package cchost
* @subpackage api
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-feed.php');

/**
* RSS Feed generator 
*/
class CCFeedsRSS 
{
    function OnApiQuerySetup( &$args, &$queryObj, $validate)
    {
        $f = $args['format'];

        if( ($f == 'rss') || ($args['limit'] == 'feed') )
            $queryObj->ValidateLimit('max-feed');

        if( $f != 'rss' )
            return;

        if( empty($args['datasource']) )
            $args['datasource'] = 'uploads';
        switch( $args['datasource'] )
        {
            case 'topics':
                $args['template'] = 'rss_20_topics.php' ;
                break;
            case 'pool_items':
                $args['template'] = 'rss_20_pool_items.php';
                break;
            case 'uploads': // fallthru
            default:
                $args['template'] = 'rss_20.php';
                break;                
        }
        $queryObj->GetSourcesFromTemplate($args['template']);
        $queryObj->ValidateLimit('max-feed');
    }

    function OnApiQueryFormat( &$records, $args, &$result, &$result_mime )
    {
        if( $args['format'] != 'rss' )
            return;

        global $CC_GLOBALS;

        $skin = new CCSkinMacro($args['template'],false);

        $targs['channel_title'] = cc_feed_title($args,$skin);
        $targs['home-url'] = htmlentities(ccl());
        $targs['channel_description'] = cc_feed_description();
        $targs['lang_xml'] = $CC_GLOBALS['lang_xml'];
        $targs['rss-pub-date'] = 
        $targs['rss-build-date'] = CCUtil::FormatDate(CC_RFC822_FORMAT,time());
        $targs['feed_url'] = htmlentities(cc_current_url());

        $k = array_keys($records);
        $c = count($k);
        $is_topics = $args['datasource'] == 'topics';
        $is_pool_items = $args['datasource'] == 'pool_items';
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $records[$k[$i]];
            if( $is_pool_items )
            {
                $R['pool_item_name']   = cc_feed_encode($R['pool_item_name']);                                
                $R['pool_item_artist'] = cc_feed_encode($R['pool_item_artist']);                                
                $desc_tag =<<<EOF
  
  <br />
  <br />From the <a href="{$R['pool_url']}">{$R['pool_name']}</a>  Sample Pool.
  <br />Licensed under a <a href="{$R['license_url']}">{$R['license_name']}</a>
EOF;
                $R['pool_item_description_plain'] = htmlspecialchars($R['pool_item_description'] . $desc_tag);
                $R['pool_item_description'] = cc_feed_encode($R['pool_item_description'] . $desc_tag );
            }
            else
            {
                $R['user_real_name'] = cc_feed_encode($R['user_real_name']);
                if( $is_topics )
                {
                    // we're going to experiment with turnin this off since all this text 
                    // will be displayed within CDATA blocks which freely allow angle
                    // brackets and ampersands. What other characters may blow up this
                    // section I'm not sure
                    //
                    //$R['topic_text_html']   = cc_feed_safe_html($R['topic_text_html']) ;

                    $R['topic_text_plain']  = cc_feed_encode($R['topic_text_plain']);
                    $R['topic_name']        = cc_feed_encode($R['topic_name']);
                    $this->_check_for_enclosure($R);
                }
                else
                {
                    // see note above
                    //
                    //$R['upload_description_html']  = cc_feed_safe_html($R['upload_description_html']) ;

                    $R['upload_description_plain'] = cc_feed_encode($R['upload_description_plain']);
                    $R['upload_name']              = cc_feed_encode($R['upload_name']);
                    $R['user_avatar_url']          = str_replace(' ','%20',$R['user_avatar_url']); // required by validation
                }
            }
        }

        if( $is_topics )
        {
            // yes, yes, this should be an admin option

            $targs['topics_license_url'] = 'http://creativecommons.org/licenses/by/2.5';
        }

        $targs['records'] =& $records;

        require_once('cchost_lib/cc-template.php');
        header("Content-type: text/xml; charset=" . CC_ENCODING); 
        $skin->SetAllAndPrint($targs,false);
        exit;
    }

    function _check_for_enclosure(&$R)
    {
        if( strpos($R['topic_text_html'],'enclosure_url') !== false )
        {
            preg_match_all('/enclosure_(url|size|type|duration)%([^%]+)%/U',$R['topic_text_html'],$m);
            for( $n = 0; $n < 4; $n++ )
            {
                if( !empty($m[1][$n]) )
                    $R['enclosure_' . $m[1][$n]] = $m[2][$n];
            }
        }
    }
    
    function OnAddPageFeed(&$page,$feed_info)
    {
        cc_feed_add_page_links($page,$feed_info,'feed-icon16x16.png','RSS 2.0','rss','feed_rss',array('uploads','topics'));
    }

}


?>
