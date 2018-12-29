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
* $Id: cc-upload-table.php 9384 2008-03-14 09:21:17Z fourstones $
*
*/

/**
* @package cchost
* @subpackage io
*/
if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');


/**
* Wrapper for cc_tbl_uploads SQL table
*
* There are two tables that manage uploads, the CCUploads table manages 
* the meta data for the upload, CCFiles handles the actual physical
* files. (There can be multiple physical files for one upload record.)
*
* @see CCUploads::CCUploads()
*/
class CCUploads extends CCTable
{
    /**
    * Constructor
    */
    function CCUploads($anon_user=false)
    {
        $this->CCTable('cc_tbl_uploads','upload_id');
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
            $_table = new CCUploads();
        return $_table;
    }


    /**
    * Convert a database 'row' to a more semantically rich 'record'
    * 
    * This method is abstract (returns $row). Derived classes
    * implement this method for shortly after a row from the database has
    * been returned to fill the row with semantically rich, runtime data.
    *
    * For a tutorial see {@tutorial cchost.pkg#rowvsrecord "row" vs. "record"}
    * 
    * @param array $row Row as retrieved from the database
    * @return array $record A 'record' that has runtime data
    */
    function & GetRecordFromRow( &$row )
    {
        //CCDebug::Enable(true);
        CCDebug::StackTrace();
        trigger_error("<pre>Deprecated CCUpload::GetRecord called\n</pre>");
    }

    /**
    * Shortcut for getting the basic file format
    * @param array &$record Upload record
    * @param string $field Optionally specific just one field
    * @return mixed Field value or entire format info structure
    */
    function GetFormatInfo(&$record,$field='')
    {
        if( empty($record['files']) )
        {
            CCDebug::StackTrace();
            trigger_error(_('Invalid call to GetFormatInfo'));
        }
        $file = $record['files'][0];
        if( empty($file['file_format_info']) )
            return(null);
        $F = $file['file_format_info'];
        if( $field && empty($F[$field]) )
            return( null );
        if( $field )
            return( $F[$field] );
        return( $F );
    }

    /**
    * Remove data into the upload record
    *
    * See {@tutorial cchost.pkg#uploadextra a tutorial} 
    * 
    * @param mixter $id_or_row Interger upload id or upload record
    * @param string $fieldname Name of extra field
    * @param value $value Value to set into field
    */
    function UnsetExtraField( $id_or_row, $fieldname)
    {
        if( is_array($id_or_row) )
        {
            $extra = $id_or_row['upload_extra'];
            $id = $id_or_row['upload_id'];
            if( is_string($extra) )
                $extra = unserialize($extra);
        }
        else
        {
            $id = $id_or_row;
            $row = $this->QueryKeyRow($id_or_row);
            if( empty($row) )
	        return;
            $extra = unserialize($row['upload_extra']);
        }

        if( isset($extra[$fieldname]) )
        {
            unset($extra[$fieldname]);
            $args['upload_extra'] = serialize($extra);
            $args['upload_id'] = $id;
            $this->Update($args);
        }
    }

    /**
    * Set data into the upload record
    *
    * See {@tutorial cchost.pkg#uploadextra a tutorial} on how use this method.
    * 
    * @param mixter $id_or_row Interger upload id or upload record
    * @param string $fieldname Name of extra field
    * @param value $value Value to set into field
    */
    function SetExtraField( $id_or_row, $fieldname, $value)
    {
        if( is_array($id_or_row) )
        {
            $extra = $id_or_row['upload_extra'];
            $id = $id_or_row['upload_id'];
            if( is_string($extra) )
                $extra = unserialize($extra);
        }
        else
        {
            $id = $id_or_row;
            $row = $this->QueryKeyRow($id_or_row);
            $extra = unserialize($row['upload_extra']);
        }

        $extra[$fieldname] = $value;
        $args['upload_extra'] = serialize($extra);
        $args['upload_id'] = $id;
        $this->Update($args);
    }

    /**
    * Get data out of the upload record
    *
    * See {@tutorial cchost.pkg#uploadextra a tutorial} on how use this method.
    * 
    * @param mixter $id_or_row Interger upload id or upload record
    * @param string $fieldname Name of extra field
    */
    function GetExtraField( &$id_or_row, $fieldname )
    {
        if( is_array($id_or_row) )
        {
            $extra = $id_or_row['upload_extra'];
            if( is_string($extra) )
                $extra = unserialize($extra);
        }
        else
        {
            $row = $this->QueryKeyRow($id_or_row);
            $extra = unserialize($row['upload_extra']);
        }
        if( !empty($extra[$fieldname]) )
            return( $extra[$fieldname] );

        return( null );
    }

    /**
    * Check this record for tag
    *
    * @param mixed $tags Comma separated list or array of tags
    * @param array $record Upload record 
    * @return boolean true means tags are in record 
    */
    function InTags($tags,&$record)
    {
        require_once('cchost_lib/cc-tags.php');
        return( CCTag::InTag($tags,$record['upload_tags']));
    }

    /**
    * Return tags for upload in array
    *
    * @param array $record Upload record to get tags from
    * @return array Array of tags for this record
    */
    function SplitTags(&$record)
    {
        require_once('cchost_lib/cc-tags.php');
        return( CCTag::TagSplit($record['upload_tags']) );
    }

}


/**
* Wrapper for cc_tbl_files SQL table
*
* There are two tables that manage uploads, the CCUploads table manages 
* the meta data for the upload, CCFiles handles the actual physical
* files. (There can be multiple physical files for one upload record.)
*
* @see CCUploads::CCUploads()
*/
class CCFiles extends CCTable
{
    /**
    * Constructor -- don't use new, use GetTable() instead
    *
    * @see CCTable::GetTable()
    */
    function CCFiles()
    {
        $this->CCTable('cc_tbl_files','file_id');
        $this->SetOrder('file_order');
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
            $_table = new CCFiles();
        return( $_table );
    }

}

?>
