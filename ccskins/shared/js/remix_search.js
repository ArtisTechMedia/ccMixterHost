
ccRemixSearch = Class.create();

ccRemixSearch.prototype = {

    oldTypeVal: -1,
    useTextIndex: false,

    initialize: function( useTextIndex ) {

        this.useTextIndex = useTextIndex;
        var me = this;
        if( pools.length > 0 )
        {
            var html = '<select id="pools"><option value="-1" selected="selected">' + str_remix_this_site + '</option>';
            pools.each( function(p) {
                var sel = pool_id && (pool_id == p.pool_id) ? 'selected="selected"' : '';
                html += '<option '+sel+'value="' + p.pool_id + '">' + p.pool_name + '</option>';
            });
            html += '</select>';
            $('pool_select_contaner').innerHTML = html;
            Event.observe($('pools'),'change',me.onPoolChange.bindAsEventListener(me));
        }

        Event.observe( 'do_remix_search', 'click', me.onDoRemixSearch.bindAsEventListener(me) );
        
        if( $('remix_toggle_link') )
            Event.observe( 'remix_toggle_link', 'click', me.onToggleBox.bindAsEventListener(me) );

        this._scan_checks(true);

        if( pool_id )
        {
            this.onPoolChange(null);
        }
    },

    _scan_checks: function(check_all) {
        try
        {
            var me = this;
            $('license_info_container').style.display = 'none';
            var remix_sources = [];
            var pool_sources = [];
            var numChecked = 0;
            $$('.remix_checks').each( function(e) { 
                if( check_all )
                    e.checked = true;

                var m = e.name.match(/(remix|pool)_sources\[([0-9]+)\]/);
                var id = m[2];
                var label = $('rc_' + id );

                if( check_all || e.checked )
                {
                    numChecked++;
                    if( m[1] == 'remix' )
                        remix_sources.push(id);
                    else
                        pool_sources.push(id);
                    Element.addClassName(label,'remix_source_selected');
                }
                else
                {
                    Element.removeClassName(label,'remix_source_selected');
                }

                if( !e.hooked )
                {
                    Event.observe(e,'click',me.onRemixCheck.bindAsEventListener(me, id ));
                    e.hooked = true;
                }

            });

            if( numChecked )
            {
                var url = home_url + '/remixlicenses' + q + 'remix_sources=' + remix_sources + '&pool_sources=' + pool_sources;
                new Ajax.Request(url, { method: 'get', onComplete: this.onLicenseResults.bind(this) } );
            }

            $('remix_search_toggle').style.display = numChecked ? 'block' : 'none';

            if( $('form_submit') )
                $('form_submit').disabled = numChecked ? false : true;
        }
        catch (e)
        {
            alert('remix_search.js (1): ' + e);
        }
    },

    onRemixCheck: function( ev, id ) {
        this._scan_checks(false);
        var controls = $('remix_search_controls');
        if( controls.style.display == 'none' )
            this._toggle_open();
    },

    onLicenseResults: function(resp,json) {
        if( json.options )
        {
            var html = '<select id="licpicker">';
            json.options.each( function(lic) {
                    html += '<option value="' + lic.license_id + '"';
                    if( lic.license_id == json.license_id )
                    {
                        html += ' selected="selected" ';
                    }
                    html += '>' + lic.license_name + '</option>';
                } );
            html += '</select>';
            $('license_info').innerHTML = 'Your remix will be licensed as: ' + html;
            Event.observe( 'licpicker', 'change', this.onLicenseChange.bind(this) );
        }
        else
        {
            $('license_info').innerHTML = str_remix_lic.replace('%s','<a href="' + json.license_url 
                                                                     + '">' + json.license_name + '</a>' );
        }
        $('license_info_container').style.display = 'block';
        $('upload_license').value = json.license_id;
    },

    onLicenseChange: function(ev) {
        var box = $('licpicker');
        $('upload_license').value = box.options[ box.selectedIndex ].value;
    },
    
    onToggleBox: function(ev){ 
        this._toggle_open();
    },

    _toggle_open: function() {
        var controls = $('remix_search_controls');
        var show_now = controls.style.display == 'none';
        $$('.remix_checks').each( function(e) { 
            if( !e.checked )
            {
                var id = e.id.match(/[0-9]+$/);
                $('rl_' + id).style.display = show_now ? 'block' : 'none';
            }
        });
        controls.style.display = show_now ? 'block' : 'none';
        var t = show_now ? str_remix_close : str_remix_open;
        $('remix_toggle_link').innerHTML = '<span>' + t + '</span>';
        this._check_results_box_size(!show_now);
    },

    _check_results_box_size: function(force_off)
    {
        var div = $('remix_search_results');
        if( force_off || (div.offsetHeight < 200) )
        {
            div.style.overflow = '';
            div.style.height = '';
            div.style.border = '';
            div.style.padding = '';
        }
        else
        {
            div.style.overflow = 'scroll';
            div.style.height = '200px';
            div.style.border = "1px solid #666";
            div.style.padding = "3px";
        }
    },

    onDoRemixSearch: function(ev) {
        ccPopupManager.StartThinking(ev);
        var value = $('remix_search').value.strip();
        if( value.length < 3 )
        {
            alert(str_remix_no_search_term);
            return;
        }
        $('remix_no_match').innerHTML = '&nbsp;';

        var sel_pool = pools.length ?  $('pools').options[ $('pools').selectedIndex ].value : -1;
        if( sel_pool == -1 )
        {
            var search_type = $('remix_search_type');
            var query = query_url + 't=remix_checks&f=html&dataview=' + search_type.options[ search_type.selectedIndex ].value +
                                     '&match=' + value + '&user=-' + user_name;
        }
        else
        {
            var query = home_url + 'pools/search/' + sel_pool + q + 't=remix_pool_checks&search=' + value;
        }
        new Ajax.Request(query, { method: 'get', onComplete: this.onSearchResults.bind(this,value) } );
    },

    onSearchResults: function( value, resp ) {
        try
        {
            if( resp.responseText.length )
            {
                var ids = $$('.remix_checks').inject([], function(array, e) {
                    if(!e.checked)
                        array.push('rl_' + e.id.match(/[0-9]+$/) );
                    return array;
                });
                ids.each( function(id) {
                    Element.remove(id);
                });
                var div = $('remix_search_results');
                new Insertion.Top(div,resp.responseText);
                this._check_results_box_size(false);
            }
            else
            {
                var _wstr = this.useTextIndex ? str_remix_no_matches : str_remix_no_matches_gen;
                $('remix_no_match').innerHTML = _wstr.gsub('%s',value);
            }
            this._scan_checks(false);
        }
        catch (e)
        {
            alert('remix_search.js (2): ' + e);
        }
    },

    onPoolChange: function(ev) {
        var pools = $('pools');
        var pool = pools.options[ pools.selectedIndex ].value;
        var search_type = $('remix_search_type');
        if( pool == -1 )
        {
            search_type.disabled = false;
            search_type.selectedIndex = this.oldTypeVal;
        }
        else
        {
            this.oldTypeVal = search_type.selectedIndex;
            search_type.selectedIndex = 2;
            search_type.disabled = true;
        }
    }
}
