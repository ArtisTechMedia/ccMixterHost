<?/*
[meta]
    type = template_component
    desc = _('Manage files for user')
    embedded = 1
    dataview = manage_files
    breadcrumbs = home,user,text(str_manage_files)
[/meta]
[dataview]
function manage_files_dataview()
{
    $manurl    = ccl('file','manage') . '/';
    $propurl   = ccl('files','edit')  . '/';
    $rmxurl    = ccl('file','remixes') . '/';

    $sql =<<<EOF
    SELECT 
           CONCAT( '$manurl', upload_id ) as manage_url,
           CONCAT( '$propurl', user_name, '/', upload_id ) as prop_url,
           CONCAT( '$rmxurl', upload_id ) as rmx_url,
           upload_name, upload_id
           %columns%
    FROM cc_tbl_uploads
    JOIN cc_tbl_user ON upload_user=user_id
        %joins%
        %where%
        %order%
        %limit%
EOF;
    $sql_count  =<<<EOF
    SELECT COUNT(*)
    FROM cc_tbl_uploads
    JOIN cc_tbl_user ON upload_user=user_id
    %joins%
    %where%
EOF;

    return array( 'e' => array(),
                   'sql' => $sql,
                   'sql_count' => $sql_count );
}
[/dataview]
*/


$submit_types = cc_get_submit_types(false,'(Select type)');

?>
<h1>%text(str_files_manage)%</h1>
%if_empty(records)%
    %return%
%end_if%

<style>
.edit_files_submit td {
    padding: 8px 0px 8px 19px;
    border-bottom: 1px solid #999;
}
</style>

<table class="edit_sort_buttons"><tbody><tr>
<td><a href="javascript://sort by date" class="small_button" id="files_by_date"><span>Sort by date</span></a></td>
<td><a href="javascript://sort by name" class="small_button" id="files_by_name"><span>Sort by name</span></a></td>
</tr></tbody></table>
         
<!-- template manage_files -->
<table class="edit_files_submit" cellspacing = "0" >
%loop(records,R)%
<tr>
    <td style="font-size:1.2em;font-weight:bold;text-align:right;padding-right:0.3em;" class="edit_upload_name">%(#R/upload_name)%</td>
    <td><a href="%(#R/prop_url)%" class="cc_gen_button"><span>%text(str_file_properties_v)%</span></a></td>
    <td><a href="%(#R/manage_url)%" class="cc_gen_button"><span>%text(str_files_manage_v)%</span></a></td>
    <td><a href="%(#R/rmx_url)%" class="cc_gen_button"><span>%text(str_files_manage_remixes_v)%</span></a></td>
</tr>
%end_loop%
</table>
%call(prev_next_links)%
<script type="text/javascript">

ccManageFiles = Class.create();

ccManageFiles.prototype = {

    initialize: function() {
        Event.observe( 'files_by_date', 'click', this.onFilesBy.bindAsEventListener(this,'date') );
        Event.observe( 'files_by_name', 'click', this.onFilesBy.bindAsEventListener(this,'name') );
    },

    onFilesBy: function(event, type) {
        var url = query_url + 't=manage_files&limit=50&user=' + user_name;
        var on, off;
        if( type == 'name' )
            url += '&sort=name&ord=asc';
        document.location = url;
        return false;
    }

}
new ccManageFiles();

</script>
