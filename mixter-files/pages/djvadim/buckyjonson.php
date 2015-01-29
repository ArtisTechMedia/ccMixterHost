
<div style="font-family: Verdana; font-size:11px">
<style type="text/css">
.bbemsg {
  color: #944;
}
#inner_content {
    width: 550px;
    margin: 0px auto;
}
</style>

<script>var meat_url = 'ccmixtermedia';</script>

<div class="box">
    <h2>BBE Music and ccMixter presents: Bucky Jonson</h2>
    <div style="float: right; margin: 8px;" >
        <a href="http://bbemusic.com/"><img src="/mixter-files/pages/djvadim/bbe-logo.jpg" /></a>
        <br />
        <a href="http://creativecommons.org"><img src="/mixter-files/images/cc-logo.png" /></a>
    </div>
    <p>
    <?= file_get_contents('mixter-files/pages/djvadim/bucky_1.txt') ?>
    </p>
</div>

</div>
    <div style="float:right;margin-top:13px;margin-left:20px;">
    <img src="/mixter-files/pages/djvadim/bucky_portraits.png" />
</div>

<div style="padding: 20px 0px 20px 0px;">
    <img src='/mixter-files/pages/djvadim/bucky_logo.png' style="float:right;margin:13px" />
    <h3 style="text-align: left">Remixers</h3>
    <p style="font-size:13px">
        <?= file_get_contents('mixter-files/pages/djvadim/bucky_2.txt') ?>
    </p>
    <div id="sources">
<?
    $module = 'cchost_lib/snoopy/Snoopy.class.php';
    require_once($module);
    $snoopy = new Snoopy();
    $snoopy->fetch('http://ccmixter.org/media/buckyjonson');
    print $snoopy->results;
?>
    </div>
    <p>
      NOTE: we have <a href="<?= $A['home-url'] ?>thread/611">strict policies about copyright material</a>. No wink-wink. Violators
      will be banned from the site.
    </p>
</div>


</div>
