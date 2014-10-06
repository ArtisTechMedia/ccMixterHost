<?
$collab = $A['collab'];
$C = $collab['collab'];
$collab_id = $C['collab_id'];
?>
<!-- template collab_view -->
<style>
#inner_content {
    width: 85%;
    margin: 0px auto;
}
</style>
<link  rel="stylesheet" type="text/css" href="%url('css/collab.css')%" title="Default Style"></link>
<fieldset>
<legend class="dark_bg light_color">%text(str_info)%</legend>
    %if_not_null(#collab/is_owner)%
    <div style="float:right">
        <a class="cc_gen_button" href="%(home-url)%collab/edit/%(#collab_id)%"><span>%text(str_collab_edit)%</span></a>
    </div>
    %end_if%

   %if_null(#C/collab_confirmed)%
    <div  class="cc_collab_desc light_bg dark_border" style="margin:0.5em auto;width:60%;text-align:center;">%text(str_collab_list_when_conf)%</div>
   %end_if%

    <div  class="cc_collab_by">
        %text(str_collab_created_by)%: <a href="%(home-url)%people/%(#C/user_name)%">%(#C/user_real_name)%</a> 
        %(#C/collab_date)%
   </div>
   <div  class="cc_collab_desc light_bg dark_border">%(#C/collab_desc)%</div>
</fieldset>

 <div  class="cc_collab_fields">
 <fieldset >
 <legend class="dark_bg light_color" >%text(str_artists)%</legend>
    <div  id="user_lines" class="user_lines">
    </div>
    <div  id="invite_container">
    </div>
</fieldset>

<fieldset>
<legend class="dark_bg light_color" >%text(str_files)%</legend>
    <div  class="file_list" id="file_list">
    </div>
    %if_not_null(#collab/is_member)%
       <iframe  style="display:none;" name="upload_frame"></iframe>
        <form  target="upload_frame" 
                enctype="multipart/form-data" 
                action="<?= $A['home-url']?>collab/upload/file/%(#collab_id)%" 
                method="post" id="upform" name="upform">%text(str_collab_upload_file)%: 
            <?
                // suck out the form types so we have a notion of what
                // types an upload might be...

                $_c_forms = cc_get_config('submit_forms');
                print '<select  name="uptype" id="uptype">';
                foreach($_c_forms as $_c_form)
                {
                    if( !$_c_form['enabled'] ) 
                        continue;
                    $tags = $_c_form['tags'];
                    if( is_array($tags) )
                        $tags = join(',',$tags);
                    $name = $_c_form['submit_type'];
                    print "<option value=\"{$tags}\">" . substr($T->String($name),0,10) . "</option>\n";
                }
                print '</select>';
             ?>
        <input type="file" id="upfile" name="upfile"></input> %text(str_collab_name)%: <input  name="upname" id="upname" type="text"></input><select  name="lic" id="lic">
        %loop(#collab/lics,lic)%
            <option  value="%(#lic/license_id)%">%(#lic/license_name)%</option>
        %end_loop%</select>
        <button  id="fileok">%text(str_collab_ok)%</button>
        </form>
        <div  id="upcover" style="position:absolute;display:none;" class="light_bg"> 
        <img  style="margin-left:45%" src="%url('images/spinner.gif')%" /></div>
    %end_if%
</fieldset>
%if_not_null(#collab/is_member)%
<fieldset>
<legend class="dark_bg light_color" >%text(str_conversation)%</legend>
    <p >%text(str_collab_this_conv)%:</p>
    <?= cc_query_fmt('noexit=1&nomime=1&f=html&t=collab_thread&ord=ASC&type=collab&match='.$collab_id); ?>
    <div class="c_commands">
        <a href="%(home-url)%collab/topic/add/%(#collab_id)%" class="cc_gen_button" id="collab_topic_add" ><span>%text(str_collab_add_topic)%</span></a>
    </div>
</fieldset>
%end_if%       
</div><!-- collab_fields -->

<script  src="%url('js/autocomp.js')%" type="text/javascript" ></script>
<script  src="%url('js/collab.js')%"   type="text/javascript" ></script>
<script type="text/javascript">
var cu = new ccCollab('%(#collab_id)%','%(#collab/is_member)%','%(#collab/is_owner)%');
cu.updateFiles();
cu.updateUsers();

function upload_done(upload_id,msg)
{
  $('upcover').style.display = 'none';
  if( upload_id > 0)
  {
    cu.updateFiles('%(#collab_id)%');
    ccPopupManager.ShowMessage('message','%text(str_collab_upload_succeeded)%',4.0);
  }
  else
  {
    ccPopupManager.ShowMessage('error','%text(str_collab_upload_failed)%: ' + (msg || '???'),5.0);
  }
}
</script>
