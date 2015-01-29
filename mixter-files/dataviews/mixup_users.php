<?/*
[meta]
    type     = dataview
    desc     = _('Mixups user listing')
    datasource = mixup_users
[/meta]
*/
function mixup_users_dataview()
{
    $mixer_avatar = cc_get_user_avatar_sql('mixer', 'mixer_avatar_url' );
    $mixee_avatar = cc_get_user_avatar_sql('mixee', 'mixee_avatar_url');
    $mixer_name   = cc_fancy_user_sql('mixer_name', 'mixer');
    $mixee_name   = cc_fancy_user_sql('mixee_name', 'mixee');
    
    $urlp = ccl('people') . '/';
    $urlf = ccl('files') . '/';
    
    $urlremove = CCUser::IsAdmin() ? "CONCAT('".ccl('api','mixup','adminremove') ."/',mixup_user_mixup,'/',mixer.user_id)" : "0";
    
    $sql =<<<EOF
       SELECT
          {$mixer_name},
          {$mixer_avatar},
          CONCAT( '{$urlp}', mixer.user_name ) as mixer_page_url,
          mixup_user_mixup as mixup_user_id,
          mixer.user_email as mixer_email,
          
          {$mixee_name},
          {$mixee_avatar},
          IF( mixup_user_other,  CONCAT( '{$urlp}', mixee.user_name ), '' ) as mixee_page_url,
          mixee.user_name  as mixee_user_name,
          
          IF( mixup_user_upload, CONCAT('{$urlf}', mixer.user_name, '/', mixup_user_upload), '' ) as file_page_url,
          upload_name,
          
          {$urlremove} as admin_remove_url, mixup_user_confirmed
          
      FROM cc_tbl_mixup_user
                 JOIN cc_tbl_user as mixer ON mixup_user_user   = mixer.user_id
      LEFT OUTER JOIN cc_tbl_user as mixee ON mixup_user_other  = mixee.user_id
      LEFT OUTER JOIN cc_tbl_uploads ON mixup_user_upload = upload_id
      %joins%
      %where%
      %order%
      
EOF;
   
   return array( 'e' => array(), 'sql' => $sql, 'sql_count' => 'select 1' );
}

?>