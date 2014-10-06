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
* $Id: form.js 12566 2009-05-07 19:42:06Z fourstones $
*
*/

function cc_trim(str,c)
{
	while (str.substring(0,1) == c)
	    str = str.substring(1, str.length);
	while (str.substring(str.length-1, str.length) == c)
	    str = str.substring(0,str.length-1);
	return str;
}

function cc_get_sel(obj)
{
	if( document.selection )
	    return ( document.selection.createRange() );

	var selLength = obj.textLength;
	var selStart = obj.selectionStart;
	var selEnd = obj.selectionEnd;
	if (selEnd == 1 || selEnd == 2)
	    selEnd = selLength;

	return ((obj.value).substring(selStart, selEnd));
}


function cc_apply_url(fname)
{
	var obj = $(fname);
	var url = cc_get_sel(obj);
	_cc_apply_format(obj, '"' + url + '":', url );
}

function cc_apply_format(fname,tag)
{
	var obj = $(fname);
	var open;
	if( tag == 'url' )
	{
		var url = prompt('URL:','http://');
		if( url )
			open = '[url=' + url + ']';
	}
    if( tag == 'img' )
    {
        var url = prompt('Image URL:','http://');
        if( url )
            open = '[img=' + url + ']';
    }

	if( !open )
		open = '[' + tag + ']';

	var close = '[/' + tag + ']';

	_cc_apply_format(obj,open,close);
}

function _cc_apply_tag(text,open,close)
{
	var arr = text.split("\n");
	var len = arr.length;

	var trimmed = cc_trim(text,' ');
	var R;
	if( trimmed != text )
	{
		var rstr = '^(\\s+)?' + trimmed + '(\\s+)?$';
		R = new RegExp(rstr);
		text = text.replace(R,"$1" + open + trimmed + close + "$2");
	}
	else
	{
		text = open + text + close 
	}

	return(text);
}


function _cc_apply_format(obj,open,close)
{
	obj.focus();
	if( document.selection )
	{
	    var sel = document.selection.createRange();
		var text = _cc_apply_tag(sel.text,open,close);
		sel.text = text;
	}
	else
	{
		var selLength = obj.textLength;
		var selStart = obj.selectionStart;
		var selEnd = obj.selectionEnd;
		if (selEnd == 1 || selEnd == 2)
			selEnd = selLength;

		var s1 = (obj.value).substring(0,selStart);
		var s2 = (obj.value).substring(selStart, selEnd)
		var s3 = (obj.value).substring(selEnd, selLength);

		var text = _cc_apply_tag(s2,open,close);

		obj.value = s1 + text + s3;
		var cursorLoc = selEnd + open.length + close.length;
		obj.selectionStart = cursorLoc;
		obj.selectionEnd = cursorLoc;
	}
}

function cc_format_preview(fname)
{
	var obj = $(fname);
	var txt = obj.value;
	txt = escape(txt);
	var pbox = $('format_preview_' + fname);
	pbox.style.display = 'block';
	obj.focus();
    var url = home_url + 'format/preview?ptext=' + txt;
    var e = $('format_inner_preview_' + fname);
    e.innerHTML = '...';
    new Ajax.Updater( e, url, {method: 'get'});
}

function cc_hide_preview(fname)
{
	var pbox = $('format_preview_' + fname);
	pbox.style.display = 'none';
}

function cc_grow_textarea(elemname)
{
  var elemobj;
  var elemlink;
  elemobj = $(elemname);
  if( elemobj )
  {
      elemlink = $('grow_' + elemname);
      if( elemobj.style.height == '100px' )
      {
          elemlink.innerHTML = '[ - ]';
          elemobj.style.height = '300px';
          elemobj.style.width = '450px';
      }
      else
      {
          elemlink.innerHTML = '[ + ]';
          elemobj.style.height = '100px';
          elemobj.style.width = '300px';
      }
      elemobj.focus();
  }
}

function cc_add_tag(tag,fieldname)
{
  f = $(fieldname);  
  tagstr = f.value;
  tags = tagstr.split(',');
  tags.push(tag);
  tagstr = tags.join(',');
  if( tagstr.charAt(0) == ',' )
    tagstr = tagstr.substr(1);
  f.value = tagstr;
}


