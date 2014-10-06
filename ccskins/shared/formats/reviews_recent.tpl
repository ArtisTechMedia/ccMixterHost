<?/*
[meta]
    type = template_component
    desc = _('7 most recent reviews')
    datasource = topics
    dataview = review_recent
    embedded = 1
[/meta]
[dataview]
function review_recent_dataview() 
{
    $baseurl = ccl('reviews') . '/';
    $baseup  = ccl('files') . '/';
    $baseus  = ccl('people') . '/';

    $sql =<<<END
        SELECT count(*) as cnt, 
               ups.upload_name, 
               user_real_name,
               CONCAT( '$baseurl', user_name, '/', upload_id ) as revurl,
               CONCAT( '$baseup', user_name, '/', upload_id ) as file_page_url,
               CONCAT( '$baseus', user_name ) as artist_page_url
        FROM (
            SELECT * FROM `cc_tbl_topics`
            %where% AND (topic_upload >1)
                AND (topic_type = 'review')
                
            ORDER BY topic_date DESC
            ) AS tbl
        JOIN cc_tbl_uploads ups ON tbl.topic_upload = ups.upload_id
        JOIN cc_tbl_user    user ON ups.upload_user = user.user_id
        GROUP BY tbl.topic_upload
        ORDER BY  cnt DESC 
        LIMIT 7
END;

    return array( 'sql' => $sql,
                   'e'  => array()
                );
}
[/dataview]
*/?>

<table class="cc_topic_table">
  %loop(records,R)%
  <tr>
    <td>
      <a class ="cc_hot_review" href="%(#R/revurl)%"><?= $T->String( array('str_reviews_n',$R['cnt']) ) ?></a>
      %text(str_for)%
      <a class="cc_file_link" href="%(#R/file_page_url)%">%(#R/upload_name)%</a>  
      %text(str_by)%
      <a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a>
    </td>
  </tr>
  %end_loop%
</table>
