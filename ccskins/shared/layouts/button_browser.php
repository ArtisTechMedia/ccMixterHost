<?
/*
[meta]
    type  = button_style
    desc  = _('Browser button')
[/meta]    
*/


$A['end_script_text'][] = 'cc_browser_buttons();';

?>
<style type="text/css">
button._bsb {
	font-size: 9px;
}
</style>
<script type="text/javascript" >
function cc_browser_buttons()
{
    CC$$('.cc_gen_button').each( function( e ) {
        var text = e.innerHTML;
        var href = e.href;
        e.href = 'javascript://';
        e.innerHTML = '<button onclick="document.location.href=\'' + href +'\'">'+text+'</button>';
    } );
    CC$$('.small_button').each( function( e ) {
        var text = e.innerHTML;
        var href = e.href;
        e.href = 'javascript://';
        e.innerHTML = '<button class="_bsb" onclick="document.location.href=\'' + href +'\'">'+text+'</button>';
    } );
}
</script>
