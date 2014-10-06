#! /usr/bin/perl -w
# Try to detect markup errors in translations.
#
# $Id: cc-host-check-po-markup.sh 12729 2009-06-06 05:42:01Z fourstones $
#
# Author: Peter Moulder <pmoulder@mail.csse.monash.edu.au>
# Author: Jon Phillips <jon@rejon.org>
# Copyright (C) 2004 Monash University
# Copyright (C) 2006 Creative Commons
# Copyright (C) 2006 Jon Phillips
# License: GNU GPL v2 or (at your option) any later version.
#
# Initial egrep version:
# mydir=`dirname "$0"`
# egrep '<b>[^<>]*(>|<([^/]|/([^b"]|b[^>])))' "$mydir"/*.po
# Somewhat simplified by use of negative lookahead in perl.
# (The egrep version as written can't detect problems that span a line,
# e.g. unterminated `<b>'.  One way of doing the s/"\n"//g thing would be 
# with tr and sed, but that requires a sed that allows arbitrary line 
# lengths, which many non-GNU seds don't.)
#
# JON: I adapted this tool to check for some more html and other tags that
# might creep into our language files. Ideally, we don't want any HTML in
# our strings and should find ways around this, unless absolutely possible, 
# for emphasis and styling. I mark where I have changed this.
#
# This has been adapted from Inkscape's po folder so its requirements are 
# a bit different.
#
# Oh, if this gets too ugly, please replace it with the one from Inkscape.org's
# SVN repository out of the main trunk in the po/ folder.

use strict;

