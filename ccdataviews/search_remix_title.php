<?/*
[meta]
    type = dataview
    name = search_remix_title
    required_args = match
[/meta]
*/

function search_remix_title_dataview() 
{
    $sql =<<<EOF
SELECT 
    upload_id, user_name, upload_name, user_real_name
     %columns% 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where% AND MATCH(upload_name) AGAINST( '%match%' IN BOOLEAN MODE )
%group%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>