<?

/*
  $Id: mixter-bucky.php 14229 2010-03-10 23:48:43Z fourstones $
*/

//error_reporting(E_ALL);

if( !empty($_GET['bucky_format']) )
{
    require_once('cchost_lib/snoopy/Snoopy.class.php');
    $snoopy = new Snoopy();
    $snoopy->fetch('http://ccmixter.org/media/buckyjonson');
    print $snoopy->results;
    exit;
}


?>
