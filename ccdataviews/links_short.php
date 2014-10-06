<?/*
[meta]
    type = dataview
    name = links
[/meta]
*/

function links_dataview() 
{
    $urlf = ccl('files') . '/';

    $sql =<<<EOF
SELECT 
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    upload_name
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