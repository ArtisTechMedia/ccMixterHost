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
* $Id: cc-api.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* Implementation of the ccHost RESTful API (e.g. Sample Pool API)
*
* @package cchost
* @subpackage api
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
require_once('cchost_lib/cc-feedreader.php');


/**
* @package cchost
* @subpackage api
*/
class CCRestAPI
{
    function MakeUrl( $base, $cmd, $args = '' )
    {
        $url = CCUtil::CheckTrailingSlash($base,true);
        if( !preg_match("#$cmd?$#", $url ) )
            $url .= $cmd;
        if( !empty($args) )
        {
            if( strchr($url,'?') === false )
                $args = '?' . $args;
            else
                $args = '&' . $args;
        }
        return( $url . $args );
    }

    function Info()
    {
        require_once('cchost_lib/cc-query.php');
        $queryapi = new CCQuery();
        $args['ids'] = 1; // well, actually any invalid upload_id
        if( empty($_REQUEST['format']) )
            $args['format'] = 'rss';
        $args = $queryapi->ProcessUriArgs($args);
        $queryapi->Query($args);
    }

    function Search()
    {
        if( empty($_POST) )
        {
            if( !empty($_GET) )
                $req =& $_GET;
            else
                $req = array();
        }
        else
        {
            $req =& $_POST;
        }
        if( empty( $req['query'] ) && empty( $req['q'] ) )
            $this->Info();

        if( empty($req['format']) && empty($req['f']) )
            $req['format'] = 'rss';

        if( !empty($req['type']) )
        {
             $req['search_type'] = $req['type'];
             $req['type'] = '';
        }
        
        require_once('cchost_lib/cc-query.php');
                
        $queryapi = new CCQuery();
        $queryapi->QueryURL();
    }

    function _get_upload_id_from_guid($guid)
    {
        if( intval($guid) > 0 )
            return $guid;

        if( is_string($guid) )
        {
            $guid = urldecode(CCUtil::StripText($guid));
            if( preg_match( '#/([0-9]*)$#', $guid, $m ) )
            {
                return($m[1]);
            }
        }
        
        return null;
    }

    function File($guid='')
    {
        if( empty($guid) )
        {
            if( empty($_REQUEST['guid']) )
                CCUtil::Send404(true,"missing guid");
            $guid = urldecode($_REQUEST['guid']);
        }

        $upload_id = CCRestAPI::_get_upload_id_from_guid($guid);
        require_once('cchost_lib/cc-query.php');
        $queryapi = new CCQuery();
        $args['ids'] = $upload_id;
        if( empty($_REQUEST['format']) )
            $args['format'] = 'rss';
        $args = $queryapi->ProcessUriArgs($args);
        $queryapi->Query($args);
    }

    //
    // 1. guid=[song_page_url]
    // 2. remixid=[remixguid] 
    // 3. poolsite=[poolsite_api_url]
    //    
    //
    function UBeenRemixed()
    {
        
        if( empty($_REQUEST['guid']) ||
            empty($_REQUEST['remixguid']) ||
            empty($_REQUEST['poolsite']) )
        {
            CCUtil::Send404(true,"missing arguments");    
        }
            
        
        global $CC_GLOBALS;

        require_once('cchost_lib/cc-pools.php');
        require_once('cchost_lib/cc-feedreader.php');
        
        $guid        = $_REQUEST['guid'];
        $remixguid   = $_REQUEST['remixguid'];
        $poolsiteurl = $_REQUEST['poolsite'];

        $upload_id   = $this->_get_upload_id_from_guid($guid);
        if( empty($upload_id) )
        {
            $this->error_exit("Missing or invalid parameter: guid=$guid");
        }
        $remixguid   = urldecode(CCUtil::StripText($remixguid));
        if( empty($remixguid) )
        {
            $this->error_exit("Missing or invalid parameter: (remixguid=$remixguid)");
        }
        $poolsiteurl = urldecode(CCUtil::StripText($poolsiteurl));
        if( empty($poolsiteurl) )
        {
            $this->error_exit("Missing or invalid parameter: (poolsiteurl=$poolsiteurl)");
        }

        $uploads =& CCUploads::GetTable();
        $uploadargs['upload_id'] = $upload_id;

        if( $uploads->CountRows($uploadargs) != 1 )
        {
            $this->error_exit("That source identifier is not valid (it might have been deleted by original artist).");
        }

        // Check for spam throttle
        $ip = CCUtil::EncodeIP( $_SERVER['REMOTE_ADDR'] );
        $where = "(pool_ip = '$ip' OR pool_api_url = '$poolsiteurl')";

        $pools =& CCPools::GetTable();
        $matching_pools = $pools->QueryRows($where);
        $pool_items =& CCPoolItems::GetTable();
        $total_unapproved_entries = 0;
        $count = count($matching_pools);
        for( $i = 0; $i < $count; $i++ )
        {
            $where = array();
            $where['pool_item_pool'] = $matching_pools[$i]['pool_id'];
            $where['pool_item_approved']   = '0';
            $total_unapproved_entries += $pool_items->CountRows($where);
        }

        if( $total_unapproved_entries >= $CC_GLOBALS['pool-remix-throttle'] )
        {
            $this->error_exit("Maximum remix limit reached.");
        }

        // poolsite not in pools table? add it
        //
        $where = array();
        $where['pool_api_url'] = $poolsiteurl;
        $pool = $pools->QueryRow($where);
        if( empty($pool) )
        {
            $pool = CCPool::AddPool($poolsiteurl);
        }

        if( is_string($pool) )
        {
            $this->error_exit('Could not verify calling remix site: '. $pool);
        }

        $guid_url = $this->MakeUrl( $poolsiteurl, 'file', 'guid=' . urlencode($remixguid) );

        //$rss = CCFeeds::ReadFeed( $guid_url );

        $fr = new CCFeedReader();
        $rss = $fr->cc_parse_url($guid_url);

        if( !empty($rss->ERROR) )
        {
            $this->error_exit("Could not retrieve remix information: {$rss->ERROR}");
        }

        if( empty($rss->items) )
        {
            $this->error_exit("Remix information was not returned on request");
        }

        // remember where this pool is calling from in case we need to throttle
    
        $poolargs['pool_id'] = $pool['pool_id'];
        $poolargs['pool_ip'] = $ip;
        $pools->Update($poolargs);

        $item =& $rss->items[0];

        if( empty($item['link']) )
        {
            $this->error_exit( "Missing remix link from return information" );
        }

        $link = $item['link'];

        $pool_items =& CCPoolItems::GetTable();

        $workswhere['pool_item_url'] = $link;
        $pool_item = $pool_items->QueryRow($workswhere);

        if( empty($pool_item) )
        {
            $pool_item = CCPool::AddItemtoPool( $pool, $item );
            if( is_string($pool_item) )
                $this->error_exit($pool_item);
        }

        $pool_tree = new CCTable('cc_tbl_pool_tree','pool_tree_parent');
        $remtreeargs['pool_tree_parent'] = $upload_id;
        $remtreeargs['pool_tree_pool_child'] = $pool_item['pool_item_id'];
        $pool_tree->Insert($remtreeargs);

        $upload_pool_count = $uploads->QueryItemFromKey( 'upload_num_pool_remixes', $upload_id );
        $sync_args['upload_id'] = $upload_id;
        $sync_args['upload_num_pool_remixes'] = intval($upload_pool_count) + 1;
        $uploads->Update($sync_args);

        $this->success_exit();
    }

