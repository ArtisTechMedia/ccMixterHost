/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: collab.js 10445 2008-07-09 16:28:34Z fourstones $
*
*/

ccCollab = Class.create();

ccCollab.prototype = {

    collab_id: null,
    autoComp: null,
    userCredit: null,
    userContact: null,
    is_member: false,
    is_owner: false,

    initialize: function(collab,is_member,is_owner) {
        this.collab_id = collab;
        this.is_member = is_member;
        this.is_owner = is_owner;
        if( is_member )
        {
            var pickFunk = this.onUserPick.bind(this);
            this.autoComp =  new ccAutoComplete( {  url: home_url + 'browse' + q + 'user_lookup=', onPick: pickFunk } );
            var container = $('invite_container');
            container.innerHTML = this.autoComp.genControls( 'collab_user', '', str_collab_invite );
            this.autoComp.hookUpEvents();
            if( $('fileok') )
                Event.observe( 'fileok', 'click', this.onFileSubmitOK.bindAsEventListener(this) );
        }
    },

    updateFiles: function() {
        var url = home_url + 'collab/upload/update/' + this.collab_id ;
        new Ajax.Request( url, { method: 'get', onComplete: this._req_updatefiles.bind(this) } );
    },

    _req_updatefiles: function( resp ) {
        var flist = $('file_list');
        flist.innerHTML = resp.responseText;
        var me = this;
        CC$$('.file_remove',flist).each( function(a) {
            var id = a.id.match(/[0-9]+$/);
            Event.observe( a, 'click', me.onUploadRemove.bindAsEventListener(me,id) );
        });
        CC$$('.file_publish',flist).each( function(a) {
            var id = a.id.match(/[0-9]+$/);
            Event.observe( a, 'click', me.onUploadPublish.bindAsEventListener(me,id) );
        });
        CC$$('.file_tags',flist).each( function(a) {
            var id = a.id.match(/[0-9]+$/);
            Event.observe( a, 'click', me.onUploadTags.bindAsEventListener(me,id) );
        });
    },

    updateUsers: function() {
        var url = home_url + 'collab/users/' + this.collab_id ;
        new Ajax.Request( url, { method: 'get', onComplete: this._req_updateusers.bind(this) } );
    },

    _req_updateusers: function( resp ) {
        var ulines = $('user_lines');
        ulines.innerHTML = resp.responseText;
        var me = this;

        CC$$('.remove_user',ulines).each( function(e) {
            var username = e.id.match(/remove_(.+)$/)[1];
            Event.observe( e, 'click', me.onUserRemove.bindAsEventListener(me,username) );
        });

        CC$$('.edit_credit',ulines).each( function(e_credit) {
            var username = e_credit.id.match(/_credit_(.+)$/)[1];
            Event.observe( e_credit, 'click', me.onUserCredit.bindAsEventListener(me,username) );
        });

        CC$$('.edit_contact',ulines).each( function(e_contact) {
            var username = e_contact.id.match(/_contact_(.+)$/)[1];
            Event.observe( e_contact, 'click', me.onUserContact.bindAsEventListener(me,username) );
        });

        CC$$('.confirm_user',ulines).each( function(e_confirm) {
            var username = e_confirm.id.match(/_confirm_(.+)$/)[1];
            Event.observe( e_confirm, 'click', me.onUserConfirm.bindAsEventListener(me,username) );
        });
    },

    onFileSubmitOK: function( e ) {
        ccPopupManager.StartThinking(e);
        Position.clone( $('upform') , $('upcover'));
        $('upcover').style.display = 'block';
        $('upform').submit();            
    },

    onUploadPublish: function( e, id ) {
        ccPopupManager.StartThinking(e);
        var url = home_url + 'collab/upload/' + this.collab_id + '/' + id + '/publish';
        new Ajax.Request( url, { method: 'get', onComplete: this._req_publishupload.bind(this) } );
    },

    _req_publishupload: function( resp, json ) {
        if( !json.err )
        {
            $('_pubtext_' + json.upload_id).innerHTML = json.published ? str_collab_hide : str_collab_publish;
        }
    },

    onUploadRemove: function( e, id ) {
        ccPopupManager.StartThinking(e);
        var url = home_url + 'collab/upload/' + this.collab_id + '/' + id + '/remove';
        new Ajax.Request( url, { method: 'get', onComplete: this._req_removeupload.bind(this) } );
    },

    _req_removeupload: function( resp, json ) {
        if( !json.err )
        {
            $('_file_line_' + json.upload_id).remove();
        }
    },

    onUploadTags: function(e, id) {
        this.closeContact();
        this.closeCredit();
        if( this.uploadTags && (this.uploadTags != id) )
            this.closeTags();
        var file_line = $("_file_line_" + id);
        this.uploadTags = id;
        var tags = $('_user_tags_' + id ).innerHTML;
        // style="position:absolute;background:white;padding: 10px;"
        var html = '<div id="tags_editor" >'+str_collab_tags_label+
                      ': <input type="text" id="tags_edit" value="' + tags +
                     '" /> <a class="small_button" href="javascript://ok tags" id="ok_tags"><span>'+str_ok+'</span></a> ' +
                     '<a class="small_button" href="javascript://ok edit" id="cancel_tags"><span>'+str_cancel+'</span></a></div>';
        new Insertion.Before(file_line,html);
        //Position.clone( file_line, $('tags_editor'), { setHeight: false, setWidth: false } ); // 
        file_line.style.display = 'none';
        this.okTagsWatcher = this.onTagsOk.bindAsEventListener(this,id);
        this.cancelTagsWatcher = this.onTagsCancel.bindAsEventListener(this,id);
        Event.observe( 'ok_tags',     'click', this.okTagsWatcher );
        Event.observe( 'cancel_tags', 'click', this.cancelTagsWatcher );
    },

    onUserCredit: function(e, user_name) {
        this.closeContact();
        this.closeTags();
        if( this.userCredit && (this.userCredit != user_name) )
            this.closeCredit();
        var user_line = $("_user_line_" + user_name);
        var credit = $("_credit_" + user_name);
        this.userCredit = user_name;
        this.userCreditValue = credit.innerHTML;
        var text = str_collab_enter_role.replace(/%s/,user_name);
        var html = '<div id="credit_editor">'+text+': <input type="text" id="credit_edit" value="' + this.userCreditValue +
                     '" /> <a class="small_button"href="javascript://ok edit" id="ok_edit"><span>'+str_ok+'</span></a> ' +
                     '<a class="small_button"href="javascript://ok edit" id="cancel_edit"><span>'+str_cancel+'</span></a></div>';
        new Insertion.Before(user_line,html);
        user_line.style.display = 'none';
        this.okWatcher = this.onCreditOk.bindAsEventListener(this,user_name);
        this.cancelWatcher = this.onCreditCancel.bindAsEventListener(this,user_name);
        Event.observe( 'ok_edit',     'click', this.okWatcher );
        Event.observe( 'cancel_edit', 'click', this.cancelWatcher );
    },

    onUserContact: function(e, user_name) {
        this.closeTags();
        this.closeCredit();
        if( this.userContact && (this.userContact != user_name) )
            this.closeContact();
        var user_line = $("_user_line_" + user_name);
        var credit = $("_credit_" + user_name);
        this.userContact = user_name;
        text = str_collab_send_mail_to.replace(/%s/,user_name);
        var html = '<div id="contact_editor">'+text+':<br /><textarea style="width:60%;height:35px;" id="contact_edit"></textarea>' +
                     '<a class="small_button" href="javascript://contact ok" id="ok_contact"><span>'+str_ok+'</span></a> ' +
                     '<a class="small_button" href="javascript://contact cancel" id="cancel_contact"><span>'+str_cancel+'</span></a></div>';
        new Insertion.Before(user_line,html);
        user_line.style.display = 'none';
        this.okContactWatcher     = this.onContactOk.bindAsEventListener(this,user_name);
        this.cancelContactWatcher = this.onContactCancel.bindAsEventListener(this,user_name);
        Event.observe( 'ok_contact',     'click', this.okContactWatcher );
        Event.observe( 'cancel_contact', 'click', this.cancelContactWatcher );
    },

    closeTags: function() {
        if( this.uploadTags ) {
            Event.stopObserving( 'ok_tags',     'click', this.okTagsWatcher );
            Event.stopObserving( 'cancel_tags', 'click', this.cancelTagsWatcher );
            $('tags_editor').remove();
            $("_file_line_" + this.uploadTags).style.display = 'block';
            $("_file_line_" + this.uploadTags).style.visibility = '';
            this.uploadTags = null;
        }
    },

    closeContact: function() {
        if( this.userContact ) {
            Event.stopObserving( 'ok_contact',     'click', this.okContactWatcher );
            Event.stopObserving( 'cancel_contact', 'click', this.cancelContactWatcher );
            $('contact_editor').remove();
            $("_user_line_" + this.userContact).style.display = 'block';
            this.userContact = null;
        }
    },

    closeCredit: function() {
        if( this.userCredit ) {
            Event.stopObserving( 'ok_edit',     'click', this.okWatcher );
            Event.stopObserving( 'cancel_edit', 'click', this.cancelWatcher );
            $('credit_editor').remove();
            $("_user_line_" + this.userCredit).style.display = 'block';
            this.userCredit = null;
        }
    },

    onTagsOk: function(e, id) {
        ccPopupManager.StartThinking(e);
        var value = $('tags_edit').value;
        var url = home_url + 'collab/upload/tags/' + this.collab_id + '/' + id + '?tags=' + value;
        new Ajax.Request( url, { method: 'get', onComplete: this._req_tagsupload.bind(this) } );
        this.closeTags();
    },


    onCreditOk: function(e, user_name) {
        ccPopupManager.StartThinking(e);
        var value = $('credit_edit').value;
        var url = home_url + 'collab/user/' + this.collab_id + '/' + user_name + '/credit?credit=' + value;
        new Ajax.Request( url, { method: 'get', onComplete: this._req_credituser.bind(this) } );
        this.closeCredit();
    },

    onContactOk: function(e, user_name) {
        var url = home_url + 'collab/user/' + this.collab_id + '/' + user_name + '/contact';
        new Ajax.Request( url, { method: 'post', 
                                 parameters: 'text=' + $('contact_edit').value,
                                 onComplete: this._req_contactuser.bind(this) } );
        this.closeContact();
    },

    _req_contactuser: function(resp,json) {
    },

    _req_credituser: function(resp,json) {
        if( !json.err )
        {
            $("_credit_" + json.user_name).innerHTML = json.credit;
        }
    },

    _req_tagsupload: function(resp,json) {
        if( !json.err )
            $("_user_tags_" + json.upload_id).innerHTML = json.user_tags;
    },

    onCreditCancel: function(e, user_name) {
        this.closeCredit();
    },

    onContactCancel: function(e, user_name) {
        this.closeContact();
    },

    onTagsCancel: function(e, user_name) {
        this.closeTags();
    },

    onUserConfirm: function( e, user_name ) {
        ccPopupManager.StartThinking(e);
        var url = home_url + 'collab/user/' + this.collab_id + '/' + user_name + '/confirm';
        new Ajax.Request( url, { method: 'get', onComplete: this._req_confirmuser.bind(this) } );
    },

    _req_confirmuser: function( resp, json ) {
        if( !json.err )
        {
            $('confirm_link').remove();
            $('_confirm_label_' + json.user_name).innerHTML = str_collab_confirmed;
        }
    },


    onUserRemove: function( e, user_name ) {
        ccPopupManager.StartThinking(e);
        var url = home_url + 'collab/user/' + this.collab_id + '/' + user_name + '/remove';
        new Ajax.Request( url, { method: 'get', onComplete: this._req_removeuser.bind(this) } );
    },

    _req_removeuser: function( resp, json ) {
        if( !json.err )
        {
            this.closeCredit();
            this.closeContact();
            $('_user_line_' + json.user_name).remove();
        }
    },

    onUserPick: function( ac, elem, value ) {
        //ccPopupManager.StartThinking();
        var url = home_url + 'collab/user/' + this.collab_id + '/' + value + '/add';
        new Ajax.Request( url, { method: 'get', onComplete: this._req_adduser.bind(this) } );
        return true;
    },

    msg: function( text, type ) {
        ajax_msg(type,text);
    },
 
    _req_adduser: function( resp, json ) {
        if( !json.err )
        {
            this.updateUsers();
            $(this.autoComp.options.editID).value = '';
            this.autoComp._list_close(); 
        }
    }
}
