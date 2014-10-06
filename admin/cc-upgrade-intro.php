<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-upgrade-intro.php 8953 2008-02-11 21:44:18Z fourstones $
*
*/
?>
<h2>Welcome to the ccHost 5 Upgrade</h2>
<p>We'll ask you for some information, you're going to give it to us and hopefully, working
together we can get you up and running in just a few minutes. </p>
<h3>Upgrade Information</h3>
<p>
    Before you take another step....
</p>
    <h2 style="color:red">BACK UP YOUR DATABASE</h2>
<p>
    Please take the time, right now, to make a complete back up of all the ccHost tables
    in your database (including cc_tbl_config, including everything). If something
    goes wrong during the upgrade you can start over by restoring the database.
</p>
<p>
    ccHost is no longer using the phpTAL template engine so <b>your skin customizations will no longer 
    work</b> as they currently are. After your upgrade is complete you'll be able to import individual
    XML pages like your home page (with some modifications).
</p>
<p>
    Several of your admin customizations (such as sidebar content, submit form logs, etc) will have to be 
    reset using our new system. We think you'll find that such things will be worth it.
</p>
<h3>After you have <span style="color:red">backed up your database</span> proceed to <a href="?up_step=2">next step...</a></h3>