    function success_exit($status='ok', $msg = 'operation succeeded')
    {
        header('Content-type: text/xml');
        print('<?xml version="1.0" encoding="utf-8" ?>' . "\n<results><status>$status</status><detail>$msg</detail></results>");
        exit;
    }

    function error_exit($msg='')
    {
        $this->success_exit('error',$msg);
    }

    function PoolRegister()
    {
        global $CC_GLOBALS;

        if( empty($CC_GLOBALS['allow-pool-register']) )
            $this->error_exit("remote registration not allowed at this site");

        $pool_api_url = urldecode( $_REQUEST['poolsite'] );
        if( empty($pool_api_url) )
            $this->error_exit("missing parameter: poolsite");

        $pools =& CCPools::GetTable();
        $where['pool_api_url'] = $pool_api_url;
        if( $pools->CountRows($where) == 0 )
        {
            $api = new CCPool();
            $pool = $api->AddPool($pool_api_url);
            if( is_string($pool) )
                $this->error_exit("Error adding pool: $pool_api_url");
        }

        $this->success_exit();
    }

    function ISampledThis($works_page)
    {
        
    }

    function Version()
    {
        header("Content-type: text/plain");
        print '2.0';
        exit;
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        // compat mappings
        CCEvents::MapUrl( ccp('api','info'),                 array( 'CCRestAPI', 'Info'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', '(alias for pool/api/info)', CC_AG_deprecated); 
        CCEvents::MapUrl( ccp('api','search'),               array( 'CCRestAPI', 'Search'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', '(alias for pool/api/search)', CC_AG_deprecated); 
        CCEvents::MapUrl( ccp('api','file'),                 array( 'CCRestAPI', 'File'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', '(alias for pool/api/file)', CC_AG_deprecated); 
        CCEvents::MapUrl( ccp('api','ubeensampled'),         array( 'CCRestAPI', 'UBeenRemixed'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', '(alias for pool/api/ubeensampled)', CC_AG_deprecated); 

        // happy new mappings
        CCEvents::MapUrl( ccp('api','pool','info'),                 array( 'CCRestAPI', 'Info'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Display header info for site'), CC_AG_SAMPLE_POOL ); 
        CCEvents::MapUrl( ccp('api','pool','search'),               array( 'CCRestAPI', 'Search'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Search pool for sources'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp('api','pool','file'),                 array( 'CCRestAPI', 'File'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '[guid]', _('Display info for file'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp('api','pool','ubeensampled'),         array( 'CCRestAPI', 'UBeenRemixed'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '',_('Notification this pool has been sampled remotely'), CC_AG_SAMPLE_POOL );

        CCEvents::MapUrl( ccp('api','poolregister'),         array( 'CCRestAPI', 'PoolRegister'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '?poolsite', _('Remote pool site registration'), CC_AG_SAMPLE_POOL );
        CCEvents::MapUrl( ccp('api','version'),         array( 'CCRestAPI', 'Version'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '', _('Display version info for site'), CC_AG_SAMPLE_POOL );
    }
    
}



?>
