<?/*
[meta]
    type = dataview
    name = search_remix_artist
    required_args = match
[/meta]
*/

function search_remix_artist_dataview() 
{
    $sql =<<<EOF
SELECT 
    upload_id, user_name, upload_name, 
    IF( 
        LOWER(user_real_name) = LOWER(user_name), 
        user_name, 
        CONCAT( user_real_name, ' (', user_name, ')' ) 
       ) as user_real_name
    %columns% 
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where% AND MATCH(user_name,user_real_name) AGAINST( '%match%' IN BOOLEAN MODE )
%group%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>