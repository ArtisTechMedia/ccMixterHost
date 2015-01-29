<?

function fix_all()
{
    fix_collabs();
    fix_lics();
    fix_pool_resync();
    flush();
    fix_topics();
    require_once( dirname(__FILE__) . '/cc-content-topics.php' );
    inject_content_topics();    
}

function fix_lics()
{
    require_once('cchost_lib/cc-lics-install.php');
    print("Updating licenses<br />\n");
    cc_install_licenses( array( '3_0', '2_5' ), false );
}


function fix_cart_sync()
{
    $sql[] = "LOCK TABLES cc_tbl_cart WRITE , cc_tbl_cart_items READ";
    $sql[] = <<<EOF
UPDATE cc_tbl_cart SET cart_num_items = (
SELECT count( * )
FROM `cc_tbl_cart_items`
WHERE cart_item_cart = cart_id
)
EOF;
    $sql[] = "UNLOCK TABLES";

    print("Updating playlist cart counts<br />\n");
    CCDatabase::Query($sql);
}

function fix_pool_resync()
{
    print("Updating pool resync...");
    $sql[] = 'UPDATE cc_tbl_pool_item SET pool_item_num_remixes = 0';
    $sql[] =<<<EOF
UPDATE `cc_tbl_pool_item` AS pi SET pool_item_num_remixes = (
SELECT count( * )
FROM cc_tbl_pool_tree
WHERE pool_tree_pool_parent = pi.pool_item_id)
EOF;
    CCDatabase::Query($sql);
    print("done<br />\n");
}


function fix_topics()
{
    print("Topic tree updating...");

    $sql =<<<EOF
UPDATE `cc_tbl_forum_threads`  SET forum_thread_name = (
SELECT topic_name
FROM cc_tbl_topics
WHERE topic_id = forum_thread_oldest
)
EOF;
    CCDatabase::Query($sql);

    $sql =<<<EOF
UPDATE cc_tbl_topics  SET topic_type = 'forum' WHERE topic_type = ''
EOF;
    CCDatabase::Query($sql);

    CCDatabase::Query('LOCK TABLES cc_tbl_topics WRITE, cc_tbl_topic_tree READ');
    CCDatabase::Query('UPDATE cc_tbl_topics SET topic_left = 0, topic_right = 0');

    // after some muddling through this was the fastest way to do this. In 
    // the case of ccMixter we had 20,000 non-reply topics, however, 2/3rds
    // of those did have replies (mainly because they were reviews)

    // these are topics that actually have children 
    // we group because has several children and would show up multiple
    // times 
    $sql =<<<EOF
SELECT topic_id, topic_upload, topic_thread FROM cc_tbl_topics 
join cc_tbl_topic_tree on topic_tree_parent = topic_id 
where topic_type <> 'reply'
group by topic_id
ORDER BY topic_date ASC
EOF;

    $qr = CCDatabase::Query($sql);
    $right = 0;
    while( $_tid = mysql_fetch_row($qr) )
    {
        $right = rebuild_tree($_tid[0],$_tid[1],$right);
    }

    // these are ones without parents
    $sql =<<<EOF
SELECT topic_id FROM cc_tbl_topics WHERE topic_left = 0 ORDER BY topic_date ASC
EOF;

    $qr = CCDatabase::Query($sql);
    while( $row = mysql_fetch_row($qr) )
    {
        $left = $right + 1;
        $right = $left + 1;
       mysql_query("UPDATE cc_tbl_topics SET topic_left=$left, topic_right=$right WHERE topic_id={$row[0]}" )
           or die(mysql_error());
    }
    CCDatabase::Query('UNLOCK TABLES');
    print("done<br />");
}

function rebuild_tree($parent, $upload, $left) {
   // the right value of this node is the left value + 1
   $right = $left+1;

   // get all children of this node
   $result = mysql_query( "SELECT topic_tree_child FROM cc_tbl_topic_tree WHERE topic_tree_parent=$parent") or die(mysql_error());
   while ($row = mysql_fetch_array($result)) {
       $right = rebuild_tree($row['topic_tree_child'], $upload, $right);
   }

   // we've got the left value, and now that we've processed
   // the children of this node we also know the right value
   mysql_query("UPDATE cc_tbl_topics SET topic_left=$left, topic_right=$right, topic_upload = $upload WHERE topic_id=$parent" )
       or die(mysql_error());

   // return the right value of this node + 1
   return $right+1;
} 


function fix_collabs()
{
    $sql =<<<EOF
    SELECT upload_id,upload_tags
        FROM cc_tbl_collab_uploads
        JOIN cc_tbl_uploads ON upload_id=collab_upload_upload
        WHERE collab_upload_type = ''
EOF;
    $qr = CCDatabase::Query($sql);
    while( list($id,$tags)= mysql_fetch_row($qr) )
    {
        if( preg_match('/,(remix|acappella|sample),/',$tags,$m) )
        {
            $type = $m[1];
        }
        else
        {
            if( !preg_match('/$,([^,]+),/',$tags,$m) )
                continue; // this should really never frakin happen
            $type = $m[1];
        }
        $sql = "UPDATE cc_tbl_collab_uploads SET collab_upload_type = '{$type}' WHERE collab_upload_upload = {$id}";
        CCDatabase::Query($sql);
    }

    print('Collaboration type field updated<br />'."\n");

    CCDatabase::Query('UPDATE cc_tbl_collab_users SET collab_user_confirmed = 1');
    $sql = "UPDATE cc_tbl_collabs SET collab_confirmed = " .
           "(SELECT COUNT(collab_user_confirmed) > 1 FROM `cc_tbl_collab_users` WHERE collab_user_collab = collab_id)";
    CCDatabase::Query($sql);

    print('Collaboration user confirmed field updated<br />'."\n");
}

?>
