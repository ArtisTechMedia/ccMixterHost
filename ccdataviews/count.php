<?/*
[meta]
    type = dataview
    name = count
[/meta]
*/

function count_dataview() 
{
    $sql = 'SELECT COUNT(*) from cc_tbl_uploads %joins% %where%';

    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>