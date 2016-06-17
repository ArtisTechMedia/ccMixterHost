<?

require_once('mixter-lib/lib/status.inc');

define('INALID_PERMISSION', "you don't have permssions to do this");

class CCLibUpload
{

  function Permissions($upload_id,$user_name) {
    $results = $this->_check_rate_review_perms($upload_id,$user_name);
    return _make_ok_status($results);
  }

  function PutProperties($upload_id,$props) {
    if( !$this->_check_owner_perms($upload_id,CCUser::CurrentUserName() ) ) {
      return _make_err_status(INALID_PERMISSION);
    }

    require_once('cchost_lib/cc-uploadapi.php');
    if( !empty($props['bpm']) ) {
      require_once('cchost_lib/ccextras/cc-bpm.inc');
      $bpmapi = new CCBPM();
      $bpmapi->SetBPM($upload_id,$props['bpm']);
    }

    $info = array(
          'sql' => 'SELECT *, user_name FROM cc_tbl_uploads JOIN cc_tbl_user ON upload_user=user_id WHERE upload_id='.$upload_id,
          'e'   => array( CC_EVENT_FILTER_FILES, CC_EVENT_FILTER_EXTRA )
          );
    $dv = new CCDataView();
    $record = $dv->PerformInfo( $info, array(), CCDV_RET_RECORD);
    $db_args = array(
      'upload_user' => CCUser::CurrentUser(),
      'upload_config' => 'media',
      'upload_id' => $upload_id,
      'upload_name' => empty($props['upload_name']) ? $record['upload_name'] : $props['upload_name'],
      'upload_tags' => empty($props['upload_tags']) ? $record['upload_tags'] : $props['upload_tags'],
      'upload_description' => empty($props['upload_description']) ? $record['upload_description'] : $props['upload_description'],
      );

    require_once('cchost_lib/cc-uploadapi.php');
    $ret= CCUploadAPI::PostProcessEditUpload(  $db_args, 
                                               $record,
                                               $record['upload_extra']['relative_dir']); 
    if( is_string($ret) ) {
      return _make_err_status($ret);
    }
    return _make_ok_status($props);
  }

  function Rate($upload_id,$user_name) {
    $results = $this->_check_rate_review_perms($upload_id,$user_name);
    if( !$results['okToRate'] ) {
      return _make_err_status(INALID_PERMISSION);
    }
    
    require_once('cchost_lib/cc-ratings.php');
    $ratings =& CCRatings::GetTable();
    $score = 500;
    $R['ratings_score']  = $score;
    $R['ratings_upload'] = $upload_id;
    $R['ratings_user']   = CCUser::IDFromName($user_name);
    if( !empty($_SERVER['REMOTE_ADDR']) ) {
      $R['ratings_ip'] = $_SERVER['REMOTE_ADDR'];
    }
    $ratings->Insert($R);

    $upload = $this->_upload_record($upload_id);
    CCEvents::Invoke( CC_EVENT_RATED, array( $R, $score/100, &$upload ) );
    
    return _make_ok_status();
  }
  
  function Review($upload_id,$user_name,$textbody) {
    $results = $this->_check_rate_review_perms($upload_id,$user_name);
    if( !$results['okToReview'] ) {
      return _make_err_status(INALID_PERMISSION);
    }

    require_once('cchost_lib/ccextras/cc-reviews.inc');

    $luser_name = strtolower($user_name);
    $user       = CCDatabase::QueryRow("SELECT user_id,user_real_name FROM cc_tbl_user WHERE LOWER(user_name) = '{$luser_name}'");
    $upload     = CCDatabase::QueryRow("SELECT * FROM cc_tbl_uploads WHERE upload_id = {$upload_id}");

    $reviews =& CCReviews::GetTable();
    $values['topic_id'] = $reviews->NextID();
    $values['topic_upload'] = $upload_id;
    $values['topic_date'] = date('Y-m-d H:i:s',time());
    $values['topic_user'] = $user['user_id'];
    $values['topic_type'] = 'review';
    $values['topic_text'] = $textbody;
    $values['topic_name'] = sprintf(_("Review of '%s' by '%s'"), 
                                    $upload['upload_name'], $user['user_real_name']);

    if( !$this->_check_post_perms($user_name,$values) ) {
      return _make_err_status(INVALID_PERMISSION);
    }

    $reviews->InsertNewTopic($values,0);

    $row = $reviews->QueryKeyRow($values['topic_id']);
    // these will go into notification mail 
    $row['user_real_name'] = $user['user_real_name'];
    $row['topic_permalink'] = ccl('reviews',$user_name, $upload_id ) . '#' . $values['topic_id'];

    CCEvents::Invoke( CC_EVENT_REVIEW, array( &$row, &$upload ) );
    return _make_ok_status();

  }

