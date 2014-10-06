<div class="box" style="width:50%">
%text(str_openid_contact_disc)%
<ul>
%loop(contact_info,CI)%
<li><a href="%(#CI)%" class="cc_openid_link">%(#CI)%</a></li>
%end_loop%
</ul>
</div>
