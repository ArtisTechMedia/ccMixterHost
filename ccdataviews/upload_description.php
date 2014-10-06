<? /*
[meta]
    type     = dataview
    desc     = _('Upload Description')
    datasource = uploads
[/meta]
*/
function upload_description_dataview() 
{

    $sql =<<<EOF
SELECT 
    upload_id, upload_name,
    upload_description as format_html_upload_description,
    upload_description as format_text_upload_description
    %columns%
FROM cc_tbl_uploads
%joins%
%where%
LIMIT 1
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FILTER_FORMAT )
                );
}

