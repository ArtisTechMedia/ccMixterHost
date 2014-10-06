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
* $Id: playerembed.js 10445 2008-07-09 16:28:34Z fourstones $
*
*/


var ccEPlayer = null;

var ccEmbeddedPlayer = Class.create();

ccEmbeddedPlayer.prototype = {

    flash:           null,
    flashOK:         false,
	playing:         false,
    paused:          false,
    sliderPos:       0,         // mouse tracking
    volumePos:       50,
    currButton:      null,
    draggingVol:     false,
    playlist:        [],
    playlist_cursor: 0,
    bump_done:       false,
    currSongPos:     0,        // player tracking
    lastSongPos:     0, 

    initialize: function( options, flashMajorVersion ) {
        
        this.flashOK = flashMajorVersion >= 8;

        this.options = {

            doBumpCount: true,               // true means call back server for every Play
            bumpMinimum: 25,                 // percentage of song play before calling bump

            // classes applied and assumed

            cPlayer: 'cc_player_controls',        // wrapper for controls

            cButton: 'cc_player_button',          // generic button (other button classes are added to this)
            cHear:   'cc_player_hear',            // speaker button
            cPlay:   'cc_player_play',            // used for play/pause button
            cPause:  'cc_player_pause',           // used for play/pause button
            cStop:   'cc_player_stop',            // added to 'hear' button during play
            cPrev:   'cc_player_prev',            // for playlist button
            cNext:   'cc_player_next',            // for playlist button

            cVol:    'cc_player_volume',          // volume slider
            cKnob:   'cc_player_knob',            // volume slider knob
            cPos:    'cc_player_pos',             // position slider
            cPosBk:  'cc_player_pos_bk',          // position slider background
            cSlider: 'cc_player_slider',          // current position slider 
            cVolumeHover: 'cc_player_knob_hover', // class added to volume knob hovering
            pos_msg:   str_loading,

            // hehavoir mod
                                                  // there are two different set of controls:
                                                  // one for individual buttons and one for
                                                  // 'global' playlist controls.
            controls: null,
            showVolume: true,                     // applies only to indv. buttons
            showProgress: true,                   // applies only to indv. buttons
            autoHook: true,                       // 'true' means page is already rendered 

            plc_controls: null,                   // global playlist controls
            plcc_id: null                         // playlist controls container

        };

        Object.extend( this.options, options || {} );

        try
        {
            if( this.flashOK )
            {
                this.flash = window.uploadMovie;
                if( !this.flash )
                    this.flash = window.document.uploadMovie;
                this.flashOK = !!this.flash;
            }
        }
        catch (err)
        {
            alert('playerembed.js (1): ' +  err);
        }

        ccEPlayer = this;

        if( this.options.autoHook ) 
            this.hookElements();
    },

    hookElements: function(parent) {
        var me = this;
        return CC$$('.' + this.options.cButton, parent).inject( [], function(arr, e) {
                if( e.href.match(/\.mp3$/) )
                {
                    var  href;
                    if( me.flashOK )
                    {
                        href = e.href;
                        Event.observe( e, 'click', me.onPlayClick.bindAsEventListener(me,e.href) );
                        e.href = "javascript://play";
                    }
                    else
                    {
                        href = e.href = query_url + 'f=m3u&ids=' + e.id.match(/[0-9]+$/);
                    }

                    arr[ arr.length ] = [ e.id, href ];
                }
                return arr;
            }); 
    },

    _create_pl_controls: function() {
        if( !this.options.plcc_id || $(this.options.plcc_id + '_player') )
            return;

        if( !this.options.plc_controls )
        {
            // used for a 'global' volume and slider controls...

            this.options.plc_controls =
                  '<div id="#{player_id}" class="#{global_player_class}" >' +
                      '<a href="javascript://prev" class="#{prev_button_class} #{button_class}" id="#{prev_id}" > </a>' +
                      '<a href="javascript://next" class="#{next_button_class} #{button_class}" id="#{next_id}" > </a>' +
                      '<div class="#{vol_class}" id="#{vol_id}" >' +
                            '<div class="#{knob_class}" id="#{knob_id}"></div>' +
                      '</div>' +
                      '<div class="#{pos_class}" id="#{pos_id}" >' +
                            '<div class="#{slider_class}" id="#{slider_id}"></div>' +
                      '</div>' +
                  '</div>';
        }

        this._create_controls( this.options.plcc_id, this.options.plc_controls, true );
    },

    _create_indv_controls: function(id) {
        if( !this.options.controls )
        {
            this.options.controls = 
                    '<div id="#{player_id}" class="#{player_class}">' +
                          '<a class="#{button_class} #{play_class}" href="javascript://stop" id="#{pause_id}"> </a> ';

            if( this.options.showVolume )
            {
                this.options.controls +=
                      '<div class="#{vol_class}" id="#{vol_id}" >' +
                            '<div class="#{knob_class}" id="#{knob_id}"></div>' +
                      '</div>';
            }

            if( this.options.showProgress )
            {
                this.options.controls +=
                          '<div class="#{pos_class}" id="#{pos_id}" >' +
                                '<div class="#{slider_class}" id="#{slider_id}"></div>' +
                          '</div>'; 
            }

            this.options.controls += '</div>';
        }

        this._create_controls( id, this.options.controls, false );
    },

    _create_controls: function(id, controls, place_in) {
        if( !controls )
            return;

        var me = this;
        
        var vars = {
            play_class:    me.options.cPlay,

            player_class:  me.options.cPlayer,
            player_id:     id + '_player',
            button_class:  me.options.cButton,
            pause_id:      id + '_pause',
            vol_class:     me.options.cVol,
            vol_id:        id + '_vol',
            knob_class:    me.options.cKnob,
            knob_id:       id + '_knob',
            pos_class:     me.options.cPos,
            pos_id:        id + '_pos',
            pos_bk:        me.options.cPosBk,
            pos_bk_id:     id + '_posbk',
            pos_msg:       me.options.pos_msg,
            slider_class:  me.options.cSlider,
            slider_id:     id + '_slider',
            

            prev_button_class: me.options.cPrev,
            prev_id:           id + '_prev',
            next_button_class: me.options.cNext,
            next_id:           id + '_next'
        };

        var html = new Template( controls ).evaluate( vars );
        if( place_in )
        {
            $(id).innerHTML = html;
        }
        else
        {
            new Insertion.After(id, html );
            Position.clone(id,vars.player_id,{offsetLeft: 40, setWidth: false,setHeight: false});
        }

        Event.observe( vars.player_id, 'click', this.onPlayerClick.bindAsEventListener(this) );

        if( $(vars.pause_id) )
            Event.observe( vars.pause_id,  'click', this.onPauseClick.bindAsEventListener(this) );

        if( $(vars.next_id) )
            Event.observe( vars.next_id,  'click', this.Next.bind(this) );

        if( $(vars.prev_id) )
            Event.observe( vars.prev_id,  'click', this.Prev.bind(this) );

        var pos = $(vars.pos_id);
        if( pos )
        {
            Event.observe( pos,  'click',     this.onSliderClick.bindAsEventListener(this) );
            Event.observe( pos,  'mousemove', this.onSliderHover.bindAsEventListener(this));
        }

        var vol = $(vars.vol_id);
        if( vol )
        {
            Event.observe( vol,  'mousedown',  this.onVolumeDown.bindAsEventListener(this));
            Event.observe( vol,  'mousemove',  this.onVolumeHover.bindAsEventListener(this), true);
            Event.observe( vol,  'mouseup',    this.onVolumeStop.bindAsEventListener(this));
            Event.observe( vol,  'mouseover',  this.onVolumeIn.bindAsEventListener(this));
            Event.observe( vol,  'mouseout',   this.onVolumeOut.bindAsEventListener(this));
        }
    },

    onPlayClick: function(e,href) {
        this.playlist_cursor = this._cursor_for(href);
        this.Play( Event.element(e), href );
        Event.stop(e);
    },

    _cursor_for: function(href) {
        if( !this.playlist.length )
            return 0;
        var i;
        for( i = 0; i < this.playlist.length; i++ )
            if( this.playlist[i][1] == href )
                return i + 1;
        return 0;
    },

    Play: function( element, href ) {
        var id = element.id;
        if( !$(id + '_player') )
            this._create_indv_controls(id);
        this._create_pl_controls();
        var prevCurrButton = this.currButton;
        this._stop_element();
        if( element != prevCurrButton )
        {
            this.currButton = element;
            this._start_element(href);
        }
    },

    onPlayerClick: function(e) {
        // stop bubbling past here
        Event.stop(e);
    },

    onPauseClick: function(e) {
        var element = Event.element(e), i = this.paused ? 1 : 0, cls = [ this.options.cPlay, this.options.cPause ];
        Element.removeClassName( element, cls[ i ] );
        Element.addClassName( element, cls[ i ^ 1 ] );
        this.paused = !this.paused;
        this.flash.ccPlayPause();
    },

    _show_player: function(style) {
        var player_id = this.currButton.id + '_player';
        if( $(player_id) )
            $(player_id).style.display = style;
        if( this.options.plcc_id )
        {
            player = $(this.options.plcc_id);
            if( player )
                player.style.display = style;
        }
    },

    _start_element: function(song) {
        Element.removeClassName( this.currButton, this.options.cHear );
        Element.addClassName( this.currButton, this.options.cStop );
        this.playing = true;
        this.paused  = false;
        this._show_player('block');
        this.resetBump();
        this.flash.ccPlaySong(song);
        this._set_vol_num();
    },

    _stop_element: function() {
        if( !this.currButton )
            return;
        Element.removeClassName( this.currButton, this.options.cStop );
        Element.addClassName( this.currButton, this.options.cHear );
        var pause = $(this.currButton.id + '_pause');
        if( pause && Element.hasClassName( pause, this.options.cPause ) )
        {
            Element.removeClassName( pause, this.options.cPause );
            Element.addClassName( pause, this.options.cPlay );            
        }
        this._show_player('none');
        this.playing = false;
        this.paused  = false;
        this.flash.ccStop();
        this.currButton = null;
    },

    songDone: function() {
        var id = this.currButton.id;
        var player_id = id + '_player';
        this._stop_element(player_id);
        if( this.playlist.length )
            this._play_next();
    },


    _set_fill_val: function(id,ax) {
        if( !$(id) || isNaN(ax) )
            return null;
        var val = Math.floor(ax);
        var intval = val;
        if( val < 1 )
            val = '1px';
        else
            val += 'px';
        $(id).style.width = val;
        return intval;
    },

    _get_base_id: function(postfix) {
        if( this.options.plcc_id )
            return this.options.plcc_id + postfix;
        return this.currButton.id + postfix;
    },

    onSongFill: function(ax) {
        this._set_fill_val(this._get_base_id('_pos'), ax);
    },

    onSongPos: function (ax) {
        this.currSongPos = this._set_fill_val(this._get_base_id('_slider'), ax);
        if( this.options.doBumpCount && !this.bump_done )
        {
            if( this.currSongPos == (this.lastSongPos + 1) )
            {
                if( this.currSongPos > this.options.bumpMinimum )
                {
                    this.bump_done = true;
                    this.doBump(this.currButton.id);
                }
            }
            this.lastSongPos = this.currSongPos;
        }
    },

    resetBump: function() {
        this.bump_done = false;
        this.currSongPos = this.lastSongPos = 0;
    },

    doBump: function(id) {
        var url = home_url + 'api/playlist/bump/' + id;
        new Ajax.Request( url, { method: 'get' } );
    },

    onVolumeDown: function(e) {
        this.draggingVol = true;
    },

    onVolumeStop: function(e) {
        if( this.draggingVol )
            this._set_vol(e);
        this.draggingVol = false;
    },

    onVolumeHover: function(e) {
        if( this.draggingVol )
            this._set_vol(e);
    },

    onVolumeIn: function(e) {
        var knob = $(this._get_base_id('_knob'));
        Element.addClassName( knob, this.options.cVolumeHover );
    },

    onVolumeOut: function(e) {
        var knob = $(this._get_base_id('_knob'));
        Element.removeClassName( knob, this.options.cVolumeHover );
    },

    _set_vol: function(e) {
        var vol_id = this._get_base_id('_vol');
        this.volumePos = Event.pointerX(e) - this._find_pos( $(vol_id) );
        this._set_vol_num();
    },

    _set_vol_num: function() {
        this._set_fill_val( this._get_base_id('_knob'), this.volumePos );
        this.flash.ccsetVolume( this.volumePos * 2 );
    },


    onSliderHover: function(e) {
        this.sliderPos = Event.pointerX(e) - this._find_pos( Event.element(e) );
    },

    onSliderClick: function(e) {
		this.flash.ccsetPos(this.sliderPos); 
    },

    _find_pos: function(obj) {
        var xy = Position.cumulativeOffset( obj );
        return xy[0];
    },

    /* playlist funcs */


    Next: function() {
        if( this.playlist.length )
            this._play_next();
    },

    _play_next: function() {
        if( this.playlist_cursor < this.playlist.length )
        {
            var pl_item = this.playlist[ this.playlist_cursor++ ];
            this.Play( $(pl_item[0]), pl_item[1] );
        }
    },

    Prev: function() {
        if( this.playlist.length )
            this._play_prev();
    },

    _play_prev: function() {
        if( this.playlist_cursor > 1 )
        {
            this.playlist_cursor -= 2;
            this._play_next();
        }
    },

    SetPlaylist: function(pl,overwrite) {
        try
        {
            if( !this.playlist.length || overwrite )
            {
                if( typeof pl == 'string' )
                    pl = eval(pl);
                this.playlist = pl;
            }
        }
        catch (err)
        {
            alert( 'playerembed.js (2): ' +  err );
        }
    },

    StartPlaylist: function() {
        this.playlist_cursor = 0;
        this._play_next();
    }


}

function SongDone() {
    ccEPlayer.songDone();
}

function setouter(ax){    
    ccEPlayer.onSongFill(ax);
}

function setpos(ax){
    ccEPlayer.onSongPos(ax);
}