ccGridEditor = Class.create();
ccGridEditor.prototype = {

    form_id: '',
    curr_row: 1,
    num_rows: 1,
    cols: null,

    initialize: function(form_id) {
        this.form_id = form_id;
        var me = this;
        $$('.menu_item_title').each( function(e) {
            ++me.num_rows;
            var num = e.id.replace(/mit_/,'');
            Event.observe( e, 'click', me.onMenuTitleClick.bindAsEventListener(me,num) );
        });
        this._select_row();
        var adder = $(form_id + '_adder');
        if( adder )
        {
            Event.observe( adder, 'click', me.onAdderClick.bindAsEventListener(me) );
        }
        /*
        $$('.gcol_name').each( function(e) {
            var num = e.id.match(/^[^\[]+\[([0-9]+)/)[1];
            var eid = 'mit_' + (parseInt(num)+1);
            Event.observe( e, 'keyup', me.onNameChange.bindAsEventListener(me,e.id,eid) );
        });
        */
    },

    onNameChange: function(e,src,target) {
        $(target).innerHTML = $(src).value;
    },
    onAdderClick: function() {
        try
        {
            var html = '';
            var ci;
            ++this.num_rows;
            for( ci = 0; ci < this.cols.length; ci++ )
            {
                var id = this.form_id + '_meta_' + (ci+1);
                html += '<div class="f"><span class="col">' + this.cols[ci] + '</span>' +
                         $(id).innerHTML.replace(/%i%/g,"" + this.num_rows).replace(/\\/g,'') +
                        '</div><div class="gform_breaker"></div>';
            }
            var div = document.createElement('div');
            var mitid = 'mit_' + this.num_rows;
            var divid = this.form_id + '_' + this.num_rows;
            div.id = divid;
            div.innerHTML = html;
            div.style.display = 'none';
            $('fields_' + this.form_id).appendChild(div);
            var me = this;
            /*
            $(divid).getElementsByClassName('gcol_name').each( function(e) {
                Event.observe( e, 'keyup', me.onNameChange.bindAsEventListener(me,e.id,mitid) );
            });
            */
            div = document.createElement('div');
            div.id = 'dmit_'+this.num_rows;
            div.className = 'med_bg dark_border';
            div.innerHTML = '<a href="javascript://menu item" ' +
                   'class="menu_item_title light_color" id="'+mitid+'" >'+str_new_row+ ' (' + this.num_rows + ')</a>';
            $('names_' + this.form_id).appendChild(div);
            Event.observe( mitid, 'click', this.onMenuTitleClick.bindAsEventListener(this,this.num_rows) );
            this._deselect_row();
            this.curr_row = this.num_rows;
            this._select_row();
            if( this.PostStufferScript )
                this.PostStufferScript();
        }
        catch (e)
        {
            alert('cchost.js (7): ' + e);
        }
    },

    _select_row: function() {
        var _id = this.form_id + '_' + this.curr_row;
        $(_id).style.display = 'block';
        _id = 'mit_' + this.curr_row;
        Element.removeClassName($(_id),'light_color');
        Element.addClassName($(_id),'dark_color');
        _id = 'dmit_' + this.curr_row;
        Element.removeClassName($(_id),'med_bg');
        Element.addClassName($(_id),'light_bg');
        Element.addClassName($(_id),'selected');
    },

    _deselect_row: function() {
        var _id = this.form_id + '_' + this.curr_row;
        $(_id).style.display = 'none';
        _id = 'mit_' + this.curr_row;
        Element.addClassName($(_id),'light_color');
        Element.removeClassName($(_id),'dark_color');
        _id = 'dmit_' + this.curr_row;
        Element.addClassName($(_id),'med_bg');
        Element.removeClassName($(_id),'light_bg');
        Element.removeClassName($(_id),'selected');
    },

    onMenuTitleClick: function(e,num) {
        try
        {
            if( num == this.curr_row )
                return;
            this._deselect_row();
            this.curr_row = num;
            this._select_row();
        }
        catch (e)
        {
            alert('cchost.js (8): ' + e);
        }
    }
}


var add_row_num = 1;
function add_flat_row(form_id,num_grid_rows,num_grid_cols)
{
    var tableobj = $('table_' + form_id);
    var row_num = num_grid_rows + add_row_num;
    ++add_row_num;
    var row = tableobj.insertRow(row_num);
    var num_cols = num_grid_cols;
    var td;
    var ci;
    for( ci = 0; ci < num_cols; ci++ )
    {
        td = row.insertCell(ci);
        var nid = ci + 1;
        td.innerHTML = $(form_id + '_meta_' + nid).innerHTML.replace(/%i%/g,"" + row_num); 
    }
}

function cc_date_disable(ename,disabled)
{
    ['m','d','y','h','i','a'].each( function(x) {
        $( ename + '[' + x + ']').disabled = disabled;
    });
}

function cc_date_stick(ename)
{    
    cc_date_disable(ename,$('sticky_check_' + ename).checked);
}
