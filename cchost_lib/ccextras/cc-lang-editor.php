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
* $Id: cc-lang-editor.php 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/**
* @package cchost
* @subpackage lang
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

//CCEvents::AddHandler(CC_EVENT_MAP_URLS,    array( 'CCLanguageEditorAPI', 'OnMapUrls' ));

class CCLanguageEditorAPI
{
    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('admin','terms'),  
                            array( 'CCLanguageEditor', 'Menu'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','termseditor'),  
                            array( 'CCLanguageEditor', 'Editor'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','terms','edit'),  
                            array( 'CCLanguageEditor', 'EditString'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','terms','getstring'),  
                            array( 'CCLanguageEditor', 'GetString'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','terms','writepot'),  
                            array( 'CCLanguageEditor', 'WritePot'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','terms','genconfig'),  
                            array( 'CCLanguageEditor', 'GenConfigStrings'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','terms','makedomain'),  
                            array( 'CCLanguageEditor', 'MakeDomain'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','terms','makelanguage'),  
                            array( 'CCLanguageEditor', 'MakeLanguage'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');

        CCEvents::MapUrl( ccp('admin','terms','saveto'),  
                            array( 'CCLanguageEditor', 'SaveTo'), CC_ADMIN_ONLY, 
                            'cchost_lib/ccextras/cc-lang-editor.inc');
    }

}


?>
