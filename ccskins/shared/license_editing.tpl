<!-- template license_editing -->
%macro(license_choice)%
<table>
    %loop(field/license_choice,L)%
        <tr>
            <td><img  class="cc_license_image" src="%(#L/license_img_big)%" /></td>
            <td><input type="radio" checked="%(#L/license_checked)%" name="upload_license" value="%(#L/license_id)%"
                  id="%(#L/license_id)%"></input><label for="%(#L/license_id)%">%(#L/license_text)%</label>
            </td>
        </tr>
    %end_loop%
</table>
%end_macro%

