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
* $Id: cc-tags.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/
 
/**
* @package cchost
* @subpackage folksonomy
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
 * Helper API and system event watcher for attribute tags
 *
 *  Just to confuse things there are two types of 'tags':
 *
 *   <ul><li>ID3 tags that are read from and stamped into and from files (like MP3s)
 *         This class has nothing to do with that</li>
 *     
 *        <li>Tags as in del.io.us and flikr where an item is catalogued
 *         according to some attributes. The attributes are searchable
 *         across the system to find 'like' items. This class is for
 *         facilitating this kind of 'tag'.</li>
 *   </ul>
*/
class CCTag
{
    function TagSplit($tagstr)
    {
        if( empty($tagstr) )
            return( array() );
        if( is_array($tagstr) )
            return $tagstr;
        $ok = preg_match_all( "/(?:(^|\W))(-?\w+)/", $tagstr, $m); 
        if( $ok )
            return array_filter($m[2]);
        return array();
    }

    function InTag($needles,$haystack)
    {
        if( is_array($haystack) )
            $haystack = implode(', ',$haystack);

        $needles = preg_replace('/, ?/','|',$needles);

        $regex =  "/(^| |,)($needles)(,|\$)/";

        return( preg_match( $regex, $haystack ) );
    }

    function ExpandOnRow(&$row,$inkey,$baseurl,$outkey,$label='',$usehash=false)
    {
        CCTag::ExpandOnRowA($row,$inkey,$baseurl,$outkey,$label, $row, $usehash );
    }

    function ExpandOnRowA(&$row,$inkey,$baseurl,$outkey,$label, &$outrow, $usehash )
    {
        if( empty($row[$inkey]) )
            return;
        if( empty($outrow) )
            $outrow =& $row;

        $tagstr = $row[$inkey];
        $tags = CCTag::TagSplit($tagstr);
        if( !empty($label) )
        {
            $count = empty($outrow[$outkey]) ? '0' : count($outrow[$outkey]);
            $outsubkey = $outkey . $count;
            $outrow[$outkey][$outsubkey]['label'] = $label;
        }
        foreach($tags as $tag)
        {
            $taglink = array( 'tagurl' => $baseurl . '/' . $tag,
                              'tag'    => $tag );

            if( $usehash )
                $taglink['tagurl'] .= '#' . $tag;

            if( empty($label) )
            {
                $outrow[$outkey][] = $taglink;
            }
            else
            {
                $outrow[$outkey][$outsubkey]['value'][] = $taglink;
            }
        }
    }

    /**
    * Event handler for {@link CC_EVENT_SOURCES_CHANGED}
    * 
    * @param integer $upload_id ID of upload row
    * @param array &$src_uploads Array of remix sources
    */
    function OnSourcesChanged($upload_id, &$src_uploads )
    {
        global $CC_GLOBALS;
        
        if( empty($CC_GLOBALS['tags-inherit']) )
            return;
        
        $inherit_tags = $this->TagSplit($CC_GLOBALS['tags-inherit']);
        if( empty($inherit_tags) )
            return;

        $intersect = array();

        $n = count($src_uploads);
        for( $i = 0; $i < $n; $i++ )
        {
            $tags = $this->TagSplit($src_uploads[$i]['upload_tags']);
            $intersect = array_merge($intersect, array_intersect($tags,$inherit_tags) );
        }

        require_once('cchost_lib/cc-uploadapi.php');
        if( empty($intersect) )
        {
            // just incase this upload HAD the itags, 
            // this call would remove them...
            CCUploadAPI::UpdateCCUD($upload_id,'',$inherit_tags);
        }
        else
        {
            CCUploadAPI::UpdateCCUD($upload_id,$intersect,'');
        }
    }
}


?>
