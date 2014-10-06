<?/*
[meta]
    type = dataview
    name = count_pool_items
    datasource = pool_items
[/meta]
*/

function count_pool_items_dataview() 
{
    $sql = 'SELECT COUNT(*) from cc_tbl_pool_item %columns% %joins% %where%';

    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>
