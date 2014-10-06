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
* $Id: cc-defines.php 13254 2009-08-09 21:46:58Z fourstones $
*
*/

/**
* Core defines for the system
*
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-defines-events.php');
require_once('cchost_lib/cc-defines-access.php');
require_once('cchost_lib/cc-defines-filters.php');

/**
 * The name of this software. This is to not be changed.
 */
define('CC_APP_NAME', 'cchost');
define('CC_APP_NAME_PRETTY', 'ccHost');

/**
 * This constant is for a generic PROJECT_NAME for the project. This is
 * specific to a project and is not generally the same for every project.
 * @see CCLanguage
 * @see CCLanguage::LoadLanguages()
 * @see CCLanguage::SetLocalePref()
 * @see CCLanguage::GetLocalePref()
 */
define('CC_PROJECT_NAME', 'CC_APP_NAME');


/**
* Current Version
*/
define('CC_HOST_VERSION', '5.1');

define( 'CC_GLOBAL_SCOPE', 'media' );
define( 'CC_LOCAL_SCOPE',  'local' );

define('CC_1MG', 1024 * 1024);

define('CC_USER_COOKIE', 'lepsog3');

/**
 * Need this for defining default encoding across the board
 */
define('CC_ENCODING', 'UTF-8');

/**
 * Need this for defining default dealing with special characters.
 */
define('CC_QUOTE_STYLE', ENT_COMPAT);

/* LANGUAGE DEFINES */

/** 
 * Default language is nothing so that the default strings in the code are the 
 * default. This is en_US because the original author strings are written by 
 * english speakers
 * @see CCLanguage
 */
define('CC_LANG', 'en_US');

/**
 * This constant is the default locale folder to find i18n translations.
 * @see CCLanguage
 * @see CCLanguage::LoadLanguages()
 * @see CCLanguage::CCLanguage()
 */
define('CC_LANG_LOCALE', 'locale');

/**
 * This constant is the default locale preference folder to find different
 * locale sets for possible different translations depending on installation
 * and user preference that are larger than just per-language differences of
 * i18n translations.
 * @see CCLanguage
 * @see CCLanguage::CCLanguage()
 * @see CCLanguage::LoadLanguages()
 * @see CCLanguage::SetLocalePref()
 * @see CCLanguage::GetLocalePref()
 */
define('CC_LANG_LOCALE_PREF', 'default');

/**
 * This constant is the default full path relative to an installation / web
 * root for the locale preference directory.
 * @see CCLanguage
 * @see CCLanguage::CCLanguage()
 * @see CCLanguage::LoadLanguages()
 * @see CCLanguage::SetLocalePref()
 * @see CCLanguage::GetLocalePref()
 */
define('CC_LANG_LOCALE_PREF_DIR', CC_LANG_LOCALE . '/' . CC_LANG_LOCALE_PREF);

/**
 * This constant is the domain for messages and is usually the same short
 * name for the project or package to be installed.
 * @see CCLanguage
 * @see CCLanguage::CCLanguage()
 * @see CCLanguage::LoadLanguages()
 * @see CCLanguage::SetDomain()
 * @see CCLanguage::GetDomain()
 */
define('CC_LANG_LOCALE_DOMAIN', CC_APP_NAME);

/**
 * This constant is the default full po filename.
 * @see CCLanguage
 * @see CCLanguage::SetDomain()
 * @see CCLanguage::GetDomain()
 */
define('CC_LANG_PO_FN', CC_LANG_LOCALE_DOMAIN . '.po');


/**#@+
* menu action flag
* @access private
*/
define('CC_MENU_DISPLAY', 1);
define('CC_MENU_EDIT',    2);
/**#@-*/

/**#@+
* Form field flag. See {@link CCForm::AddFormFields()} for details.
*/
define('CCFF_NONE',             0);
define('CCFF_SKIPIFNULL',     0x01); // insert/update - GetFormValues
define('CCFF_NOUPDATE',       0x02); // insert/update

define('CCFF_POPULATE',       0x04); // populate - PopulateValues
define('CCFF_POPULATE_WITH_DEFAULT', CCFF_POPULATE | 0x400); 

define('CCFF_HIDDEN',         0x08); // html form - GenerateForm

define('CCFF_REQUIRED',       0x20); // validate - ValidateFields
define('CCFF_NOSTRIP',        0x40); // validate
define('CCFF_NOADMINSTRIP',   0x80); // validate
define('CCFF_STATIC',        0x100); // validate
define('CCFF_HTML',          0x200); // populate/validate

define('CCFF_HIDDEN_DEFAULT',  CCFF_HIDDEN | CCFF_POPULATE);
/**#@-*/



/**#@+
* Resevered system tags (originally called upload descriptor, hence CCUD)
*/
define('CCUD_ORIGINAL',  'original');
define('CCUD_REMIX',     'remix');
define('CCUD_SAMPLE',    'sample');

define('CCUD_MEDIA_BLOG_UPLOAD',     'media');

define('CCUD_CONTEST_MAIN_SOURCE',   'contest_source');
define('CCUD_CONTEST_SAMPLE_SOURCE', 'contest_sample');
define('CCUD_CONTEST_ALL_SOURCES',   'contest_sample, contest_source');
define('CCUD_CONTEST_ENTRY',         'contest_entry');
define('CCUD_CONTEST_ALL',           'contest_entry,contest_sample,contest_source');
/**#@-*/

/**#@+
* Tag type, used by the {@link CCTags::CCTags() tagging system}
*/
define('CCTT_SYSTEM', 1);
define('CCTT_ADMIN',  2);
define('CCTT_USER',   4);
/**#@-*/

/**#@+
* Search criteria flag
*/
define( 'CC_SEARCH_USERS', 1 );
define( 'CC_SEARCH_UPLOADS', 2 );
define( 'CC_SEARCH_ALL',  CC_SEARCH_USERS | CC_SEARCH_UPLOADS);
/**#@-*/

/**#@+
* Upload event type flag (see {@link CC_EVENT_UPLOAD_DONE}
*/
define( 'CC_UF_NEW_UPLOAD', 1 );
define( 'CC_UF_FILE_REPLACE', 2 );
define( 'CC_UF_FILE_ADD', 3 );
define( 'CC_UF_PROPERTIES_EDIT', 4 );
/**#@-*/

/**
* not used?
* @access private
*/
define( 'CCMF_CUSTOM', 1 );

/**
* @access private
*/
define('CC_ENABLE_KEY', 'jimi');

/**
* When listing multiple records, how many links show in the 'Samples are used in' box
* before the 'More...' link shows up?
*/
define('CC_MAX_SHORT_REMIX_DISPLAY', 3);

/**#@+
* User registration type mode
*/
define('CC_REG_USER_EMAIL', 3 );
define('CC_REG_ADMIN_EMAIL', 2 );
define('CC_REG_NO_CONFIRM', 0 );
/**#@-*/


/**
* Dataview flags
*/
define('CC_DV_MENUS',  1 );
define('CC_DV_REMIXES',  2 );
define('CC_DV_REMIXES_3',  4 );
define('CC_DV_FILES',  8 );
define('CC_DV_TAGLINKS',  0x10 );

?>
