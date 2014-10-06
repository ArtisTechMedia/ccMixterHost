<?
/*
[meta]
    desc = _('Links to page topics')
    datasource = topics
[/meta]
*/
function topic_page_links_dataview($queryObj)
{
    $page       = $queryObj->args['page'];
    $topic_type = cc_get_content_page_type($page);
    $page_url   = url_args(ccl($page),'topic=');
    $slug       = cc_get_topic_name_slug();

    $sql =<<<EOF
        SELECT topic_name, 
            {$slug} as topic_slug,
            CONCAT( '{$page_url}', {$slug} ) as topic_url
        %columns%
        FROM cc_tbl_topics
        %joins%
        %where% AND (topic_type = '{$topic_type}')
        %order%
        %limit%
EOF;
        return array( 'sql' => $sql, 'e' => array() );
}

?>