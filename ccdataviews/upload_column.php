<?/*
[meta]
    type = dataview
    name = upload_column
[/meta]
*/

function upload_column_dataview() 
{
    $sql =<<<EOF
    SELECT upload_id, %columns%
    FROM cc_tbl_uploads
%joins%
%where%
%group%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>