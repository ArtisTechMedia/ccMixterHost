%%
[meta]
    type = template_component
    page_title = _('Edit System Licenses')
    desc = _('Edit System Licenses')
    breadcrumbs = home,global,page_title
    dataview = pass_thru
    access = admin
[/meta]    
%%
<?

$A['lics'] = CCDatabase::QueryRows('SELECT * FROM cc_tbl_licenses ORDER by license_id ASC' );

$lic_edit = ccl('admin','licenses');
$submit_admin = ccl('admin','submit');

?>
<div id="license_menu">
<table id="license_menu_table">
%loop(lics,L)%
    <tr>
        <? $urlsafe = urlencode(urlencode($L['license_id'])); ?>
        <td  style="padding-bottom:11px" ><a class="small_button" href="%(#lic_edit)%/%(#urlsafe)%">Edit</a></td>
        <td>%(#L/license_name)%</td>
    </tr>
%end_loop%
</table>
<p>
   <a class="small_button" href="%(#lic_edit)%">Add a New License</a>
</p>
<p>
  To enabled these licenses in submit forms click here: <a class="small_button" href="%(#submit_admin)%">Manage Submit Forms</a>
</p>
<p>
  To offer users alternative licenses to Public Domain waivers click here: <a class="small_button" href="%(home-url)%admin/waiver">Configure Upgrade Alternatives</a>
</p>
</div>
