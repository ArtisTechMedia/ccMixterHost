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
* $Id: playlist.js 12523 2009-05-03 15:47:02Z fourstones $
*
*/


/************************************************
*
*  Playlist popup menu for page listing
*
*************************************************/
ccPlaylistMenu = Class.create();

ccPlaylistMenu.prototype = {

    initialize: function(options) {
        this.options = Object.extend( { autoHook: true }, options || {} );
        this.infos = new ccUploadInfo();
        if( this.options.autoHook )
            this.hookElements();
    },

    hookElements: function(parent) {
        var me = this;
        CC$$('.cc_playlist_button',parent).each( function(e) {
            var id = e.href.match( /([0-9]+$)/ )[1];
            Event.observe( e, 'click', me.onMenuButtonClick.bindAsEventListener( me, id ) );
        });

        this.infos.hookInfos('.info_button',parent);
    },

    onMenuButtonClick: function(event,id) {

        var pid = 'pl_menu_' + id;
        if( $(pid) )
        {
            if( $(pid).needRefresh ) 
            {
                this._refresh_menu( event, id, pid );
            }
            else
            {
                ccPopupManager.reopenPopupOrCloseIfOpen(event,pid);
            }
        }
        else
        {
            var element = Event.element(event);
            while( element.tagName != 'A' )
                element = element.parentNode;
            this._create_controls( event, element, pid, id );
        }
        return false;
    },

    _create_controls: function( event, link, pid, id ) {
        var pos = Prototype.Browser.IE ? '' : 'position:absolute;';
        var html = '<div id="' + pid + '" style="opacity:0.0;'+pos+'" class="cc_playlist_popup light_bg dark_border">&nbsp;</div>';
        new Insertion.After( link, html );
        var pp = $(pid);
        Position.clone( link, pid, {  setWidth:   false,
                                      setHeight:  false,
                                      offsetTop:  20,
                                      offsetLeft: 20 } );
        this._refresh_menu( event, id, pid );
    },

    _refresh_menu: function( event, id, pid ) {
        ccPopupManager.userClickDataFetch(event,pid);
        var url = home_url + 'api/playlist/with/' + id + q + 'f=html&m=playlist_popup'
        this.transport = new Ajax.Request( url, { method: 'get', onComplete: this._req_with.bind(this,pid) } );
    },

    _req_with: function( pid, resp ) {
        var e = $(pid);
        e.needRefresh = false;
        e.innerHTML = resp.responseText;
        var me = this;
        CC$$('.pl_menu_item',e).each( function( a ) {
            var url = a.href;
            a.href = 'javascript://playlist menu item';
            Event.observe( a, 'click', me.onMenuItemClick.bindAsEventListener( me, url, pid ) );
        });
        ccPopupManager.dataFetchedOpenPopup(pid);
    },

    onMenuItemClick: function( event, url, pid ) {
        var p = $(pid);
        ccPopupManager.userClickDataFetch(event,pid);
        new Ajax.Request( url, { method: 'get', onComplete: this._req_status.bind(this,pid) } );
        return false;
    },

    _req_status: function( pid, resp, json ) {
        $(pid).needRefresh = true;
        if( json.command )
            this.onJSONCommand(json);
    },
    
    /* 
    *
    * playlist stuff ... doesn't really belong here yet here it is... 
    *
    */

    onJSONCommand: function(json) {
        if( json.command = 'delete' )
        {
            var id = '_ep_' + json.cart_id + '_' + json.upload_id;
            var this_row = $(id);
            if( this_row )
            {
                while( !Element.hasClassName(this_row,'trr') )
                    this_row = this_row.parentNode;
                this_row.remove();
            }
        }
    }
}

/************************************************
*
*  Embedded playler manager
*
*************************************************/

