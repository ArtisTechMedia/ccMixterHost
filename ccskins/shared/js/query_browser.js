ccQueryBrowser = Class.create();

ccQueryBrowser.prototype = {

    options: {
        prev_link_id:   'browser_prev',
        next_link_id:   'browser_next',
        browser_id:     'browser',
        rss_link_id:    'rss_link',
        play_link_id:   'mi_play_page', // 'play_link_container'
        stream_link_id: 'mi_stream_page',
        podcast_link_id: 'mi_podcast_page',
        download_link_id: 'mi_download_page',
        s2pl_link_id: 'mi_save_to_playlist', 
        autoRefresh:     true
        },
    onQueryResultsBind: null,
    onQuerySubmitBind: null,
    prev_link: null,
    next_link: null,
    browser:  null,
    rss_link: null,
    play_link: null,
    stream_link: null,
    download_link: null,
    currOffset: 0,
    totalCount: 0,
    paging: false,

    initialize: function( options ) {
        try
        {
            if( options )
                this.options = Object.extend( this.options, options );
            this.fillCountBind = this.fillCount.bind(this);
            this.fillContentBind = this.fillContent.bind(this);
            this.options.filters.hookFilterSubmit( this.refresh.bind(this) );

            this.prev_link   = this.options.prev_link_id   ? $(this.options.prev_link_id) : null;
            this.next_link   = this.options.next_link_id   ? $(this.options.next_link_id) : null;
            this.s2pl_link   = this.options.s2pl_link_id   ? $(this.options.s2pl_link_id) : null; 
            this.browser     = this.options.browser_id     ? $(this.options.browser_id) : null; 
            this.paging = (this.prev_link != null) && (this.next_link != null);

            if( this.prev_link )
                Event.observe( this.prev_link, 'click', this.onPrevClick.bindAsEventListener( this ) );

            if( this.next_link )
                Event.observe( this.next_link, 'click', this.onNextClick.bindAsEventListener( this ) );

            if( this.options.autoRefresh )
                this.refresh();
        }
        catch (e)
        {
            alert('query_browser.js (1): ' +  e );
        }
    },

    _check_feed_links: function() {
        if( this._feed_links_checked )
            return;

        this.rss_link    = this.options.rss_link_id    ? $(this.options.rss_link_id) : null;
        this.play_link   = this.options.play_link_id   ? $(this.options.play_link_id) : null;
        this.stream_link = this.options.stream_link_id ? $(this.options.stream_link_id) : null;
        this.podcast_link = this.options.podcast_link_id ? $(this.options.podcast_link_id) : null;
        this.download_link = this.options.download_link_id ? $(this.options.download_link_id) : null;

        if( this.play_link )
            Event.observe( this.play_link, 'click',    this.play_in_popup.bindAsEventListener( this ) );

        this._feed_links_checked = true;
    },

    getQuery: function(withTemplate) {
        var query = this.options.filters.queryURL(withTemplate);
        var params = ('?' + query).toQueryParams();
        this.limit = params.limit ? params.limit : 0;
        return query;
    },

    play_in_popup: function()
    {
        var query = this.options.filters.queryString(false);
        var url = query_url + 'popup=1&t=playable_list&' + query;
        if( this.currOffset > 0 )
            url += '&offset='+this.currOffset;
        url += '&f=page'; // someone is putting f=html on here (check it out later)
        var dim = "height=400,width=650";
        var win = window.open( url, 'cchostplayerwin', "status=1,toolbar=0,location=0,menubar=0,directories=0," +
                    "resizable=1,scrollbars=1," + dim );
    },

    clearUI: function() {
        if( this.prev_link )
            this.prev_link.style.display = 'none';
        if( this.next_link )
            this.next_link.style.display = 'none';        
        this.browser.innerHTML = str_getting_data + '...';
        if( this.rss_link )
            this.rss_link.style.display = 'none';
        if( this.play_link )
            this.play_link.style.display = 'none';
        if( this.stream_link )
            this.stream_link.style.display = 'none';
        if( this.podcast_link )
            this.podcast_link.style.display = 'none';
        if( this.download_link )
            this.download_link.style.display = 'none';
        if( this.s2pl_link )
            this.s2pl_link.style.display = 'none';
    },

    refresh: function()
    {
      if( this.paging )
        this.refreshCount();
      else
        this.refreshContent();
    },

    refreshCount: function() {
        this.clearUI();
        this.currOffset = 0;
        var url = this.options.filters.queryCountURL();
        new Ajax.Request( url, { method: 'get', onComplete: this.fillCountBind } );
    },

    fillCount: function( resp ) {
        this.totalCount = eval( "(" + resp.responseText + ")" )[0];
        if( this.totalCount > 0 )
        {
          this.refreshContent();
        }
        else
        {
          this.browser.innerHTML = str_filter_no_records_match;
        }
    },

    refreshContent: function() {
        this.clearUI();
        var url = this.getQuery(true);
        if( this.currOffset > 0 )
            url += '&offset='+this.currOffset;
        new Ajax.Request( url, { method: 'get', onComplete: this.fillContentBind } );
    },

    fillContent: function( resp ) {
        try
        {
            this.browser.innerHTML = resp.responseText;

            if( this.paging )
            {
                var prev_mode = this.currOffset > 0 ? 'block' : 'none';
                var next_mode = (this.totalCount - this.limit) > this.currOffset ? 'block' : 'none';
                this.prev_link.style.display = prev_mode;
                this.next_link.style.display = next_mode;
            }


            var queryStr = '?' + this.options.filters.queryString(false);
            var p = queryStr.parseQuery();
            p.offset = this.currOffset;

            this._check_feed_links();

            if( this.stream_link != null )
            {
                p.f = 'm3u';
                var url = home_url + 'api/query/stream.m3u' + q + $H(p).toQueryString();
                this.stream_link.href = url;
            }

            if( this.s2pl_link != null )
            {
                p.f = 'page';
                var pl_url = home_url + 'playlist/save' + q + $H(p).toQueryString();
                this.s2pl_link.href = pl_url;
                this.s2pl_link.style.display = '';
            }

            if( (this.rss_link != null) || (this.podcast_link != null) )
            {
                p.f = 'rss';
                var feed_url = query_url + $H(p).toQueryString();
                if( this.rss_link != null )
                {
                    this.rss_link.style.display = 'block';
                    this.rss_link.href = feed_url;
                }
                if( this.podcast_link != null )
                    this.podcast_link.href = feed_url;
            }

            if( this.download_link )
            {
                p.f = 'html';
                p.t = 'download';
                var down_url = query_url + $H(p).toQueryString();
                //ajax_debug(down_url);
                // so much for clean, parameterized code, fix after alpha
                var id = this.options.download_link_id;
                var id_parent = 'mi_download_page_parent';
                if( $(id_parent) )
                {
                    $(id_parent).innerHTML = '<a id="'+ id +'">' + str_download_this_page + '</a>';
                    this.download_link = $(id);
                }
                this.download_link.href = down_url;
                new modalHook( [ id ] );
            }

            /* this is audio stuff.... */
            if( window.ccEPlayer )
            {
                ccEPlayer.hookElements(this.browser);
                if( this.play_link )
                    this.play_link.style.display = 'block';
                if( this.stream_link )
                    this.stream_link.style.display = 'block';
                if( this.podcast_link )
                    this.podcast_link.style.display = 'block';
                //$('mi_stream_page').href = 'javascript://stream';
            }

            if( !this.playlistMenu )
                this.playlistMenu = new ccPlaylistMenu( { autoHook: false, playlist: this } );

            // hook the menus, info button, et. al.
            this.playlistMenu.hookElements(this.browser);

            // the 'action' buttons (if any)
            if( !this.actionHooker )
                this.actionHooker = new queryPopup('menuup_hook','ajax_menu',str_action_menu);
            if( window.user_name )
                this.actionHooker.width = 720;
            this.actionHooker.hookLinks(this.browser);
      }
      catch (err)
      {
        alert('query_browser.js (2): ' + err);
      }
    },

    onPrevClick: function() {
        this.currOffset -= parseInt(this.limit);
        this.refreshContent();
    },

    onNextClick: function() {
      this.currOffset += parseInt(this.limit);
      this.refreshContent();
    }

};
