<?

/*
  $Id: config-catch.php 13835 2009-12-25 13:19:34Z fourstones $
*/

//error_reporting(E_ALL);

// This module was created to try to catch the dreaded version auto-change
// bug.

// I'm turning it off because, well, it didn't work, the version "changed
// itself" and this code didn't catch it.

//CCEvents::AddHandler(CC_EVENT_CONFIG_CHAGNED, 'config_changed' );

function config_changed( &$spec, &$old_value, &$new_value )
{
    if( count($new_value) != 1 || !array_key_exists('mod-stamp',$new_value) )
    {
        _catch_log($spec,"Config changed: type:{$spec['config_type']}");
    }

}

function _catch_log($spec,$stra)
{
    $str = '';
    if( CCUser::IsLoggedIn() )
        $str = '[' . CCUser::CurrentUserName() . ']';
    $str .= str_replace(ccl(),'',cc_current_url()) . ' ';
    CCDebug::Log($str . ' ' . $stra);
}

?>