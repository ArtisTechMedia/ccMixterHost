<!--
%%
[meta]
    name = mixups
    type = template_component
    desc = _('Display mixups')
    dataview = mixups
    bread_crumbs = home
[/meta]
%%
-->
<!-- template mixups -->
<link  rel="stylesheet" type="text/css" href="%url('css/info.css')%"  title="Default Style" />
<link  rel="stylesheet" type="text/css" href="%url('css/rate.css')%"  title="Default Style" />
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous" title="Default Style" />
<link  rel="stylesheet" type="text/css" href="%url('mixup-player/mixup-player.css')%"  title="Default Style" />
<style title="Default Style" type="text/css">
	#mixup_table {
		margin: 0px auto;
		width: 80%;
	}
	.mixup_mode_type {
		display: block;
		border: 1px solid black;
		padding: 3px;
		color: white;
		float: left;
		margin: 0.7em;
		font-style: italic;
	}

	.mixup_name {
		font-size: 150%;
	}

	#mixup_table div {
		margin-bottom: 1em;
	}

	#mixup_table #mixup-player div {
		margin-bottom: 0;
	}

	.mixup_status p {
		padding: 0.7em;
	}

	.pictoggle, #admin_button {
		float: right;
		margin-right: 8%;
	}

	.results_info {
		width: 600px;
		padding: 6px;
		font-size: 120%;
		font-style: italic;
		margin: 0px auto;
		border-top: 2px solid #777;
		border-bottom: 2px solid #777;
		text-align: center;
	}

	#mixup_faq_link {
		margin: 1em auto 3em 70%;
	}

	.admin_remove {
		margin-top: 0.24em;
		font-size: 80%;
	}
	.mixup_mode_type_<?= CC_MIXUP_MODE_DISABLED  ?> { background-color: orange; }
	.mixup_mode_type_<?= CC_MIXUP_MODE_SIGNUP    ?> { background-color: green; }
	.mixup_mode_type_<?= CC_MIXUP_MODE_MIXING    ?> { background-color: green; }
	.mixup_mode_type_<?= CC_MIXUP_MODE_UPLOADING ?> { background-color: green; }
	.mixup_mode_type_<?= CC_MIXUP_MODE_REMINDER  ?> { background-color: green; }
	.mixup_mode_type_<?= CC_MIXUP_MODE_DONE      ?> { background-color: red; }
	.mixup_mode_type_<?= CC_MIXUP_MODE_CUSTOM    ?> { background-color: inherit; color: inherit; }

	#user_status {
		margin: 1em 23%;
		padding: 1em;
		background-color: #BDF;
		border-bottom: 1px solid black;
		border-right:  1px solid black;
		border-top:    1px solid #777;
		border-left:   1px solid #777;
	}
</style>

<div id="mixup_faq_link">
	What's going on? <a class="small_button" href="%(home-url)%api/mixup/faq">Read the FAQ</a>&nbsp;&nbsp;&nbsp;<a href="%(home-url)%mixup/all">Other mixups &gt;&gt;</a>
</div>

