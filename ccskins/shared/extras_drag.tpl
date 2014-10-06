
%macro(macros)%
    %map(mac_target,field/mac_target)%
    %map(mac_map, field/mac_map)%
    <div style="margin-left:25%"><b>%text(Available Sidebar Extras)%</b></div>
    <style>
    table.flat_form td.form_label { width: 5px; }
    .ddex { list-style: none; padding: 0px; }
    .ddex li { cursor: move; padding: 4px; margin: 5px; border: 1px solid #999}
    #targetmacros { border:1px solid black; }
    </style>
    <div style="width:50%;margin:0px auto;">
        <ul id="draglistmacros" class="ddex">
          %loop(field/mac_source,x)%
            <li id="%(#k_x)%">%(#x)%</li>
          %end_loop%
        </ul>
    </div>
    <span id="macros_go_here">&nbsp;</span>
%end_macro%
