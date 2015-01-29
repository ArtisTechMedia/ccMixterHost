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
* $Id: mixter-contest.inc 12467 2009-04-29 05:09:20Z fourstones $
*
*/

/**
*
* How to close a contest:
*
* 1. Fill out the form at to http://ccmixter.org/media/contest/snap
*
*    This step might take a while depending on how many entries in 
*    the contest. You should end up at the home page for the final 
*    entries. Note the URL, this what you give to whoever is picking 
*    up the entries.
*
* 2. Open a shell and go to mixter's web root directory. Execute
*    the shell script called (contest_name)_snap.sh This step will 
*    copy all the entries from the upload directory to the final
*    entries so it might take a while.
*
* 3. To remove the artist's name and track name from the mp3s go to:
*    http://ccmixter.org/media/contest/tagentries/(contest_name)
*
* 
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

require_once('cchost_lib/cc-form.php');

class MxSnapContestForm extends CCForm
{
    function MxSnapContestForm($contest='')
    {
        $this->CCForm();

        $dirs = array();
        if ($cc_dh = opendir('.')) 
        {
            while (($name= readdir($cc_dh)) !== false) 
            {
                if( $name{0} == '.' )
                    continue;
                $d = realpath( './' . $name);
                if( is_dir($d) )
                    $dirs[$name] = $name;
            }
            closedir($cc_dh);
        }

        $fields = array(
             'contest' => array(
                 'label' => 'Contest Short Name',
                 'formatter' => 'textedit',
                 'value' => $contest,
                 'flags' => CCFF_REQUIRED,
                 ),
             'prefix' => array(
                 'label' => 'Output prefix',
                 'class' => 'cc_form_input_short',
                 'formatter' => 'textedit',
                 'flags' => CCFF_REQUIRED,
                 ),
             'outdir' => array(
                 'label' => 'Output Directory',
                 'formatter' => 'select',
                 'options'  => $dirs,
                 'flags' => CCFF_NONE,
                 ),
             'killsubmit' => array(
                 'label' => 'Delete Contest Submit Form',
                 'formatter' => 'checkbox',
                 'flags' => CCFF_NONE,
                 ),
         );
      
        $this->AddFormFields($fields);
    }
}

class MixterContest
{
    function OnMapUrls()
    {
        CCEvents::MapUrl( ccp('contest','snap'), array( 'MixterContest', 'Snap' ), CC_ADMIN_ONLY, ccs(__FILE__),'','','ccMixter'  );
        CCEvents::MapUrl( ccp('contest','tagentries'), array( 'MixterContest', 'TagEntries' ), CC_ADMIN_ONLY, ccs(__FILE__),'','','ccMixter'  );
    }

    function TagEntries($contest)
    {
        $tdir = empty($_GET['tdir']) ? '_final_entries' : $_GET['tdir'];
        $dir = "$tdir/$contest";
        $id3 =& CCGetID3::InitID3Obj();
        getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__, true);

        $tagwriter = new getid3_writetags;
        $tagwriter->tagformats = array( "id3v1", "id3v2.3" );
        $tagwriter->overwrite_tags = true;
        $tagwriter->remove_other_tags = true;

        print("<html><body>");

        if ($cc_dh = opendir($dir)) 
        {
            while (($name= readdir($cc_dh)) !== false) 
            {
                if( $name{0} == '.' )
                    continue;
                $d = realpath( "$dir/$name");
                if( is_dir($d) )
                    continue;
                if( preg_match('/.*[^0-9]([0-9]+)\.mp3$/',$name,$m) )
                {
                    $n = '"' . $m[1] . '"';
                    $tags['ORIGINAL_ARTIST'] = array( $n );
                    $tags['TITLE'] = array( $n );
                    $tags['ARTIST'] = array( $n );
                    $tagwriter->filename = $d;
                    $tagwriter->tag_data = $tags;
                    $res = $tagwriter->WriteTags();
                    if( empty($res) )
                    {
                        print("<pre>");
                        print_r($tagwriter);
                        print("</pre>");
                        exit;
                    }
                    print("$name: $res<br />");
                }
            }
            closedir($cc_dh);
        }
        
        print("</body></html>");
        exit;
    }

    function Snap($contest='')
    {
        $page =& CCPage::GetPage();
        $page->SetTitle('ccMixter Contest Snap');

        $form = new MxSnapContestForm($contest);
        if( empty($_POST['snapcontest']) || !$form->ValidateFields() )
        {
            $page->AddForm( $form->GenerateForm() );
        }
        else
        {
            $form->GetFormValues($values);
            $this->_do_snap($values);
        }
    }

    function _do_snap($values)
    {
        CCDebug::Enable(true);
        extract($values);

        if( !empty($killsubmit) )
        {
            $configs =& CCConfigs::GetTable();
            $forms = $configs->GetConfig('submit_forms');
            if( !empty($forms[$contest]) )
            {
                unset($forms[$contest]);
                $configs->SaveConfig('submit_forms',$forms,'media',false);
            }
        }

        $uploads =& CCUploads::GetTable();
        $uploads->SetTagFilter("$contest,contest_entry",
                               'all');
        $uploads->SetDefaultFilter(true,true);
        $uploads->SetOrder('upload_id');
        $records = $uploads->GetRecords('');
        $count = count($records);
        $total = 0;
        $total_size = 0;
        $userinfos = array();
        $nouserinfos = array();
        $html = '<table cellspacing="0" cellpadding="0" >';

        $c = array( '',
                    'class="c"' );

        $ci = 0;
        $csv = "filename,login_name,legal_name,email,phone,country,birthdate\n";

        $shell_script =<<<END
ccc()
{
    if [  ! -f "$1" ]
    then
        echo "$1 doesn't exists"
    else
        if [  -f "$2" ]
        then
            echo "$2 already exists"
            rm "$2"
        fi
        cp "$1" "$2"
        echo "Copying: $1"
        echo "  -  to: $2"
     fi
}
END;

        $tdir = "$outdir/$contest/";

        for( $i = 0; $i < $count; $i++ )
        {
          $R =& $records[$i];
          $username = $R['user_name'];
          if( empty($userinfos[$username]) )
          {
             if( empty($R['user_extra']
                  ['user_info'][$contest]) )
             {
                $nouserinfo[] = $username;
             }
             else
             {
                $userinfos[$username] = $R['user_extra']
                  ['user_info'][$contest];
             }
          }

          $U =& $userinfos[$username];
          $m = substr($U['ux_name'], 0, 34);
          $p = $U['ux_phone'];
          $y = $U['ux_country'];
          $b = $U['ux_birthdate'];
          $e = $R['user_email'];

          $fcount = count($R['files']);
          for( $n = 0; $n < $fcount; $n++ )
          {
            $F =& $R['files'][$n];
            if( $F['file_format_info']['media-type']
                  != 'audio' )
            {
               continue;
            }
            $total++;
            $fn = $F['file_name'];
            $fid = $F['file_id'];
            $ext = $F['file_format_info']['default-ext'];
            $nn = "{$prefix}{$fid}.{$ext}";
            $total_size += ($F['file_rawsize'] / 1024);
            $shell_script .= "\nccc \"{$F['local_path']}\" ".
                  '"' . $tdir . $nn . '"';

           $k = "<a href=\"$nn\">$fid</a>";

           $cls = $c[ $ci ];
           $ci ^= 1;
           $html .= "<tr $cls><td>$k</td><td>$username</td>" .
                    "<td>$m</td><td>$e</td><td>$p</td>" .
                    "<td>$y</td><td>$b</td></tr>\n";
           $csv .= "$nn,$username,\"$m\",$e,\"$p\",\"$y\",$b\n";
          }
        }

        $shell_script .= "\n";

        $csv_name = "{$contest}_entries.csv";
        $csv_path = '/' . $tdir . $csv_name;

        $size = number_format( $total_size / 1024, 2 );

        $page =<<<END
<html>
<head>
<style type="text/css">
   body,table,a {
     font-family: Verdana;
     font-size: 11px;
     color: black;
   }
   td {
     white-space: nowrap;
   }
   a {
     color: blue;
   }
   .c {
     background-color: #DDD;
   }
</style>
</head> 
<body>
<pre>
Total files: $total
Total size: $size MB
<a href="$csv_path">$csv_name</a>

</pre>
$html
</body>
</html>
END;

        $target_dir = "$outdir/$contest";
        CCUtil::MakeSubDirs($target_dir,0777);

        $path_to_index = "$outdir/$contest/index.htm";
        $f = fopen($path_to_index,'w+');
        fwrite($f,$page);
        fclose($f);
        chmod($path_to_index,0777);

        $script = $contest . '_snap.sh';
        $f = fopen($script, 'w+');
        fwrite($f,$shell_script);
        fclose($f);
        chmod($script,0777);

        $path_to_csv = "$outdir/$contest/$csv_name";
        $f = fopen($path_to_csv, 'w+');
        fwrite($f,$csv);
        fclose($f);
        chmod($path_to_csv,0777);

        $url = ccd($path_to_index);
        CCUtil::SendBrowserTo($url);
    }

    function kill_crammed()
    {
        if( !CCUser::IsAdmin() || empty($_GET['killcrammed']) )
            return;

        $data = array (
                'remix' => array (
                    'enabled' => 1,
                    'submit_type' => 'Remix',
                    'text' => 'Submit a Remix',
                    'help' => 'A remix using samples downloaded from this site. When submitting a remix make sure to properly attribute the artist you sampled to comply with the Attribution part the Creative Commons license. The next screen will have a search function that allows you do just that.',
                    'tags' => array (
                        0 => 'media',
                        1 => 'remix',
                        ),
                    'suggested_tags' => '',
                    'weight' => 5,
                    'form_help' => 'There is 10Mg limit on uploads.<br />
    Please do not submit until you have read <a href="/terms"><b>our terms of use</b></a>.<br />
    Having trouble? <a href="/media/viewfile/isitlegal.xml#upload_problems"><b>click here</b></a>.',
                    'isremix' => 1,
                    'media_types' => 'audio',
                    'action' => '',
                    'logo' => 'mixter-remix.gif',
                    'type_key' => 'remix',
                    ),
                'pella' => array (
                    'enabled' => 1,
                    'submit_type' => 'A Cappella',
                    'text' => 'Submit an A Cappella',
                    'help' => 'Stand alone vocal parts, either spoken word or sung. Mono recording with no effects  (reverb, delay, etc.) on them are best because they are the most flexible to work with. Many singers think they sound "better" with a lot of effects but it is always better to leave those choices to a producer/remixer to allow them to use their creative skills to the fullest potential.',
                    'tags' => array (
                        0 => 'acappella',
                        1 => 'media',
                        ),
                    'suggested_tags' => 'melody,rap,spoken_word',
                    'weight' => 10,
                    'form_help' => 'There is 10Mg limit on uploads.<br />
    Please do not submit until you have read <a href="/terms"><b>our terms of use</b></a>.<br />
    Having trouble? <a href="/media/viewfile/isitlegal.xml#upload_problems"><b>click here</b></a>.',
                    'isremix' => '',
                    'media_types' => 'audio',
                    'action' => '',
                    'logo' => 'mixter-pella.gif',
                    'type_key' => 'pella',
                    ),
                'samples' => array (
                    'enabled' => 1,
                    'submit_type' => 'Sample',
                    'text' => 'Submit Samples',
                    'help' => 'Samples can be a loop, a one-shot note or drum hit or any other snippet of sound that might be useful to a producer or remixer. You are encouraged to make a collection of samples and upload them together in archive format (ZIP), however sound files are accepted as well. By far the most flexible samples to work with are mono and have no effects for acoustic instruments and minimal effects for synthesized sounds.',
                    'tags' => array (
                        0 => 'sample',
                        1 => 'media',
                        ),
                    'suggested_tags' => '',
                    'weight' => 15,
                    'form_help' => 'There is 10Mg limit on uploads.<br />
    Please do not submit until you have read <a href="/terms"><b>our terms of use</b></a>.<br />
    Having trouble? <a href="/media/viewfile/isitlegal.xml#upload_problems"><b>click here</b></a>.',
                    'isremix' => '',
                    'media_types' => 'audio,archive',
                    'action' => '',
                    'logo' => 'mixter-loop.gif',
                    'type_key' => 'samples',
                    ),
                'userform1' => array (
                    'enabled' => 1,
                    'submit_type' => 'ccMixter Radio Promo',
                    'text' => 'Submit a ccMixter Radio Promo',
                    'help' => 'Submit a 5-10 second promo to be used in ccMixter radio streams. Make sure to mention the site, feel free to mention yourself (e.g. "This is Dr. Concoction and you\'re listening to c. c. Mixter dot org radio")',
                    'tags' => array (
                        0 => 'site_promo',
                        1 => 'media',
                        ),
                    'weight' => 40,
                    'form_help' => 'Submit a 5-10 second promo to be used in ccMixter radio streams. Anything deemed too long for a radio promo will be deleted immediately.',
                    'isremix' => '',
                    'media_types' => 'audio',
                    'logo' => 'mixter-promo.png',
                    'type_key' => 'userform1',
                    ),
                'fullmix' => array (
                    'enabled' => 1,
                    'submit_type' => 'Original',
                    'text' => 'Submit a Fully Mixed Track',
                    'help' => 'An original track that is fully mixed is <i>extremely unlikely</i> to be remixed because of the extra work the producer or remixer has to do the extract the parts they actually wish to use. Before uploading your track here, consider uploading to one of several free hosting sites sponsored by Creative Commons such <a href="http://archive.org/audio">Internet Archive</a> or <a href="http://ourmedia.org">Our Media</a> both of which might be more appropriate places to post completely mixed tracks.',
                    'tags' => array (
                        0 => 'media',
                        1 => 'original',
                        ),
                    'suggested_tags' => '',
                    'weight' => 50,
                    'form_help' => 'There is 10Mg limit on uploads.<br />
    Please do not submit until you have read <a href="/terms"><b>our terms of use</b></a>.<br />
    Having trouble? <a href="/media/viewfile/isitlegal.xml#upload_problems"><b>click here</b></a>.
    <p style="color:red">The Fort Minor contest is <b>over</b>.</p>',
                    'isremix' => '',
                    'media_types' => 'audio',
                    'action' => '',
                    'logo' => 'mixter-mixed.gif',
                    'type_key' => 'fullmix',
                    ),
               );

        $configs =& CCCOnfigs::GetTable();
        $configs->SaveConfig('submit_forms',$data,'media',false);

        $page =& CCPage::GetPage();
        $page->Prompt('crammed killed');
    }

}

?>
