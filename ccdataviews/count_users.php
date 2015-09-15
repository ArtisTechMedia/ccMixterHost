<?/*
[meta]
    type = dataview
    name = count_users
    datasource = user
[/meta]
*/

function count_users_dataview() 
{
    $sql = 'SELECT COUNT(*) from cc_tbl_user %columns% %joins% %where%';

    return array( 'sql' => $sql,
                   'e'  => array( )
                );
                
}

?>
