

ccShareLinks = Class.create();

ccShareLinks.prototype = {

    initialize: function(options) {

        if( !ccShareSites )
        {
            alert( 'sharesites.js was not included' );
            return;
        }
        var html = '<table align="center" id="share_table" cellpadding="0" cellspacing="6">';
        var inPopUp = options.inPopUp;
        var title = options.title;
        var url = options.url;
        var site = options.site_title;
        ccShareSites.each( function(row) {
            html += '<tr>';
            row.each( function(col) {
                if( col && (col.length == 4) )
                {
                    var shareurl = '';
                    if( inPopUp )
                    {
                        shareurl = col[2].replace('%title%',title).replace('%url%',url).replace('%site_title%',site);
                    }
                    else
                    {
                        shareurl = "javascript://share";
                    }
                    html += '<td><a href="'+shareurl+'" id="' + col[0] + '"><img title="' + col[1] + '" src="' + 
                                col[3] + '" />&nbsp;&nbsp;' + col[1] + '</a></td>\n';
                }
            });
            html += '</tr>\n';
        });

        html += '</table>';
        $('share_div').innerHTML = html;

        if( !inPopUp )
        {
            var me  = this;
            ccShareSites.each( function(row) {
                row.each( function(col) {
                    var shareurl = col[2].replace('%title%',title).replace('%url%',url).replace('%site_title%',site);
                    Event.observe( col[0], 'click', me.onSharePopup.bindAsEventListener(me,shareurl) );
                })
            });
        }

    },

    onSharePopup: function( e, shareurl ) {
        window.open( shareurl, 'cchostsharewin', 
            'status=1,toolbar=0,location=0,menubar=0,directories=0,resizable=1,scrollbars=1,height=480,width=550');
    },

    d: function() {
    }
}

