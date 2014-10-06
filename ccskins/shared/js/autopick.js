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

$Id: autopick.js 10390 2008-07-04 04:37:05Z fourstones $

*/
/******************************************
*
*  Autopicker
*
*******************************************/
ccAutoPick = Class.create();

ccAutoPick.prototype = {

    is_showing: false,
    selected_id: null,
    selected: [],
    line_count: 0,
    scrolling_on: false,
    options: [],

    initialize: function(options) {
        this.options = Object.extend( { selClass: 'cc_autocomp_selected',
                                        pickClass: 'cc_autocomp_picked',
                                        lineTag: 'p',
                                        borderClass: 'cc_autocomp_border',
                                        showButtonClass: 'cc_autocomp_show small_button',
                                        clearButtonClass: 'cc_autocomp_clear small_button'
                                       },
                                      options );
    },

    requestList: function() {
        if( !this.list_filled )
        {
            var url = this.options.url;
            this.list_filled = true;
            new Ajax.Request( url, { onComplete: this._reponse_lookup.bind(this),
                                     method: 'get' } );
        }
    },

    hookUpEvents: function() {
        try
        {            
            Event.observe( this.options.listID, 'click',     this.onListClick.bindAsEventListener(this) );
            Event.observe( this.options.listID, 'mouseover', this.onListHover.bindAsEventListener(this) );
            Event.observe( this.options.listID, 'keyup',     this.onListKey.bindAsEventListener(this) );
            Event.observe( this.options.showID, 'click',     this.onShowClick.bindAsEventListener(this) );
            Event.observe( this.options.clearID, 'click',    this.onClearClick.bindAsEventListener(this) );

            $(this.options.listID).style.display = 'none';

        }
        catch (e)
        {
            alert( 'autopick.js (1)' + e);
        }

    },

    genControls: function(id,value,pre_text) {
        this.options.listID = list_id = '_ap_list_' + id;
        this.options.statID = stat_id = '_ap_stat_' + id;
        this.options.showID = show_id = '_ap_show_' + id;
        this.options.clearID = clear_id = '_ap_clear_' + id;
        this.options.targetID = id;
        this.options.pre_text = pre_text;

        var clear_display = 'none';
        if( value )
        {
            this.selected = value.split(/,\s?/);
            pre_text = this.selected.join(', ');
            clear_display = '';
        }
        else
        {
            value = '';
        }

        return '<table cellspacing="0" cellpadding="0" style="">' +
               '<tr><td>' +
                    '<span class="cc_autocomp_stat" id="' + stat_id + '"><i>' + pre_text + '</i></span> ' +
               '</td></tr>' +
               '<tr><td>'  + 
                   '<a class="'+this.options.showButtonClass+'" href="javascript://show list" id="' + show_id + '"><span>' + str_filter_show_list + 
                   '</a></span>&nbsp;' +
                   '<a class="'+this.options.clearButtonClass+'" style="display:'+clear_display+'" href="javascript://clear list" id="' + clear_id + 
                    '"><span>' + str_filter_clear + '</span></a></td></tr>' +
               '<tr><td><input type="hidden" name="' + id + '" id="' + id + '" value="' + value + '" />' +
                    '<div class="cc_autocomp_list" id="' + list_id + '"><!-- --></div>' +
                    '<div style="border-left: 180px transparent solid;font-size: 2px;height:2px;"></div>' + 
               '</td></tr></table>';
    },

    onShowClick: function(e) {
        this.requestList();
        var link = $(this.options.showID);
        var list = $(this.options.listID);
        if( this.is_showing ) 
        {
            Effect.BlindUp(list);
            //list.style.display = 'none';
            this.is_showing = false;
            var t = this.options.str_filter_show_list || str_filter_show_list;
            link.innerHTML = '<span>' + t + '</span>';
        }
        else
        {
            list.style.opacity = '0.0';
            list.style.display = 'block';
            Effect.Appear(list);
            this.is_showing = true;
            var t = this.options.str_filter_hide_list || str_filter_hide_list;
            link.innerHTML = '<span>' + t + '</span>';
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
        var element = Event.element(e);
        if( element.id && (element.id.match(/_ac_/) != null) )
            this._pick_line(element);
    },

    onClearClick: function(e) {
        if( this.options.onPick )
        {
            this.options.onPick(this,element,'');
        }
        else
        {
            this.selected.each( function(tag)  {
                Element.removeClassName( '_ac_' + tag, this.options.pickClass );
            }.bind(this));
            this.selected.clear();
            this._update_elements();
            if( this.options.submitID )
                $(this.options.submitID).style.display = '';
        }
    },

    _pick_line: function( element ) {
        var value = $(element).id.replace('_ac_','');
        if( this.options.onPick )
        {
            this.options.onPick(this,element,value);
        }
        else
        {
            if( this.selected.include(value) )
            {
                Element.removeClassName( element, this.options.pickClass );
                this.selected = this.selected.reject( function(t) { return t == value; } );
            }
            else
            {
                Element.addClassName( element, this.options.pickClass );
                this.selected.push( value );
            }
            
            this._update_elements();
            if( this.options.submitID )
                $(this.options.submitID).style.display = '';
        }
    },

    _update_elements: function() {
        var disp = this.selected.length == 0 ? 'none' : '';
        $(this.options.clearID).style.display = disp;
        $(this.options.targetID).value = this.selected.join( ' ' );
        if( $(this.options.targetID).value )
            $(this.options.statID).innerHTML = this.selected.join( ', ' );
        else
            $(this.options.statID).innerHTML = '<i>' + this.options.pre_text + '</i>';
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

    onListKey: function(e) {
        switch( e.keyCode )
        {
            case Event.KEY_RETURN:
            {
                if( this.selected_id )
                {
                    this._pick_line(this.selected_id);
                    Event.stop(e);
                    return;
                }
            }
            case Event.KEY_ESC:
            {
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
    },

    _reponse_lookup: function( resp, json ) {
        try
        {
            if( !json )
                json = eval(resp.responseText);
            var target = $(this.options.listID);
            json = eval( resp.responseText );
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
            if( this.onDataReceived )
                this.onDataReceived();
            this.selected.each( function(tag)  {
                Element.addClassName( '_ac_' + tag, this.options.pickClass );
            }.bind(this));

        }
        catch (err)
        {
            alert( 'autopick.js (2)' + err + '  "' + resp.responseText + '"');
        }
    }
}
