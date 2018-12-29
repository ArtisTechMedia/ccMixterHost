<?
/*
  $Id: opt-in-report.php 13835 2009-12-25 13:19:34Z fourstones $
*/


CCEvents::AddHandler( CC_EVENT_MAP_URLS, 'opt_in_report_url_map' );
//CCEvents::AddHandler( CC_EVENT_APP_INIT, 'opt_in_app_init' );



function opt_in_report_url_map()
{
    CCEvents::MapUrl( ccp('admin','opt-in'), 'opt_in_report', CC_ADMIN_ONLY );
}

function opt_in_report()
{
    require_once('cchost_lib/cc-page.php');
    $page =& CCPage::GetPage();
    $page->SetTitle('Opt-in Report');
    $names = array();
    $qr = CCDatabase::Query('SELECT user_id,user_extra,user_name FROM cc_tbl_user WHERE user_extra > \'\' order by user_name');
    while( $row = mysql_fetch_assoc($qr) )
    {
        $ex = unserialize($row['user_extra']);
        if( !empty($ex[CC_OPT_IN_FLAG]))
        {
            $names[] = $row['user_name'];
        }
    }
    
    $html = '<p>Total opt-ins: <b>' . count($names) . '</b></p><style>#optable td { padding: 3px; }</style>';
    $rows = array_chunk($names,8);
    $html .= '<table id="optable">';
    foreach( $rows as $R )
    {
        $html .= '<tr>';
        foreach( $R as $N )
            $html .= '<td>' . $N. '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    $page->AddContent($html);
}
    