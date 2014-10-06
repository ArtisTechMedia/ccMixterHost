<? if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
%macro(form_fields)%
<table class="form_table flat_form" cellspacing="0" cellpadding="0">
    %loop(curr_form/html_form_fields,F)%
        %if_not_empty(#F/form_error)%
            <tr class="form_error_row"><td></td><td class="form_error">%text(#F/form_error)%</td></tr>
        %end_if%
        <tr class="form_row" id="%(#k_F)%_field_row">
        <td  class="form_label">
            %if_not_null(#F/label)%<div>%text(#F/label)%</div>%end_if%
            %if_not_null(#F/form_tip)%<span>%text(#F/form_tip)%</span>%end_if%</td>
        <td class="form_element">%if_not_null(#F/macro)% %map(field,#F)%%call(#F/macro)%%end_if%%(#F/form_element)%</td></tr>
    %end_loop%
</table>
%end_macro%

%macro(horizontal_form_fields)%
<table class="form_table horizontal_form" cellspacing="0" cellpadding="0">
    <tr class="form_row" >
    %loop(curr_form/html_form_fields,F)%
        <td  class="form_element" id="%(#k_F)%_field_row">
            %if_not_null(#F/label)%<div>%text(#F/label)%</div>%end_if%
            %if_not_null(#F/form_tip)%<span>%text(#F/form_tip)%</span>%end_if% 
            %if_not_null(#F/macro)% %map(field,#F)%<!-- -->%call(#F/macro)% %end_if%<!-- -->%(#F/form_element)%</td>
    %end_loop%
    %if_not_null(curr_form/submit_text)%
        <td>
        <? $submit_text = $T->String($A['curr_form']['submit_text']); ?>
        <input  type="submit" name="form_submit" id="form_submit" class="cc_form_submit" value="<?=$submit_text?>"></input>
        </td>
        <? $A['curr_form']['submit_text'] = null; ?>
    %end_if%
    </tr>
</table>
%end_macro%


%macro(stacked_form_fields)%
<table class="form_table stacked_form" cellspacing="0" cellpadding="0">
    %loop(curr_form/html_form_fields,F)%
        %if_not_empty(#F/form_error)%
            <tr class="form_error_row"><td class="form_error">%text(#F/form_error)%</td></tr>
        %end_if%
        <tr class="form_row" id="%(#k_F)%_field_row">
        <td>
             <div class="form_label">%text(#F/label)%</div>
             %if_not_null(#F/form_tip)%<span>%text(#F/form_tip)%</span>%end_if%
             <div class="form_element">
                %if_not_null(#F/macro)%
                   %map(field,#F)%
                   %call(#F/macro)%
                %end_if%
                %(#F/form_element)%</div>
         </td></tr>
    %end_loop%
</table>
%end_macro%

%macro(fieldset_form_fields)%
    %loop(curr_form/html_form_fields,F)%
    <fieldset class="form_fieldset" id="%(#k_F)%_field_row">
        %if_not_empty(#F/label)%
            <legend class="form_label med_bg light_color">%text(#F/label)%</legend>
        %end_if%
        %if_not_empty(#F/form_error)%
            <div class="form_error_row"><div class="form_error">%text(#F/form_error)%</div></div>
        %end_if%
        %if_not_null(#F/form_tip)%<span>%text(#F/form_tip)%</span>%end_if%
        <div class="form_element">
           %if_not_null(#F/macro)%
                %map(field,#F)%
                %call(#F/macro)%
            %end_if%
            %(#F/form_element)%</div>
    </fieldset>
    %end_loop%
%end_macro%

%macro(flat_grid_form_fields)%
<table class="grid_form_table" id="table_%(curr_form/form_id)%" cellspacing="0" cellpadding="0">
<tr class="grid_form_header_row">
%loop(curr_form/html_form_grid_columns,C)%
  <th class="grid_form_header">%text(#C/column_name)%</th>
%end_loop%
</tr>
%loop(curr_form/html_form_grid_rows,R)%
   %if_not_null(#R/form_error)%
      <tr class="form_error_row"><td></td><td class="form_error">%text(#R/form_error)%</td></tr>
   %end_if%
   <tr class="form_row">
   %loop(#R/html_form_grid_fields,F)%
     <td class="form_element" id="%(#k_F)%_field_row">
       %if_not_null(#F/macro)%  %map(field,#F)% <!-- -->%call(#F/macro)% %end_if%
       <!-- -->%(#F/form_grid_element)%
     </td>
   %end_loop%
   </tr>
%end_loop%
</table>
%map(post_form_goo,'form_fields.tpl/post_flat_grid_form')%
%end_macro%

%macro(grid_form_fields)%
<table class="cc_2by_grid_form_table" id="table_%(curr_form/form_id)%" cellspacing="0" cellpadding="0">
<tr>
<td class="cc_2by_grid_names med_bg" id="names_%(curr_form/form_id)%">
    %loop(curr_form/html_form_grid_rows,R)%
       <div id="dmit_%(#i_R)%" class="med_bg dark_border"><a href="javascript://menu item" class="menu_item_title light_color" id="mit_%(#i_R)%" >%(#R/name)%</a></div>
    %end_loop%
</td>
<td class="cc_2by_grid_fields light_bg" id="fields_%(curr_form/form_id)%">
%map(#C,curr_form/html_form_grid_columns)%
%loop(curr_form/html_form_grid_rows,FR)%
    <div id="%(curr_form/form_id)%_%(#i_FR)%" style="display:none">
   %loop(#FR/html_form_grid_fields,F)%
        <div class="f">%if_not_null(#F/form_grid_element)%<span class="col"><?= $C[$i_F-1]['column_name'] ?></span>%end_if%
           %if_not_null(#F/macro)%  %map(field,#F)% <!-- -->%call(#F/macro)% %end_if%
           <!-- -->%(#F/form_grid_element)%
        </div>
        <div class="gform_breaker"></div>
   %end_loop% 
   </div>
%end_loop%
</td>
</tr>
%if_not_null(curr_form/html_add_row_caption)%
<tr class="form_add_row"><td colspan="2">
    <a href="javascript://add a row" id="%(curr_form/form_id)%_adder">%(curr_form/html_add_row_caption)%</a>
</td></tr>
%end_if%
</table>
<script type="text/javascript"> var %(curr_form/form_id)%_editor = new ccGridEditor('%(curr_form/form_id)%'); 
%if_not_null(curr_form/stuffer_script)%
%(curr_form/form_id)%_editor.PostStufferScript = %(curr_form/stuffer_script)%;
%end_if%
</script>
%map(post_form_goo,'form_fields.tpl/post_grid_form')%
%end_macro%

%macro(post_grid_form)%
    %if_empty(curr_form/html_meta_row)%
        %return%
    %end_if%
<div style="display:none">
%loop(curr_form/html_meta_row,meta)%
<div id="%(curr_form/form_id)%_meta_%(#i_meta)%">%(#meta)%</div>
%end_loop%
</div><!-- meta rows -->
<script type="text/javascript">
%(curr_form/form_id)%_editor.cols = [
    %loop(curr_form/html_form_grid_columns,C)% '%(#C/column_name)%'%if_not_last(#C)%, %end_if% %end_loop%
    ];

</script>
%end_macro%

%macro(post_flat_grid_form)%
    %if_empty(curr_form/html_meta_row)%
        %return%
    %end_if%
<div style="display:none">
%loop(curr_form/html_meta_row,meta)%
<div id="%(curr_form/form_id)%_meta_%(#i_meta)%">%(#meta)%</div>
%end_loop%
</div><!-- meta rows -->
%if_not_null(curr_form/html_add_row_caption)%
    <button  onclick="do_add_row(); return false;">%(curr_form/html_add_row_caption)%</button>
%end_if%
<script type="text/javascript">
function do_add_row()
{
    add_flat_row( '%(curr_form/form_id)%', 
                   %(curr_form/html_form_grid_num_rows)%,
                   %(curr_form/html_form_grid_num_cols)%
                );
}
</script>
%end_macro%

%macro(select)%
<select id="%(field/name)%" name="%(field/name)%" %if_attr(field/class,class)%>
%if_not_null(field/value)%
    %map(#selval,field/value)%
%else%
    %map(#selval,'')%
%end_if%
%loop(field/options,opt)%
    <? $selected = ($k_opt == $selval) ? 'selected="selected"' : ''; ?>
    <option value="%(#k_opt)%" %(#selected)%>
        %text(#opt)%
    </option>
%end_loop%
</select>
%end_macro%

%macro(multi_checkbox)%
<div %if_attr(field/class,class)%>
%map(#opts,field/options)%
%if_not_null(field/value)%
    %map(#selval,field/value)%
%else%
    %map(#selval,'')%
%end_if%
%if_not_null(field/cols)%
    %map(#cols,field/cols)%
%else%
    %map(#cols,1)%
%end_if%
<? $chunks = array_chunk($opts,count($opts)/$cols,true); ?>
<table>
<tr>
%loop(#chunks,chunk)%
    <td>
        %loop(#chunk,opt)%
           <? $selected = strpos(','.$selval.',', ','.$k_opt.',') === false ? '' : 'checked="checked"'; ?>
           <div><input type="checkbox" %(#selected)%
                     name="%(field/name)%[%(#k_opt)%]" 
                      id="%(field/name)%[%(#k_opt)%]"></input>
                      <label for="%(field/name)%[%(#k_opt)%]">%text(#opt)%</label>
            </div>
        %end_loop%
    </td>
%end_loop%
</tr>
</table>
</div>
%end_macro%


<?

function _suck_out_grid_row_name(&$row)
{
    foreach( $row['html_form_grid_fields'] as $field )
    {
        $html = $field['form_grid_element'];
        if( strstr($html,'checkbox') || strstr($html,'select') || strstr($html,'radio'))
            continue;
        if( preg_match('/value="([^"]+)"/',$html,$m) )
            return $m[1];
    }

    return 'hmmmm';
}

?>
