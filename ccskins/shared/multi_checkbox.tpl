<!-- template multi_checkbox -->
<div class="multi_checkbox">
%loop(field/options,opt)%
   <div><input type="checkbox" checked="%(#opt/checked)%"
             name="%(field/name)%[%(#k_opt)%]" value="%(#opt/value)%"
              id="%(field/name)%[%(#k_opt)%]"></input>
              <label for="%(field/name)%[%(#k_opt)%]">%(#opt/text)%</label>
    </div>
%end_loop%
</div>
