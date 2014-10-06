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
* $Id: cc-host-gettext-from-templates.php 4620 2006-11-05 18:30:47Z fourstones $
*
* Copyright 2006, Creative Commons, www.creativecommons.org.
* Copyright 2006, Victor Stone.
* Copyright 2006, Jon Phillips, jon@rejon.org.
*/

/*
* extract all strings from templates
*
*/

error_reporting(E_ALL);

if( preg_match( '#[\\\\/]bin$#', getcwd() ) )
    chdir('..');

$strings = array();

if ($cc_dh = opendir('cctemplates')) 
{
   while (($cc_file = readdir($cc_dh)) !== false) 
   {
       if( preg_match('/.*\.xml$/',$cc_file) )
       {
           $text = file_get_contents( 'cctemplates/' . $cc_file );
           if( preg_match_all( '/(?:_|CC_lang)\([\'"](.*)[\'\"]\)/Ui', $text, $m ) )
           {
               $strings = array_merge($strings,$m[1]);
           }
       }
   }
   closedir($cc_dh);
}

$strings = array_unique($strings);
natsort($strings);

print( '<' . "?\n\n  \$_d = array( \n\n" );
foreach( $strings as $s )
    print("   _('$s'), \n");
print( "\n);\n\n?" . '>' );

?>