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
* $Id: query_filter.js 10393 2008-07-04 06:47:59Z fourstones $
*
*/
/******************************************
*
*  Query Browser Filters
*
*******************************************/
ccQueryBrowserFilters = Class.create();
ccQueryBrowserFilters.prototype = {

    options: {},

    initialize: function(options) {

      try
      {
        this.options = options;

        if( !this.options.query_url )
            this.options.query_url = query_url;
        if( !this.options.format )
            this.options.format = 'html';

        var types;

        if( this.options.optset.types.inject )
        {
            types = this.options.optset.types.inject( [], function(arr,tags) {
                        arr.push( [ tags.tags, tags.text ] );
                        return arr;
                    });
        }
        else
        {
            types = [];
        }
        
        // 'types' as the user sees it, is really 'reqtags' in query-api-speak

        this.reqtags =     { name: 'Type', fmt: 'dropdown', param: 'reqtags', vals: types };
        if( this.options.optset.user )
        {
            this.user       = { name: str_artist, fmt: 'user_lookup', param: 'user' };
            this.remixesof  = { name: str_filter_remixes_of, fmt: 'remix_user', param: 'remixesof' };
        }

        this.tags       = { name: str_tags, fmt: 'tag_lookup', param: 'tags' };
        this.type       = { name: str_filter_match, fmt: 'dropdown', param: 'type' ,
                                         vals: [  [ 'all', str_filter_match_all_tags ],
                                                  [ 'any', str_filter_match_any_tags  ]
                                               ]
                                          };
        var sort_vals = null;
        if( this.options.optset.user )
        {
             sort_vals = [  [ 'date', str_filter_date ],
                      [ 'name', str_filter_name ],
                      [ 'fullname', str_filter_artist ],
                      [ 'rank', str_filter_score ] ];
        }
        else
        {
             sort_vals = [  [ 'date', str_filter_date ],
                      [ 'name', str_filter_name ] ];
        }

        this.sort       = { name: str_filter_sort, fmt: 'dropdown', param: 'sort',
                                         vals: sort_vals
                                                };

        this.ord        = { name: str_filter_order, fmt: 'dropdown', param: 'ord',
                                         vals: [  [ 'desc', str_filter_most_to_least ],
                                                  [ 'asc', str_filter_least_to_most ] ] };
        /*
        this.score      = { name: 'Ratings', fmt: 'dropdown', param: 'score',
                                         vals: [  [ '*', 'all'],
                                                  [ 500, '5.00' ], 
                                                  [ 450, '4.5 or better' ], 
                                                  [ 400, '4.0 or better' ], 
                                                  [ 350, '3.5 or better' ], 
                                                  [ 300, '3.0 or better' ]
                                               ]
                          };
        */
        if( this.options.optset.license )
        {
            this.lic  = { name: str_filter_license, fmt: 'dropdown', param: 'lic',
                                             vals: [  [ '*', str_filter_all],
                                                      [ 'by', str_lic_attribution],
                                                      [ 'nc', str_lic_non_commercial],
                                                      [ 'sa', str_lic_share_alike],
                                                      [ 'byncsa', str_lic_nc_share_alike],
                                                      [ 's', str_lic_sampling],
                                                      [ 'splus', str_lic_sampling_plus],
                                                      [ 'ncsplut', str_lic_nc_sampling_plus],
                                                      [ 'pd', str_lic_public]
                                                   ]
                              };
        }
        this.sinced = { name: str_filter_since, fmt: 'dropdown', param: 'sinced',
                                         vals: [  
                                                  [ '*', str_filter_all_time],
                                                  [ '1 days ago', str_filter_yesterday],
                                                  [ '1 weeks ago', str_filter_a_week_ago],
                                                  [ '2 weeks ago', str_filter_2_weeks_ago],
                                                  [ '1 months ago', str_filter_last_month],
                                                  [ '3 months ago', str_filter_3_months_ago],
                                                  [ '1 years ago', str_filter_a_year_ago]
                                               ]
                          };

        var limits = [  [ 1 ], [ 5 ], [ 10 ], [ 15 ], [ 25 ], [ 50 ], [ 100 ] ];

        this.limit = { name: str_filter_limit, fmt: 'dropdown', param: 'limit', value: 25, vals: limits };

        // this should be "shuffle mode"
        // this.rand       = { name: 'Random Sort', fmt: 'checkbox', param: 'rand' };

        if( this.options.init_values )
        {
            if( this.options.init_values.limit && !limits.flatten().include(this.options.init_values.limit) )
                this.options.init_values.limit = 25;

            var me = this;
            $H(this.options.init_values).each( function(pair) {
                if( me[pair[0]] )
                    me[pair[0]].value = pair[1];
            });
        }

        if( !this.options.formatter )
            this.options.formatter = new ccFormatter();
        if( !this.options.formInfo )
            this.options.formInfo = this.makeForm( 'ff', this.options.formatter );
        if( !this.options.filter_form )
            this.options.filter_form = 'filter_form';
        $(this.options.filter_form).innerHTML = this.options.formInfo.html;
        this.options.formatter.setup_watches();
        $(this.options.formInfo.innerId).style.display = 'block';
        if( this.options.onFilterSubmit )
            this.hookFilterSubmit(this.options.onFilterSubmit);
        $(this.options.formInfo.submitId).innerHTML = '<span>' + this.options.submit_text + '</span>';
      }
      catch(e) {
          alert('query_filter.js (1): ' + e);
      }
    },

    hookFilterSubmit: function(func) {
        Event.observe(this.options.formInfo.submitId,'click',func);
    },

    queryURL: function(withTemplate) {
        return this.options.query_url + this.queryString(withTemplate);
    },

    queryString: function(withTemplate) {
        var str = this._queryString() + '&f=' + this.options.format;
        if( withTemplate && this.options.optset.template )
            str += '&t=' + this.options.optset.template;
        return str;
    },

    queryCountURL: function() {
        return this.options.query_url + this._queryString() + '&f=count';
    },

    _queryString: function() {
        var elements = Form.getElements($(this.id));
        var q = Array();

        for (var i = 0; i < elements.length; i++) {
          var element = elements[i];
          if( !element.name.match(/^_/) )
          {
            var val = $F(element);
            if( val && (val != '*' ) )
                q.push(element.name + '=' + val);
          }
        }

        return q.join('&');
    },

    makeForm: function( baseId, formatter_arg ) {
        
        var _id = baseId + '_filter';
        var formInfo = {
            id: _id,
            innerId: _id + '_inner',
            closeId: _id + '_close',
            submitId: _id + '_submit',
            html: ''
        };

        formInfo.html = ' <div class="filterform" id="' + formInfo.id + '" ><div id="' 
                       + formInfo.innerId + '" style="display:none"><a href="javascript://close filter" id="' 
                       + formInfo.closeId 
                       + '" class="close_button small_button"><span>'+str_close+'</span></a>'
                   ;

        var formatter = formatter_arg || new ccFormatter();

        $H(this).each( function(af) {
            var f = af[1];
            if( f.fmt )
              formInfo.html += '<div><span class="th">' + f.name + ':</span><span class="field">' 
                        + formatter[f.fmt](f,0) + '</span></div>\n';
        });

        formInfo.html += '\n<div class="filterbuttontray"><a class="cc_gen_button" href="javascript://filter go" id="'
                   + formInfo.submitId + '"><span>'+str_see_results+'</span></a></div>\n</div></div>\n';

        this.id = _id;
        return(formInfo);
    }
}

