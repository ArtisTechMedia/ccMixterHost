%%
[meta]
    type = head
    desc = _('Embed styles and scripts in each page')
[/meta]
%%
<head> 

%if_empty(page-caption)%
  <title>%text(site-title)% - %text(site-description)%</title>
%else%
  <title>%text(site-title)% - <?= $T->String($A['page-caption']) ?></title>
%end_if%
 
%if_not_empty(site-meta-keywords)%
    <meta name="keywords" content="%(site-meta-keywords)%" />
%end_if%
%if_not_empty(site-meta-description)%
    <meta name="description" content="%(site-meta-description)%" />
%end_if%

%if_not_empty(extra-meta)%
    %(extra-meta)%
%end_if%

<meta name="robots" content="index, follow" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script type="text/javascript">
//<!--
var home_url  = '%(home-url)%';
var root_url  = '%(root-url)%';
var query_url = '%(query-url)%';
var q         = '%(q)%';
var user_name = %if_not_null(logged_in_as)% '%(logged_in_as)%'; %else% null; %end_if%

//-->
</script>

%loop(feed_links,feed)%
    <link rel="%(#feed/rel)%" type="%(#feed/type)%" href="%(#feed/href)%" title="%(#feed/title)%"/>
%end_loop%

%loop(head_links,head)%
    <link rel="%(#head/rel)%" type="%(#head/type)%" href="%(#head/href)%" title="%(#head/title)%"/>
%end_loop%

%loop(script_links,script_link)%
    <script type="text/javascript" src="%url(#script_link)%" ></script>
%end_loop%

%loop(script_blocks,script_block)%
    %call(#script_block)%
%end_loop%

%customize%

%loop(style_sheets,css)%
    <link rel="stylesheet" type="text/css" href="%url(#css)%" title="Default Style" />
%end_loop%

%loop(style_sheets_blocks,css_block)%
    <style type="text/css" title="Default Style">
      %(#css_block)%
    </style>
%end_loop%

<script src='https://www.google.com/recaptcha/api.js'></script>  
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">
	var $j = jQuery.noConflict();

  function LoadCCPlusBlock() {
		if (!$j("div.taglinks").length){return;}

    if ($j("div.taglinks").html().indexOf("ccplus") > -1) {
      var NavURL = "http://tunetrack.net/license/" + window.location.host + window.location.pathname;
      var AppendString = '<div class="box"><h2>CCPlus Licensing</h2><p>Like this song? Click <a href="' + NavURL + '">here to license this media</a> for commercial use.</p></div>';
      //get existing license box
      var lBox = $j('#license_info').parent().html();
      $j('#license_info').parent().html(lBox + AppendString);
    }
  }

  var FlashReplaceString = home_url + "api/query/stream.m3u?f=m3u&ids=";

  $j(document).ready(function () {
    LoadCCPlusBlock();
    ProcessIDsList();
    //may need a second pass
    setTimeout(ProcessIDsList, 1000);
  });

  var ProcessIDsList = function () {
    var IDList = GetIDsList();
    if (IDList.length == 0) {
      return;
    }
    for (var i = 0; i < IDList.length; i++) {
      SetupPlayer(IDList[i]);
    }
  }

  var SetupPlayer = function (DownloadID) {
    var SubmitURL = "http://ccmixter.org/api/query/api?ids=" + DownloadID + "&f=json&dataview=links_dl";

    $j.ajax({
      type: "GET",
      url: SubmitURL,
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      success: function (data) {
        if (data.length > 0) {
          var fileResult = data[0];
          if (fileResult.files.length > 0) {
            var DownloadURL = fileResult.files[0].download_url;
            var ReplaceURL = "_ep_" + DownloadID;
            var ReplaceContent = "<audio preload='none' controls><source src='" +
              DownloadURL + "' type='audio/mpeg'>Your browser does not support the audio element</audio>\r\n";
            $j("a[id='" + ReplaceURL + "']").parent().html(ReplaceContent);

            document.addEventListener('play', function(e) {
              var audio = document.getElementsByTagName('audio');
              for(var i = 0, len = audio.length; i < len;i++) {
                if(audio[i] != e.target) {
                  audio[i].pause();
                }
              }
            }, true);
          }
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        //leave it.
      }
    });

  }

  var GetIDsList = function () {
    var IDList = new Array();
    var list = $j("a[href*='m3u']").each(function (index) {
      var IDToProcess = $j(this).attr("href").replace(FlashReplaceString, "");
      IDList.push(IDToProcess);
    });
    return IDList;
  }
</script>
</head>
