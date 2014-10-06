
%macro(upload_history_line)%
  %if_not_null(records)%
      %map(#RH,records/0)%
      <li>
         <? if( !empty($RH['user_id']) )   { $A['all_users'][] = $RH['user_id']; } 
            if( !empty($RH['upload_id']) ) { $A['all_ids'][]   = $RH['upload_id']; } ?>
         <p>"<a href="%(#RH/file_page_url)%">%(#RH/upload_name)%</a>" %text(str_by)%
            <a href="%(#RH/artist_page_url)%">%(#RH/user_real_name)%</a></p>
         <p><a href="%(#RH/license_url)%"><img src="%(#RH/license_logo_url)%" /></a><br />
              <a href="%(#RH/license_url)%">%(#RH/license_name)%</a></p>
         %if_not_null(#RH/pool_name)%
            <p>%text(str_pool_from)%: <a href="%(#RH/pool_site_url)%">%(#RH/pool_name)%</a></p>
            <p>%text(str_external_link)%: <a class="cc_external_link" href="%(#RH/pool_item_url)%"><span>%chop(#RH/pool_item_url,44)%</span> 
            <img src="%url(images/remote.gif)%" /></a></p>
         %end_if%
         <ul class="histogram history_parent">
            %loop(#RH/remix_parents,P)%
                <?
                    $old_recs = $A['records'];
                    if( !empty($P['pool_item_id']) ) // this is pool item
                    {
                        $A['records'] = cc_query_fmt('dataview=pool_item_history_list&ids='.$P['pool_item_id']);
                    }
                    else
                    {
                        $A['records'] = cc_query_fmt('dataview=upload_histogram&ids='.$P['upload_id']);
                    }
                    $T->Call('upload_history.tpl/upload_history_line');
                    $A['records'] = $old_recs;
                ?>
            %end_loop%
         </ul>          
      </li>
  %end_if%      
%end_macro%
