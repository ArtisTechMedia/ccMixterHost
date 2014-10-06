<?

chdir('..');

require_once('cchost_lib/cc-defines.php');

if( !function_exists('gettext') )
    require_once('cchost_lib/ccextras/cc-no-gettext.inc');

$step = empty($_REQUEST['up_step']) ? '1' : $_REQUEST['up_step'];

$do_buffer = !empty($_POST);

if( $do_buffer )
{
    ob_start();
}

$install_title = 'ccHost Upgrade';
include( dirname(__FILE__) . '/cc-install-head.php');
grey_me_script();
$stepfunc = 'up_step_' . $step;
$stepfunc();
print '</body></html>';

if( $do_buffer )
{
    $text = ob_get_contents();
    ob_end_clean();
    print $text;
}

exit; // otherwise we see install script

function up_step_1()
{
    include( dirname(__FILE__) . '/cc-upgrade-intro.php');
}

function up_step_2()
{
    ?><h2></h2><?
    
    $step = empty($_REQUEST['impf']) ? '1' : $_REQUEST['impf'];
    $impffunc = 'impf_' . $step;
    $impffunc();
}

function up_step_3()
{
    $username = empty($_POST['username']) ? '' : $_POST['username'];
    
    if( !empty($_POST['submitbutton']) )
    {
        require_once('cchost_lib/cc-non-ui.php');
        
        if( empty($_POST['username']) )
        {
            $err = 'Can not leave username blank';
        }
        elseif ( empty($_POST['password']) )
        {
            $err = 'Can not leave password blank';
        }
        else
        {
            $user_id = CCDatabase::QueryItem('SELECT user_id FROM cc_tbl_user WHERE user_name = \''.
                             $_POST['username'] . '\'');
            if( empty($user_id) )
            {
                $err = 'Do not know that username';
            }
            else
            {
                $md5pw = CCDatabase::QueryItem('SELECT user_password FROM cc_tbl_user WHERE user_id='.$user_id);
                if( $md5pw != md5($_POST['password']) )
                {
                    $err = 'That password does not match the user name';
                }
                else
                {
                    $configs =& CCConfigs::GetTable();
                    $settings = $configs->GetConfig('settings');
                    $_admins = $settings['admins'];
                    if( !(preg_match( "/(^|\W|,){$_POST['username']}(\W|,|$)/i",$_admins) > 0) )
                    {
                        $err = 'That user is not an administrator';
                    }
                    else
                    {                    
                        require_once( 'cchost_lib/cc-login.php' );
                        $lapi = new CCLogin();
                        $lapi->_create_login_cookie(1,$_POST['username'],md5($_POST['password']));
                        $root_url = url_args(ccl(),'update=1');
?>
                        <p>You upgrade is almost complete. Please <b>rename ccadmin</b> to something else.</p>
                        <p>After you have done that, <a href="<?=$root_url?>">CLICK HERE</a> to finish up.</p>
<?                        

                        return;
                    }
                }
            }
        }
    }
?>
    <p>
       We need to log to you in as admin to perform the last step.
    </p>
    <form action="?up_step=3" method="post">
    <table>
       <tr>
           <td>Your admin login name:</td><td><input name="username" value="<?= $username ?>"/></td>
       </tr>
<?
    if( !empty($err) )
    {
?>
        <tr>
             <td colspan="2" style="color:red;font-weight: bold;"><?= $err ?></td>
        </tr>
<?
    }
?>
       <tr>
            <td>Your password: </td><td><input type="password" name="password" /></td>
       </tr>
       <tr>
            <td></td><td><input name="submitbutton" type="submit" value="Log in" ?></td>
       </tr>
    </table>
    </form>
<?    
}

function dx(&$obj)
{
    print '<pre>';
    if( is_array($obj) )
        print_r($obj);
    else
        var_dump($obj);
    print '</pre>';
    exit;
}
function _filt_path($path)
{
    return array_diff(array_filter(preg_split("#[;\n\r]#",$path)),array('cctemplates','ccfiles')); 
}

function impf_1()
{
    print_dir_form();
}

