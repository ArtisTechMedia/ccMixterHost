<?/*
[meta]
    type     = dataview
    desc     = _('Mixups mail for macro replacements')
    datasource = mixups
[/meta]
*/
function mixup_mail_dataview()
{
    
    $name  = cc_fancy_user_sql('mixer_full_name','mixer');
    $other = cc_fancy_user_sql('mixee_full_name','other');
    $urlp  = ccl('people') . '/';
    $urlf  = ccl('files') . '/';
    $urlc  = ccl('people','contact') . '/';
    $urlm  = ccl('mixup') . '/';
    $urlq  = ccl('mixup','confirm') . '/';
    
    $sql =<<<EOF
     SELECT
        {$name},
        mixer.user_name                      as mixer_user_name,
        CONCAT( '{$urlp}', mixer.user_name ) as mixer_page_url,
        mixer.user_id                        as mixer_user_id,
        
        {$other},        
        other.user_name                      as mixee_user_name,
        CONCAT( '{$urlp}', other.user_name ) as mixee_page_url,
        
        IF( mixup_user_upload, CONCAT('{$urlf}', mixer.user_name, '/', mixup_user_upload), '' )
                                             as remix_page_url,
        mixup_display,
        mixup_name,
        CONCAT( '{$urlm}', mixup_name )      as mixup_url,
        CONCAT( '{$urlc}', admin.user_name ) as mixup_admin_contact,
        CONCAT( '{$urlq}', mixup_id )        as mixup_confirm_url,
        
        mixup_mode_name,                                            
        DATE_FORMAT( mixup_mode_date, '%W, %M %e, %Y' ) as mixup_mode_date
        
                                            
      FROM cc_tbl_mixup_user
                 JOIN cc_tbl_mixups        ON mixup_user_mixup = mixup_id
                 JOIN cc_tbl_mixup_mode    ON mixup_mode = mixup_mode_id
                 JOIN cc_tbl_user as admin ON mixup_admin = admin.user_id
                 JOIN cc_tbl_user as mixer ON mixup_user_user  = mixer.user_id
      LEFT OUTER JOIN cc_tbl_user as other ON mixup_user_other = other.user_id
      
      %joins%
      %where%
      %order%
      %limit%
      
EOF;
   
   return array(
                  'e' => array(
                                CC_EVENT_FORMAT_MIXUP
                              ),
                    'sql' => $sql );

}
?>