ccPlayerMethods = {
    hook_playlist: function(playlist_id,parent) {
        var play_all = $('_pla_' + playlist_id); // comes from cc-playlist-browse.inc
        if( window.ccEPlayer && ccEPlayer.hookElements )
        {
            // this will hook all the 'play' buttons...
            var play_ids = ccEPlayer.hookElements(parent);

            // this only happens with a playlist 'header' with
            // a 'Play All' link
            if( play_all )
            {
                if( ccEPlayer.flashOK )
                {
                    play_all.href = 'javascript:// play all';
                    var ids = play_ids.inspect();
                    play_all._playlist = ids;
                    ccEPlayer.SetPlaylist(ids,false);
                    Event.observe( play_all, 'click', this.onPlayAll.bindAsEventListener(this) );
                }
                else
                {
                    var ids_str = '';
                    ids_str = play_ids.inject( ids_str, function(str,idarr) {
                        str += idarr[0].match(/[0-9]+$/) + ';';
                        return str;
                    });
                    play_all.href = query_url + 'f=m3u&nosort=1&ids=' + ids_str;
                }
            }

            // this only happens with a playlist 'header' with
            // a '` Window' link
            var play_win = $('_plw_' + playlist_id);
            if( play_win )
            {
                if( ccEPlayer.flashOK )
                    Event.observe( play_win, 'click', this.onPlayWin.bindAsEventListener(this,playlist_id) );
                else
                    play_win.style.display = 'none';
            }
        }
    },

    onPlayWin: function(event,playlist_id) {
        var qs  = location.search.substring(1);
        //var url = home_url + 'playlist/popup/' + playlist_id + q + qs;
        var url = query_url + 'popup=1&t=playlist_2_popup_window&playlist=' + playlist_id;
        var dim = "height=400,width=650";
        var win = window.open( url, 'cchostplayerwin', "status=1,toolbar=0,location=0,menubar=0,directories=0," +
                      "resizable=1,scrollbars=1," + dim );
        Event.stop(event);
        win.focus();
    },

    onPlayAll: function(event) {
        if( window.ccEPlayer )
        {
            var element = Event.element(event);
            while( !element.href )
                element = element.parentNode;
            ccEPlayer.SetPlaylist(element._playlist,true);
            ccEPlayer.StartPlaylist();
        }
        Event.stop(event);
    },

    initialize: function(playlist_id) {
        if( playlist_id )
            this.hook_playlist(playlist_id);
    }
}

ccPagePlayer = Class.create();

ccPagePlayer.prototype = Object.extend( {}, ccPlayerMethods );

/************************************************
*
*  Playlist browser
*
*************************************************/

var ccdbg = '';

var ccPlaylistBrowserObject = {

    selected: null,

    /*
       get the list of playlists
    */
    initialize: function(container_id,options) {
        this.options = Object.extend( this.options || {}, options || {} );
        this.container_id = container_id;
        //this._get_carts();
        this._hook_container();
    },


    _hook_container: function()
    {
        if( !$(this.container_id)._cc_hooked ) 
        {
            Event.observe( this.container_id, 'mouseover', this.onListHover.bindAsEventListener(this) );
            Event.observe( this.container_id, 'click',     this.onListClick.bindAsEventListener(this) );
            $(this.container_id)._cc_hooked = true;
        }
    },

    onListClick: function(event) {
        if( this.selected )
            this.openPlaylist(this.selected,event);
    },

    openPlaylist: function(id, event) {
        try
        {
            var cart_id = $(id).id.replace('_pl_','');
            var detailId = '_pld_' + cart_id;

            if( $(detailId) )
            {
                this.reopenPopupOrCloseIfOpen(event,detailId);
            }
            else
            {
                // this is a request to open playlist but we don't have
                // the contents, go to the server to get the playlist
                // details...

                var html = '\n<div id="'+detailId
                               + '" class="cc_playlist_detail"></div>\n';
                new Insertion.After(this.selected, html);
                this.userClickDataFetch(event,detailId);
                this.refreshDetails(cart_id);
            }
        }
        catch (err)
        {
            this._report_error( 'playlist.js (2)', err);
        }
    },

    KillBubble: function(event) {
        ccPopupManager.clearWindowClick();
        Event.stop(event);
        return false;
    },

    /*
    * Call back to the server to get the playlist information
    */
    refreshDetails: function( cart_id ) {
        var url = query_url + 't=playlist_2_nostyle&f=html&playlist=' + cart_id;
        new Ajax.Request( url, { method: 'get', onComplete: this._resp_playlist.bind(this, cart_id) } );
    },

    /*
    * Server responded with playlist info...
    */
    _resp_playlist: function( cart_id, resp, json ) {

        try
        {
            var id = '_pld_' + cart_id; // generated by this module
            var e = $(id);
            e.style.display = 'none';
            e.innerHTML = resp.responseText;

            cc_playlist_hook(cart_id,this);
            this.dataFetchedOpenPopup(id);

        }
        catch (err)
        {
            this._report_error( 'playlist.js (3): ', err );
        }
        this.openingRec = false;
    },

    onListHover: function(event) {
        try
        {
            
            var e = Event.element(event);

            var hasClass = Element.hasClassName( e, 'cc_playlist_line' );
            if( !hasClass )
            {
                while( e.tagName != 'DIV' )
                {
                    e = e.parentNode;
                    if( Element.hasClassName( e, 'cc_playlist_line' ) )
                    {
                        hasClass = true;
                        break;
                    }
                }
            }

            if( hasClass )
            {
                if( this.selected && (this.selected.id != e.id) )
                    Element.removeClassName(this.selected, 'selected_area'); 
                if( !Element.hasClassName( e, 'selected_area') )
                    Element.addClassName( e, 'selected_area'); 
                this.selected = e;
            }
            else
            {
                if( this.selected )
                    Element.removeClassName(this.selected, 'selected_area'); 
                this.selected = null;
            }
        }
        catch (err)
        {
            alert( 'playlist.js (3a) ' + err );
        }
    },

    _report_error: function(cap,err) {
        alert( cap + ' :' + err );
    }

}

