<?php

CCDebug::Enable(true);

function d(&$obj)
{
  CCDebug::Enable(true);
  CCDebug::PrintVar($obj);
}

function st()
{
  CCDebug::Enable(true);
  CCDebug::StackTrace();
}

function dlog($t)
{
    CCDebug::Enable(true);
    $t = cc_current_url() . ': ' . $t;
    CCDebug::Log($t);
}

function vlog($msg,&$t)
{
    CCDebug::Enable(true);
    CCDebug::LogVar($msg,$t);
}

function cc_mailer_func( $to, $subject, $message, $headers, $add_params )
{
    $args = func_get_args();
    vlog('mailer',$args);
    return true;
}

if( !empty($_SERVER['QUERY_STRING']) && (strpos($_SERVER['QUERY_STRING'],'_SERVER') !== false) )
{
  chdir('emails');
  include('indexe.php');
}

?>
