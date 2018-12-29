%macro(debot_submit)%
<script type="text/javascript">
function swap_bot_name()
{
    $('search_flag').value = 'notabot';
    return true;
}
Event.observe('%(field/form_id)%','submit', swap_bot_name );

</script>
%end_macro%
