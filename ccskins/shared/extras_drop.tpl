<div class="ddex">
  <p style="padding:3px;">
    <b>Sidebar Extras In Use</b>
    <br />
    <span style="font-weight:normal"> Drag items to here...</span>
  </p>
  %if_empty(mac_target)%
        <ul id="targetmacros" class="ddex" style="height:14px"></ul>
  %else%
      <ul id="targetmacros" class="ddex">
        %loop(mac_target,x)%
            <li id="%(#k_x)%">%(#x)%</li>
        %end_loop%
      </ul>
  %end_if%
</div>
<script type="text/javascript">

function on_macros_drop(a,b)
{
    $('targetmacros').style.height = "";
}

Sortable.create("draglistmacros",
 {dropOnEmpty:true,containment:['draglistmacros',"targetmacros"],constraint:false});
Sortable.create("targetmacros",
 {dropOnEmpty:true,containment:['draglistmacros',"targetmacros"],constraint:false,onUpdate: on_macros_drop});

function extras_submit(evt)
{
    var mac_map = [];
    %loop(mac_map,x)%
       mac_map[%(#k_x)%] = "%(#x)%";
    %end_loop%

    var html = '';
    Sortable.sequence('targetmacros').each( function(m) {
       html += '<input type="hidden" name="macros['+m+']" value="' + mac_map[m] + '" >';
        });
    $('macros_go_here').innerHTML = html;
}

Event.observe(window,'load',function(e) {
       Event.observe('extrasform','submit',extras_submit);
    } );

</script>
