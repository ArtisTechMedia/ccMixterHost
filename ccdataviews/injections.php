<?/*
[meta]
    type = dataview
    name = injections
[/meta]
*/

function injections_dataview() 
{
    $sql =<<<EOF
  SELECT upload_id,
         user_real_name, upload_name, upload_license, user_name, upload_id, file_name,  upload_extra
    %columns% 
  FROM cc_tbl_uploads 
  LEFT OUTER JOIN cc_tbl_injested ON injested_upload=upload_id 
  JOIN cc_tbl_files ON upload_id = file_upload 
  JOIN cc_tbl_user  ON upload_user = user_id 
  %joins% 
  %where% AND injested_upload IS NULL AND file_order = 0 
  %order%
  %limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( CC_EVENT_FILTER_EXTRA )
                );
}

?>