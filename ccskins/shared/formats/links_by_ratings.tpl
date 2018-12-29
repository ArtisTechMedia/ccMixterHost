%%
[meta]
    type     = format
    desc     = _('Links to upload page w/ ratings, upload date')
    dataview = links_by_ratings
[/meta]
%%
<ul>
%loop(records,R)%
  <li>%(#R/upload_date)% [%(#R/upload_num_scores)%] [%(#R/score)%] [%(#R/temperature)%] <a href="%(#R/file_page_url)%">%chop(#R/upload_name,chop)%</a> %text(str_by)%
     <a href="%(#R/artist_page_url)%">%chop(#R/user_real_name,chop)%</a> 
  </li>     
%end_loop%
</ul>
