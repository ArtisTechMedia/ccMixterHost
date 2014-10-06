<!-- template playerembed -->
<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_playerembed_eplayer(&$T,&$_TV) 
{
    if( empty($_TV['poptions']) ) $_TV['poptions'] = '';
?>

<link  href="<?= $T->URL('css/playerembed.css') ?>" rel="stylesheet" type="text/css" title="Default Style"></link>
<script  type="text/javascript" src="<?= $T->URL('js/playerembed.js') ?>"></script>
<script  type="text/javascript" src="<?= $T->URL('/js/swfobject.js') ?>"></script>
<div  id="flash_goes_here"></div>
<script  type="text/javascript">
    var swfObj = new SWFObject('<?= $_TV['root-url']?>cchost_lib/fplayer/ccmixter2.swf', 'uploadMovie', '1', '1', '8', "#FFFFFF" );
    swfObj.addVariable('allowScriptAccess','always');
    swfObj.write('flash_goes_here');
    var flashVersion = deconcept.SWFObjectUtil.getPlayerVersion();
    new ccEmbeddedPlayer( { <?= empty($_TV['player_options']) ? $_TV['poptions'] : $_TV['player_options'] ?> }, flashVersion['major'] );
  </script>
<?
} 

?>
