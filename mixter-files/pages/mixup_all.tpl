<!--
%%
[meta]
    desc           = _('Dump of mixups')
    type           = template component
    dataview       = mixups_all
    datasource     = mixups
    embedded       = 1
    breadcrumbs   = home,url(mixup Secret Mixups),page_title
[/meta]
[dataview]
function mixups_all_dataview()
{

    $urlm = ccl('mixup') . '/';

    $sql =<<<EOF
      SELECT CONCAT( '$urlm', mixup_name ) as url, mixup_display as text
        FROM cc_tbl_mixups
        %joins%
        %where%
        %order%
EOF;


    return array( 'e' => array(), 'sql' => $sql, 'sql_count' => 'SELECT 1' );

}
[/dataview]
%%
-->
<!-- template mixup_all -->

<table style="margin:40px auto; width: 400px">
%loop(records,R)%
    <tr>
            <td><a style="font-size:125%" href="%(#R/url)%">%(#R/text)%</a>
            </td>
    </tr>
%end_loop%
</table>
