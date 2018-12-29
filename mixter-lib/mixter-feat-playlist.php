<?

/*
  $Id: mixter-feat-playlist.php 13835 2009-12-25 13:19:34Z fourstones $
*/

CCEvents::AddHandler(CC_EVENT_FILTER_CART_MENU,   'playlist_feature_OnFilterCartMenu');
CCEvents::AddHandler(CC_EVENT_MAP_URLS,           'playlist_feature_OnMapUrls');

function playlist_feature_OnFilterCartMenu(&$records)
{
    if( empty($records[0]['cart_id']) || !CCUser::IsAdmin() )
        return;

    $row =& $records[0];
    $playlist_id = $row['cart_id'];

    $row['menu'][] = array( 'url'   => ccl( 'playlist', 'feature', $playlist_id ),
                             'class' => 'cc_playlist_playlink',
                             'id'    => '_feat_' . $playlist_id,
                             'text'  => '*Feature' );
}

function playlist_feature_OnMapUrls()
{
    CCEvents::MapUrl( ccp('playlist', 'feature'), 'playlist_feature_playlist', CC_ADMIN_ONLY, ccs(__FILE__),'','','ccMixter' );
}

function playlist_feature_playlist($playlist_id)
{
      $sql =<<<EOF
        UPDATE cc_tbl_cart SET cart_subtype = 'featured' where cart_id = {$playlist_id}
EOF;
      CCDatabase::Query($sql);
/*
    require_once('cchost_lib/ccextras/cc-topics.inc');

    $topics = new CCTopics();
    $values['topic_id']   = $topics->NextID();
    $values['topic_date'] = date('Y-m-d H:i:s',time());
    $values['topic_user'] = CCUser::CurrentUser();
    $values['topic_type'] = 'feat_playlist';
    list( $values['topic_text'], $values['topic_name'] ) = _get_playlist_stuff($values,$playlist_id);
    $topics->InsertNewTopic($values,0);
*/
    CCUtil::SendBrowserTo( ccl('view','media','playlists') );
}

function _get_playlist_stuff(&$values,$playlist_id)
{
    $text =<<<EOF
[left][query=t=avatar&u=%user%][/query][/left][query=t=playlist_2_info&ids=%id%][/query]
[query=t=yahoo_black&playlist=%id%][/query]
EOF;

    $sql =<<<EOF
     SELECT cart_name, user_name
     FROM cc_tbl_cart 
     JOIN cc_tbl_user ON cart_user=user_id
     WHERE cart_id = {$playlist_id}
EOF;

    $info = CCDatabase::QueryRow($sql);

    $topic_name  = $info['cart_name'];
    if( preg_match('/cool music/i', $topic_name ) )
    {
        $last_cool_topic = CCDatabase::QueryItem('SELECT topic_name FROM cc_tbl_topics WHERE topic_name LIKE \'ccMixter Radio%\''.
                                                  ' ORDER BY topic_date DESC LIMIT 1');
        if( !empty($last_cool_topic) )
        {
            if( preg_match('/([0-9]+)(?:[^0-9]|$)/',$last_cool_topic,$m) )
            {
                $topic_name = 'ccMixter Radio ep. ' . ($m[0] + 1);
            }
        }
    }

    $text = str_replace( '%user%', $info['user_name'], str_replace('%id%', $playlist_id, $text ) );

    return array( $text, $topic_name );
}

?>
