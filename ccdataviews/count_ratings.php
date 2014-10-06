<?/*
[meta]
    type = dataview
    name = count_ratings
    datasource = ratings
[/meta]
*/

function count_ratings_dataview() 
{
    $sql = 'SELECT COUNT(*) from cc_tbl_ratings %columns% %joins% %where%';

    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>