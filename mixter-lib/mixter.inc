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
* $Id: mixter.inc 8957 2008-02-11 22:01:26Z fourstones $
*
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

class CCMixter
{
    function Samples()
    {
        $page =& CCPage::GetPage();
        
        $sample_link = $song_link = $all_link = false;

        if( !empty($_REQUEST['tags']) )
            $tags = CCUtil::StripText($_REQUEST['tags']);

        if( empty($tags) )
            $tags = 'sample';

        $base = ccl('view','media','samples');

        $args['sample']   = array( 'text' => 'samples only',
                                    'normal' => true,
                                    'selected' => false,
                                    'action' => url_args( $base, 'tags=sample' ) ) ;

        $args['sample original']   = array( 'text' => 'samples &amp; fully mixed tracks', 
                                    'selected' => false,
                                    'normal' => true,
                                    'action' => url_args( $base, 'tags=sample+original' ) );

        $args['original']   = array( 'text' => 'fully mixed tracks only',
                                    'selected' => false,
                                    'normal' => true,
                                    'action' => url_args( $base, 'tags=original' ) );

        if( !empty($args[$tags]) )
        {
            $args[$tags]['selected'] = true;
            $args[$tags]['normal'] = false;
        }


        $show_plugs = empty($_REQUEST['offset']);

        $page->PageArg('show_plugs',$show_plugs);
        $page->PageArg('samples_links',$args);

        $tagapi = new CCTag();
        $tagapi->BrowseTags($tags,'any');

        $page->ViewFile('samples.xml');
    }

}


?>