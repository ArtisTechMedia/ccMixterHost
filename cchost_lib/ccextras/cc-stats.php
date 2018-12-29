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
* $Id: cc-stats.php 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
*/
CCEvents::AddHandler(CC_EVENT_FILTER_USER_PROFILE,       'cc_stats_on_user_row' );
 
function cc_stats_on_user_row(&$rows)
{
    $row =& $rows[0];

    $username = $row['user_real_name'];

    $num_remixes = $row['user_num_remixes'];
    $num_remixed = $row['user_num_remixed'];

    // todo: not sure how to mangle the string system to work the artist name into the query title
    //$title = urlencode(sprintf(_('Remixes of %s'),$row['user_real_name']));
    // here's a hack
    require_once('cchost_lib/cc-page.php');
    $page = CCPage::GetPage();
    $title = urlencode($page->String(array('str_remixes_of_s',$row['user_real_name'])));

    $rurl = url_args( ccl('api','query'), 'remixesof=' . $row['user_name'] . '&title=' . $title );
    $linka = "<a href=\"$rurl\">";
    $linkb = '</a>';

    if( empty( $num_remixes ) )
    {
        if( empty( $num_remixed ) )
        {
            // _('%s has no remixes and has not been remixed')
            $text = array( 'str_remix_stats_1', $username );
        }
        else
        {
            //'%s has no remixes and has been remixed %s%d time%s.'
            // '%s has no remixes and has been remixed %s%d times%s.'
            $fmt = $num_remixed == 1 ? 'str_remix_stats_2' : 'str_remix_stats_3';
            $text = array( $fmt, $username, $linka, $num_remixed, $linkb );
        }
    }
    else
    {
        if( empty($num_remixed) )
        {
            // '%s has %d remix and has not been remixed'
            // '%s has %d remixes and has not been remixed'
            $fmt = $num_remixes == 1 ? 'str_remix_stats_4' : 'str_remix_stats_5';
            $text = array( $fmt, $username, $num_remixes );
        }
        else
        {
            if( $num_remixes == 1 )
            {
                if( $num_remixed == 1 )
                {
                    //$fmt = _('%s has one remix and has been %sremixed once%s.');
                    $text = array( 'str_remix_stats_6', $username, $linka, $linkb );
                }
                else
                {
                    //$fmt = _('%s has 1 remix and has been remixed %s%d times%s.');
                    $text = array(  'str_remix_stats_7', $username, $linka, $num_remixed, $linkb );
                }
 
            }
            else
            {
                if( $num_remixed == 1 )
                {
                    //$fmt = _( '%s has %d remixes and has been %sremixed once%s.' );
                    $text = array(  'str_remix_stats_8', $username, $num_remixes, $linka, $linkb );
                }
                else
                {
                    //$fmt = _('%s has %d remixes and has been remixed %s%d times%s.');
                    $text = array( 'str_remix_stats_9', $username, $num_remixes, $linka, $num_remixed, $linkb );
                }
            }
        }

    }


    $row['user_fields'][] = array( 'label' => 'str_stats',
                                   'value' => $text,
                                   'id'    => 'user_num_remixes' );
}

?>
