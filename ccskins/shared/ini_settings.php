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
* $Id: ini_settings.php 12729 2009-06-06 05:42:01Z fourstones $
*
*/

if( empty($A['is_admin']) )
    return;
?>
    <style type="text/css">
    .ini_table .c
    {
        text-align: center;
    }
    .ini_table .r
    {
        text-align: right;
    }
    .ini_table td
    {
       padding: 3px;
       vertical-align: top;
    }
    .ini_table th
    {
        border-bottom: solid 1px #999;
    }
    .file_name
    {
        font-family:Courier New, courier, serif;
        font-size:smaller;
    }
    </style>

<?
function cc_check_ini_settings()
{
    $v['file_uploads']['v'] = ini_get('file_uploads');
    $v['file_uploads']['s'] = 'On (1)';
    $v['file_uploads']['m'] = 'This is required to be <b>On</b> to allow uploads';
    $v['file_uploads']['k'] = ($v['file_uploads']['v'] && ($v['file_uploads']['v'] != 'Off'));
    $v['file_uploads']['i'] = ' ';

    $v['upload_max_filesize']['v'] = ini_get('upload_max_filesize');
    $v['upload_max_filesize']['s'] = '10M';
    $v['upload_max_filesize']['m'] = 'Determines the overall maximum file upload size. (Typical MP3 song is encoded at 1M per minute.)';
    preg_match('/([0-9]*)/',$v['upload_max_filesize']['v'],$m);
    $i = intval($m[1]);
    $v['upload_max_filesize']['i'] = $i;
    $v['upload_max_filesize']['k'] = $i < 10 ? false : true;

    $v['post_max_size']['v'] = ini_get('post_max_size');
    $v['post_max_size']['s'] = '10M';
    $v['post_max_size']['m'] = 'Determines the maximum file upload size from an HTML form.';
    preg_match('/([0-9]*)/',$v['post_max_size']['v'],$m);
    $i = intval($m[1]);
    $v['post_max_size']['k'] = $i < 10 ? false : true;
    $v['post_max_size']['i'] = $i;

    $v['memory_limit']['v'] = ini_get('memory_limit');
    $v['memory_limit']['s'] = '25';
    if( $v['memory_limit']['v'] )
    {
        $v['memory_limit']['m'] = 'Dealing with large file can consume a lot of memory, being too stingy can have adverse affects.';
        preg_match('/([0-9]*)/',$v['memory_limit']['v'],$m);
        $i = intval($m[1]);
        $v['memory_limit']['k'] = $i < 25 ? false : true;
        $v['memory_limit']['i'] = $i;
    }
    else
    {
        $v['memory_limit']['m'] = '<i>It looks as though your installation of PHP is not compiled to use <a target="_blank"  href="http://us3.php.net/manual/en/ini.core.php#ini.memory-limit">this setting</a>.</i>';
        $v['memory_limit']['k'] = 1;
        $v['memory_limit']['i'] = '';
    }

    $v['max_execution_time']['v'] = ini_get('max_execution_time');
    $v['max_execution_time']['s'] = '120';
    $v['max_execution_time']['m'] = 'Number of seconds a script will execute before aborting. You have to allow for users who upload large files over slow connections.';
    $i = intval($v['max_execution_time']['v']);
    $v['max_execution_time']['i'] = $i;
    $v['max_execution_time']['k'] = $i < 120 ? false : true;

    $v['max_input_time']['v'] = ini_get('max_input_time');
    $v['max_input_time']['s'] = '-1';
    $v['max_input_time']['m'] = 'Number of seconds a form\'s script will execute before aborting. You have to allow for users who upload large files over slow connections. (setting to -1 allows unlimited time)';
    $i = intval($v['max_input_time']['v']);
    $v['max_input_time']['i'] = $i;
    $v['max_input_time']['k'] = ($i > -1) && ($i < 120) ? false : true;

?>
    <h1>Your PHP environment</h1>
    <p>There are several things you should know about uploading files to a PHP environment.</p>
    <p>The default settings for a PHP install may not be the ideal. A list of all PHP settings, where they can
    be changed and what version they apply to can be found <a href="http://us3.php.net/manual/en/ini.php#ini.list">here</a>.</p>
    <p>Below are some settings you should be aware of. You might want to
    print or save this page for future reference.</p>
<?
    
    $ini_location = get_php_ini_location();
    $local_ini = dirname(dirname(dirname(__FILE__))) . '/php.ini';

    if( !empty($ini_location) )
    {
        $inimsg = "These can be updated in your global php initialization file which appears to be located at
     <span class='file_name'>$ini_location</span>.";
    }
    else
    {
        $inimsg = 'These can be updated in your php.ini file; on gentoo this is located at: /etc/php/apache2-php4/php.ini';
    }

    $inimsg =<<<EOF
        <p>$inimsg</p>
    <p>If you do not have access to the global php.ini you can create one with just these settings
    and place in them <span class="file_name">$local_ini</span></p>

EOF;

    print $inimsg;

?>
    <table class="ini_table" style="width:60%">
    <tr><th>Setting Name</th><th>Description</th><th>Current<br />Value</th><th>Suggested<br />Value</th></tr>
<?
    $html = '';
    foreach( $v as $n => $d )
    {
        $html .= "<tr><td class='r'><b>$n</b></td><td>{$d['m']}</td><td class='c'";
        if( !$d['k'] )
            $html .= " style='color:red' ";
        $html .= ">{$d['v']}</td><td class='c'>{$d['s']}</td></tr>\n";
    }
    print($html);
?>
    </table>

<?
}

function get_php_ini_location()
{
    ob_start();
    phpinfo();
    $info = ob_get_contents();
    ob_end_clean();
    preg_match( '#(?:>|=> )+([^\s]+php\.ini)#', $info, $m );
    if( !empty($m[1]) )
        return $m[1];
    return '';
}

cc_check_ini_settings();
?>

