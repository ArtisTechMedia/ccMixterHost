
ccSkinEditor = Class.create();

ccSkinEditor.prototype = {

    id: 0,
    currentPick: '',

    initialize: function(classname,id,currPick) {
        this.id = id;
        var me = this;
        $$('.' + classname).each( function(e)  {
            Event.observe(e,'click',me.onItemPick.bindAsEventListener(me,e));
        });
        this.currentPick = $(currPick);
        Element.addClassName(this.currentPick,'med_bg');
    },

    onItemPick: function(event,e) {
        if( this.currentPick )
        {
            Element.removeClassName(this.currentPick,'med_bg');
        }
        this.currentPick = e;
        Element.addClassName(e,'med_bg');
        $(this.id).value = e.ref;
    }
}

