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
# $Id: cc-host-fix-live-setup.sh 4123 2006-08-31 00:49:56Z kidproto $
# 
# Copyright 2005-2006, Creative Commons, www.creativecommons.org.
# Copyright 2006, Jon Phillips, jon@rejon.org.
#
# Fixes the current setup so that ccHost will work. You have to move the 
# ccadmin folder out of the way and also, if we have a backed up config file
# then this script makes it default and saves the old one.
#


CURR_DATE=`date +%F`


if [ -e "ccadmin" ]
then :
    if [ -e "ccadmin_backup" ]
    then :
        mv ccadmin_backup ccadmin_backup_${CURR_DATE}
    fi
    mv ccadmin ccadmin_backup
fi
    
if [ -e "cc-config-db.php.backup" ]
then : 

    if [ -e "cc-config-db.php" ]
    then :
        mv cc-config-db.php cc-config-db.php.backup_${CURR_DATE}
    fi
    mv cc-config-db.php.backup cc-config-db.php
fi


