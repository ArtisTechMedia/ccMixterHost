<?
if( !defined('IN_CC_HOST') )
    die('Welcome to ccHost');

function _t_howtovirtual_init($T,&$targs) {
    
}
?><div >

<style >
li { margin-bottom: 7px; }
</style>
<div  style="font-family:Courier New, courier, serif;font-size: 12px">
<h1 >How to Create a Virtual Installation</h1>
<p >
Your ccHost site can be configured to look like many different
sites with just a few menu selections (and no changes to your 
web server configuration).
</p><p >

Why do this? Let's say you are running a remix community as
your main site but also want to run a contest (or you
mainly run contests and you also want to have a personal
'media weblog' on the side -- or -- whatever).
</p><p >

In this case you want to have a 'virtual face' to the site
without the hassle of reinstalling or major configuation
hoops.
</p>
<h2 >Creating a Virtual Personal Media Weblog</h2>

Use <a  href="<?= $A['home-url']?>admin/cfgroot">this form</a> to create a new virtual root.

<p >After naming the virtual root you can then use that name in the URL address to access it. For example, 
let's say you name your new root '<b >myroot</b>', to access that root simply tack it on to the domain and directory 
you installed ccHost into: <b ><?= $A['root-url']?>myroot</b>.</p>
<p >Most of the configuration settings you make will only be applied to your
new virtual site, so each site can have it's own skin, menus, it's own admins, page
content, navigation tabs, rules for file naming, MP3/ID3 tagging, etc.</p>
<p >An important feature you may not want to overlook is that navigatation tabs
can be configured to view only uploads that were made in the current virtual root.</p>
<p >

For the personal remix weblog you will probably want to restrict
the menus so that only you (as admin) have the ability to upload
or register so in the menu configuation you mark everything you
don't want anybody to see as 'Admin only'.
</p>
<p >When you create a contest, the system <i >automatically</i> creates a
virtual root with the 'internal' name of the contest. </p>
<p >After you create a virtual root you will probably want to immediately customize
it using the 'Settings', 'Menu', 'Sidebar Content' configurations screens.</p>
<h2 >Other Virtual Sites</h2>
<p >
The two cases above are what we came up with. You may
have a whole other set of cases. 
</p><p >
For example, in a community setting you may chose to give 
each member (or selected members) the ability to customize 
their own pages. As administrator you can setup a virtual 
site for each person assigning their member's page as the 
home page for that virtual site and even allow them to 
submit a CSS skin just for their version of the site.
</p><p >

Because you edit the menus you can control what that user
or other members see when they are looking at that virtual
site. 
</p><p >

And whatever else you can think of.
</p><p >

[note: If you are running on Apache and have mod_rewrite
enabled then you can use 'pretty urls' as they are in
the above examples. Otherwise, you'll need to put '?ccm=/' 
into every address (e.g. Every time you see "<?= $A['root-url']?>" you
need to substitute with "<?= $A['root-url']?><b >?ccm=/</b>"). ]
</p>
</div>
</div>