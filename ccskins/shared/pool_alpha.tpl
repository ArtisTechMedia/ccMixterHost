<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template pool_alpha -->
<div id="pool_filter"><span id="pool_filter_label">&nbsp;</span> <div id="pool_target"></div></div>

<script type="text/javascript">
ccPoolAlpha = Class.create();

ccPoolAlpha.prototype = {

    thisPool: null,
    selTag: null,
    baseUrl: null,

    initialize: function(pool_id,sel_tag) {
        this.thisPool = pool_id;
        this.selTag = sel_tag;
        var url = home_url + 'pools/pool_hook/alpha/' + pool_id;
        var me = this;
        new Ajax.Request( url, { method: 'get', onComplete: me.onPoolAlpha.bind(me) } );
    },

    onPoolAlpha: function(resp) {
        try {
            if( !resp.responseText ) 
                return;
            var vals = eval(resp.responseText);
            $('pool_filter_label').innerHTML = '%text(str_pool_filter)%' + ': ';
            var html = '<select id="pool_alphas"><option value="">' + str_filter_all + '</option>';
            var me = this;
            vals.each( function(c) {
                html += '<option value="' + c + '"';
                if( me.selTag == c )
                    html += ' selected="selected" ';
                html += '>' + c + '</option>';
            });
            html += '</select>';
            $('pool_target').innerHTML = html;
            Event.observe('pool_alphas','change',me.onPoolAlphaChange.bindAsEventListener(me));
            this.baseUrl = vals.base_url;
        } catch( e ) {
            alert(e);
        }
    },

    onPoolAlphaChange: function() {
        var sel = $('pool_alphas');
        window.location.href = home_url + 'pools/pool/' + this.thisPool + '/' + sel.options[sel.selectedIndex].value;
    }
}

new ccPoolAlpha('%(pool_id)%','%(pool_alpha_char)%');
</script>
