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
* $Id: cc-skin-export.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/**
* @package cchost
* @subpackage ui
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Class for interfacing with 3rd part web tools
*
*/
class CCSkinExport
{

    /**
    * Method called by cchost customized skins to output ccHost look/feel.
    *
    * The call to this method is made directly from the ccHost template
    * look in 3rd party web tools (like WordPress and phpBB2)
    *
    * To see what this method returns type: http://yourserver/skin/head
    *
    * Put a call to this between head tags
    *
    */
    function GetHeader()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetArg('head-type','ccskins/shared/head.tpl');        
        $html = $page->Show(false);
        $html = preg_replace('%<title.*/title>%','',$html);
        preg_match('%<head>(.*)</head>%Usi',$html,$m);
        $html = $m[1];
        $this->_out_html_and_exit($html);
    }

    /**
    * Called by cchost customized skin 
    *
    * Put a call to this right after the <body> tag in a 3rd
    * party skin system like Wordpress.
    *
    */
    function GetBodyHeader()
    {
        require_once('cchost_lib/cc-page.php');
        $page =& CCPage::GetPage();
        $page->SetArg('head-type','ccskins/shared/head.tpl');
        $page->AddMacro('skin_splitter');
        $naviator_api = new CCNavigator();
        $naviator_api->ShowTabs($page);
        $html = $page->Show(false);
        preg_match('%<body>(.*)(<!-- CONTENT STARTS -->)%si',$html,$m);
        $html = $m[1];
        $this->_out_html_and_exit($html);
    }

    /**
    * Method called by cchost customized skins to output ccHost look/feel.
    *
    * The call to this method is made directly from the ccHost templates
    * for 3rd party tools like WordPress and phpBB2
    *
    * To see what this method returns type: http://yourserver/skin/body_end
    *
    * Put a call to this right before the </body> tag
    *
    */
    function GetFooter($simple='')
    {
        require_once('cchost_lib/cc-page.php');
        $page = new CCPage();
        $page->SetArg('head-type','ccskins/shared/head.tpl');
        $page->AddMacro('skin_splitter');
        $html = $page->Show(false);
        if( $simple )
        {
            preg_match('%(</div>\s{0,}<div id="footer".*)</body>%si',$html,$m);
            if( empty($m[1]) )
                die('This skin does not support simple footer: missing &lt;div id="footer"');
        }
        else
        {
            preg_match('%<!-- CONTENT ENDS -->(.*)</body>%si',$html,$m);
        }
        $html = $m[1];
        $this->_out_html_and_exit($html);
    }


    function _out_html_and_exit($html)
    {
        $html = addslashes($html);
        $lines = cc_split("\n",$html);

        header("Content-type: text/plain");
        foreach($lines as $line)
        {
            $line = trim($line);
            if( $line )
                print( " document.writeln('$line');\n ");
        }

        exit;
    }

    /**
    * Event handler for {@link CC_EVENT_MAP_URLS}
    *
    * @see CCEvents::MapUrl()
    */
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('skin','head'),   
                          array( 'CCSkinExport', 'GetHeader'),    
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),
                          '', _('Export head tag to 3rd party skins'), CC_AG_SKIN);

        CCEvents::MapUrl( ccp('skin','body_begin'),   
                          array( 'CCSkinExport', 'GetBodyHeader'),    
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),
                          '', _('Export between body tag and content to 3rd party skins'), CC_AG_SKIN);

        CCEvents::MapUrl( ccp('skin','body_end'),   
                          array( 'CCSkinExport', 'GetFooter'),    
                          CC_DONT_CARE_LOGGED_IN, ccs(__FILE__),
                          '[simple]', _('Export skin footer to 3rd party skins'), CC_AG_SKIN);
    }

}

?>
