<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>

<style>
.bbemsg {
  color: #944;
}
#inner_content {
    width: 540px; 
    margin: 0px auto;
}
</style>

<? $bbet = file_get_contents('mixter-files/pages/djvadim/bbe_1.txt'); ?>

<div class="box"> 
    <h2>BBE Music and ccMixter</h2>
    <div style="float: right; margin: 8px;">
        <a href="http://bbemusic.com/"><img  src="/mixter-files/pages/djvadim/bbe-logo.jpg" /></a>
        <br/>
        <a  href="http://creativecommons.org"><img  src="/mixter-files/images/cc-logo.png" /></a>
    </div>
    <p><?= $bbet ?></p>
</div>

<? $bbet = file_get_contents('mixter-files/pages/djvadim/bbe_2.txt'); ?>

<div style="background: url('/mixter-files/pages/djvadim/sc_cover_faded.jpg') repeat-y  top right; 
            padding: 20px 110px 20px 0px;">
    <h3 style="text-align: left">Remixers</h3>
    <p style="font-size:13px"><?= $bbet ?></p>
    <? $bbet = file_get_contents('mixter-files/pages/djvadim/bbe_3.txt'); ?>
    <div  id="sources">
    <?
    $module = 'cchost_lib/snoopy/Snoopy.class.php';
    require_once($module);
    $snoopy = new Snoopy();
    $snoopy->fetch('http://ccmixter.org/media/djvadim/tracks');
    print $snoopy->results;

    ?>
    </div>
    <p >
      NOTE: we have <a  href="/thread/611">strict policies about copyright material</a>. No wink-wink. Violators
      will be banned from the site.
    </p>
</div>
