<? /*
[meta]
    type     = template_component
    desc     = _('For listing unpublished and moderated uploads')
    dataview = unpub
    embedded = 1
[/meta]
[dataview]
function unpub_dataview() 
{
    global $CC_GLOBALS;

    $urlf = ccl('files') . '/';
    $urlp = ccl('people') . '/';
    $urlb = ccl('admin','ban') . '/';
    $urlh = ccl('files','publish') . '/';
    $urle = ccl('files','edit') . '/';
    $urld = ccl('files','delete') . '/';
    $isadmin = CCUser::IsAdmin() ? 1 : 0;
    $unban = _('unban');
    $pub = _('publish');
    $edit = _('properties');
    $del = _('delete');

    $sql =<<<EOF
SELECT 
    upload_id, upload_name, user_real_name, 
    IF( upload_published, '', 'upload_hidden' ) as hidden_class,
    IF( upload_banned, 'upload_banned', '' ) banned_class,
    IF( upload_banned AND $isadmin, CONCAT('<a href="$urlb',upload_id,'">$unban</a>'), '') as banned_link,
    IF( upload_published, '', CONCAT('<a href="$urlh',user_name, '/', upload_id,'">$pub</a>')) as publish_link,
    CONCAT('<a href="$urle',user_name, '/', upload_id,'">$edit</a>')  as edit_link,
    CONCAT('<a href="$urld',upload_id,'">$del</a>')  as delete_link,
    user_real_name, 
    CONCAT( '$urlp', user_name ) as artist_page_url,
    DATE_FORMAT( upload_date, '%m-%d-%Y %h:%i %p' ) as upload_date_format
    %columns%
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
%order%
%limit%
EOF;

    $sql_count =<<<EOF
SELECT COUNT(*)
FROM cc_tbl_uploads
JOIN cc_tbl_user ON upload_user = user_id
%joins%
%where%
EOF;

    return array( 'sql' => $sql,
                  'sql_count' => $sql_count,
                   'e'  => array( )
                );
}
[/dataview]
*/ ?>
<link rel="stylesheet" type="text/css" title="Default Style" href="%url('css/unpub.css')%" />
<div id="unpub_listing">
<table>
%loop(records,R)%
<tr>
<td>%(#R/upload_date_format)%</td>
<td><a href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a></td>
<td><span class="%(#R/banned_class)% %(#R/hidden_class)%">%(#R/upload_name)%</span></td>
<td>%(#R/banned_link)%</td>
<td>%(#R/publish_link)%</td>
<td>%(#R/edit_link)%</td>
<td>%(#R/delete_link)%</td>
</tr>
%end_loop%
</table>
</div>
%call(prev_next_links)%
