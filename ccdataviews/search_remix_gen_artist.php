<?/*
[meta]
    type = dataview
    name = search_remix_gen_artist
    required_args = match
[/meta]
*/

function search_remix_gen_artist_dataview() 
{
    $fun = cc_fancy_user_sql('user_real_name');

    $sql =<<<EOF
SELECT 
    upload_id, user_name, upload_name, {$fun}
    %columns% 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where% AND LOWER(CONCAT(user_name,user_real_name)) LIKE LOWER('%%match%%')
%group%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>