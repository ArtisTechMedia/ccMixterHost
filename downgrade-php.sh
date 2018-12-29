#!/bin/sh
#
# Copyright (c) 2011, Vladimir Osintsev
#
# Trying to make a universal script that will be suitable on Debian
# and Ubuntu systems (supporting several versions). This script generates 
# necessary configuration files in /etc/apt and perform PHP downgrade
 
 
# Get distribution and release name
if [ -f /etc/lsb-release ]; then . /etc/lsb-release
 
# Using lsb_release utility if lsb-release config is missing
elif [ -e /usr/bin/lsb_release ]; then
  DISTRIB_ID=`lsb_release --id | awk '{print $3}'`
  DISTRIB_CODENAME=`lsb_release --codename | awk '{print $2}'`
 
else 
  echo "Can't get necessary distribution info." && exit 1
fi
 
RELEASE_NAME=$DISTRIB_CODENAME
 
# Set default settings according to the distrib name
case $DISTRIB_ID in
  'Debian') TARGET_NAME='lenny' ;;
  'Ubuntu') TARGET_NAME='hardy' ;;
esac
 
# Generate sources.list configuration file
if egrep -q "\ $RELEASE_NAME\ " /etc/apt/sources.list; then
  grep "\ $RELEASE_NAME\ " /etc/apt/sources.list | \
    sed "s/$RELEASE_NAME/$TARGET_NAME/g" | \
      tee /etc/apt/sources.list.d/$TARGET_NAME.list
else 
  echo "Can't find $RELEASE_NAME in sources.list file." && exit 2
fi
 
PKGINSTALLED=`dpkg -l | grep ^ii | grep php5 | awk '{print $2}'`
 
# Generate apt-pinning rules and store them in preferences file
for PACKAGE in $PKGINSTALLED; do
  # Add apt-pinning if package entry not yet in config file
  if ! egrep -sq "^Package: ?$PACKAGE" /etc/apt/preferences; then
    echo "Package: $PACKAGE\nPin: release a=$TARGET_NAME\nPin-Priority: 991\n" | \
      tee -a /etc/apt/preferences
  fi
done
 
# Updating first
aptitude update > /dev/null
 
# All main work is doing here
aptitude purge $PKGINSTALLED
aptitude install -y $PKGINSTALLED
