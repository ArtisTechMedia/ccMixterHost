<!-- template flash_popup_play -->
%if_not_null(flash_popup_infos)%
<script type="text/javascript">
var flashPopupHook = Class.create();

flashPopupHook.prototype = {

    initialize: function() {
        var infos = <?= cc_php_to_json($A['flash_popup_infos']); ?>;
        var me = this;
        infos.each( function( info ) {
            var e = $(info.id);
            if( e )
            {
                Event.observe( e, 'click', me.onClick.bindAsEventListener( me, info ) );
            }
        } );
    },

    onClick: function( e, info ) {
        if( info.url.indexOf('?') == -1 )
            info.url += '?popup=1';
        else
            info.url += '&popup=1';
        var url = query_url + 't=flash_popup&popup=1&url=' + info.url + '&h=' + info.h + '&w=' + info.w;
        var dim = "height=" + info.h + ",width=" + info.w ;
        var win = window.open( url, 'cchostextrawin', "status=1,toolbar=0,location=0,menubar=0,directories=0," +
                                      "resizable=1,scrollbars=1," + dim );
        win.title = info.title;

    }
}
</script>
<? $A['end_script_text'][] = "\nnew flashPopupHook();\n"; ?>
%end_if%
