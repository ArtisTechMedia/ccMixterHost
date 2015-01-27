<?/*
[meta]
    type = dataview
    desc = _('Tags that only have categories')
    name = tags_with_cat
    datasource = tags
[/meta]
*/
function tags_with_cat_dataview() 
{
    $sql =<<<EOF
SELECT tags_count, tags_tag, tag_category
    FROM cc_tbl_tags
    LEFT JOIN cc_tbl_tag_category ON tags_category = tag_category_id
%joins%
%where%
%order%
%limit%
EOF;
    $sql_count =<<<EOF
SELECT COUNT(*)
    FROM cc_tbl_tags
    LEFT OUTER JOIN cc_tbl_tag_category ON tags_category = tag_category_id
%joins%
%where%
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array()
                );
}

