var favsPlaylistHook = Class.create();

favsPlaylistHook.prototype = {

    return_macro: null,
    cart_id: null,
    upload_ids: null,

    initialize: function() {
        try
        {
            // first we need to get this user's favorites playlist
            // this call will create one if not already there
            req_url = home_url + 'api/playlist/getfavorite';
            new Ajax.Request( req_url, { method: 'get', onComplete: this.onGetFavoritesPlaylist.bind(this) } );
        }
        catch( e )
        {
            alert( 'fav_playlist (1)' + e );
        }
    },

    onGetFavoritesPlaylist: function(resp,json) {
        try
        {
            if( !json )
                json = eval( '(' + resp.responseText + ')' );
           
            this.cart_id       = json.cart_id; 
            this.upload_ids    = json.upload_ids;
            this.hookFavsPlaylistBlocks();
        }
        catch( e )
        {
            alert( 'fav_playlist (2)' + e );
        }
    },
    
    hookFavsPlaylistBlocks: function() {
        try
        {
            var me = this;
            $$('.fav_playlist_block').each( function(e) {
                var id = e.id.match(/[0-9]+$/);
                me.updateHTML(e,id);
                Event.observe(e,'click',me.onFavPlaylistClick.bindAsEventListener(me,id));
            });
        }
        catch (e)
        {
            alert( 'favs_playlist.js (3): ' + e);
        }
    },

    updateHTML: function( elem, id ) {
        if( this.upload_ids.include(id) )
        {
            text = '<a href="javascript://favs" class="fav_playlist_remove">'+str_pl_favs_remove_from+'</a>';
        }
        else
        {
            text = '<a href="javascript://favs" class="fav_playlist_add">'+str_pl_favs_add_to+'</span>';
        }
        elem.innerHTML = text;
    },    
    
    onFavPlaylistClick: function(event,id) {
        try 
        {
            indxOf = this.upload_ids.indexOf(id);
            if( indxOf == -1  )
            {
                url = home_url + 'api/playlist/add/' + id + '/' + this.cart_id;
                this.upload_ids = this.upload_ids.concat(id);
            }
            else
            {
                url = home_url + 'api/playlist/remove/' + id + '/' + this.cart_id;
                this.upload_ids.splice(indxOf,1);
            }
            d_elem = $('fav_playlist_'+id);
            d_elem.innerHTML = '...';
            new Ajax.Request( url, { method: 'get', onComplete: this.onFavPlaylistEdit.bind(this,id) } );
        }
        catch (e)
        {
            alert( 'favs_playlist.js (4): ' + e);
        }
    },
    
    onFavPlaylistEdit: function(id) {
        try
        {
            d_elem = $('fav_playlist_'+id);
            this.updateHTML(d_elem,id);
        }
        catch (e)
        {
            alert( 'favs_playlist.js (5): ' + e);
        }
    }

}
