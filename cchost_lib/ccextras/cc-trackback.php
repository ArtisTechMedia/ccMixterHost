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
* $Id: cc-trackback.php 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_MAP_URLS, array( 'CCTrackback', 'OnMapUrls') );

class CCTrackBack
{
    function Track($type,$upload_id)
    {
        require_once('cchost_lib/cc-pools.php');
        require_once('cchost_lib/cc-pools-ui.php');
        $link = $this->_clean_url($_POST['trackback_link']);

        // http://www.youtube.com/watch?v=9NE3NPytch4

        $xx = CCDatabase::QueryRow("SELECT pool_item_id,pool_item_extra,pool_item_approved FROM cc_tbl_pool_item " . 
                                    "WHERE pool_item_url = '$link'",false);

        list( $pool_item_id, $existing_extra, $pre_approved ) = $xx;

        $pool_items = new CCPoolItems();
        if( empty($pool_item_id) )
        {
            $pool_id        = CCPoolUI::GetWebSamplePool();
            $upload_license = CCDatabase::QueryItem('SELECT upload_license FROM cc_tbl_uploads WHERE upload_id='.$upload_id);
            $email          = CCUTil::Strip($_POST['trackback_email']);
            $name           = CCUTil::Strip($_POST['trackback_your_name']);
            if( empty($name) )
                $name = $email;

            $a['pool_item_id']             = $pool_item_id = $pool_items->NextID();
            $a['pool_item_pool']           = $pool_id;
            $a['pool_item_url']            = $link;
            $a['pool_item_download_url']   = '';
            $a['pool_item_description']    = '';
            $a['pool_item_license']        = $upload_license;
            $a['pool_item_name']           = $this->_get_item_name(CCUtil::Strip($_POST['trackback_name']),$link);
            $a['pool_item_artist']         = $this->_get_item_user(CCUtil::Strip($_POST['trackback_artist']),$link);
            $a['pool_item_approved']       = 0;
            $a['pool_item_timestamp']      = time();
            $a['pool_item_num_remixes']    = 0;
            $a['pool_item_num_sources']    = 0;
            $a['pool_item_extra']          = serialize( array( 'ttype'     => $type, 
                                                               'embed'     => CCUtil::StripSlash($_POST['trackback_media']),
                                                               'poster'    => $name,
                                                               'email'     => $email,
                                                               'upload_id' => $upload_id,
                                                        ) );

            $pool_items->Insert($a);
        }
        else
        {
            if( $pre_approved )
            {
                CCPoolUI::ApproveTrackback($pool_item_id,$upload_id);
            }
            else
            {
                $existing_extra = unserialize($existing_extra);
                $existing_extra['upload_id'] .= ',' . $upload_id;
                $up['pool_item_extra'] = serialize($existing_extra);
                $up['pool_item_id'] = $pool_item_id;
                $pool_items->Update($up);
            }
        }
        print 'ok';
        exit;
    }

    function _get_item_name($name,$link)
    {
        if( !empty($name) )
            return $name;
        require_once('cchost_lib/snoopy/Snoopy.class.php');
        $snoopy = new Snoopy();
        @$snoopy->fetch($link);
        if( !empty($snoopy->error) )
        {
            $text1 = _('There was an error trying to validate the web address. Test it in your %sbrowser%s to make sure.');
            $text1 = sprintf($text1,"<a target=\"_blank\" href=\"$link\">",'</a>');
            $text2 = _('Gorey details...');
            $msg =<<<EOF
{$text1} 
(<a href="javascript://show error" onclick="$('err_details').style.display='block';return false">{$text2}</a>
<div style="display:none" id="err_details">{$snoopy->error}</div>
EOF;
            $this->_error_out($msg);
        }
        if( preg_match( '/<meta name="title" content="([^"]+)">/U',$snoopy->results,$m ) )
            return $m[1];
        if( preg_match( '#<title>([^<]+)</title>#',$snoopy->results,$m) )
            return $m[1];
        if( preg_match( '#/([^/]+)$#',$link,$m) )
            return $m[1];
        return substr( str_replace('http://','',$link), 0, 20 );
    }

    function _get_item_user($user,$link)
    {
        if( !empty($user) )
            return $user;
        $purl = parse_url($link);
        return str_replace('www.','',$purl['host']);
    }


    function _clean_url($text)
    {
        if( substr($text,0,7) != 'http://' )
            $text = 'http://' . $text;
        return trim($text);
    }

    function _error_out($msg)
    {
        print $msg;
        exit;
    }

    function OnMapUrls()
    {
        // ajax call (I think)
        CCEvents::MapUrl( ccp('track'),  array('CCTrackBack', 'Track'),
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{type}/{upload_id}', _('Add a trackback (ajax call)'),
            CC_AG_SAMPLE_POOL );
    }

}
?>
