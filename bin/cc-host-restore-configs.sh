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
# $Id: cc-host-restore-configs.sh 4123 2006-08-31 00:49:56Z kidproto $
# 
# Copyright 2005-2006, Creative Commons, www.creativecommons.org.
# Copyright 2006, Jon Phillips, jon@rejon.org.
#
# Restores any backed up config files so that they are the primary configs.
#

CURR_DATE=`date +%F`


if [ -e "ccadmin" ]
then :
    mv ccadmin ccadmin_old
fi

if [ -e "ccadmin_backup" ]
then :
    mv ccadmin_backup ccadmin
fi

if [ -e "cc-config-db.php" ]
then : 
    mv cc-config-db.php cc-config-db.php.old
fi


if [ -e "cc-config-db.php.backup" ]
then : 
    mv cc-config-db.php.backup cc-config-db.php
fi
