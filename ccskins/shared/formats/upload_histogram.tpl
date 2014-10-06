
%%
[meta]
    type     = template_component
    desc     = _('Upload sample history')
    dataview = upload_histogram
    require_args = ids
[/meta]
%%
<style>
table#history_table td {
    vertical-align: top;
}
ul.histogram {
    list-style: none;
}
ul.histogram li {
    margin: 4px;
    border-top: 1px solid green;
}
ul.history_parent li {
  background: url(%url(images/reply-arrow.gif)%) top left no-repeat;
  padding-left: 25px;
}
ul.history_users {
    list-style: none;
}
ul.history_users li {
    border: 1px solid blue;
    padding: 4px;
    margin: 4px;
}
ul.history_users li img {
    float: left;
    margin: 4px;
}

ul.history_users li .history_user_breaker {
    clear: left;
}
div.history_user_info {
    position: relative;
}
.history_contact {
    position:absolute;
    bottom: 5px;
    right: 5px;
}
</style>
<h1>Derivation History for "%(records/0/upload_name)%"</h1>

<?
    $A['all_users'] = array();
    $A['all_ids']   = array();
?>
<table id="history_table">
  <tr>
  <td>
    <ul class="histogram">
        %call('upload_history.tpl/upload_history_line')%
    </ul>
  </td>
  <td>
  <?
      $all_users = join(',',array_unique($A['all_users']));
      $users = cc_query_fmt('dataview=user_basic&ids='.$all_users);
      $qplayurl = $A['query-url'] . 'f=docwrite&t=mplayerbig.xml&ids='.join('+',array_unique($A['all_ids']));
  ?>
   <script src="%(#qplayurl)%" type="text/javascript"></script>
   <ul class="history_users">
    %loop(#users,U)%
       <li>
         <div class="history_user_info">
          <img src="%(#U/user_avatar_url)%" />
          <a href="%(#U/artist_page_url)%">%(#U/user_real_name)%</a>
          %if_not_null(#U/user_homepage)%
            <br /><a href="%(#U/user_homepage)%">%(#U/user_homepage)%</a>
          %end_if%
          <div class="history_contact"><img src="%url(images/shareicons/email.gif)%" /> 
            <a class="small_button contact_button" href="%(home-url)%people/contact/%(#U/user_name)%">
                %text(str_contact)%</a> 
          </div>
          <br class="history_user_breaker" />
         </div>
       </li>
    %end_loop%
   </ul>
  </td>
  </tr>
</table>
<script type="text/javascript" >
    new popupHook( CC$$('.contact_button'), { width: 500 } );  
</script>
