<?/*
[meta]
    type = dataview
    desc = _('Dig Topics')
    datasource = topics
[/meta]
*/
function digtopics_dataview() 
{
    $sql =<<<EOF
SELECT  topic_name, topic_text
FROM cc_tbl_topics AS topic
%joins%
%where% 
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_topics AS topic
%joins%
%where%
EOF;
    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array()
                );
}

