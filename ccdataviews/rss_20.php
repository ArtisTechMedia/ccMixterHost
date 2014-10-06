<?/*
[meta]
    type = dataview
    name = rss_20
    desc = _('For RSS 2.0 Feed')
[/meta]
*/

function rss_20_dataview()
{
    $ccf = ccl('files') . '/';
    $TZ = ' ' . CCUtil::GetTimeZone();
    $avatar_sql = cc_get_user_avatar_sql();

    // Thu, 27 Dec 2007 09:28:38 PST
    // %a,  %d %b %Y    %T

    $sql =<<<EOF
        SELECT $avatar_sql, upload_id, upload_name, upload_name, upload_contest, user_name, user_real_name,
        CONCAT( '$ccf', user_name, '/', upload_id ) as file_page_url, 
        upload_tags, license_url,
        upload_description as format_text_upload_description,
        upload_description as format_html_upload_description,
        CONCAT( DATE_FORMAT(upload_date,'%a, %d %b %Y %T'), '$TZ' ) as rss_pubdate
        %columns%
        FROM cc_tbl_uploads
        JOIN cc_tbl_user ON upload_user=user_id
        JOIN cc_tbl_licenses ON upload_license=license_id
        %joins%
        %where%
        %order%
        %limit%
EOF;

    return array(   'sql' => $sql,
                    'e' => array(
                            CC_EVENT_FILTER_DOWNLOAD_URL,
                            CC_EVENT_FILTER_FORMAT,
                            ) );
}
