<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template reviews -->
<?
if( empty($_GET['offset']) )
{
    $T->Call('reviews_recent_edit');
    $offset = 0;
}
else
{
    $offset = $_GET['offset'];
}

?><h3><?= $T->String('str_reviews_most_recent'); ?></h3><?

cc_query_fmt('f=html&noexit=1&nomime=1&t=reviews_browse&paging=on&limit=30&offset=' . $offset,1);

$T->Call('prev_next_links');
?>
