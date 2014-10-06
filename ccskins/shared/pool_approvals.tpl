<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');


$post_url = ccl( 'admin', 'pools', 'approve', 'submit' );
$heads = array( '', _('Approve'), _('Delete'), _('None'), _('Uploads'),_('Author'), _('Site/Links') );
$tr = array( '<' => '&lt;', '>' => '&gt' );
?>
<!-- template pool_approvals -->
<style>
.cc_pool_approval_list table td {
    vertical-align: top;
    border-right: 1px solid #ccc;
    border-bottom: 1px solid #ccc;
    padding: 1px;
}

.cc_pool_approval_list table th {
    border-bottom: 1px solid #444;
}

.em_code {
    color: #777;
    font-family: Courier New, serif;
}
.poster {
    float: right;
    text-align: right;
}
</style>
<form action="%(#post_url)%" method="post">
  <div class="cc_pool_approval_list">
    <table>
      <tr>%loop(#heads,head)%<th>%(#head)%</th>%end_loop%</tr>
      %loop(records,r)%
      <tr>
         <td><a href="%(#r/item_edit_url)%" id="edit_link_%(#r/pool_item_id)%" class="small_button"><span>edit</span></a></td>
         <td><input type="radio" name="action[%(#r/pool_item_id)%]" value="approve"/></td>
         <td><input type="radio" name="action[%(#r/pool_item_id)%]" value="delete" /></td>
         <td><input type="radio" name="action[%(#r/pool_item_id)%]" value="nothing"  checked="checked"  /></td>
         <td>
            %loop(#r/uploads,u)%
               <a href="%(#u/file_page_url)%">%(#u/upload_name)%</a>
                &nbsp;%text(str_by)%&nbsp;%(#u/user_real_name)% (%(#u/user_name)%)
                %if_not_last(u)%<br /> %end_if%
            %end_loop%
         </td>
         <td>%(#r/pool_item_artist)%</td>
         <td>
         <a href="%(#r/pool_item_url)%" target="_blank" >%(#r/pool_item_name)%</a>
         %if_not_null(#r/pool_item_extra/ttype)%
             <br />
             <div class="poster"><?= _('Poster') ?>: <a href="mailto:%(#r/pool_item_extra/email)%">%(#r/pool_item_extra/poster)%</a></div>
             <i><?= _('Type') ?>: %(#r/pool_item_extra/ttype)%</i>
             %if_not_null(#r/pool_item_extra/embed)%
                <div class="em_code"><? print '<br />' . wordwrap( strtr($r['pool_item_extra']['embed'],$tr), 80, '<br />', true ); ?></div>
                <!-- a href="javascript://doshowembed" class="embed_popup" id="id_%(#r/pool_item_id)%"><?= _('see embed') ?></a -->
             %end_if%
         %end_if%
         </td>
      </tr>
      %end_loop%
    </table>
  </div>
  <input type="submit" value="Submit" />
</form>
