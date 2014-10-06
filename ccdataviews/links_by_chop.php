<?/*
[meta]
    type = dataview
    name = links_by_chop
[/meta]
*/

function links_by_chop_dataview() 
{
    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';

    $sql =<<<EOF
SELECT 
    upload_id,
    CONCAT( '$urlf', user_name, '/', upload_id ) as file_page_url,
    IF( LENGTH(upload_name) > 19, CONCAT( SUBSTRING(upload_name,1,16), '...' ), upload_name ) as upload_name,
    CONCAT( '$urlp', user_name ) as artist_page_url,
    IF( LENGTH(user_real_name) > 13, CONCAT( SUBSTRING(user_real_name,1,11), '...' ), user_real_name) as user_real_name
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                  'name' => 'links_by_chop',
                   'e'  => array()
                );
}

?>