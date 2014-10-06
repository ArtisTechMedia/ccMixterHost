<?/*
[meta]
    type = dataview
    name = upload_extra
[/meta]
*/

function upload_extra_dataview() 
{
    $sql =<<<EOF
    SELECT upload_id, upload_extra
    FROM cc_tbl_uploads
%joins%
%where%
%group%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FILTER_EXTRA )
                );
}

?>