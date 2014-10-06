%%
[meta]
    type     = format
    desc     = _('Big Contact Flash Player')
    dataview = passthru
[/meta]
%%

<? 
    $autoplay = empty($_GET['autoplay']) ? 'no' : 'yes';
    $url = urlencode($A['query-url'] . $A['qstring'] . '&f=rss'); 
    $src = "http://www.bigcontact.com/feedplayer-slim.swf?r=0&xmlurl={$url}";
?>

 <embed src="<?=$src?>"
    style="width:220px; height:160px;" 
id="FeedPlayerAudioSlim" 
align="middle" 
type="application/x-shockwave-flash" 
allowScriptAccess="always" 
quality="best" 
bgcolor="#ffffff" 
scale="noScale" 
wmode="window" 
salign="TL"  
FlashVars="initialview=menu&autoplay=<?=$autoplay?>&standalone=no&share=yes&repeat=no"></embed>