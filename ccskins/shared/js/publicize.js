/*
Creative Commons has made the contents of this file
available under a CC-GNU-GPL license:

 http://creativecommons.org/licenses/GPL/2.0/

 A copy of the full license can be found as part of this
 distribution in the file LICENSE.TXT

You may use the ccHost software in accordance with the
terms of that license. You agree that you are solely 
responsible for your use of the ccHost software and you
represent and warrant to Creative Commons that your use
of the ccHost software will comply with the CC-GNU-GPL.

$Id: publicize.js 10379 2008-07-03 09:01:28Z fourstones $
*/

ccPublicize = Class.create();

ccPublicize.prototype = {

    is_preview: true,
    preview: null,
    target_text: null,
    src_preview: null,
    username: '',

    initialize: function(username) {

        this.preview     = $('preview');
        this.target_text = $('target_text');
        this.src_preview = $('src_preview');
        this.username    = username;
        var me = this;

        $$('.queryparam').each( function(e) {
                Event.observe( e, 'change', me.updateTarget.bindAsEventListener(me) );
        });

        if( $('usertypechanger') )
        {
            Event.observe( 'usertypechanger', 'change', me.updateUserType.bindAsEventListener(me) );
            this.updateUserType();
        }

        if( $('preview_button_link') )
            Event.observe( 'preview_button_link', 'click', me.togglePreview.bindAsEventListener(me) );
    },

    updateTarget: function(){
        this.set_p(str_loading);
        var url = query_url + Form.serialize('puboptions_form');
        var curr_template = decodeURIComponent(url.match(/template=([^&]+)&/)[1]);
        var is_embed = embed_templates.include(curr_template);
        if( is_embed )
        {
           this.target_text.value = '';
        }
        else
        {
           var text = '<' + 'script type="text/javascript" src="' + url + '&format=docwrite" ><' + '/script>';
           this.target_text.value = text;
        }
        var me = this;
        new Ajax.Request( url + '&format=html', { method: 'get', onComplete: me.resp_updateTarget.bind(me,is_embed) } );    
    },

    set_p: function(t) {
       if( this.preview.innerHTML )
            this.preview.innerHTML = t;
       else if( this.preview.innerText )
            this.preview.innerText = t;
       else
           alert('wups');
    },

    resp_updateTarget: function(is_embed,req) {
        var text = req.responseText;
        this.set_p(text);
        text = text.replace(/&_cache_buster=[0-9]+/,'');
        this.src_preview.innerHTML = text.escapeHTML();
        if( is_embed )
            this.target_text.value = text.replace(/&_cache_buster=[0-9]+/,'');
    },

    updateUserType: function() {

        var box = $('usertypechanger');
        var text = '';
        switch( box.selectedIndex )
        {
            case 1:
                text = '<' + 'input type="hidden" name="remixesof" value="' + this.username + '" />';
                break;
            case 0:
                text = '<' + 'input type="hidden" name="tags" value="remix" />';
                // fall thru:
            case 2:
                text += '<' + 'input type="hidden" name="user" value="' + this.username + '" />';
                break;
        }

        $('type_target').innerHTML = text;

        this.updateTarget();
    },

    togglePreview: function() {

        this.is_preview = !this.is_preview;
        Element.show($( this.is_preview ? 'preview' : 'src_preview' ));
        Element.hide($( this.is_preview ? 'src_preview' : 'preview' ));
        Element.show($( this.is_preview ? 'preview_warn' : 'html_warn' ));
        Element.hide($( this.is_preview ? 'html_warn' : 'preview_warn' ));
        $('preview_button').innerHTML = this.is_preview ? seeHTML : showFormatted;
    }
}