ccPlaylistBrowser = Class.create();
ccPlaylistBrowser.prototype = Object.extend( { _hook_window: function() { } }, ccPlayerMethods);
Object.extend(ccPlaylistBrowser.prototype, ccPlaylistBrowserObject);
Object.extend(ccPlaylistBrowser.prototype, ccPopupManagerMethods );
Object.extend(ccPlaylistBrowser.prototype, {
    ShowElement: function(id) {
        Effect.BlindDown( id, { duration: 0.5, restoreAfterFinish: false } ); // IE requires no restoreAfterFinish
    },

    HideElement: function(id) {
        Effect.BlindUp( id, { duration: 0.5 } );
    } } );


/*
    parent redirector
*/
ccParentRedirector = Class.create();

ccParentRedirector.prototype = {

    initialize: function() {
        var me = this;
        $$('.cc_playlist_owner_menu LI A').each( function(a) {
            if( !a.href.match(/^javascript:/) )
                Event.observe(a,'click',me.onClickAndClose.bindAsEventListener(me));
                a.plocation = a.href;
                a.href = 'javascript:// menu option';
        });

        $$('A').each( function(a) {
            if( !a.href.match(/^javascript:/) && !a.plocation )
                Event.observe(a,'click',me.onClick.bindAsEventListener(me));
                a.plocation = a.href;
                a.href = 'javascript:// open link';
        });
    },

    onClickAndClose: function(event) {
        this._click(event,true);
    },

    onClick: function(event) {
        this._click(event,false);
    },

    _click: function(event,doClose) {
        var e = Event.element(event);
        while( !e.plocation )
            e = e.parentNode;
        self.opener.location = e.plocation;
        Event.stop(event);
        if(doClose)
            self.close();
        return false;
    }

}

function cc_playlist_hook(playlist_id,player,options)
{
    try
    {
        
        var opts = options || { play_buttons: true, dl_button: true, action_buttons: true, info_buttons: true };

        var cart = $('cartlines'+ playlist_id );

        if( opts.play_buttons )
        {
            // Play buttons, Play All, Play in Window
            player.hook_playlist(playlist_id,cart);
        }

        if( opts.dl_button )
        {
            var dlbut = $('dlcart'+ playlist_id);
            if( dlbut )
            {
                // download button
                var dlpopup = new queryPopup('','',str_download);
                dlpopup.height = 550;
                dlpopup.hookLink(dlbut,'f=html&t=download&playlist='+ playlist_id);
            }
        }

        if( opts.action_buttons )
        {
            // 'action' buttons
            var menu_hook = new queryPopup("menuup_hook","ajax_menu",str_action_menu);
            if( window.user_name )
                menu_hook.width = 720;
            menu_hook.hookLinks(cart);
        }

        if( opts.info_buttons )
        {
            // 'info' buttons
            new ccUploadInfo().hookInfos('.info_button',cart);
        }
    }
    catch (err)
    {
        alert('playlist.js (4): ' + err);
    }
}

