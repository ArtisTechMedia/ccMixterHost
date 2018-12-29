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
* $Id: cc-lang-editor.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/**
* @package cchost
* @subpackage lang
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

class CCLanguageEditor
{
    function Menu($seldom='',$sellang='',$msg='')
    {
        $lang = new CCLanguage();
        $langs = $lang->GetAllLanguages();

        $dirs = glob( CC_LANG_LOCALE . '/*', GLOB_ONLYDIR );
        foreach( $dirs as $dir )
        {
            $dom = basename($dir);
            if( empty($langs[CC_LANG_LOCALE][$dom]) )
                $langs[CC_LANG_LOCALE][$dom] = array( 'path' => $dir );

            $langdirs = glob( $dir . '/*', GLOB_ONLYDIR );
            $langinfo = array();
            foreach( $langdirs as $langdir )
            {
                $langname = basename($langdir);
                if( empty($langs[CC_LANG_LOCALE][$dom]['language'][$langname]) )
                {
                    $langs[CC_LANG_LOCALE][$dom]['language'][$langname] = 
                            array( 'path' => $langdir );
                }
            }
        }

        $args['locales'] = $langs['locale'];

        if( !empty($sellang) )
        {
            $args['locales'][$seldom]['language'][$sellang]['selected'] = 'selected';
        }
        elseif( !empty($seldom) )
        {
            $args['locales'][$seldom]['selected'] = 'selected';
        }

        $args['localedirs'] = 
        $args['syslocales'] = @$lang->GetSystemLocales();

        $args['str_explain'][]         = _('ccHost can be configured to host many different media types. A site that specializes in a particular media type might have a different "personality" than another media type. For example the terms used in a community music remixing site may not be appropriate for a legal document sharing site or a personal picture blog');
        $args['str_explain'][]         = _('That is what we mean by a "language terms domain." If the default terms used by ccHost do not fit your type of domain, you should create a new domain (e.g. called legaldocs), add a language (use \'en_US\' for stateside English) and edit the terms to fit your needs.');
        $args['str_explain'][]         = _("After you are done editing, you can set the site's language preferences to use your new domain and language term set and internation translators can use this screen to create tranlations of that domain's language terms.");

        $args['str_createnewdomain'] = _('Create a new domain:');
        $args['str_go']              = _('Go');
        $args['str_createnewlang']   = _('Create a new language/term set in:');
        $args['str_editanexisting']  = _('Edit an existing language/term set:');
        $args['str_nodomname']       = _('Missing domain name');
        $args['str_missinglangname'] = _('Missing language name');
        $args['msg']                 = $msg;

        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->PageArg('language_editor_menu', 'language-editor.xml/language_editor_menu');
        $page->PageArg('LEM',$args,'language_editor_menu');
        $page->SetTitle(_('Language Domain Terms Editor'));
    }

    function MakeDomain($domname='')
    {
        $domname = CCUtil::Strip($domname);
        if( empty($domname) )
        {
            $this->Menu('','',_('Not a valid domain name'));
        }
        else
        {
            $dompath = 'locale/' . $domname;
            if( file_exists($dompath) )
            {
                $this->Menu('','',_('A file or directory of that name already exists'));
            }
            else
            {
                CCUtil::MakeSubdirs($dompath);
                $this->Menu($domname,'',_('Created domain'));
            }
        }
    }

    function MakeLanguage($domain='',$lang='')
    {
        $domain = CCUtil::Strip($domain);
        $lang   = CCUtil::Strip($lang);
        if( empty($domain) || empty($lang) )
        {
            $this->Menu( '', '', _('Not a valid domain or language name'));
        }
        else
        {
            $langpath = 'locale/' . $domain . '/' . $lang . '/LC_MESSAGES';
            if( file_exists($langpath) )
            {
                $this->Menu($domain,$lang,
                    _('A file or directory of that name already exist, you can edit it now.'));
            }
            else
            {
                CCUtil::MakeSubdirs($langpath);
                $this->Menu($domain,$lang,_('Created language path, you must edit the language before using it.'));
            }
        }
    }

    function Editor($domain='',$language='',$zone=0)
    {
        global $CC_GLOBALS;

        $page =& CCPage::GetPage();
        $page->SetTitle(_('Language Terms Editor'));

        $domain     = CCUtil::Strip($domain);
        $language   = CCUtil::Strip($language);

        if( empty($domain) || empty($language) )
        {
            $page->Prompt(_('Missing domain or language'));
            return;
        }

        $tt = $this->_get_lang_file($domain,$language);
        $q = empty($_REQUEST['q']) ? '' : CCUtil::Strip($_REQUEST['q']);

        if( empty($q) )
        {
            if( !intval($zone) || $zone > 4 )
                $zone = 0;
            $part = count($tt) / 5;
            $start = $part * $zone;
            $end = $start + $part;
            $strs = array();
            $keys = array_keys($tt);
            for( $i = $start; $i < $end; $i++ )
            {
                $e = $tt[$keys[$i]];
                if( empty($e[1]) )
                    $e[0] = '*' . $e[0];
                $strs[$keys[$i]] = $e;
            }
        }
        else
        {
            $this->_filter_search = '/' . preg_quote($q) . '/i';
            $strs = array_filter($tt, array( $this, '_search_strings' ) );
            $k = array_keys($strs);
            $c = count($k);
            for( $i = 0; $i < $c; $i++ )
            {
                $e =& $strs[$k[$i]];
                if( empty($e[1]) )
                    $e[0] = '*' . $e[0];
            }
        }

        $args = array();
        $args['string_table'] = $strs;
        $args['domain'] = $domain;
        $args['language'] = $language;
        $args['what_part'] = $zone;

        $args['str_select_a_string'] =     _('Select a string');
        $args['str_domain'] =              _('Domain'); 
        $args['str_generate_pot'] =        _('Generate po');
        $args['str_original_string'] =     _('Original String');    
        $args['str_copy_this_string'] =    _('copy this string');   
        $args['str_translates_to'] =       _('Translates to');      
        $args['str_commit_and_advance'] =  _('Save String and Advance'); 
        $args['str_commit'] =              _('Save String');
        $args['str_revert'] =              _('Revert');          
        $args['str_will_be_saved_to'] =    _('Will be saved to');
        $args['str_missing_domain'] =      _('You must specify a target domain');
        $args['str_waiting'] =             _('waiting...');
        $args['str_part1'] =               _('Part 1');
        $args['str_part2'] =               _('Part 2');
        $args['str_part3'] =               _('Part 3');
        $args['str_part4'] =               _('Part 4');
        $args['str_part5'] =               _('Part 5');
        $args['str_search'] =              _('Search');
        $args['str_empty_search'] =        _('No search string!');
        $args['str_currently_ed'] =        _('Current editing:');
        $args['str_save_to'] =             _('Save to...');
        $args['str_missing_language'] =    _('Missing language');
        $args['str_clear'] =               _('Clear Search');
        $args['str_query'] =               $q;

        $page->PageArg('language_editor', 'language-editor.xml/language_editor');
        $page->PageArg('LE',$args,'language_editor');
    }

    function _search_strings($str)
    {
        return preg_match( $this->_filter_search, $str[0] );
    }


    function GetString($domain,$lang,$hash)
    {
        require_once('cchost_lib/zend/json-encoder.php');
        $tt = $this->_get_lang_file($domain,$lang);
        $json['msgid']       = $tt[$hash][0];
        $json['msgstr']      = $tt[$hash][1];
        $json['context']     = $tt[$hash][2];
        $json['target_file'] = "locale/$domain/$lang/cchost.lang";
        $js = CCZend_Json_Encoder::encode($json);
        header( "X-JSON: $js" );
        header( 'Content-type: text/plain' );
        print($js);
        exit;
    }

    function EditString($domain,$lang,$hash)
    {
        if( empty($hash) ) {
            print("Invalid hash");
            exit;
        }
        $tt = $this->_get_lang_file($domain,$lang);
        $tt[$hash][1] = CCUtil::StripSlash(urldecode($_REQUEST['string']));
        $text = serialize($tt);
        $target_file = "locale/$domain/$lang/cchost.lang";
        $f = fopen($target_file,'w');
        fwrite($f,$text);
        fclose($f);
        chmod($target_file,cc_default_file_perms());
        $this->_return_stat( _('String saved to:') . $target_file);
    }

    function WritePot($domain,$lang)
    {
        $target_file = $this->_write_pot_file($domain,$lang);
        $this->_return_stat( _('po file written:') . $target_file);
    }
    
    function _return_stat($msg)
    {
        require_once('cchost_lib/zend/json-encoder.php');
        $json['statmsg'] = $msg;
        $js = CCZend_Json_Encoder::encode($json);
        header( "X-JSON: $js" );
        header( 'Content-type: text/plain' );
        print($js);
        exit;
    }

    function _parse_pot_file($pot_file,$fname)
    {
        $text = file_get_contents($pot_file);
        //$regex2 = '/msgid (.*)msgstr(.*)\n\n/sU';
        $regex2 = '/#:(.*)(#,.*)?msgid (.*)msgstr(.*)\n\n/sU';
        preg_match_all($regex2,$text,$matches,PREG_SET_ORDER);

        $regex3 = '/"(.+)"$/mSU';

        $hash = array();
        foreach( $matches as $m)
        {
            preg_match_all($regex3,$m[3],$t);
            $msgid = str_replace('\'','\\\'', join( "\n", $t[1] ));
            if( !$msgid )
                continue;
            if( $m[4] )
            {
                preg_match_all($regex3,$m[4],$t);
                $msgstr = str_replace('\'','\\\'',join( "\n", $t[1] ));
            }
            else
            {
                $msgstr = '';
            }
            $key = CCUtil::HashString($msgid);
            $hash[ $key ] = array( $msgid, $msgstr, $m[1] );
        }

        $this->_merge_config_strings($hash);

        uasort($hash,'_cc_str_cmp');

        $text = serialize($hash);

        $f = fopen($fname,'w');
        fwrite($f,$text);
        fclose($f);
        chmod($fname,0777);
    }

    function _merge_config_strings(&$hash)
    {
        $changed = false;
        $configs =& CCConfigs::GetTable();
        // don't do this through GetConfig becuase
        // that will translate the strings
        $args['config_type'] = 'strhash';
        $row = $configs->QueryRow($args);
        if( empty($row) )
            return false;
        $hashtable = unserialize($row['config_data']);
        $hashkeys = array_keys($hashtable);
        $cstr = ' config';
        foreach( $hashkeys as $hkey )
        {
            if( empty($hash[ $hkey ]) )
            {
                $str = $hashtable[ $hkey ];
                $hash[ $hkey ] = array( $str , '', " config\n");
                $changed = true;
            }
            else
            {
                if( strpos($hash[ $hkey ][2], $cstr ) === false )
                    $hash[ $hkey ][2] .=  "#: config\n";
            }
        }

        return $changed;
    }

    function _create_lang_file($domain,$language)
    {
        $lang_file = "locale/$domain/$language/cchost.lang";
        $pot_file = "locale/$domain/$language/LC_MESSAGES/cchost.po";
        if( !file_exists($pot_file) )
        {
            $pot_file = "cchost.pot";
            if( !file_exists($pot_file) )
            {
                $page =& CCPage::GetPage();
                $page->SystemError(_('Can not find language map') . " ($pot_file)" );
                return;
            }
        }
        $this->_parse_pot_file($pot_file,$lang_file);
    }

    function _get_lang_file($domain,$language)
    {
        $fname = "locale/$domain/$language/cchost.lang";
        if( !file_exists($fname) )
        {
            $this->_create_lang_file($domain,$language);

            // config merge has already happened
            // so just return...
            $text = file_get_contents($fname);
            return unserialize($text);
        }

        $text = file_get_contents($fname);
        $tt = unserialize($text);
        if( $this->_merge_config_strings($tt) ) // true if changed
        {
            // write out the new merged .lang
            $text = serialize($tt);
            $f = fopen($fname,'w');
            fwrite($f,$text);
            fclose($f);
            chmod($fname,0777);
        }
        return $tt;
    }

    function _write_pot_file($domain,$lang)
    {

        global $CC_GLOBALS;

        $year = date('Y');
        $user = $CC_GLOBALS['user_real_name'];
        $email = $CC_GLOBALS['user_email'];
        $admin_email = $CC_GLOBALS['mail_sender'];
        $date = gmdate('Y-m-d H:i+0000');
        $configs =& CCConfigs::GetTable();
        $ttag = $configs->GetConfig('ttag');
        $proj = $ttag['site-title'];
        $ver = $CC_GLOBALS['cc-host-version'];

        $txt =<<<END
# This is a language file used for translation
# This file is distributed under the same license as the cchost package.
# Copyright (C) Creative Commons <info@creativecommons.org>, $year.
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: ccHost: $ver\\n"
"Report-Msgid-Bugs-To: $email\\n"
"POT-Creation-Date: $date\\n"
"PO-Revision-Date: $date\\n"
"Last-Translator: $user <$email>\\n"
"Language-Team: $proj-language <$admin_email>\\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=2;plural=(n>1);\n"
"X-Generator: ccHost\n"
END;
        $intext = file_get_contents( "locale/$domain/$lang/cchost.lang" );
        $I = unserialize($intext);
        $keys = array_keys($I);
        $c = count($keys);
        $heads = array( 'msgid: ', 'msgstr: ' );

        for( $i = 0; $i < $c; $i++ )
        {
            $e =& $I[$keys[$i]];

            $txt .= "\n#:{$e[2]}";
            for( $n = 0; $n < 2; $n++ )
            {
                $txt .= $heads[$n];
                $strs = cc_split("\n",$e[$n]);
                if( count($strs) > 1  ) 
                {
                    $txt .= "\"\"\n";
                    foreach( $strs as $s )
                        $txt .= "\"$s\"\n";
                }
                else
                {
                    $txt .= "\"{$strs[0]}\"\n";
                }
            }
        }

        $outname = "locale/$domain/$lang/LC_MESSAGES/cchost.po";
        $f = fopen($outname,'w');
        fwrite($f,$txt);
        fclose($f);
        chmod($outname,0777);
        return $outname;
    }

}

function _cc_str_cmp($a,$b)
{
    return strncasecmp($a[0],$b[0],25);
}
?>
