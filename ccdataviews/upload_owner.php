<?/*
[meta]
    type = dataview
    desc = _('Upload name, id with owner name, id')
    name = upload_owner
[/meta]
*/

function upload_owner_dataview() 
{
    $sql =<<<EOF
    SELECT user_name, user_id, upload_id, upload_name
    FROM cc_tbl_uploads
    JOIN cc_tbl_user ON upload_user = user_id
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