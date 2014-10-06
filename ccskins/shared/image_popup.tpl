<!-- template image_popup -->
%if_not_null(image_popup_infos)%
<script type="text/javascript">
var imagePopupHook = Class.create();

imagePopupHook.prototype = {

    initialize: function() {
        var infos = <?= cc_php_to_json($A['image_popup_infos']); ?>;
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
        var url = info.url;
        var dim = "height=" + info.h + ",width=" + info.w ;
        var win = window.open( url, 'cchostimagepop', "status=1,toolbar=0,location=0,menubar=0,directories=0," +
                                      "resizable=1,scrollbars=1," + dim );
        win.title = info.title;
    }
}
</script>
<? $A['end_script_text'][] = "\nnew imagePopupHook();\n"; ?>
%end_if%