/******************************************
*
*  Query Browser Formatters
*
*******************************************/
ccFormatter = Class.create();

ccFormatter.prototype = {

    initialize: function() {
    },

    chop: function(s,col,x) {
        if( s.length > col.amt )
            s = s.substr(0,col.amt-3) + '...';
        return(s);
    },

    score: function(n,col,hidden) {
        n = parseInt(n);
        if( !n )
            return( 'n/r'  );
        var hi = Math.floor(n/100);
        var lo = n % 100;
        if( !lo )
            lo = '00';

        var ret = hi + '.' + lo;
        if( hidden && hidden.length )
        {
            ret += '/';
            var val = parseInt(hidden.pop());
            if( val < 10 )
                ret += '0';
            ret += val;
        }
        return( ret );
    },

    date: function(str,col) {
        // '2006-12-08 04:40:11'
        var sd = str.split(' ')[0].split('-');
        // how the heck do you locale this? parsing Date.toString()?
        var m = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];
        return m[ sd[1]-1 ] + ' ' + sd[2] + ', ' + sd[0];
    },

    _tag: function(n,f) {
        return '<' + n + ' id="' + f.param + '" name="' + f.param + '" ';
    },

    _input: function(t,f,x) {
        var html = this._tag('input type="'+t+'"',f);
        var x = x || '';
        if( f.value )
            html += 'value="' + f.value + '" ';
        html += ' ' + x + ' />';
        return html;
    },

    user_lookup: function(f,col) {
        var autoComp = this.user_picker = new ccAutoComplete( {  url: home_url + 'browse' + q + 'user_lookup=' } );
        this._watches.push( { func: autoComp.hookUpEvents.bind(autoComp) } );
        return autoComp.genControls( f.param, f.value, str_filter_enter_user );
    },

    remix_user: function( f, col ) {
        this.remix_id    = f.param;
        this.remix_label = 'label_' + f.param;
        var val          = f.value || '';
        var checked      = f.value ? 'checked="checked"' : '';
        var html         = this._input('checkbox',f,checked) + ' <label id="' + this.remix_label +
                             '" for="' + f.param + '">'+val+'</label>';

        this._watches.push( { id: f.param, ev: 'click', func: this.onRemixesOfCheck.bindAsEventListener(this) } );
        if( this.user_picker )
            this.user_picker.options.onPick = this.onUserPick.bind(this);
        return html;
    },

    onUserPick: function( autoComp, element, value ) {
        if( $(this.remix_id).checked ) 
        {
            this.savedOffUser = '';
        }
        else
        {
            $(this.remix_id).value = value;
            $(this.remix_label).innerHTML = value;
        }
        return false;
    },

    onRemixesOfCheck: function(e) {
        var element = Event.element(e);
        if( element.checked )
        {
            this.savedOffUser = $('user').value;
            $('user').value = '';
        }
        else
        {
            if( this.savedOffUser ) 
                $('user').value = this.savedOffUser;
        }
    },

    tag_lookup: function(f,col) {
        var autoPick = new ccAutoPick( {  url: home_url + 'browse' + q + 'min=3&type=4&tag_lookup=*' } );
        this._watches.push( { func: autoPick.hookUpEvents.bind(autoPick) } );
        return autoPick.genControls( f.param, f.value, '' );
    },

    user_tags: '',

    dropdown: function(f,col) {
        var html = this._tag('select',f) + '>';
        f.vals.each( function(opt) {
            var val = opt[0], text = opt.length > 1 ? opt[1] : val;
            var sel = val == f.value ? ' selected="selected" ' : '';
            html += '<option ' + sel + 'value="' + val + '" >' + cc_str(text.toString()) + '</option>';
        });
        return html + '</select>';
    },

    checkbox: function(f,col) {
        return this._input('checkbox',f);
    },

    hidden: function(f, col) {
        return '<span id="stat_' + f.param + '">' + f.value + '</span>' +
               this._input('hidden',f);
    },
    
    edit: function( f,col ) {
        return this._input('text',f);
    },

    _watches: [],

    setup_watches: function() {
        if( !this._watches.length )
            return;
        this._watches.each( function(w) {
            if( w.ev )
                Event.observe( w.id, w.ev, w.func, false );
            else
                w.func(w);
        });
    }

}

