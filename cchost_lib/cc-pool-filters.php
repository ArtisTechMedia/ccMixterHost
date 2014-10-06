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
* $Id: cc-pool-filters.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* High volume (called often) routings for pool stuff
*
* @package cchost
* @subpackage admin
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to ccHost');

function cc_filter_pool_items(&$records,&$dataview_info)
{
    $k = array_keys($records);
    $c = count($k);
    for( $i = 0; $i < $c; $i++ )
    {
        $R =& $records[$k[$i]];
        if( isset($R['pool_item_extra']) && is_string($R['pool_item_extra']) )
            $R['pool_item_extra'] = unserialize($R['pool_item_extra']);
    }
}

?>
