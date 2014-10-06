<?/*
[meta]
    type = dataview
    name = ids
[/meta]
*/

function ids_dataview() 
{
    $sql = 'SELECT upload_id  %columns% FROM cc_tbl_uploads %joins% %where% %order% %limit%';

    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>