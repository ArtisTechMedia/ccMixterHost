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
* $Id: cc-install-intro.php 12553 2009-05-06 04:17:42Z fourstones $
*
*/
?>
<h2>Welcome to ccHost Installation</h2>
<p>We'll ask you for some information, you're going to give it to us and hopefully, working
together we can get you up and running in just a few minutes. But first...</p>
<h3>Installation Requirements</h3>
<p>In order to run ccHost you need to install some other software and perform
one database task.</p>
<ul>
<li><b>PHP 4 or greater</b>
<p> <? print $vmsg; ?></p></li>

<li><b>mySQL</b>
<p>Currently mySQL is the only database supported. ccHost 5.0 has been tested with mySQL 4 and 5.</p></li>

<li><b>Create a Database</b>
<p>Before you continue installing ccHost you need to create a database. If you are running at a hosted site
the administrators can either do it for you or have already told you how to do it. Take note of the following things:</p>
<ul>
  <li>The name of the database</li>
  <li>The name of the database user</li>
  <li>Password of the database user</li>
  <li>The server the database is running on</li>
</ul>
<p>&nbsp;</p></li>

<li><b>GetID3</b>

<p>ccHost uses the <a href="http://www.getid3.org/">getID3 library</a> to verify 
the formats of file uploads of all types of media and archive files. It also uses it to tag
ID3 format files (like MP3s) with things like artist, song title, license, etc.
Make sure to download at least <b>stable</b> version (<b>1.7.9</b> or higher is suggested).
You can download this version from <a href="http://www.getid3.org/#download">here</a>. </p>

<p>Installing getID3 is actually very simple: just unzip the library to
a directory (e.g. <b class="d"><? print $id3suggest; ?></b>).</p>

</li>

</ul>

<h3>If you've done all the above onto <a href="?step=1a">the next step...</a></h3>
