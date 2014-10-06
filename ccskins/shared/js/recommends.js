ccReccommendFilter = Class.create();

ccReccommendFilter.prototype = {

    query: '',
    limit: 0,

    initialize: function(options) {
        this.options = Object.extend( { autoHook: true }, options || {} );
        this.hookElements();
    },

    hookFilterSubmit: function(func) {
        this.options.onFilterSubmit = func;
    },

    queryURL: function(withTemplate) {
        return query_url + this.queryString(withTemplate);
    },

    queryString: function(withTemplate) {
        var str = 'f=html&reccby=' + ruser + '&limit='+this.limit;
        if( withTemplate )
            str += '&t=playlist_list_lines';
        return str;
    },

    queryCountURL: function() {
        return query_url + 'f=count&reccby=' + ruser;
    },

    hookElements: function() {
      i = 0;
      var limit_picker = $('limit_picker');
      limit_picker.options[i++] = new Option( '5', '5' );
      limit_picker.options[i++] = new Option( '10', '10' );
      limit_picker.options[i++] = new Option( '15', '15' );
      limit_picker.options[i++] = new Option( '25', '25' );
      limit_picker.options[i++] = new Option( '50', '50' );
      limit_picker.selectedIndex = 3;
      this.limit = 25;
      Event.observe( limit_picker, 'change', this.onLimitClick.bindAsEventListener( this ) );
      if( this.options.onFilterSubmit )
        this.options.onFilterSubmit();
    },

    onLimitClick: function() {
        var limit_picker = $('limit_picker');
        this.limit = limit_picker.options[limit_picker.selectedIndex].value;
        this.options.onFilterSubmit();
    }

}

