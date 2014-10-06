<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template tag_filter -->
<style text="type/css">

.autocomp_label, .cc_autocomp_stat
{
    display: block;
    float: right;
    margin-left: 9px;
    padding: 2px;
}

a.autocomp_links {
   float: right;
   margin-left: 9px;
}

#user_tags_filter p.cc_autocomp_line {
	margin: 1px;
    background-color: white;
}

#user_tags_filter p.cc_autocomp_selected {
    /*background-color: #CCC;*/
}

#user_tags_filter p.cc_autocomp_picked {
	font-style: italic;
	color: blue; 
}

#user_tags_filter .cc_autocomp_list {
	margin-top: 3px;
	cursor: pointer;
	width: 220px;
    background-color: white;
}

#user_tags_filter  {
    margin: 0px 0px 11px 10px;
}

.cc_autocomp_stat {
    color: green;
    font-weight: bold;
}

#user_tags_filter .cc_autocomp_border {
    border-top: 4px solid #666;
    border-left: 4px solid #666;
    border-right: 7px solid #444;
    border-bottom: 7px solid #444;
}

</style>

<div id="user_tags_filter" style="position:relative">
<a class="cc_autocomp_clear autocomp_links cc_gen_button" 
    href="javascript://clear list" id="_ap_clear_utg"><span>%text(str_filter_clear)%</span></a>
<a class="cc_autocomp_submit autocomp_links cc_gen_button" 
    href="javascript://results" id="_ap_submit_utg"><span>%text(str_filter_go)%</span></a>
<span class="cc_autocomp_stat" id="_ap_stat_utg"></span> 
<a class="cc_autocomp_show autocomp_links cc_gen_button" 
    href="javascript://show list" id="_ap_show_utg"><span>%text(str_user_filter_tags)%</span></a>

<div style="clear: both">&nbsp;</div>
    <div style="overflow: scroll; display: none; height: 170px;float:right;" 
                   class="cc_autocomp_list cc_autocomp_border" id="_ap_list_utg">
    </div>
</div>
<input name="utg" id="utg" value="" type="hidden" />

<script type="text/javascript" src="%url('js/autopick.js')%" ></script>
<script type="text/javascript">
ccTagFilter = Class.create();

ccTagFilter.prototype = {

    options: 
        {  url: home_url + 'tags',
           tags: 'remix',
           id: 'utg'
        },
    autoPick: null,

    initialize: function( options )
    {
        this.options = Object.extend( this.options, options || {} );
        this.autoPick = new ccAutoPick( {url: this.options.url });
        this.autoPick.onDataReceived = this.latePosition.bind(this);
        var id = this.options.id;
        this.autoPick.options.listID = '_ap_list_' + id;
        this.autoPick.options.statID = '_ap_stat_' + id;
        this.autoPick.options.showID = '_ap_show_' + id;
        this.autoPick.options.clearID = '_ap_clear_' + id;
        this.autoPick.options.submitID = '_ap_submit_' + id;
        this.autoPick.options.targetID = id;
        this.autoPick.options.pre_text = '';

        if( options.tags )
        {
            this.autoPick.selected = options.tags.split(/[,\s]+/);
            $(this.autoPick.options.statID).innerHTML = this.autoPick.selected.join(', ');
            $(this.autoPick.options.clearID).style.display = '';
        }
        else
        {
            $(this.autoPick.options.clearID).style.display = 'none';
        }

        $(this.autoPick.options.submitID).style.display = 'none';

        this.autoPick.hookUpEvents();
        Event.observe( this.autoPick.options.submitID, 'click', this.onSubmitClick.bindAsEventListener(this) );
    },

    latePosition: function() {
        if( !this.list_posed )
        {
            if( !Prototype.Browser.IE )
                Position.absolutize($(this.autoPick.options.listID));
            this.list_posted = true;
        }
    },

    onSubmitClick: function(event) {
        var url = this.options.target_url + $('utg').value.replace(' ','+');
        location.href =  url;
        return false;
    }
}
</script>
<input name="utg" id="utg" value="" type="hidden" />
