# Makefile for ccHost distribution and packaging
#
# This original Makefile was created for Gotmail, http://gotmail.sf.net/ and
# is used here in accordance with the GNU GPL which is copied in the COPYING 
# file accompanying this file.
# 
# Copyright 2005-2006, Jon Phillips & Paul Howarth
# Copyright 2005-2006, Creative Commons.
#
# It generates the distribution packages but does not
# include "make install" type functionality itself.
#
# TODO: Make sure that the user changes user:group with chown before building 
# packages.
# 

RELEASE_NUM = $(shell cat VERSION)
APPNAME = cchost
PACKAGEDIR=packages
DATETIME=$(shell date +%F_%H%M%S)
SIGNPACKAGE=gpg --detach-sign --armor
MD5SUM=md5sum

LAST_RELEASE_DATE=2006-10-17

# must be multiple of 3 because only allowing tar.gz, tar.bz2, and zip
MAX_SNAPSHOTS=90

# The next line includes the vars which contain our needed file names
include Makefile.include

DIST_FILES_OTHER=Makefile.dist

all:	$(DIST_FILES_GENERATED) $(DIST_FILES_OTHER) ChangeLog
	$(MAKE) -f Makefile.dist all
	if [ -e Makefile.language ]; then \
	    $(MAKE) -f Makefile.language all; fi

press:
	[ -x `which txt2html` ] && txt2html --xhtml PRESS > PRESS.html

ChangeLog: ChangeLog.in
	sed -e "s/PROJECT_VERSION/$(RELEASE_NUM)/" $@.in > $@
	php bin/cc-host-stat.php -s "$(LAST_RELEASE_DATE)" >> $@ 

install: all
	$(MAKE) -f Makefile.dist install

uninstall:
	$(MAKE) -f Makefile.dist uninstall

uninstall-all:
	$(MAKE) -f Makefile.dist uninstall-all

clean:
	rm -f $(DIST_FILES_GENERATED)
	$(MAKE) -f Makefile.dist clean
	rm -f PRESS.html
	rm -f ChangeLog
	if [ -e Makefile.language ]; then \
	    $(MAKE) -f Makefile.language clean; fi 

#$(APPNAME): $(APPNAME).in
#	sed -e "s/PROJECT_VERSION/$(RELEASE_NUM)/" $(APPNAME).in > $(APPNAME)
#	chmod 755 $(APPNAME)

% :: %.in
	sed -e "s/PROJECT_VERSION/$(RELEASE_NUM)/" $@.in > $@

