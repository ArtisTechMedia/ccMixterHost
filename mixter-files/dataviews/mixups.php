<?/*
<?/*%%
[meta]
    type     = dataview
    desc     = _('Mixups')
    datasource = mixups
[/meta]
*/
function mixups_dataview()
{
    $urlm = ccl('mixup') . '/';
    $urlc = ccl('people','contact') . '/';
    
    $sql =<<<EOF
        SELECT
            mixup_id, mixup_name, mixup_tag, mixup_display,
            CONCAT( '{$urlc}', admin.user_name ) as mixup_admin_contact,
            mixup_mode_id, mixup_mode_type, mixup_mode_name,
            mixup_mode_mail,
            mixup_desc      as format_text_mixup_desc,
            mixup_mode_desc as format_text_mixup_mode_desc,
            mixup_desc      as format_html_mixup_desc,
            mixup_mode_desc as format_html_mixup_mode_desc,
            DATE_FORMAT( mixup_date,      '%W, %M %e, %Y' ) as mixup_date,
            DATE_FORMAT( mixup_mode_date, '%W, %M %e, %Y' ) as mixup_mode_date,
            CONCAT( '{$urlm}', mixup_name ) as mixup_url,
            mixup_playlist,
            mixup_thread
        FROM cc_tbl_mixups
        JOIN cc_tbl_mixup_mode ON mixup_mode = mixup_mode_id
        JOIN cc_tbl_user as admin ON mixup_admin = admin.user_id
        %joins%
        %where%
        %order%
        %limit%
EOF;

    $sql_count =<<<EOF
    SELECT COUNT(*)
    FROM cc_tbl_mixups
    JOIN cc_tbl_mixup_mode ON mixup_mode = mixup_mode_id
    %where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                  'e' => array(
                                CC_EVENT_FILTER_FORMAT,
                                CC_EVENT_FORMAT_MIXUP
                              )
                );
}
?>
