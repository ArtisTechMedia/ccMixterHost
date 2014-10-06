#!/bin/bash
#
# Creative Commons has made the contents of this file
# available under a CC-GNU-GPL license:
# 
# http://creativecommons.org/licenses/GPL/2.0/
# 
# A copy of the full license can be found as part of this
# distribution in the file COPYING.
# 
# You may use the ccHost software in accordance with the
# terms of that license. You agree that you are solely 
# responsible for your use of the ccHost software and you
# represent and warrant to Creative Commons that your use
# of the ccHost software will comply with the CC-GNU-GPL.
# 
# $Id: cc-host-fix-permissions.sh 12610 2009-05-13 17:48:23Z fourstones $
# 
# Copyright 2005-2006, Creative Commons, www.creativecommons.org.
# Copyright 2006, Jon Phillips, jon@rejon.org.
#
# Fixes permissions so that the web server and the currently logged in user 
# have all the authority.
#

DEFAULT_CHMOD=775
DEFAULT_LOCAL_DIR=cchost_files

echo "What username do you want the files to have? [DEFAULT = `whoami`]"
read USERNAME
echo "What is your webserver's group? [DEFAULT = apache]"
read WWWGROUP 

if [ -z "$USERNAME" ]
then :
    USERNAME=`whoami`
fi

if [ -z "$WWWGROUP" ]
then :
    WWWGROUP=apache
fi

# Make sure that you are in the group apache
chown ${USERNAME}:${WWWGROUP} ./
chmod $DEFAULT_CHMOD ./
chmod $DEFAULT_CHMOD locale
chown -R ${USERNAME}:${WWWGROUP} locale

if [ -e "$DEFAULT_LOCAL_DIR" ];
then :
    chown -R ${USERNAME}:${WWWGROUP} $DEFAULT_LOCAL_DIR
    chmod -R $DEFAULT_CHMOD $DEFAULT_LOCAL_DIR
fi
