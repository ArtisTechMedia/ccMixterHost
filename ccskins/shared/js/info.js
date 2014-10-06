
ccUploadInfo = Class.create();

ccUploadInfo.prototype = {

    initialize: function() {
    },

    hookInfos: function(class_i,parent) 
    {
        var me = this;
        CC$$(class_i,parent).each( function(pli) {
            var upload_id = pli.id.match(/[0-9]+$/);
            Event.observe( pli, 'click', me.onInfoClick.bindAsEventListener( me, upload_id ) );
        });            
    },

    onInfoClick: function(event, upload_id ) 
    {
        var info_id = '__plinfo__' + upload_id;
        if( $(info_id) )
        {
            ccPopupManager.reopenPopupOrCloseIfOpen(event,info_id);
        }
        else
        {
            // these coordinates are relative to <body> tag...
            var y = (Event.pointerY(event) + 12), x = (Event.pointerX(event) - 50);
            
            var html = '<div class="info_popup" id="' + info_id + '" ' +
                       'style="opacity:0.0;position:absolute;height:auto;top:'+y+'px;left:'+x+'px"></div>';
                       
            // so we insert the div directly into the top of it, otherwise
            // it would be positioned relative to the first parent into the
            // tree that is not position:static
            new Insertion.Top(document.body,html);
            
            ccPopupManager.userClickDataFetch(event,info_id);
            var url = query_url + 'f=html&t=info&ids=' + upload_id;
            new Ajax.Request( url, { method: 'get', onComplete: this._resp_info.bind(this, info_id ) } );
        }
        return false;
    },

    _resp_info: function( info_id, resp ) {
        var info = $(info_id);
        info.innerHTML = resp.responseText;
        var x = (document.body.offsetWidth/2) - (info.offsetWidth/2);
        if( x < 0 )
            x = 100;
        info.style.left = x + 'px';
        ccPopupManager.dataFetchedOpenPopup(info_id);
    }

}
