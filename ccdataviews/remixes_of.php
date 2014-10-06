<?/*
[meta]
    type = dataview
    name = remixes_of
[/meta]
*/

function remixes_of_dataview() 
{
    $sql =<<<EOF
%joins%
%where%
%group%
%order%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}

?>