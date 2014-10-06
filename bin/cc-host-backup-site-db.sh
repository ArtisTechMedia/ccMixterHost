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
# $Id: cc-host-backup-site-db.sh 4123 2006-08-31 00:49:56Z kidproto $
# 
# Copyright 2005-2006, Creative Commons, www.creativecommons.org.
# Copyright 2006, Jon Phillips, jon@rejon.org.
#
# Script for backing up your cchost database
#
# Usage:
#
#    $ DBUSER=editor ./backup_site_db.sh
#

if [ -z ${DBHOST} ]; then
    DBHOST=localhost
fi

if [ -z ${DBUSER} ]; then
    DBUSER=localuser
fi

if [ -z ${DBTABLE} ]; then
    DBTABLE=cchost
    #DBTABLE=--all-databases
fi

mysqldump -h ${DBHOST} -u ${DBUSER} -p ${DBTABLE} > mysql_dump_${DBTABLE}_`date +%F`.sql

exit 0