function print_dir_form($msg='')
{
    $config = get_old_config();

    if( empty($config['template-root']) )
    {
        // this is a pre 3.1 installation
        die('sorry, we are not set up right now to do upgrade on installations before ccHost 3.1');
    }
    else
    {
        $dirs = _filt_path($config['files-root']);
        $keys = array_keys($dirs);
        $first = $dirs[$keys[0]];
        $roots[] = $first;
        while( $first )
        {
            $first = dirname($first);
            if( $first == '.' )
                break;
            if( $first )
                $roots[] = $first;
        }
        $roots = array_reverse($roots);
        $idirs = array();
        if( !empty($config['install-user-root']) )
        {
            $idirs = array(preg_replace('#/$#','',$config['install-user-root']));
        }
        $dirs = array_unique(array_merge($idirs,$roots,$dirs));
        $keys = array_keys($dirs);
        $c = count($keys);
        $checked = 'checked="checked"';

        print $msg;
?>
        <p>
            We're going to create some new directories for your custom files. Please make <b>sure</b>
            this directory is 
            <ul>
                <li>Writable to PHP script</li>
                <li>Under your web server root so it's visble and accessable to web browsers</li>
            </ul>
            Where would like us to put these new directories?
        </p>
        <form method="post" action="?up_step=2&impf=2">
        <table id="froot">
        <tr><td style="text-align:right">Under:</td><td></td></tr>
<?
        for( $i = 0; $i < $c; $i++ )
        {
            $dir = $dirs[$keys[$i]];
        ?>
            <tr><td style="text-align:right;width:220px;"><input <?= $checked ?> type="radio" name="root_file_dir" value="<?=$dir?>"  /></td>
            <td><b><?= $dir ?></b></td></tr>
        <?
            $checked = '';
        }
    ?>
        <tr><td style="text-align:right;">Another directory: <input type="radio" name="root_file_dir" onclick="enable_other()" value=".other." /></td>
        <td><input size="45" name="other_dir" id="other_dir" style="display:none" /></td></tr>

        <tr><th colspan="2" style="padding-top:1em;">Domain Change</th></tr>
        <tr><td colspan="2" style="padding-top:1em;">If you moved your data from another domain please let us know the change:</td><tr>
    <?
        $ttag = get_old_config('ttag');
        $old_domain = preg_replace('%http://([^/]+)/%','$1',$ttag['root-url']); 
        $new_domain = !empty($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : $old_domain;
    ?>
        <tr><td style="text-align:right">Old domain:</td><td><input name="old_domain" value="<?= $old_domain ?>" /></td></tr>
        <tr><td style="text-align:right">New domain:</td><td><input name="new_domain" value="<?= $old_domain ?>" /></td></tr>
    <?

        if( ($config['user-upload-root'] == 'people') && !empty($config['pretty-urls']) ) 
        {
    ?>
            <tr><th colspan="2" style="padding-top:1em;">'People' Change</th></tr>
            <tr><td colspan="2" style="padding-top:1em;">The upload directory 'people' conflicts with changes made to pretty-url setups.</td><tr>
            <tr><td style="text-align:right">Change 'people' to:</td><td><input name="new_people" value="content" /></td></tr>
    <?
        } else {
    ?>
            <tr><td /><td><input name="new_people" type="hidden" value="<?= $config['user-upload-root'] ?>" /></td></tr>
    <?
        }
    ?>
        <tr><td></td><td style="padding-top:2em;"><input type="submit" value="Let's go..." /></td></tr>
        </table>
        </form>
    <?
    }
}

function impf_2()
{
    if( empty($_POST['root_file_dir']) )
    {
        $msg = '<p style="color:red">Please specify a directory (?)</p>';
        print_dir_form($msg);
        return;
    }

    if( $_POST['root_file_dir'] == '.other.' )
    {
        if( empty($_POST['other_dir']) )
        {
            $msg = '<p style="color:red">Please specify a directory</p>';
            print_dir_form($msg);
            return;
        }
        $local_base_dir = $_POST['other_dir'];
    }
    else
    {
        $local_base_dir = $_POST['root_file_dir'];
    }

    $new_config = array(
            'dataview-dir'        => $local_base_dir . '/dataviews/',
            'template-root'       => $local_base_dir . '/skins/' , 
            'image-upload-dir'    => $local_base_dir . '/images/',
            'files-root'          => $local_base_dir . '/pages/',
        );

    foreach( $new_config as $newdir )
    {
        $newdir = preg_replace('#/$#','',$newdir);
        if( file_exists($newdir) )
        {
            $i = 0;
            do {
                $new_base = basename($newdir) . '_' . ++$i;
                $renamed = dirname($newdir) . '/' . $new_base;
            } while( file_exists($renamed) );
            rename($newdir, $renamed);
            chmod($renamed,0777);
            print("Renamed '$newdir' to '$renamed' to get it out of the way<br />\n");
        }
    }

    install_local_files($local_base_dir);

    print("Created new directories<br />\n");

    untangle_the_freakin_config_mess($local_base_dir,$new_config);


?>
    <p>
    The next step involves updating the structure of your database. Depending on the amount
    of data and the speed of your server this could take a few minutes. (WARNING: Your
    ccHost installation is in an incomplete, unusable state until you finish this upgrade.)
    </p>
    <p>
    <h3>Continue on to the next upgrade step: <span id="msg"></span><a onclick="grey_me(this,'msg')" href="?up_step=2&impf=3">Do it...</a></h3>
<?
}

function impf_3()
{
    setup_old_db();

    require_once( dirname(__FILE__) . '/cc-upgrade-db.php');

    print("Database structure upgraded<br />\n");

?>
    <p>
    The next step involves updating the some internal pointers in the database. If you have a lot of reviews
    or forum messages this could take a few minutes. (WARNING: Your
    ccHost installation is in an incomplete, unusable state until you finish this upgrade.)
    </p>
    <p>
    <h3>Continue on to the next upgrade step: <span id="msg"></span><a onclick="grey_me(this,'msg')" href="?up_step=2&impf=4">Do it...</a></h3>
<?
}

function impf_4()
{
    setup_old_db();
    require_once('cchost_lib/cc-includes.php');
    require_once( dirname(__FILE__) . '/cc-upgrade-data.php');
    fix_all();

?>
    <p>
    The next step will update your configuration (WARNING: Your
    ccHost installation is in an incomplete, unusable state until you finish this upgrade.)
    </p>
    <p>
    <h3>Continue on to the next upgrade step: <span id="msg"></span><a onclick="grey_me(this,'msg')" href="?up_step=2&impf=5">Do it...</a></h3>
<?
}

function impf_5()
{
    update_config_db($err);
    if( $err )
    {
        print "$err\n<br />";
        return;
    }

    up_step_3();    
}

function update_config_db(&$err)
{
    include('cc-config-db.php');
    $dbconfig ['database']['v'] = $CC_DB_CONFIG['db-name'];
    $dbconfig ['dbserver']['v'] = $CC_DB_CONFIG['db-server'];
    $dbconfig ['dbuser']['v']   = $CC_DB_CONFIG['db-user'];
    $dbconfig ['dbpw']['v']     = $CC_DB_CONFIG['db-password'];
    install_db_config($dbconfig,$err);
}

function setup_old_db()
{
    require_once('cchost_lib/cc-debug.php');
    require_once('cchost_lib/cc-database.php');
    CCDebug::Enable(true);
    CCDatabase::_config_db('cc-config-db.php');
}

function get_old_config($config='config')
{
    setup_old_db();
    $row = CCDatabase::QueryItem("SELECT config_data FROM cc_tbl_config WHERE config_scope = 'media' AND config_type = '$config'");
    return unserialize($row);
}

function _do_unhash(&$v,$strhash,$domswitch)
{
    if( is_array($v) )
    {
        $k = array_keys($v);
        $c = count($k);
        for( $i = 0; $i < $c; $i++ )
        {
            $ph =& $v[$k[$i]];
            _do_unhash($ph,$strhash,$domswitch);
        }
    }
    else
    {
        if( isset($strhash[$v]) )
            $v = $strhash[$v];
        if( !empty($domswitch) )
            $v = str_replace($domswitch[0],$domswitch[1],$v);
    }
}

function untangle_the_freakin_config_mess($local_base_dir,$new_config)
{
    setup_old_db();
    // we've got to unhash the strings
    $strhash = CCDatabase::QueryItem("SELECT config_data FROM cc_tbl_config WHERE config_type = 'strhash'");
    if( $strhash )
    {
        // hack for ccMixter
        $strhash = str_replace('Ã',  'e',$strhash);
        $strhash = str_replace('ƒÂ©',' ',$strhash);
        $strhash = unserialize($strhash);
    }
    if( $_POST['old_domain'] != $_POST['new_domain'] )
    {
        $dom_switch = array($_POST['old_domain'],$_POST['new_domain'] );
    }
    else
    {
        $dom_switch = array();
    }

    $old_config = get_old_config();
    $new_people = $_POST['new_people'];
    if( $old_config['user-upload-root'] != $new_people )
    {
        if( file_exists($new_people) )
            rename($new_people,$new_people . '_previous_' . time());
        rename($old_config['user-upload-root'],$new_people);
        print("Renamed '{$old_config['user-upload-root']}' to '{$new_people}'<br />\n");
        $new_config['user-upload-root'] = $new_people;
    }

    $new_config['install-user-root']   = $local_base_dir . '/';
    $new_config['cc-host-version']   = CC_HOST_VERSION;
    $new_config['embedded_player'] = 'ccskins/shared/players/player_native.php';
    $config = array_merge( $old_config, $new_config );
    _do_unhash($config,$strhash,$dom_switch);
    $config = addslashes(serialize($config));
    mysql_query('LOCK TABLES cfg READ, cc_tbl_config WRITE');
    mysql_query("UPDATE cc_tbl_config SET config_data = '$config' WHERE config_scope = 'media' AND config_type = 'config'");

    $skin_settings = addslashes( serialize( array (
            'skin-file' => 'ccskins/cc5/skin.tpl',
            'string_profile' => 'ccskins/shared/strings/all_media.php',
            'list_file' => 'ccskins/shared/formats/upload_page_wide.php',
            'list_files' => 'ccskins/shared/formats/upload_list_wide.tpl',
            'form_fields' => 'form_fields.tpl/form_fields',
            'grid_form_fields' => 'form_fields.tpl/grid_form_fields',
            'tab_pos' => 'ccskins/shared/layouts/tab_pos_header.php',
            'box_shape' => 'ccskins/shared/layouts/box_round_native.php',
            'page_layout' => 'ccskins/shared/layouts/layout024.php',
            'font_scheme' => 'ccskins/shared/colors/font_arial.php',
            'font_size' => 'ccskins/shared/colors/fontsize_px12.php',
            'color_scheme' => 'ccskins/shared/colors/color_mono.php',
            'paging_style' => 'ccskins/shared/layouts/paging_google_ul.php',
            'formfields_layout' => 'ccskins/shared/layouts/form_fields_sets.php',
            'gridform_layout' => 'ccskins/shared/layouts/gridform_matrix.php',
            'button_style' => 'ccskins/shared/layouts/button_rounded.php',
            'max-listing' => 12,
            'head-type' => 'ccskins/shared/head.tpl',
            'skin_profile' => 'ccskins/shared/profiles/profile_cc5.php',
        ) ) );

    mysql_query("INSERT INTO cc_tbl_config ( config_data, config_scope, config_type ) VALUES( '$skin_settings', 'media', 'skin-settings')");
    
    $extras = addslashes( serialize( array (
            'macros' => array (
                1 => 'ccskins/shared/extras/extras_edpicks.tpl',
                2 => 'ccskins/shared/extras/extras_latest.tpl',
                3 => 'ccskins/shared/extras/extras_podcast_stream.php',
                4 => 'ccskins/shared/extras/extras_search_box.tpl',
                5 => 'ccskins/shared/extras/extras_support_cc.php',
                ),
            'macros_order' => 'targetmacros[]=1&targetmacros[]=2&targetmacros[]=3&targetmacros[]=5',
            ) ) );

    mysql_query("INSERT INTO cc_tbl_config ( config_data, config_scope, config_type ) VALUES( '$extras', 'media', 'extras')");

    print("Installed New Skin Settings<br />\n");

    $sql = "SELECT config_id,config_data FROM cc_tbl_config as cfg WHERE config_type NOT " .
            "IN('strhash','clangmap','urlmap','extras','skin-settings','config')";
    $qr = mysql_query($sql);
    while( $row = mysql_fetch_assoc($qr) )
    {
        $data = unserialize($row['config_data']);
        _do_unhash($data,$strhash,$dom_switch);
        $data = addslashes(serialize($data));
        mysql_query("UPDATE cc_tbl_config SET config_data = '$data' WHERE config_id = {$row['config_id']}");
    }

    $sql = "DELETE FROM cc_tbl_config WHERE config_type IN('strhash','clangmap','urlmap')";
    mysql_query($sql);
    mysql_query('UNLOCK TABLES');

    print("Config strings updated<br />\n");
    if( !empty($dom_switch) )
        print("Domain switched from {$_POST['old_domain']} to {$_POST['new_domain']}<br />\n");

    $sql = "SELECT * FROM cc_tbl_config WHERE config_type = 'tab_pages'";
    $qr = mysql_query($sql);
    while( $R = mysql_fetch_assoc($qr) )
    {
        $pages = unserialize($R['config_data']);
        $c = count($pages);
        $k = array_keys($pages);
        for( $i = 0; $i < $c; $i++ )
        {
            $set =& $pages[$k[$i]];
            $k2 = array_keys($set);
            $c2 = count($k2);
            for( $n = 0; $n < $c2; $n++ )
            {
                $P =& $set[$k2[$n]];
                if( !empty($P['function']) && ($P['function'] == 'all' || $P['function'] == 'any') )
                {
                    $qstring = 'tags=' . $P['tags'] . '&type=' . $P['function'];
                    $P['function'] = 'qry';
                    $P['tags'] = $qstring;
                }
                if( !empty($P['module']) )
                {
                    $P['module'] = str_replace('cclib','cclib_host',$P['module']);
                }
            }
        }
        $data = addslashes(serialize($pages));
        $sql = "UPDATE cc_tbl_config SET config_data = '{$data}' WHERE config_id = {$R['config_id']}";
        mysql_query($sql);
    }
    print "Tab pages updated<br />\n";

}

function grey_me_script()
{
?>
    <script type="text/javascript">
    function grey_me(obj,msgid)
    {
        var msg =     document.getElementById(msgid);
        msg.style.display = 'inline';
        msg.innerHTML = 'working...';
        obj.style.color = '#888';
        obj.innerHTML = '';
        return true;
    }
    function enable_other()
    {
        document.getElementById('other_dir').style.display = 'inline';
    }
    </script>
<?
}


?>
