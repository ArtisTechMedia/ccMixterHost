
ccHost 5.1 README
==================

For the latest information on this release please see our wiki

http://wiki.creativecommons.org/cchost


In this document:

1. WHAT'S NEW
2. INSTALLING & UPGRADING
3. SYSTEM REQUIREMENTS
4. KNOWN ISSUES
5. SECURITY ALERT
6. HOWTO: REPAIR A CONFIG (A.K.A. HELP!!!!)
7. CONTACTS (REPORTING BUGS)
8. LICENSING INFORMATION


1.   WHAT'S NEW
-----------------

Please read the document NEWS.


2. INSTALLING & UPGRADING
-------------------------

For installation instruction please see ccadmin/INSTALL.


3.   SYSTEM REQUIREMENTS
------------------------

a.  APACHE.  This system has been tested on Apache 1 and 2 Windows and
Linux.  If you get it working on IIS or OSX let us know.  We haven't
tried.

b.  PHP.  This system requires PHP 4 or 5.  

c.  MYSQL.  MySQL 4 or higher is required.

d.  GETID3.  Download the GetID3 (1.7.9 tested to work - STABLE version
ONLY!) library from http://www.getid3.org/#download

e.  BROWSER.  Should work on most popular browsers (not IE6 though).  
COOKIES must be enabled in your browser in order to log in 
to a session. NOTE: For several of the skins (especially 'commons') 
IE6 browser is no longer supported.


4. KNOWN ISSUES
-----------------

-  This relesae does NOT include the following:
     * international support
     * redistribution tools

-  The 'Download Manager' is not supported in non-pretty urls mode 

-  getID3 1.7.7 has a problem tagging RIFF files (WAV, AVI, etc.)
   The work around is to remove line 105 in getid3/write.php that
   looks like:
     case 'riff': // maybe not officially, but people do it anyway

-  A note to developers with ccHost plugin code writting for v4 or before:
   
   If you have written custom PHP code or plugins for ccHost v4 or before
   you should first get the ccHost 5 installation up and running before
   trying your custom code. Much has changed in the code and the chances
   of your custom 'just working' is very low. The new core APIs are still
   very fluid so expect more churn.

-  For the most up to date listing of known issues check with the wiki

   http://wiki.creativecommons.org/Cchost#Known_Issues

5. SECURITY ALERT
------------------

The new template engine in 5.0 uses a .tpl extension file that is generally
directly accessable from the web. If you don't map that extension to
some program (like php) in your server configuration then users can
see SQL syntax directly in their browsers.


6. HOWTO: REPAIR A CONFIG (A.K.A. HELP!!!!)
--------------------------------------------

If you get into 'trouble' with your system you might be able to salvage your site
by browsing to either:

http://your_server/admin/edit
http://your_server?ccm=/media/admin/edit
http://your_server/cchost_lib/cc-config-repair.php


7. CONTACTS (REPORTING BUGS)
----------------------------

Project WIKI has every link you ever wanted (including
bug reporting) and some tips contributed by users:

http://wiki.creativecommons.org/CcHost#Communication

8. LICENSING INFORMATION
-------------------------

Creative Commons has made the contents of this package
available under a CC-GNU-GPL license:

http://creativecommons.org/licenses/GPL/2.0/

A copy of the full license can be found as part of this
distribution in the file COPYING

You may use the ccHost software in accordance with the
terms of that license. You agree that you are solely 
responsible for your use of the ccHost software and you
represent and warrant to Creative Commons that your use
of the ccHost software will comply with the CC-GNU-GPL.


$Id: README 12729 2009-06-06 05:42:01Z fourstones $