my $com = qr/(?:\#[^\n]*\n)/;
my $str = qr/(?:"(?:[^"\\]|\\.)*")/;
my $attrsRE = qr/(?: +[^<>]*)?/;
# JON: Added href below because we allow for href in our language files as
# part of a tags
my $span_attr = qr/(?:\ +(?:font_(?:desc|family)|face|href|size|style|weight|variant|stretch|(?:fore|back)ground|underline|rise|strikethrough|fallback|lang)\=\\\"[^\\\"]*\\\")/;

my $rc = 0;

sub po_error ($) {
    my ($msg) = @_;
    my $name = $ARGV;
    $name =~ s,.*/,,;
    print "$name: $msg:\n$_";
    $rc = 1;
}

# Returns true iff successful.
sub check_str ($) {
    my ($str) = @_;

    $str =~ s/\A"// or die "Bug: No leading `\"' in `$str'";
    $str =~ s/"\Z// or die "Bug: No trailing `\"' in `$str'";

    if ($str =~ /\AProject-Id-Version:.*POT-Creation-Date/
        or $str =~ /\A<[^<>]*>\Z/) {
	# Not a Pango string.
	return 1;
    }

    my $is_xml = 0;

    # remove valid standalone tags like <br />
    $str =~ s{<(?:br) ?/?>}{}gi;

    # JON: added h1-h5 and a tags
    # Remove valid sequences.
    while ($str =~ s{<([bisua]|h[1..5]|big|su[bp]|small|tt|span)(${attrsRE})>[^<>]*</\1>}{}) {
	$is_xml = 1;
	my ($tag, $attrs) = ($1, $2);
	# JON: I added the check for 'a' even though its against pango
	if ($tag eq 'span' or $tag eq 'a') {
	    $attrs =~ s/${span_attr}*//g;
	    if ($attrs ne '') {
		$attrs =~ s/\A *//;
		$attrs =~ s/\\"/"/g;
		po_error("Unexpected <span> attributes `$attrs'");
		return 0;
	    }
	} else {
	    if ($attrs !~ /\A *\Z/) {
		po_error("<$tag> can't have attributes in Pango");
		return 0;
	    }
	}
    }

    # JON: added h1-h5 and a tags
    # Check for attributes etc. in non-<span> element.
    if ($str =~ m{<([bisua]|h3|big|su[bp]|small|tt)\b(?! *)>}) {
	po_error("Unexpected characters in <$1> tag");
	return 0;
    }

    # JON: added h1-h5 and a tags
    if ($str =~ m{<([bisua]|h3|big|su[bp]|small|span|tt)${attrsRE}>}) {
	po_error("unclosed <$1>");
	return 0;
    }

    # JON: added h1-h5 and a tags
    if ($str =~ m{</\ *([bisua]|h3|big|su[bp]|small|span|tt)\ *>}) {
	po_error("Unmatched closing </$1>");
	return 0;
    }

    if (!$is_xml) {
	$str =~ s/<(?:defs|image|rect|svg)>//g;
	$str =~ s/<[ 01]//g;
	$str =~ s/\A>+//;
	$str =~ s/<+\Z//;
	$str =~ s/\([<>][01]\)//g;
	$str =~ s/ -> //g;

	# Quoting.
	$str =~ s/\[[<>]\]//g;
	$str =~ s/\\"[<>]\\"//g;
	$str =~ s/\xe2\x80\x9e[<>]\xe2\x80\x9c//g;
	$str =~ s/\xc2\xab[<>]\xc2\xbb//;
    }

    $str =~ s/\A[^<>]*//;
    $str =~ s/[^<>]*\Z//;

    if ($str =~ /\A([<>])\Z/) {
	if ($is_xml) {
	    po_error("Unescaped `$1'");
	    return 0;
	} else {
	    return 1;
	}
    }

    if ($str ne '') {
	po_error("parsing error for `$str'");
	return 0;
    }

    return 1;
}

sub check_strs (@) {
    if ($#_ < 1) {
	die "check_strs: expecting >= 2 strings";
    }
    if ((($_[0] eq '""') && ($_[1] =~ /Project-Id-Version:.*POT-Creation-Date:/s))
	or ($_[0] eq '"> and < scale by:"')) {
	# Not a Pango string.
	return 1;
    }
    foreach my $str (@_) {
	$str eq '""' or check_str($str) or return 0;
    }
    return 1;
}

$/ = '';

# Reference for the markup language:
# http://developer.gnome.org/doc/API/2.0/pango/PangoMarkupFormat.html
# (though not all translation strings will be pango markup strings).
ENTRY: while(<>) {
	if (m{\A${com}*\Z}) {
	    next ENTRY;
	}

	s/"\n"//g;

	if (!m{\A${com}*msgid[^\n]*\n${com}*msgstr[^\n]*\n${com}*\Z} &&
	    !m{\A${com}*msgid[^\n]*\n${com}*msgid_plural[^\n]*\n${com}*(msgstr\[[^\n]*\n${com}*)+\Z}) {
	    po_error('Not in msg format');
	    next ENTRY;
	}
	if (!m{\A${com}*msgid ${str}\s*\n${com}*msgstr ${str}\s*\n${com}*\Z} &&
	       !m{\A${com}*msgid ${str}\s*\n${com}*msgid_plural ${str}\s*\n${com}*(msgstr\[\d+\] ${str}\s*\n${com}*)+\Z}) {
	    po_error('Mismatched quotes');
	    next ENTRY;
	}

	if (m{\n\#,\ fuzzy}) {
	    # Fuzzy entries aren't used, so ignore them.
	    # (This prevents warnings about mismatching <>/ pattern.)
	    next ENTRY;
	}

	if (m{\A${com}*msgid\ (${str})\n
	      ${com}*msgstr\ (${str})\n
	      ${com}*\Z}x) {
	    check_strs($1, $2) or next ENTRY;
	}
	elsif (m{\A${com}*msgid\ (${str})\n
		 ${com}*msgid_plural\ (${str})\n
		 ((?:${com}*msgstr\[\d+\]\ ${str}\n${com}*)+)\Z}x) {
	    my ($s1, $s2, $rest) = ($1, $2, $3);
	    my @strs = ($s1, $s2);
	    while ($rest =~ s/\A${com}*msgstr\[\d+\]\ (${str})\n${com}*//) {
		push @strs, ($1);
	    }
	    $rest eq '' or die "BUG: unparsed plural entries `$rest'";
	    check_strs(@strs) or next ENTRY;
	}
	elsif (m{$str[ \t]}) {
	    po_error('Trailing whitespace');
	    next ENTRY;
	} else {
	    po_error("parse error; may be a bug in po/check-markup");
	}
}

# Some makefiles (currently the top-level Makefile.am) expect this script to
# exit 1 if any problems found.
exit $rc;
