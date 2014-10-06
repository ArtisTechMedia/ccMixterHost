<?

// If you can't even login then change the 1 to a 0 (zero) in the next line
define('CC_REQUIRE_LOGIN', 1);

function validate_user()
{
    if( !CC_REQUIRE_LOGIN )    
        return;
        
    list( $user_name, $pw ) = unserialize(strip($_COOKIE['lepsog3'])) or die('no login cookie. log in to cchost first, otherwise edit this script');
    $qr = q('SELECT user_id FROM cc_tbl_user WHERE user_name=\''.$user_name.'\' AND user_password=\''.$pw.'\'');
    $ok = mysql_num_rows($qr) or die('can not find you in the user database');
    $data = data(array('media','config'));
    if( empty($data['supers']) )
        die('no supers');
    $supers = split(',',$data['supers']);
    if( !in_array($user_name,$supers) )
        die('you are not in the media/config/supers list');
}

error_reporting(E_ALL); validate_user(); 

$path = empty($_REQUEST['path']) ? array() : split('/',$_REQUEST['path']);

if( !empty($_REQUEST['cmd']) )
{
    switch( $_REQUEST['cmd'] )
    {
        case 'add':
            do_add_path($path);
            msg('New path created');
            break;

        case 'save':
            do_save_path($path,strip($_REQUEST['value']));
            print('Config entry has been saved');
            exit;
    }
}
$root_url = preg_replace('#(^.*)cchost_lib/cc-config-repair.*$#','\1',$_SERVER['REQUEST_URI']);
$root_url = 'http://' . $_SERVER['HTTP_HOST'] . $root_url;

?>
<html>
<head>
<script type="text/javascript" src="../ccskins/shared/js/prototype.js" ></script>
<script>
function save_config(path)
{
    var value = $('config_value').value;
    new Ajax.Updater('config_field','<?= $root_url ?>cchost_lib/cc-config-repair.php',{method:'post',parameters:'cmd=save&path='+path+'&value='+value});
    $('config_field').innerHTML = 'working...';
}
function add_path(path)
{
    var value = $('new_path_name').value;
    if( !value )
    {
        alert('empty path name');
        return;
    }
    location.href = '<?= $root_url ?>cchost_lib/cc-config-repair.php?cmd=add&path='+path+'/' + value;
    return false;
}
</script>
<style>
#config_list {
    float: left;
    width: 200px;
    margin-left: 20px;
    border: 1px solid #AAA;
    padding: 3px;
    margin-bottom: 30px;
}
a.config_path, a.config_key,#del_cmd,a.config_cmd {
    display: block;
    font-family: verdana;
    font-weight: normal;
    font-size: 11px;
    color:black;
    text-decoration: none;
    margin-bottom: 2px;
}
a.config_cmd {
    color: #777;
}
a.config_cmd:hover {
    color: red;
    background-color: #BBB;
}
a.config_path:hover, a.config_key:hover {
    color: white;
    background-color: black;
}

#config_detail{
    float: left;
    margin-left: 30px;
}
#msg {
    float: right;
    margin-right: 30px;
}
#config_field{
    margin: 2px;
    border: 1px solid black;
    padding: 2px;
}
</style>
</head>
<body>
<h2>Edit Config</h2>
<?

if( !empty($_REQUEST['cmd']) )
{
    if( $_REQUEST['cmd'] == 'delete' )
    {
        del($path);
    }
}

$qr = q('SELECT DISTINCT config_scope FROM cc_tbl_config ORDER by config_scope');
print '<div id="config_list">';
while( $row = mysql_fetch_assoc($qr) )
{
    $scope = $row['config_scope'];
    print '<a class="config_path" href="?path='.$scope.'">' . $scope . '</a>';
    if( $path && ($scope == $path[0]) )
    {
        $qr2 = q('SELECT config_type FROM cc_tbl_config WHERE config_scope = \''.$scope . '\' ORDER by config_type ');
        if( $scope != 'media' )
            print '<a class="config_cmd" href="?cmd=delete&path='.$scope.'">[delete entire \''.$scope.'\']</a> ';
        while( $type_row = mysql_fetch_row($qr2) )
        {
            $tpath = $scope . '/' . $type_row[0];
            print '<a class="config_path" href="?path='.$tpath.'">&nbsp;&nbsp;&nbsp;' . $type_row[0]. '</a>';
        }
    }
}
print '</div>';

if( empty($_REQUEST['cmd']) )
{
    if( count($path) > 1 )
    {
        $qr = q('SELECT * FROM cc_tbl_config ORDER by config_scope,config_type');
        $base = $path[0] . '/' . $path[1];
        print "\n" . 
            '<div id="config_detail"><h3>' . $_REQUEST['path']. ' <a id="del_cmd" href="?path='.$_REQUEST['path'] .'&cmd=delete">[delete]</a> </h3><div id="detail_tree">';

        $data = data($path);
        recurse_path($data,$base,$path,2);
        print '</div></div>';
    }
}


