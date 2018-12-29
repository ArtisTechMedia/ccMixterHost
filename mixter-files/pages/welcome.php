<?
if( empty($A['user_name']) )
{
?>
<script>
document.location = home_url;
</script>
<noscript>
    You must be logged in to view this page.
</noscript>
<?
    return;
}
?>
<style>
.hs
{
    font-style:Courier New, courier, serif;
    font-size: 12px;
    vertical-align: top;
}
</style>
<div style="width:70%;margin:0px auto;">
    <img src="/ccskins/shared/images/downloadicon-big.gif" style="float:left;margin:12px;" />
    <img src="/ccskins/shared/images/uploadicon-big.gif" style="float:right;margin:12px;" />
    <h2 style="text-align:center;font-size:14pt;">Thank you for creating an account with ccMixter</h2>
    <br  clear="all" />
    <h2>Your Artist's Page</h2>
    <p>
        You can keep track of your submissions and reviews by other Mixters by visiting your Artist Page:
    </p>
    <h2 style="font-weight:normal;font-family:arial;text-align:center;font-size:large;">
        <? $url = ccl('people',$A['user_name'] ); ?>
        <a href="<?= $url ?>"><?= $url ?></a>
    </h2>
    <p>You can also share this permanant, public address with friends.</p>
    <br />
    <h2>Changing Your Password and Other Preferences</h2>
    <p>
        It's <i>highly recommended</i> that you start things off by creating a new password and setting
        up other preferences on your own private <a  href="/people/profile">Edit Your Profile</a> page.
    </p>
    <br />
    <h2>Uploading Remixes and Sample Libraries</h2>
    <p>
        Of course if you want to get straight to uploading you can jump right in by selecting the "Submit Files" 
        options from the menu on the left.
    </p>
</div>
