<?/*
[meta]
    type       = ajax_component
    desc       = _('List collab users (embedded in collab page)')
    dataview   = collab_users
    embedded   = 1
    datasource = collab_users
    valid_args = collab
[/meta]
[dataview]
function collab_users_dataview() 
{
    global $CC_GLOBALS;

    $urlp = ccl('people') . '/';
    $urlc = ccl('collab') . '/';
    $me = CCUser::CurrentUser();
    $admin = CCUser::IsAdmin() ? 1 : 0;

    // here's the intent:
    // is_collab_owner - the current user looking at this page owns this collab project
    // is_collab_member - the current user looking at this page is a member 
    // is_owner_record - this record is the owner
    $sql =<<<EOF
SELECT 
    user_real_name, user_name, collab_user_role, collab_user_credit, collab_user_confirmed, 
    CONCAT( '$urlp', user_name ) as artist_page_url, 
    IF( collab_user = $me OR $admin, 1, 0 ) as is_collab_owner,
    IF( collab_user_user = $me OR $admin, 1, 0 ) as is_collab_member,
    IF( collab_user_role = 'owner', 1, 0 ) as is_owner_record,
    IF( collab_user_confirmed = 0 AND (collab_user_user = $me), 1, 0) as need_confirm_link
    %columns%
FROM cc_tbl_collab_users
JOIN cc_tbl_user ON collab_user_user = user_id
JOIN cc_tbl_collabs ON collab_user_collab = collab_id
%joins%
%where%
%limit%
EOF;
    return array( 'sql' => $sql,
                   'name' => 'collab_users',
                   'e'  => array( ), 
                );
}
[/dataview]

*/
?>
<!-- template collab_users -->

%loop(records,R)%
<div class="user_line" id="_user_line_%(#R/user_name)%">
  <div class="user"><a class="cc_user_link" href="%(#R/artist_page_url)%">%(#R/user_real_name)%</a></div>
  <div class="role">%(#R/collab_user_role)% <span id="_confirm_label_%(#R/user_name)%">
    %if_not_null(#R/collab_user_confirmed)%
       (%text(str_collab_confirmed)%)
     %else%
       (%text(str_collab_not_confirmed)%) 
            %if_not_null(#R/need_confirm_link)%
                &nbsp;<div id="confirm_link"><a href="javascript://confirm" id="_confirm_%(#R/user_name)%" class="user_cmd confirm_user">
                           <span>%text(str_collab_confirm_membership)%</span></a></div>
             %end_if%
     %end_if%
     </span></div>
  <div class="credit" id="_credit_%(#R/user_name)%">%(#R/collab_user_credit)%</div>
  %if_not_null(#R/is_collab_owner)%
      <div><a href="javascript://edit credit" id="_user_credit_%(#R/user_name)%" class="user_cmd edit_credit small_button">
        <span>%text(str_collab_credit2)%</span></a></div>
      %if_null(#R/is_owner_record)%
          <div><a href="javascript://remove user" id="_user_remove_%(#R/user_name)%" class="user_cmd remove_user small_button">
            <span>%text(str_collab_remove2)%</span></a></div>
      %end_if%
  %end_if%
  %if_not_null(is_collab_member)%
      <div><a href="javascript://contact" id="_contact_%(#R/user_name)%" class="user_cmd edit_contact  small_button">
        <span>%text(str_collab_send_email)%</span></a> </div>
  %end_if%
</div>
%end_loop%