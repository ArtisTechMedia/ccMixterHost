<?/*
[meta]
    type     = template_component
    dataview = uploads_options
    embedded = 1
[/meta]
[dataview]
function uploads_options_dataview()
{
    $sql=<<<EOF
        SELECT upload_id, upload_name, user_real_name
        FROM cc_tbl_uploads
        JOIN cc_tbl_user ON upload_user = user_id
        %where%
        %order%
EOF;

    return array( 'sql' => $sql, 'e' => array() );
}
[/dataview]
*/?>

<select <?= empty($_GET['size']) ? '' : "size={$_GET['size']}" ?> id="cc_upload_list">
%loop(records,R)%
    <option value="%(#R/upload_id)%">"%(#R/upload_name)%" %text(str_by)%<!- --> %(#R/user_real_name)%</option>
%end_loop%
</select>