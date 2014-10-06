
<!-- template forum_admin -->
<style>
.forumadmin td {
    vertical-align: top;
    padding-bottom: 18px;
}
</style>
<table  class="forumadmin">
    %loop(fadmin/forum_groups,G)%
        <tr>
            <td><i>Group:</i> <b>%text(#G)%</b></td>
            <td><a class="small_button" href="%(fadmin/edit_forum_group_link)%/%(#k_G)%"><span>%(fadmin/edit_forum_group_text)%</span></a></td>
            <td><a class="small_button" href="%(fadmin/del_forum_group_link)%/%(#k_G)%"><span>%(fadmin/del_forum_group_text)%</span></a></td>
        </tr>
    %end_loop%
    <tr>
        <td colspan="2">
          <a class="cc_gen_button" href="%(fadmin/add_forum_group_link)%"><span >%(fadmin/add_forum_group_text)%</span></a>
        </td>
    </tr>
        
    %loop(fadmin/forums,F)%
        <tr>
            <td><i>Forum:</i> %text(#F/forum_group_name)%::<b>%text(#F/forum_name)%</b></td>
            <td><a class="small_button" href="%(fadmin/edit_forum_link)%/%(#F/forum_id)%"><span>%(fadmin/edit_forum_text)%</span></a></td>
            <td><a class="small_button" href="%(fadmin/del_forum_link)%/%(#F/forum_id)%"><span>%(fadmin/del_forum_text)%</span></a></td>
        </tr>
    %end_loop%
    <tr >
        <td colspan="2">
            %if_not_null(fadmin/forum_groups)%
              <a class="cc_gen_button" href="%(fadmin/add_forum_link)%"><span >%(fadmin/add_forum_text)%</span></a>
            %end_if%
        </td>
    </tr>
</table>
