<?/*
[meta]
    type = dataview
    name = count_tags
    datasource = tags
[/meta]
*/

function count_tags_dataview() 
{
    $sql = 'SELECT COUNT(*) from cc_tbl_tags %columns% %joins% %where%';

    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>
