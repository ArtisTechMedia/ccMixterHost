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

$Id: autocomp.js 10390 2008-07-04 04:37:05Z fourstones $

*/
/******************************************
*
*  Autocomplete
*
*******************************************/
ccAutoComplete = Class.create();

ccAutoComplete.prototype = {

    selected_id: null,
    line_count: 0,
    scrolling_on: false,
    options: [],

    initialize: function(options) {
        this.options = Object.extend( { selClass: 'cc_autocomp_selected',
                                        lineTag: 'p',
                                        borderClass: 'cc_autocomp_border' },
                                      options );
    },

    hookUpEvents: function() {
        Event.observe( this.options.editID, 'keydown',     this.onKey.bindAsEventListener(this) );
        Event.observe( this.options.listID, 'click',     this.onListClick.bindAsEventListener(this) );
        Event.observe( this.options.listID, 'mouseover', this.onListHover.bindAsEventListener(this) );
        Event.observe( this.options.listID, 'keyup',     this.onListKey.bindAsEventListener(this) );
    },

    genControls: function(id,value,pre_text) {
        this.options.editID = edit_id = '_ac_edit_' + id;
        this.options.listID = list_id = '_ac_list_' + id;
        this.options.statID = stat_id = '_ac_stat_' + id;
        this.options.targetID = id;

        value = value ? value : '';
        
        return '<table cellspacing="0" cellpadding="0">' +
               '<tr><td><span id="' + stat_id + '"><i>' + pre_text + '</i></span></td></tr>' +
               '<tr><td><input autocomplete="off" class="cc_autocomp_edit" type="text" name="' + edit_id + '" id="' + 
                      edit_id + '" value="' + value + '" /></td></tr>' +
               '<tr><td><input type="hidden" name="' + id + '" id="' + id + '" value="' + value + '" />' +
                      '<div class="cc_autocomp_list" id="' + list_id + '"><!-- --></div></td></tr></table>';
    },

    _pick_line: function( element ) {

        var value = $(element).id.replace('_ac_','');
        if( this.options.onPick )
            if( this.options.onPick(this,element,value) )
                return;
        $(this.options.editID).value = '';
        $(this.options.statID).innerHTML = $(element).innerHTML;
        $(this.options.targetID).value = value;

        this._list_close(); 
    },

    _list_close: function() {
        var e = $(this.options.listID);
        e.innerHTML = '';
        e.style.overflow = '';
        e.style.height = 'auto';
        Element.removeClassName( e, this.options.borderClass );
        this.selected_id = null;
        this.scrolling_on = false;
    },

    _line_select: function( dir ) {
        var lines = $(this.options.listID).getElementsByTagName(this.options.lineTag);
        if( lines.length == 0 )
            return;
        var prevLine = null, waiting = false, newSelLine = null, firstLine = null, i = 0;
        for ( i = 0 ; i < lines.length ; i++ ) {
            var line = lines[i];
            if( !firstLine ) 
                firstLine = line;
            if( waiting )
            {
                newSelLine = line;
                break;
            }
            if( Element.hasClassName( line, this.options.selClass ) )
            {
                if( dir == -1 )
                {
                    if( prevLine )
                    {
                        newSelLine = prevLine;
                        break;
                    }
                }
                else
                {
                    waiting = true;
                }
            }
            prevLine = line;
        }

        if( !newSelLine ) {
            if( !this.selected_id && firstLine ) {
                newSelLine = firstLine;
            }
        }
        if( newSelLine && (newSelLine.id != this.selected_id) ) {
            if( this.selected_id )
                Element.removeClassName( this.selected_id, this.options.selClass );
            Element.addClassName( newSelLine, this.options.selClass );
            this.selected_id = newSelLine.id;
        }
    },

    onListHover: function(e) {
        var element = Event.element(e);
        if( element.id == this.options.listID )
            return;
        if( element.id && (element.id.match(/_ac_/) != null) )
        {
            if( this.selected_id )
                Element.removeClassName( this.selected_id, this.options.selClass );
            this.selected_id = element.id;
            Element.addClassName( this.selected_id, this.options.selClass );
        }
    },

    onListClick: function(e) {
        this._pick_line(this.selected_id);
    },

    onListKey: function(e) {
        switch( e.keyCode )
        {
            case Event.KEY_RETURN:
            {
                $D( 'on-listkey: ' + e.keyCode);
                if( this.selected_id )
                {
                    this._pick_line(this.selected_id);
                    Event.stop(e);
                    return false;
                }
            }
        }
    },
 
    onKey: function(e) {
        switch( e.keyCode )
        {
            case Event.KEY_RETURN:
            {
                $D( 'on-key: ' + e.keyCode);
                if( this.selected_id )
                {
                    this._pick_line(this.selected_id);
                }
                Event.stop(e);
                return false;
            }

            case Event.KEY_ESC:
            {
                $(this.options.editID).value = '';
                this._list_close();
                return;
            }

            case Event.KEY_UP:
            {
                this._line_select( -1 );
                return;
            }

            case Event.KEY_DOWN:
            {
                this._line_select( 1 );
                return;
            }
        }
        var val;
        if( this.options.getVal )
            val = this.options.getVal(this.options.editID);
        else
            val = $(this.options.editID).value;
        var target_id = this.options.listID;
        if( !target_id._init_for_lookup )
        {
            // put init (Absolutize?) here
            target_id._init_for_lookup = true;
        }

        if( val.length == 0 )
        {
            this._list_close();
        }
        else
        {
            if( val.length > 1 )
            {
                var url = this.options.url + val;
                new Ajax.Request( url, { onComplete: this._reponse_lookup.bind(this,target_id),
                                         method: 'get' } );
            }
        }
    },

    _reponse_lookup: function( target_id, resp, json ) {
        try
        {
            if( !json )
                json = eval(resp.responseText);
            var target = $(target_id);
            this.line_count = json.count;
            if( json.count > 10 )
            {
                target.style.overflow = 'scroll';
                target.style.height = '170px';
                Element.addClassName( target, this.options.borderClass );
                this.scolling_on = true;
            }
            else
            {
                if( json.count == 0 )
                {
                    this._list_close()
                }
                else
                {
                    Element.addClassName( target, this.options.borderClass );
                    target.style.overflow = '';
                    target.style.height = 'auto';
                    this.scolling_on = false;
                }
            }

            target.innerHTML = json.html;
        }
        catch (err)
        {
            alert( 'autocomp.js (1)' + err );
        }
    }
}