  function _check_rate_review_perms($upload_id,$user_name) {

    $results = array( 'okToRate' => false, 'okToReview' => false );
                  
    // cribbed from cc-user-hook

    /************************************************
        User is banned from all rating/reviewing
     ************************************************/
    $configs           =& CCConfigs::GetTable();
    $C                 = $configs->GetConfig('chart',CC_GLOBAL_SCOPE);

    if( !empty($C['ratings_ban']) )
    {
        require_once('cchost_lib/cc-tags.php');
        $banlist = CCTag::TagSplit($C['ratings_ban']);
        if( in_array($user_name,$banlist) ) {
          return $results;
        }
    }

    /************************************************
        User trying to rate/review their own upload
     ************************************************/
    if( $this->_check_owner_perms($upload_id,$user_name) ) {
      return $results;
    }

    /****************************************************
        Only the current user can rate (?)
     **************************************************/
    $user_id = CCUser::IDFromName($user_name);
    if( $user_id != CCUser::CurrentUser() ) {
      return $results;
    }

    /************************************************
        User has already rated this upload
     ************************************************/
    $remote_ip = $_SERVER['REMOTE_ADDR'];
    $sql =<<<EOF
      SELECT COUNT(*) 
      FROM cc_tbl_ratings 
      WHERE (ratings_ip = '{$remote_ip}' OR ratings_user = {$user_id}) AND 
            ratings_upload = {$upload_id}
EOF;

    $ok = (int)CCDatabase::QueryItem($sql) == 0;
    $results['okToRate'] = $ok;


    /************************************************
        User has already reviewed this upload
     ************************************************/
    require_once('cchost_lib/ccextras/cc-reviews.inc');
    $reviews =& CCReviews::GetTable();
    $where['topic_upload'] = $upload_id;
    $where['topic_user'] = $user_id;
    $ok = (int)$reviews->CountRows($where) == 0;
    $results['okToReview'] = $ok;

    return $results;
  }

  function _check_owner_perms($upload_id,$user_name) {
    $R         = $this->_upload_record($upload_id);
    $remote_ip = $_SERVER['REMOTE_ADDR'];
    $ip        = CCUtil::EncodeIP($remote_ip);    
    return (strtolower($R['user_name']) == strtolower($user_name)) || $R['uploader_ip'] == $ip;
  }

  function _check_post_perms($user_name,$values) {
    require_once('cchost_lib/ccextras/cc-topics.inc');

    return true;
  }

  function _upload_record($upload_id) {
    if( !empty($this->__upload_record) ) {
      return $this->__upload_record;
    }
    $sql =<<<EOF
        SELECT upload_id, upload_user, upload_name, user_name, user_real_name, user_email, user_last_known_ip,
            user_num_uploads,
            SUBSTRING(user_last_known_ip,1,8) as uploader_ip, 
            1 as trigger_sync
        FROM cc_tbl_uploads 
        JOIN cc_tbl_user ON upload_user=user_id
        WHERE upload_id = {$upload_id}
EOF;
        $record = CCDatabase::QueryRow($sql);
        $record['file_page_url'] = ccl('files',$record['user_name'],$upload_id);
        $this->__upload_record = $record;
        return $record;

  }
}
?>