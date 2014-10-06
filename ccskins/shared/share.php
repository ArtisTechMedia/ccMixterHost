<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');
?>
<!-- template share -->
<?
//------------------------------------- 
function _t_share_share_popup(&$T,&$A) 
{
  
?>
<script src="<?= $T->URL('js/sharesites.js'); ?>" type="text/javascript" /></script>
<script src="<?= $T->URL('js/share.js'); ?>" type="text/javascript" /></script>

<div  id="share_div"></div>
<div  id="share_email">
<a  href="<?= $A['PUB']['email_url']?>" class="cc_gen_button"><span ><div  id="inner_share"><?= $T->String('str_share_email') ?></div></span></a>
</div>
<script  type="text/javascript">
  new ccShareLinks( { url: '<?= $A['PUB']['bookmark_url']?>',
                      title:'<?= addslashes($A['PUB']['bookmark_title']) ?>',
                      inPopUp: false,
                      site_title: '<?= addslashes($A['site-title']); ?>'
                    } );
</script>
<?
} 
  
?>