<? if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>

<style>
.logviewer {
    height: 30em;
    overflow: scroll;
    margin-bottom: 2em;
    width: 90%;
    border: 1px solid #444;
}
#log_archive_cntr, #error_archive_cntr {
    float: right;
    padding-right: 10%;
}
</style>

<h1>Log Files</h1>

<div id="log_archive_cntr"><a class="small_button" id="log_archive" href="javascript://json stewart"><span>archive</span></a></div>
<div><b>LOG</b></div>
<div class="logviewer" id="log"></div>

<div id="error_archive_cntr"><a class="small_button" id="error_archive" href="javascript://json crowley"><span>archive</span></a></div>
<div><b>ERRORS</b></div>
<div class="logviewer" id="error"></div>

<script>

new Ajax.Updater( $('log'),   home_url + 'admin/logs/log',    {method: 'get'} );
new Ajax.Updater( $('error'), home_url + 'admin/logs/error',  {method: 'get'} );

Event.observe( $('log_archive'), 'click', 
    function() { new Ajax.Updater( $('log_archive_cntr'), home_url + 'admin/logs/archive/log', {method:'get'}) });
Event.observe( $('error_archive'), 'click', 
    function() { new Ajax.Updater( $('error_archive_cntr'), home_url + 'admin/logs/archive/error', {method:'get'}) });

</script>

