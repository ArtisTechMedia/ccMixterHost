<?/*
[meta]
    type = dataview
    name = count_topics
    datasource = topics
[/meta]
*/

function count_topics_dataview() 
{
    $sql = 'SELECT COUNT(*) from cc_tbl_topics %columns% %joins% %where%';

    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>
