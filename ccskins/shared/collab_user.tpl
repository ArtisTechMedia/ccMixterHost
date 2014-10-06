<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_component
    desc = _('Browse a users collab (profile tab)')
    datasource = collab_user
    required_args = user
    dataview = browse_user_collabs
    embedded = 1
[/meta]
[dataview]
function browse_user_collabs_dataview()
{
    $me = CCUser::CurrentUser();
    $ccc = ccl('collab') . '/';

    $sql =<<<EOF
        SELECT collab_user_confirmed, collab_id, collab_name, 
            CONCAT( '$ccc', collab_id ) as collab_url, collab_user_role, collab_user_credit,
            IF( collab_user = user_id, 1, 0 ) as is_owner,
            collab_date as collab_user_date
        FROM  cc_tbl_collab_users
        JOIN  cc_tbl_user ON collab_user_user=user_id
        JOIN  cc_tbl_collabs ON collab_user_collab=collab_id
        %where% AND (collab_user_confirmed = 1 OR user_id = $me)
        %order%
        %limit%
EOF;

    $sql_count =<<<EOF
        SELECT COUNT(*)
        FROM  cc_tbl_collab_users
        JOIN  cc_tbl_user ON collab_user_user=user_id
        JOIN  cc_tbl_collabs ON collab_user_collab=collab_id
        %where% AND (collab_user_confirmed = 1 OR user_id = $me)
EOF;

    return array( 'e' => array(),
                  'sql' => $sql,
                  'datasource' => 'collab_user',
                  'sql_count' => $sql_count );
}
[/dataview]
*/?>
<!-- template collab_user -->
<style type="text/css">
#collab_user_listing {
   width: 500px;
   margin: 3em auto;
}

.collab_name {
    font-size: 1.5em;
}
#collab_user_listing td {
    padding: 3px 11px 0px 0px;
    white-space: nowrap;
}
#collab_user_listing th {
    text-align: left;
    border-bottom: 1px solid;
}
</style>
<table id="collab_user_listing">
<tr><th>%text(str_collab_name)%</th>
<th>%text(str_collab_role)%</th>
<th>%text(str_collab_credit2)%</th>
<th></th></tr>
%loop(records,R)%
    <tr>
        <td><a class="collab_name" href="%(#R/collab_url)%">%(#R/collab_name)%</a></td>
        <td>%(#R/collab_user_role)%</a></td>
        <td>%(#R/collab_user_credit)%</a></td>
        <td>%if_not_null(#R/collab_user_confirmed)%
               (%text(str_collab_confirmed)%)
             %else%
               (%text(str_collab_not_confirmed)%) <a href="%(#R/collab_url)%">%text(str_collab_confirm_now)%</a>
             %end_if%</td>
    </tr>
%end_loop%
</table>
%call('prev_next_links')%