function recurse_path($data,$base,$path,$level)
{
    $key = count($path) > $level ? $path[$level] : null;
    ksort($data);
    foreach( $data as $K => $V )
    {
        $dpath = $base . '/' . $K;
        $space = str_repeat('&nbsp;',3*$level);
        if( isset($key) && ($K == $key) )
        {
            $type = empty($V) ? 'null' : gettype($V);
            print "\n";
            switch($type)
            {
                case 'array':
                    print '<a class="config_key" href="?path='.$dpath.'">'.$space . $K.'</a>';
                    if( count($path) == $level+1 )
                    {
                        $html =<<<EOF
  <div id="path_edit">
    <a class="config_cmd" href="javascript://#" onclick="\$('new_name_div').style.display=''; return false;">{$space}[add key to '{$K}']</a>
    <div style="display:none" id="new_name_div">
        {$space}<input id="new_path_name" /><a href="javascript://#" onclick="add_path('{$dpath}'); return false;">save</a>
    </div>
    <a class="config_cmd" href="?cmd=delete&path='{$dpath}'">{$space} [delete '{$K}']</a>
  </div>
EOF;
                        print $html;
                    }
                    recurse_path($V,$dpath,$path,$level+1);
                    continue;
                case 'object':
                    print "<div>$K is an object, sorry, can't edit";
                    break;
                case 'integer':
                case 'null':
                    print '<div id="config_field">'.$space . "$K: $space <input id='config_value' value=\"" . htmlentities($V) . '" />';
                    break;
                default:
                    print '<div id="config_field">'.$space . "$K:<br />$space <textarea id='config_value'>" 
                             . htmlentities($V) . '</textarea>';
            }
           print '<a class="config_cmd" href="javascript://" onclick="save_config(\''.$dpath.'\'); return false;">'.$space . '[save \''.$K.'\']</a> ';
           print '<a class="config_cmd" href="?cmd=delete&path='.$dpath.'">'.$space . '[delete \''.$K.'\']</a> ';
           print '</div>';
        }
        else
        {   
            $type = empty($V) ? 'null' : gettype($V);
            print '<a class="config_key" href="?path='.$dpath.'">'.$space . $K.' (' . $type .')</a>';
        }

    }
}

?>
<div style="clear:both">&nbsp;</div>

</body>
</html>
<?
function init_db()
{
    static $done;
    if( !isset($done) ) {
        define('IN_CC_HOST',1);
        require_once('../cc-host-db.php');
        $config = $CC_DB_CONFIG;
        $link = mysql_connect( $config['db-server'], 
                                $config['db-user'], 
                                $config['db-password']) or die( mysql_error() );
        
        mysql_select_db( $config['db-name'], $link ) or die( mysql_error() );
        $done = true;
    }
}

function q($sql)
{
    init_db();
    $qr = mysql_query($sql) or die( mysql_error() );
    return $qr;
}

function del($path)
{
    if( empty($path) ) die('wtf');

    if( count($path) == 1 )
    {
        q("DELETE FROM cc_tbl_config WHERE config_scope = '{$path[0]}'");
        msg('Entire scope has been deleted');
    }
    else
    {
        $where = where($path);
        if( count($path) == 2 )
        {
            q('DELETE FROM cc_tbl_config WHERE ' . $where);
            msg('Entire config entry has been deleted');
        }
        else
        {
            $data = data($path);
            $data_path = array_splice($path,2);
            if( count($data_path) == 1 )
            {
                unset($data[$data_path[0]]);
            }
            else
            {
                $str = "unset(\$data['" . join("']['",$data_path) . "']);";
                eval($str);
            }
            $data = addslashes(serialize($data));
            q("UPDATE cc_tbl_config SET config_data = '$data' WHERE $where");
            msg('Config entry has been deleted');
        }
    }
}

function msg($msg)
{
    print '<div id="msg">' . $msg . '</div>';
}

function where($path)
{
    return "config_scope = '{$path[0]}' AND config_type = '{$path[1]}'";
}

function data($path)
{
    $where = where($path);
    $sql = "SELECT * FROM cc_tbl_config WHERE $where";
    $qr = q($sql);
    $row = mysql_fetch_assoc($qr) or die( mysql_error() );
    return unserialize($row['config_data']);
}

function strip(&$mixed)
{
    if( get_magic_quotes_gpc() == 1 )
    {
        if( is_array($mixed) )
        {
            $keys = array_keys($mixed);
            foreach( $keys as $key )
                $mixed[$key] = strip($mixed[$key]);
        }
        else
        {
            $mixed = trim(stripslashes( $mixed ));
        }
    }
    return($mixed);
}

function do_add_path($path)
{
    do_save_path($path,'edit me');
}

function do_save_path($path,$value)
{
    $where = where($path);
    $data = data($path);
    $path = array_splice($path,2);
    $str = '$data[\'' . join( "']['", $path ) . "'] = '{$value}';";
    eval($str);
    $data = addslashes(serialize($data));
    q("UPDATE cc_tbl_config SET config_data = '$data' WHERE $where");
}

function d(&$obj)
{
    print '<pre>';
    if( is_array($obj) )
        print_r($obj);
    else
        var_dump($obj);
    print '</pre>';
    exit;
}
?>
