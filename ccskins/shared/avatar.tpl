<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/* %%
[meta]
    type = ajax_component
    desc = _('User avatar')
    dataview = avatar
    datasource = user
    embedded = 1
    require_args = user
[/meta]
[dataview]
function avatar_dataview()
{
    $avatar_sql = cc_get_user_avatar_sql();

    $sql =<<<EOF
        SELECT $avatar_sql
        FROM cc_tbl_user
        %where% 
        LIMIT 1
EOF;

    return array( 'sql' => $sql,
                  'e' => array( ) );

}
[/dataview] %%
*/?>
%if_not_null(records/0)%<img src="%(records/0/user_avatar_url)%" />%end_if%
