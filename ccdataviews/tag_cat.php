<?/*
[meta]
    type = dataview
    desc = _('Basic tag categories')
    name = tag_cat
    datasource = tag_cat
[/meta]
*/
function tag_cat_dataview() 
{
    $sql =<<<EOF
SELECT tag_category, tag_category_id
    FROM cc_tbl_tag_category
%joins%
%where%
%order%
%limit%
EOF;
    $sql_count =<<<EOF
SELECT COUNT(*)
    FROM cc_tbl_tag_category
%joins%
%where%
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array()
                );
}

