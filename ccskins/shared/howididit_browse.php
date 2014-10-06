<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

?><!-- template howididit_browse -->

<? /* er, IE won't accept stylesheets in ajax calbacks so we have to include it here, but what if we are in 'narrow' mode?? */ ?>
<link rel="stylesheet" type="text/css" title="Default Style" href="<?= $T->URL('css/upload_list_wide.css') ?>" />
<div class="box"><?= $T->String('str_hidi_help') ?></div>
<div class="cc_howididit_links">
    <?= $T->String('str_hidi_sort_by') ?>
    <select id="hidi_sort">
        <option value="date" selected="selected"><?= $T->String('str_hidi_date')?></option>
        <option value="name"><?= $T->String('str_hidi_name')?></option>
        <option value="user"><?= $T->String('str_hidi_author')?></option>
    </select>
</div>

<div id="cc_howididit_uploads"></div>
<div id="cc_howididit_detail"></div>

<script type="text/javascript">
ccHowIDidIt = Class.create();

ccHowIDidIt.prototype = {

    playlists_enalbed: <?= empty($A['enable_playlists']) ? 0 : 1 ?>,
    dl_hook: null, 
    menu_hook: null,

    initialize: function() {
        Event.observe( 'hidi_sort', 'change', this.fillUploads.bindAsEventListener(this) );
        this.dl_hook = new queryPopup("download_hook","download",str_download); 
        this.dl_hook.height = '550';
        this.dl_hook.width  = '700';
        this.menu_hook = new queryPopup("menuup_hook","ajax_menu",str_action_menu);
        this.fillUploads();
    },

    fillUploads: function() {
        var picker = $('hidi_sort');
        var sort = picker.options[ picker.selectedIndex ].value;
        var ord = sort == 'date' ? 'DESC' : 'ASC';
        var url = query_url + 'size=10&f=html&t=uploads_options&tags=how_i_did_it&sort=' + sort + '&ord=' + ord;
        new Ajax.Request( url, { method: 'get', onComplete: this._resp_uploads.bind(this) } );
    },

    _resp_uploads: function(resp) {
        try {
        $('cc_howididit_uploads').innerHTML = resp.responseText;
        Event.observe( 'cc_upload_list', 'change', this.onUploadSelect.bindAsEventListener(this) );
        if( this.playlists )
            {
            }
        } catch( e ) {
            alert(e);
        }
    },

    onUploadSelect: function() {
        var picker = $('cc_upload_list');
        var upload = picker.options[ picker.selectedIndex ].value;
        var url = home_url + 'howididit/detail/' + upload + q + 'noscripts=1';
        new Ajax.Request( url, { method: 'get',  onComplete: this._resp_detail.bind(this) } );
    },

    _resp_detail: function(resp) {
        try {
            var det = $('cc_howididit_detail');
            det.innerHTML = resp.responseText;
            resp.responseText.evalScripts();
            if( window.round_box_enabled )
            {
                CC$$('.cc_howididit .box',det).each( function(e) {
                    cc_round_box(e);
                });
            }
            this.dl_hook.hookLinks(); 
            this.menu_hook.hookLinks();
            if( window.ccEPlayer )
            {
                ccEPlayer.hookElements($('cc_howididit_detail'));
            }
        } catch( e ) {
            alert(e);
        }
    }
    
}

new ccHowIDidIt();

</script>
<? 
$T->Call('flash_player');
?> 
