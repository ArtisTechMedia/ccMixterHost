<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
/*
[meta]
    type = template_compontent
    name = pools
    dataview = pools
    embedded = 1
[/meta]
[dataview]
function pools_dataview() 
{
    $sql =<<<EOF
SELECT * FROM cc_tbl_pools
WHERE pool_api_url > '' AND pool_search > 0 AND pool_banned < 1
EOF;
    return array( 'sql' => $sql,
                   'e'  => array( )
                );
}
[/dataview]
*/

?>