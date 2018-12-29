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
* $Id: fourstones-import.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/*******************
*
* This file exists for historical purposes, it is the script that
* Victor used to import his audio files metadata from a proprietary
* database to ccHost 2.1
*
* NOTES:
*    - I don't remember how well this worked but this is the last snapshot
*      of the script I had on my server so I'm assuming it's the one I finally used
*
*    - The key to making any import script work is making 
*                  CCUploadAPI::PostProcessNewUpload
*      happy and not return any errors
*
*    - This script will MOVE (NOT COPY) your original files so make LOTS
*      and LOTS of backups
*
*    - This script targeted ccHost 2.1 so it would be a small miracle if it
*      worked "as is" against >3.1
*
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

CCEvents::AddHandler(CC_EVENT_MAP_URLS,           array( 'FSImporter', 'OnMapUrl') );

class FSImporter
{
    function OnMapUrl()
    {
        CCEvents::MapUrl( 'fsimport', array( 'FSImporter', 'Import' ),  CC_DONT_CARE_LOGGED_IN,'','','','ccMixter' );
    }

    function Retag()
    {
        $uploads =& CCUploads::GetTable();
        $uploads->SetTagFilter('male_vocals,female_vocals');
        $records = $uploads->GetRecords('');
        foreach( $records as $record)
        {
            $db_args = array();
            $db_args['upload_id']    = $record['upload_id'];
            $db_args['upload_tags']  = $record['upload_tags'] . ',vocals';
            $record['upload_extra']['usertags'] .= ',vocals';
            $db_args['upload_extra'] = serialize($record['upload_extra']);
            $uploads->Update($db_args);
        }
        $tags = new CCTable('cc_tbl_tags','tags_tag');
        $db_args = array();
        $db_args['tags_type'] = 4;
        $db_args['tags_tag'] = 'vocals';
        $db_args['tags_count'] = count($records);
        $tags->Insert($db_args);
    }
            /*
               OLD FIELDS:
               prop_article_type
               prop_author
               prop_bitrate
               prop_comp_type
               prop_desc
               prop_detail_lic_info
               prop_license
               prop_license_info
               prop_media
               prop_running_time
               prop_samplerate
               prop_short_desc
               prop_size
               prop_source_info
  
                NEW FIELDS:
                [upload_user] => 1
                [upload_config] => media
                [upload_name] => nay aplgoe
                [upload_tags] => 
                [upload_description] => 
                [upload_date] => 2005-11-15 19:53:21
                [upload_license] => attribution
                [upload_published] => 1
            */
    function Import($cmd='')
    {
        $page =& CCPage::GetPage(); 
            
        if( $cmd == 'retag' )
            return( $this->Retag() );

        $media_host = new CCMediaHost();

        // THESE ARE THE OLD TABLES...
        $files      = new CCTable('t_content_main','_dev_recid');
        $properties = new CCTable('t_properties','c_ext_key');

        if( !empty($_GET['offset']) )
        {
            $offset = $_GET['offset'];
        }
        else
        {
            $offset = 0;
        }

        $files->SetOffsetAndLimit($offset,20);
        $offset += 20;

        $records = $files->GetRecords('');
        if( empty($records) )
        {
            $page->Prompt("Looks like we're done");
            return;
        }
        $count = count($records);
        for( $i = 0; $i < $count; $i++ )
        {
            $record =& $records[$i];
            $key['c_ext_key'] = $record['_dev_recid'];
            $props_r = $properties->GetRecords($key);
            $c = count($props_r);
            $props = array();
            for( $n = 0; $n < $c; $n++ )
            {
                $prop_name = $props_r[$n]['c_prop_name'];
                $prop_value = $props_r[$n]['c_prop1'];
                switch( $prop_name )
                {
                    case 'prop_source_info':
                    case 'prop_comp_type':
                        $props[$prop_name][] = $prop_value;
                        break;
                    default:
                        $props[$prop_name] = $prop_value;
                        break;
                }
            }

            $args = array();
            $args['upload_user']      = 1;
            $args['upload_config']    = 'media';
            $args['upload_name']      = $record['c_title'];
            $args['upload_date']      = $record['c_created'];
            $args['upload_published'] = 1;

            
            $desc = array();
            if( empty($props['prop_desc']) )
            {
                if( !empty($props['prop_short_desc']) )
                    $desc[] = $props['prop_short_desc'];
            }
            else
            {
                $desc[] = $props['prop_desc'];
            }
            if( !empty($props['prop_source_info']) )
            {
                if( count($props['prop_source_info']) == 1 )
                {
                    $desc[] = "<br /><br \>Source: " . $props['prop_source_info'][0];
                }
                else
                {
                    $desc[] = "<br \><br \><b>Sources</b><br />";
                    foreach( $props['prop_source_info'] as $pinfo )
                        $desc[] = $pinfo . "<br />";
                }
            }

            $args['upload_description'] = join("\n",$desc);

            if( empty($props['prop_license']) )
            {
                $args['upload_license'] = 'attribution';
            }
            elseif( $props['prop_license'] == 'Public Domain' )
            {
                $args['upload_license'] = 'publicdomain';
            }
            elseif( $props['prop_license'] == 'Creative Commons' )
            {
                $args['upload_license'] = 'attribution';
            }
            else
            {
                $args['upload_license'] = 'bootleg';
            }
                
            if( !empty($props['prop_comp_type']) )
            {
                foreach( $props['prop_comp_type'] as $tag )
                {
                    switch( $tag )
                    {
                        case 'Mash':
                            $args['upload_tags'] = 'mash_up';
                            break;
                        case 'Spoken Word':
                            $args['upload_tags'] = 'spoken_word';
                            break;
                        case 'Cover':
                            $args['upload_tags'] = 'cover';
                            break;
                    }
                    if( !empty($args['upload_tags']) )
                        break;
                }
            }

            $current_path = "./" . $record['c_loc'];
            $new_name     = $args['upload_name'];
            $user_tags    = $args['upload_tags'];
            $ret = CCUploadAPI::PostProcessNewUpload(   $args, 
                                                        $current_path,
                                                        $new_name,
                                                        array( CCUD_ORIGINAL, 'media'),
                                                        $user_tags,
                                                        $media_host->_get_upload_dir('fourstones'),
                                                        null );

            if( is_string($ret) )
            {
                // oops error
            }
        }

        $url = ccl('fsimport') . '?offset=' . $offset;
        $page->Prompt("OK, processed $count records. Do <a href=\"$url\">next...</a>.");
    }

}
?>
