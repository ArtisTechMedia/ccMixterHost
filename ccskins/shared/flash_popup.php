<?/*
[meta]
   type = template_component
   desc = _('Plays flash object in popup')
   dataview = passthru
[/meta]
*/?>
<html>
<body style="margin:0">
<object width="<?=$_GET['w']?>" height="<?=$_GET['h']?>" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0">
<param name="movie" value="<?=urldecode($_GET['url'])?>">
<embed src="<?=urldecode($_GET['url'])?>" width="<?=$_GET['w']?>" height="<?=$_GET['h']?>" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" >
</embed>
</object></body>
</html>
<? exit; ?>
