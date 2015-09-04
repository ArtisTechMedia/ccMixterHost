<?/*
[meta]
    type = dataview
    name = links_u
    datasource = uploads
[/meta]
*/

function links_u_dataview() 
{
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    
    $sql =<<<EOF
SELECT 
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    upload_name, user_real_name
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array(  )
                );
}

?>