%if_null(records)%
%return%
%end_if%
%map(#R,records/0)%
<div id="mixup_table">
	<div id="mixup_record">

		<!-- MIXUP HEAD AREA -->

		<div class="box">
			%if_not_null(is_admin)%
			<a class="small_button" id="admin_button" href="%(home-url)%admin/mixup/edit/%(#R/mixup_id)%">Admin</a>
			%end_if%
			<h2>
				%(#R/mixup_display)%
			</h2>
			<div class="mixup_desc">
				%(#R/mixup_desc_html)%
			</div><!-- mixup_desc -->
			<div class="mixup_status">
				<span class="mixup_mode_type mixup_mode_type_%(#R/mixup_mode_type)%">%(#R/mixup_mode_name)%</span>
				<p>%(#R/mixup_mode_desc_html)%</p>
			</div><!-- mixup_status -->

			<div style="display:none" id="mixup_dyn_status">...</div><!-- mixup_dyn_status -->

			<div style="display:none" id="user_status">...</div><!-- user_status -->

			%if_not_null(#R/mixup_playlist)%
			View the results <a href="%(home-url)%playlist/browse/%(#R/mixup_playlist)%">as a playlist</a>.
			%end_if%
		</div><!-- desc box -->

		<!-- MIXUP DISPLAY AREA -->

		<div id="who_list" style="display:none">
			<div class="results_info">Who's signed up...</div>
			<div class="pictoggle">
				<a class="small_button" id="pictoglink" href="javascript://pictoggle">Hide avatars</a>
			</div>
			<div class="mixup_users">
				<div id="mixup_user_list">...</div><!-- mixup_user_list -->
			</div><!-- mixup_users -->
		</div>

		<div id="matches" style="display:none">
			<div class="results_info">
				Here's the results! Click on <span title="track action"><i class="fas fa-wrench"></i></span> to
				%if(logged_in_as)% review, recommend, add to playlist and %end_if% share.
			</div>
			<div id="matches_data">

			</div><!-- matches_data -->
		</div>

	</div><!-- mixup_record -->
</div><!-- mixup_table -->

<?
    // hack: turn off the 'Syndication' block
    $T->SetArg('feed_links', 0 );
?>

<script  src="%url('/js/info.js')%"></script>
<script  src="%url('mixup-player/mixup-player.js')%" ></script>
<script type="text/javascript">
  var mixupAPI = Class.create();

  mixupAPI.prototype = {

    initialize: function(mixup_id,user_template)
    {
      this.mixup_id = mixup_id;
      this.statusDiv = $('mixup_dyn_status' );
      this.userStatusDiv = $('user_status');
      this.userListDiv = $('mixup_user_list');
      this.hooked = false;
      this.updateStatus();
    },

    updateStatus: function()
    {
      var url = home_url + 'api/mixup/status/' + this.mixup_id;
      this.callHome(url);
    },

    callHome: function(url)
    {
      var param = $('param');
      if( param )
      {
        url += q + param.name + '=' + param.value;
      }
      this.statusDiv.innerHTML = '...';
      //ajax_debug(url);
      new Ajax.Request( url, { method: 'get', onComplete: this.onStatus.bind(this) } );
    },

    onStatus: function( resp, json ) {

      //alert(resp.responseText);

      if( json.message )
      {
        alert(json.message);
        return;
      }


      if( json.err )
      {
        alert(json.err);
        return;
      }

      this.statusDiv.innerHTML = '<p>' + json.msg + '</p>';

      if( json.user_status )
      {
        this.userStatusDiv.innerHTML = '<p>' + json.user_status + '</p>';
        this.userStatusDiv.style.display = '';
      }
      else
      {
        this.userStatusDiv.style.display = 'none';
      }

      if( json.show_who )
      {
        $('who_list').style.display = '';
        url = query_url + 't=mixup_users&f=html&mixup=' + this.mixup_id;
        //ajax_debug(url);
        new Ajax.Updater( this.userListDiv, url, { method: 'get' } );
        Event.observe('pictoglink','click',toggle_img)
      }

      if( json.show_matches )
      {
        url = query_url + 'f=json&t=mixup_uploads&mixup=' + this.mixup_id;
        new Ajax.Request( url, { method: 'get', onComplete: this.onShowMatches.bind(this) } );
      }

      var button = $('mixup_dyn_button');

      if( button )
      {
        url = button.href;
        button.href = "javascript:// " + url;
        //ajax_debug('observing: ')
        //ajax_debug(url);
        Event.observe( button, 'click', this.callHome.bind(this,url) );
      }
    },

    onShowMatches: function( resp, json ) {
      window.mPlayer = mPlayer = new MixupPlayer(json);
      $('matches_data').innerHTML = mPlayer.render();
      $('matches').style.display = '';
      this.hookPlayer();
    },

    hookPlayer: function()
    {
      if( this.hooked == true )
        return;

      window.mPlayer.hookEvents();

      var dl_hook = new queryPopup("download_hook","download",str_download);
      dl_hook.height = 550;
      dl_hook.width  = 700;
      dl_hook.hookLinks();
      var menu_hook = new queryPopup("menuup_hook","ajax_menu",str_action_menu);
      menu_hook.width = window.user_name ? 720 : null;
      menu_hook.hookLinks();
      var infoHook = new ccUploadInfo();
      infoHook.hookInfos('.mixup-info',$('mixup_table'));

      this.hooked = true;
    }

  }

  var miximg_on = true;

  function toggle_img()
  {
    miximg_on = !miximg_on;
    show_hide_miximg();
  }

  function show_hide_miximg()
  {
    var newstyle = miximg_on ? '' : 'none';
    var text     = miximg_on ? 'Hide avatars' : 'Show avatars';
    var height   = miximg_on ? '120px' : '';

    CC$$('.hidemixup').each( function(e) {
      e.style.display = newstyle;
    });

    $('.pictoglink').innerHTML = text;

    CC$$('.miximgbox').each( function(d) {
      d.style.height = height;
    })

    cc_set_cookie( 'miximg_on', miximg_on );
  }

  Event.observe(window,'load', function() {
    new mixupAPI(%(#R/mixup_id)%,'mixup_users');
  });
</script>