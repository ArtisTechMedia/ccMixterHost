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
* $Id: cc-license.php 12626 2009-05-19 17:24:09Z fourstones $
*
*/

/**
* Module for managing Creative Commons licenses
*
* @package cchost
* @subpackage feature
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Wrapper class for license information table
*
* This is just syntantic sugar on to of CCTable
*/
class CCLicenses extends CCTable
{
    /**
    * Constructor
    *
    */
    function CCLicenses()
    {
        $this->CCTable('cc_tbl_licenses','license_id');
        $this->AddExtraColumn('0 as license_checked');
    }

    /**
    * Returns static singleton of table wrapper.
    * 
    * Use this method instead of the constructor to get
    * an instance of this class.
    * 
    * @returns object $table An instance of this table
    */
    function & GetTable()
    {
        static $_table;
        if( !isset($_table) )
            $_table = new CCLicenses();
        return( $_table );
    }
}

class CCLicenseHV
{
    function OnFilterUploadPage(&$rows)
    {
        $keys = array_keys($rows);
        $c = count($keys);
        for( $i = 0; $i < $c; $i++ )
        {
            $R =& $rows[$keys[$i]];
            $fkeys = array_keys($R['files']);
            $fc = count($fkeys);
            $dcmi = null;
            for( $n = 0; $n < $fc; $n++ )
            {
                $F =& $R['files'][$fkeys[$n]];
                if( !empty($F['file_format_info']['media-type']) )
                {
                    switch( $F['file_format_info']['media-type'] )
                    {
                        case 'audio':
                            $dcmi = 'Sound';
                            break;
                        case 'graphic':
                            $dmci = 'Image';
                            break;
                        case 'video':
                            $dmci = 'MovingImage';
                            break;
                        case 'archive':
                            $dcmi = 'Collection';
                            break;
                        case 'document':
                            $dcmi = 'Text';
                            break;
                    }
                }
                if( !empty($dcmi) )
                    break;
            }
            if( !empty($dcmi) )
            {
                $R['dcmi'] = "http://purl.org/dc/dcmitype/" . $dcmi;
                $R['dcmirel'] = "dc:type";
            }
        }
    }
}

?>
