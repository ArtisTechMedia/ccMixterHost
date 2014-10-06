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
* $Id: cc-host-gen-urldocs.php 8952 2008-02-11 21:41:24Z fourstones $
*
*/


error_reporting(E_ALL);

if( preg_match( '#[\\\\/]bin$#', getcwd() ) )
    chdir('..');

define('IN_CC_HOST',1);
require_once('cchost_lib/cc-table.php');
require_once('cchost_lib/cc-database.php');
require_once('cchost_lib/cc-config.php');
require_once('cchost_lib/cc-defines.php');
require_once('cchost_lib/cc-debug.php');
require_once('cchost_lib/cc-util.php');
require_once('cchost_lib/cc-events.php');
if( !function_exists('gettext') )
    require_once('cchost_lib/ccextras/cc-no-gettext.inc');

require_once('cchost_lib/cc-access.php');


function main() 
{
    $perms = array(
CC_MUST_BE_LOGGED_IN       =>  'R',
CC_ONLY_NOT_LOGGED_IN       =>  'U',
CC_DONT_CARE_LOGGED_IN       =>  'E',
CC_ADMIN_ONLY       =>  'A',
CC_SUPER_ONLY       =>  'S'
           );

    $map = cc_get_url_map(true);
    foreach( $map as $section_name => $section )
    {
        $text =<<<END
<row class="cmdcat"><entry morerows="3"   class="cmdcat"><emphasis>$section_name</emphasis></entry></row>
END;
        print($text);
        foreach( $section as $AO )
        {
            /*
            [contest/edit] => CCAction Object
                (
                    [cb] => Array
                        (
                            [0] => CCContest
                            [1] => EditContest
                        )

                    [pm] => 8
                    [md] => cchost_lib/cc-contest.inc
                    [dp] => {contestname}
                    [ds] => Display contest properties form
                    [dg] => _cnt
                    [url] => contest/edit
                    [pmu] => 8
                    [pmd] => Admin/Moderators
            */
            $S = array();
            foreach( $AO as $K => $V )
                $S[$K] = $V;
            $args = '/' . $S['dp'];
            $cb = is_array( $S['cb'] ) ? $S['cb'][0] . '::' . $S['cb'][1] : $S['cb'];
            $text =<<<END
<row><entry><uri>{$S['url']}{$args}</uri></entry><entry><emphasis>{$perms[$S['pm']]}</emphasis></entry>
<entry>{$S['ds']}</entry><entry><programlisting role="php"><![CDATA[<? {$cb}(); ?>]]></programlisting></entry></row>

END;
            print($text);
        }
    }
}

main();

?>