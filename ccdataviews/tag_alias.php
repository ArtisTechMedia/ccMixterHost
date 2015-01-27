<?/*
[meta]
    type = dataview
    desc = _('Return tag aliases')
    name = didumean
    datasource = tag_alias
    require_arg = search
[/meta]
*/
function tag_alias_dataview() 
{
    
    $sql =<<<EOF
SELECT DISTINCT tag_alias_alias
%columns%
    FROM cc_tbl_tag_alias
%joins%
%where%
%group%
%order%
%limit%
EOF;
    $sql_count =<<<EOF
SELECT COUNT(*)
    FROM cc_tbl_tag_alias
%joins%
%where%
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array()
                );
}

