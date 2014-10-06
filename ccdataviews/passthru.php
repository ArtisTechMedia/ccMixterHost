<?/*
[meta]
    type = dataview
    desc = _('Pass Thru (noop)')
[/meta]
*/
function passthru_dataview() 
{
    $sql = "SELECT  1";
    return array( 'sql' => $sql,
                  'sql_count' => $sql,
                   'e'  => array()
                );
}