distprep: all
	@echo "If there are permission errors, run bin/fix_permissions.sh."
	# bin/backup_configs.sh
	# get rid of the cache
	rm -Rf cclib/phptal/phptal_cache/*.php
	rm -Rf $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM)
	mkdir -p $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM)
	cp -Rfp $(DIST_FILES_STATIC) $(DIST_FILES_GENERATED) $(DIST_FOLDERS) \
		$(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM)
	cp -p Makefile.dist $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM)/Makefile
	# copy our langauge stuff over if it exists
	if [ -e Makefile.language ] && [ -e locale ]; then \
        cp -Rfp locale/ $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM); fi
	# Get rid of all CVS folders in the packaging area
	find $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM) \
	    -depth -name ".svn" -type d -exec rm -rf {} \;
	# chmod 644 $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM)/*
	# chmod 755 $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM)/$(APPNAME)

distclean: clean
	rm -f *.tar.gz *.zip *.rpm *.tar.bz2
	rm -rf $(APPNAME)-$(RELEASE_NUM)
	rm -rf $(PACKAGEDIR)

rpm:	tarball
	rpmbuild -v --define "_rpmdir `pwd`" \
		 --define '_build_name_fmt %%{NAME}-%%{VERSION}-%%{RELEASE}.%%{ARCH}.rpm' \
		 -tb $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM).tar.gz
	mv *.rpm $(PACKAGEDIR)

srpm:	tarball
	rpmbuild --define "_srcrpmdir `pwd`" \
		 -ts $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM).tar.gz
	mv *.rpm $(PACKAGEDIR)

# NOTE: I made the following because tar was not working with our current
# procedure.
rpms: tarball
	mkdir -p $(PACKAGEDIR)/SOURCES
	mkdir -p $(PACKAGEDIR)/RPMS
	mkdir -p $(PACKAGEDIR)/SRPMS
	mkdir -p $(PACKAGEDIR)/SPECS
	mkdir -p $(PACKAGEDIR)/BUILD

	cp -Rf $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM).tar.gz $(PACKAGEDIR)/SOURCES

	rpmbuild --define "_topdir `pwd`/$(PACKAGEDIR)" \
		     --define '_build_name_fmt %%{NAME}-%%{VERSION}-%%{RELEASE}.%%{ARCH}.rpm' \
		 	 -ba cchost.spec
		 #-ta $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM).tar.gz
	# mv *.rpm $(PACKAGEDIR)

rpms_broke:	tarball
	rpmbuild --define "_rpmdir `pwd`" \
		 --define "_srcrpmdir `pwd`" \
		 --define '_build_name_fmt %%{NAME}-%%{VERSION}-%%{RELEASE}.%%{ARCH}.rpm' \
		 -ta $(PACKAGEDIR)/$(APPNAME)-$(RELEASE_NUM).tar.gz
	mv *.rpm $(PACKAGEDIR)

#deb: rpm
	# alien $(APPNAME)-*.rpm

zip: distprep
	(cd $(PACKAGEDIR); zip -r $(APPNAME)-$(RELEASE_NUM).zip $(APPNAME)-$(RELEASE_NUM))

tarball: distprep
	(cd $(PACKAGEDIR); tar czf $(APPNAME)-$(RELEASE_NUM).tar.gz $(APPNAME)-$(RELEASE_NUM))

bzip: distprep
	(cd $(PACKAGEDIR); tar -cjf $(APPNAME)-$(RELEASE_NUM).tar.bz2 $(APPNAME)-$(RELEASE_NUM))

dist: tarball zip bzip rpms


#gpg sign all the following packages baby!

zip-gpg: zip
	(cd $(PACKAGEDIR); $(SIGNPACKAGE) $(APPNAME)-$(RELEASE_NUM).zip)
	
tarball-gpg: tarball
	(cd $(PACKAGEDIR); $(SIGNPACKAGE) $(APPNAME)-$(RELEASE_NUM).tar.gz)
	 
bzip-gpg: bzip
	(cd $(PACKAGEDIR); $(SIGNPACKAGE) $(APPNAME)-$(RELEASE_NUM).tar.bz2)
	
rpms-gpg: rpms 
	@cd $(PACKAGEDIR); for i in `ls $(APPNAME)-$(RELEASE_NUM)*rpm`; \
	do $(SIGNPACKAGE) $$i; done

dist-gpg-all: zip-sign tarball-sign bzip-sign rpms-sign
	

# md5sum sign all the following packages baby!

zip-md5: zip
	(cd $(PACKAGEDIR); $(MD5SUM) $(APPNAME)-$(RELEASE_NUM).zip \
	 > $(APPNAME)-$(RELEASE_NUM).zip.DIGEST)
	
tarball-md5: tarball
	(cd $(PACKAGEDIR); $(MD5SUM) $(APPNAME)-$(RELEASE_NUM).tar.gz \
	 > $(APPNAME)-$(RELEASE_NUM).zip.DIGEST)
	 
bzip-md5: bzip
	(cd $(PACKAGEDIR); $(MD5SUM) $(APPNAME)-$(RELEASE_NUM).tar.bz2 \
	 > $(APPNAME)-$(RELEASE_NUM).zip.DIGEST)
	
rpms-md5: rpms 
	@cd $(PACKAGEDIR); for i in `ls $(APPNAME)-$(RELEASE_NUM)*rpm`; \
	do $(MD5SUM) $$i > $$i.DIGEST; done

dist-md5-all: zip-sign tarball-sign bzip-sign rpms-sign

# programmatically sign these packages 
dist-sign-all: dist-md5-all dist-gpg-all 


# the following datetime directives print date and time at the end of the list
zip-datetime: distprep
	(cd $(PACKAGEDIR); zip -r $(APPNAME)-$(RELEASE_NUM)-$(DATETIME).zip $(APPNAME)-$(RELEASE_NUM))

tarball-datetime: distprep
	(cd $(PACKAGEDIR); tar czf $(APPNAME)-$(RELEASE_NUM)-$(DATETIME).tar.gz $(APPNAME)-$(RELEASE_NUM))

bzip-datetime: distprep
	(cd $(PACKAGEDIR); tar -cjf $(APPNAME)-$(RELEASE_NUM)-$(DATETIME).tar.bz2 $(APPNAME)-$(RELEASE_NUM))

# builds the packages with datetime at the end for making snapshots
dist-datetime: tarball-datetime zip-datetime bzip-datetime

#first cleans out old snapshots over the max snapshots var and makes new ones
snapshots: distclean-max-snapshots dist-datetime

# removes how ever many snapshots are created over the MAX_SNAPSHOTS allowed
distclean-max-snapshots:
	@COUNTER=0; for i in $(shell ls -t $(PACKAGEDIR)/*.{gz,bz2,zip}); \
	do if [ $$COUNTER -ge $(MAX_SNAPSHOTS) ]; \
	then rm -f $$i; \
	fi; \
	let COUNTER=$$COUNTER+1; \
	done
	
test:
	@echo "BINDIR: $(BINDIR)"
	@echo "MANDIR: $(MANDIR)"
	@echo "MAN1DIR: $(MAN1DIR)"
	@echo "INSTALL: $(INSTALL)"

.PHONY: all install uninstall clean distprep distclean rpm srpm rpms deb zip dist tarball bzip test distclean-max-snapshots dist-datetime bzip-datetime tarball-datetime zip-datetime dist-sign-all rpms-sign bzip-sign tarball-sign zip-sign press

