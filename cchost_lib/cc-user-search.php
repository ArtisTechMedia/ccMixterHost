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
* $Id: cc-user-search.php 12466 2009-04-29 05:08:38Z fourstones $
*
*/

/**
* @package cchost
* @subpackage user
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

class CCUserSearch
{
    function OnUserSearch($field='',$tag='')
    {
        $field = CCUtil::StripText($field);
        if( empty($field) )
            CCUtil::Send404();
            
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();

        switch( $field )
        {
            case 'lookinfor':
            {
                $this->_lookin_for($tag);
                break;
            }
            case 'whatido':
            case 'whatilike':
            {
                $field = 'user_' . $field;
                $page->SetTitle('str_search_users_that', $tag);
                require_once('cchost_lib/cc-query.php');
                $query = new CCQuery();
                $sqlargs['where'] = "CONCAT($field,',') LIKE '%$tag,%'";
                $args = $query->ProcessAdminArgs('t=user_match');
                $query->QuerySQL($args,$sqlargs);
            }
        }
    }

    function _lookin_for($tag)
    {
        $page =& CCPage::GetPage();
        $page->SetTitle('str_search_wipo');

        $org_tag = $tag;
        $tag = strtolower($tag);
        $users = new CCUsers();
        $where = "(LOWER(user_whatido) REGEXP '(^| |,)($tag)(,|\$)' )";
        $count = $users->CountRows($where);
        $got_tag = $count > 0;
        $first_letter = $tag ? $tag{0} : '';
        $where = "user_whatido > ''";
        $users->SetSort('user_registered','DESC');
        $rows = $users->QueryRows($where,'user_name,user_real_name,LOWER(user_whatido) as wid');
        $whatidos = array();
        $base = ccl('people') . '/';
        foreach( $rows as $row )
        {
            $wids = cc_split(',',$row['wid']);
            unset($row['user_whatido']);
            foreach($wids as $wid)
                $whatidos[strtolower($wid)][] = 
                   "<a href=\"{$base}{$row['user_name']}\">{$row['user_real_name']}</a>";
        }

        ksort($whatidos);
        $wid_links = array();
        // TODO: This should really go into a stylesheet proper.
        $html =<<<EOF
<style type="text/css">
#wid_table td, #wid_table th {
  vertical-align: top;
}
#wid_table th {
  text-align: right;
  font-weight: normal;
  font-style: italic;
  padding-right: 4px;
}
</style>
<table id="wid_table">
EOF;
        $got_first_letter = false;
        $show_all = empty($_GET['filter']);
        foreach( $whatidos as $wid => $alinks )
        {
            if( !$show_all && (count($alinks) < 2) )
                continue;

            $html .= '<tr><th class="light_bg">' . $wid;
            if( ($got_tag && ($wid == $tag)) ||
                (!$got_tag && ($first_letter == $wid{0}) )
               )
            {
                $html .= '<a name="' . $org_tag . '" />';
            }
            $html .= '</th><td>' .
                        join(', ',$alinks) . '</td></tr>' . "\n";
        }

        $html .= '</table>';

        $page->AddContent($html);
    }
    
    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( 'search/people',  array('CCUserSearch','OnUserSearch'), 
            CC_DONT_CARE_LOGGED_IN, ccs(__FILE__), '{field}/{tags}', _("'field' is whatilike, whatido or lookinfo"), CC_AG_SEARCH );
    }
}
?>
