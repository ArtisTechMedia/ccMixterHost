<link rel="stylesheet"  type="text/css" title="Default Style" href="%url(css/access.css)%" />
<script type="text/javascript" src="%url(js/access.js)%" /></script>
<script>
function updateGroup(e)
{
    var gid = e.id.match(/[0-9]+$/);
    if( prevGroup )
    {
        pc = $('acc_knob_' + prevGroup);
        Element.classNames(pc).remove('acc_knob_selected');
        pc = $('acc_group_' + prevGroup);
        Element.classNames(pc).remove('acc_group_selected');
    }
    if( !prevGroup || (Number(prevGroup) != Number(gid)) )
    {
        pc = $('acc_knob_' + gid);
        Element.classNames(pc).add('acc_knob_selected');
        pc = $('acc_group_' + gid);
        Element.classNames(pc).add('acc_group_selected');
        prevGroup = gid;
    }
    else
    {
        prevGroup = null;
    }
    return false;
}

var prevGroup = null;

</script>

<div id="acced">
   %loop(field/access_map,g)%
   <div class="acc_head" id="acc_head_%(#i_g)%" onclick="return updateGroup(this);">
     <div class="acc_knob" id="acc_knob_%(#i_g)%">&nbsp;</div>
    %(#k_g)%
   </div>
   <div class="acc_div" id="acc_group_%(#i_g)%">
       <table class="acc_table"  cellspacing="0" cellpadding="0">
       %loop(#g,m)%
         <tr><td class="acc_url"><?= $m->url ?></td><td><?= $m->opts ?></td></tr>
         <tr><td colspan="2" class="acc_desc" ><?= $m->ds ?></td></tr>
        %end_loop%
      </table>
  </div>
  %end_loop%
</div>

