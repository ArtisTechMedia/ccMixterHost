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
* $Id: cc-archive.inc 8961 2008-02-11 22:17:33Z fourstones $
*
*/

/**
* @package cchost
* @subpackage feature
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-page.php');
require_once('cchost_lib/cc-upload.php');

/**
*
*
*/
class CCArchive
{
    function Archive($type='',$arg1='',$tags='')
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        
        $trail = array();
        $trail[] = array( 'url' => ccl(), 'text' => _('Home') );
        $trail[] = array( 'url' => ccl('archive'), 'text' => _('Archive') );
        if( $type == 'month' )
        {
            preg_match( '/^(2[0-9]{3}-(?:01|02|03|04|05|06|07|08|09|10|11|12))$/',$arg1,$m);
            if( !empty($m[1]) )
            {
                $time = strtotime($m[1] . '-01');
                $tstr = date('F Y',$time);
                $trail[] = array( 'url' => ccl('archive','month',$arg1), 'text' => $arg1 );
                if( !empty($tags) )
                    $trail[] = array( 'url' => ccl('archive','month',$arg1,$tags), 'text' => $tags);
                $page->AddBreadCrumbs($trail);

                $page->SetTitle( 'str_archive_for_s', $tstr );
                $month = $m[1];
                require_once('cchost_lib/cc-query.php');
                $query = new CCQuery();
                $args = $query->ProcessAdminArgs(array());
                $sqlargs['where'] = "SUBSTRING(upload_date,1,7) = '${m[1]}'";
                $query->QuerySQL($args,$sqlargs);
                return;
            }
        }

        $page->AddBreadCrumbs($trail);
        $base_url = ccl('archive','month');
        $sql =<<<END
            SELECT DATE_FORMAT( upload_date, '%M %Y' ) as menu_text,
                   CONCAT('{$base_url}/', SUBSTRING( upload_date, 1, 7 ) ) as action,
                   CONCAT( '(', COUNT(*), ')' ) as `help`,
                   SUBSTRING( upload_date, 1, 7 ) as mo
            FROM cc_tbl_uploads
            GROUP BY mo
            ORDER BY mo DESC 
END;
        $args = CCDatabase::QueryRows($sql);
        $page->SetTitle( 'str_archive_monthly' );
        $page->PageArg('client_menu',$args,'print_client_menu');
    }


    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('archive'), array('CCArchive','Archive'), 
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), 
                          '[month/{YYYY-MM}][/tags])', 
                          _('Display upload archive for a specific month'),
                          CC_AG_UPLOADS );
    }

}



?>
