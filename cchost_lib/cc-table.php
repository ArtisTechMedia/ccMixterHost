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
* $Id: cc-table.php 12404 2009-04-24 06:43:19Z fourstones $
*
*/

/**
* @package cchost
* @subpackage core
*/

if( !defined('IN_CC_HOST') )
   die('Welcome to CC Host');

/**
* Bass class for use by singleton table representations 
*
* For a tutorial see '{@tutorial cchost.pkg#newtable Create A New Table}'
*
*/
class CCTable
{
    /**#@+
    * @access private
    * @var string 
    */
    var $_table_name;
    var $_key_field;
    var $_joins;
    var $_join_num;
    var $_join_parts;
    var $_extra_columns;
    var $_order;
    var $_direction;
    var $_group_on;
    var $_dataview;
    var $_last_sql;
    var $_last_where;
    /**#@-*/

    /**#@+
    * @access private
    * @var integer
    */
    var $_offset;
    var $_limit;
    /**#@-*/

    /**#@+
    * @access private
    * @var boolean
    */
    var $_group_on_key;
    /**#@-*/


    /**
    * Constructor
    *
    * @param string $table_name Name in the mySQL database
    * @param string $key_field  Field to use in JOINs and key lookups
    */
    function CCTable($table_name,$key_field)
    {
        $this->_table_name = $table_name;
        $this->_key_field = $key_field;
        $this->_joins = array();
        $this->_join_num = 1;
        $this->_group_on_key = false;
    }

    /**
    * Set the dataview for the next Query
    *
    * @param string $dataview_id ID of dataview (columns, joins, etc.)
    */
    function SetDataview($dataview_id)
    {
        $this->_dataview = $dataview_id;
    }

    /**
    * Set the sort order for the next Query
    *
    * @param string $order_expression Typically a column but can be any valid mySQL expressions
    * @param string $dir Either ASC or DESC
    */
    function SetSort($order_expression, $dir = 'ASC')
    {
        $this->_order = $order_expression;
        $this->_direction = $dir;
    }

    /**
    * Alias for SetSort
    *
    * @param string $order_expression Typically a column but can be any valid mySQL expressions
    * @param string $dir Either ASC or DESC
    * @see SetSort
    */
    function SetOrder($order_expression, $dir = 'ASC')
    {
        $this->SetSort($order_expression, $dir);
    }

    /**
    * Set row offset and limit number of rows returned from queries
    *
    * Set both parameters to 0 to reset.
    *
    * @param integer $offset Row number to start query
    * @param integer $limit  Maximun number of rows to return
    */
    function SetOffsetAndLimit($offset,$limit)
    {
        $this->_offset = $offset;
        $this->_limit  = $limit;
    }

    /**
    * Generate a GROUP ON sql qualifier using the table's key
    *
    * @param boolean $on true: generate GROUP ON, false: don't generate
    */
    function GroupOnKey($on=true)
    {
        $this->_group_on_key = $on;
    }

    /**
    * Generate a GROUP ON for a given column
    *
    * @param string $column Column to group on
    */
    function GroupOn($column)
    {
        $this->_group_on = $column;
    }

    /**
    * For use by derived class, adds a column to SELECT statement.
    * 
    * This method is useful to formatting fields such as date or numerics.
    * 
    * In a derived class' contstructor:
    * 
    * <code>
    *    $this->AddExtraColumn("DATE_FORMAT(upload_date, '$CC_SQL_DATE') as upload_date_format");
    * </code>
    *
    * @param string $spec Valid mySQL string for defining a virtual column
    */
    function AddExtraColumn($spec)
    {
        if( empty($this->_extra_columns) )
            $this->_extra_columns = array();

        $this->_extra_columns[] = $spec;
    }

    /**
    * Removes extra columns added by AddExtraColumn()
    * 
    *
    * @see AddExtraColumn
    * @param string $spec Valid mySQL string for defining a virtual column
    */
    function RemoveExtraColumn($spec)
    {
        $this->_extra_columns = array_diff($this->_extra_columns,array($spec));
    }

    /**
    * For use by derived class, adds a JOIN to SELECT statement.
    * 
    * This method is useful to expanding the information returned by Query()
    * 
    * In a derived class' contstructor:
    * 
    * <code>
    * $this->AddJoin( new CCUsers(), 'upload_user');
    * </code>
    * 
    * @param string $other_cctable Instance of a CCTable class to JOIN with
    * @param string $joinfield Name of field in <b>this</b> table that the key field of the <b>other</b> table will join on.
    * @param string $jointype Valid mySQL type of JOIN 
    * @param string $other_key Column in other table (default is that table's key)
    * @return mixed $name JOIN token. Hold onto this and pass to RemoveJoin if you don't want the next Query() to use the join.
    * @see RemoveJoin
    */
    function AddJoin($other_cctable, $joinfield, $jointype = 'LEFT OUTER', $other_key='' )
    {
        $name      = 'j' . $this->_join_num++;
        $othername = $other_cctable->_table_name;
        $otherkey  = empty($other_key) ? $other_cctable->_key_field : $other_key;

        if( !strstr($joinfield,'.') )
            $jfield = $this->_table_name . '.' . $joinfield;
        else
            $jfield = $joinfield;

        $join = "\n $jointype JOIN $othername $name ON $jfield = $name.$otherkey ";

        $this->_joins[$name] = $join;
        $this->_join_parts[$name] = array( $other_cctable,
                                           $joinfield);
        return($name);
    }

    /**
    * For use by derived class, removed a previously added JOIN to SELECT statement.
    * 
    * This method will remove a JOIN placed on queries by AddJoin
    * 
    * @param mixed $joinid Token returned from AddJoin()
    * @see AddJoin
    */
    function RemoveJoin($joinid)
    {
        unset($this->_joins[$joinid]);
        unset($this->_join_parts[$joinid]);
    }

    /**
    * A hack: simulate a join 
    *
    * This method will query joined tables for a record that was hand-rolled
    * (i.e. not retrieved from the database)
    *
    */
    function FakeJoin(&$fake_record)
    {
        foreach( $this->_join_parts as $jpart )
        {
            if( !empty($fake_record[$jpart[1]]) )
            {
                $table = $jpart[0];
                $fake_record += $table->QueryKeyRow($fake_record[$jpart[1]]);
            }
        }

    }

    /**
    * Get an instance of this table.
    *
    * This method is abstract (not actually implemented here) static call
    * implemented by derived classes to return a singleton instance of themselves.
    * 
    * Tables should <i>rarely</i> be instantiated with 'new'. Instead, call GetTable()
    * to get a singleton instance of the class:
    * 
    * <code>
    * $uploads =& CCUploads::GetTable();
    * </code>
    * 
    * Where CCUploads is a derivation of CCTable.
    * @return object $table Returns a singleton instance of this object.
    */
    static function & GetTable()
    {
    }

    /**
    * Query from the virtual table
    * 
    * The $where parameter can either be a mySQL WHERE clause (without the WHERE)
    * or it can be an array where the key is the column name and the value is what 
    * to test for.
    * 
    * <code>
    * // The following is the equivalent of:
    * // ...WHERE ('file_name' = 'myfile.mp3') AND ('user' = 204 )
    *      
    * $where['file_name'] = 'myfile.mp3';
    * $where['user']      = 204;
    * $sql_result =& $table->Query($where);
    * </code>
    * 
    * @param mixed $where mySQL WHERE clause as string or array 
    * @return mixed $query_results Results of mySQL query
    */
    function Query($where ='')
    {
        return( CCDatabase::Query(  $this->_get_select($where) ) );
    }

    /**
    * Convert database 'rows' to a more semantically rich 'records'
    * 
    * @param array &$rows Rows as retrieved from the database
    * @return array $records Records that has runtime formatted data
    */
    function & GetRecordsFromRows(&$rows)
    {
        $records = array();
        $count = count($rows);
        for( $i = 0; $i < $count; $i++ )
            $records[] = $this->GetRecordFromRow($rows[$i]);
        return $records;
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
        return $row;
    }

    /**
    * Returns an array of records given keys (aka 'id')
    *
    * @param array $key array of keys to look up
    * @param boolean $clean Set to true if keys came from URL and need 'cleaning'
    * @return array $records Array of full records (as opposed to raw database rows)
    */
    function & GetRecordsFromKeys($keys,$clean=true)
    {
        if( empty($keys) )
        {
            $a = array();
            return $a;
        }

        if( $clean )
            $keys = CCUtil::CleanNumbers($keys);

        $where = '(' . $this->_key_field . ' IN (' . join(',',$keys) . '))';
        $records =& $this->GetRecords($where);

        return $records;
    }

    /**
    * Returns an array of rows given keys (aka 'id')
    *
    * @param array $key array of keys to look up
    * @param boolean $clean Set to true if keys came from URL and need 'cleaning'
    * @return array $rows Array of raw database rows
    */
    function & QueryRowsFromKeys($keys,$clean=true)
    {
        if( empty($keys) )
        {
            $a = array();
            return $a;
        }

        if( $clean )
            $keys = CCUtil::CleanNumbers($keys);

        $where = '(' . $this->_key_field . ' IN (' . join(',',$keys) . '))';
        $rows =& $this->QueryRows($where);

        return $rows;
    }

    /**
    * Return full records for a given where statement
    *
    * Param $where can be either a SQL WHERE clause or an array of
    * field names and valued. 
    *
    * @see Query
    * @param mixed $where string or array query statement
    * @return array $records Array of full records
    */
    function & GetRecords($where)
    {
        $rows = $this->QueryRows($where);
        $records =& $this->GetRecordsFromRows($rows);
        return $records;
    }

    /**
    * Return a record given key (aka 'id')
    *
    * @param integer $key key to look up
    * @return array $record Full record (as opposed to raw database row)
    */
    function & GetRecordFromID($key)
    {
        $row = $this->QueryKeyRow($key);
        if( !empty($row) )
        {
            $R =& $this->GetRecordFromRow($row);
            return $R;
        }
        $a = array();
        return $a;
    }

    /**
    * Return a record given key (aka 'id')
    *
    * Alias for GetRecordFromID()
    *
    * @see GetRecordFromID
    * @param integer $key key to look up
    * @return array $record Full record (as opposed to raw database row)
    */
    function & GetRecordFromKey($key)
    {
        $R =& $this->GetRecordFromID($key);
        return $R;
    }

    /**
    * For use by derived class if they implement _get_select
    * 
    * This method verifies the WHERE part of a mySQL query. It should
    * only be used if a derived class 
    * 
    * @param mixed $where either query string or array of 'column' => 'value' to test for
    * @return string $where_clause WHERE clause (without the WHERE)
    */
    function _where_to_string($where)
    {
        if( empty($where) )
            return('');

        if( is_string($where) )
            return($where);

        if( is_array($where) )
        {
            $str = '(';
            foreach( $where as $K => $V )
                $str .= "($K = '" . addslashes($V) . "') AND";

            $where = substr($str,0,-4) . ') ';
        }

        return($where);
    }

    /**
    * Internal helper that actually constructs SELECT statements
    *
    * @param mixed $where string or array representing WHERE clause
    * @param string $columns SELECT will be limited to these columns
    * @return string $select Fully formed SELECT statement
    */
    function _get_select($where,$columns='*')
    {
        if( empty($this->_dataview) )
        {
            $where = $this->_where_to_string($where);

            if( $where )
            {
                $this->_last_where = $where;
                $where = "WHERE $where";
            }

            $extra = '';
            if( $columns == '*' && $this->_extra_columns )
                $extra = ',' . implode(',',$this->_extra_columns);
            $join = implode(' ', $this->_joins);

            $group = $this->_group_on_key ? "\nGROUP BY " . $this->_key_field : '';
            if( empty($group) && $this->_group_on )
                $group = "\nGROUP BY " . $this->_group_on;
            $sql = "SELECT $columns $extra \nFROM $this->_table_name \n $join \n $where $group";
        }
        else
        {
            $dtable = new CCTable('cc_tbl_dataview','dataview_id');
            $row = $dtable->QueryKeyRow($this->_dataview);
            $sql = $row['dataview_query'];
            $this->_dataview_flags = $row['dataview_flags'];
            global $CC_GLOBALS;
            $sql = str_replace('%home-url%', $CC_GLOBALS['home-url'], $sql );
        }

        $order = '';
        if( $this->_order )
            $sql .= "\n" . 'ORDER BY ' . $this->_order . ' ' . $this->_direction;

        $this->_add_offset_limit($sql);

        $this->_last_sql = $sql;

        return($sql);
    }

    /**
    * Internal helper to add OFFSET and LIMIT quota to SELECT statements
    *
    * @access private
    * @param string $sql A reference to the current SELECT statment to be appended
    */
    function _add_offset_limit(&$sql)
    {
        if( empty($this->_offset) && empty($this->_limit) )
            return;

        $sql .= " LIMIT " . $this->_limit;

        if( !empty($this->_offset) )
            $sql .= " OFFSET " . $this->_offset;
    }

    /**
    * Return the value of a single item.
    * 
    * <code>
    * $where['user_id'] = 10;
    * $name = $table->QueryItem('user_name', $where );
    * </code>
    * 
    * @param string $column_name Name of table's column
    * @param mixed $where string or array representing WHERE clause
    * @see Query
    * @return mixed $item Item from database 
    */
    function QueryItem($column_name,$where)
    {
        $sql = $this->_get_select($where,$column_name);
        return( CCDatabase::QueryItem($sql) );
    }

    /**
    * Return an array of a single item.
    * 
    * <code>
    * $where['user_location'] = 'berkeley';
    * $names = $table->QueryItems('user_name', $where );
    * foreach( $names as $name )
    * {
    *    // ...
    * }
    * </code>
    * 
    * @param string $column_name Name of table's column
    * @param mixed $where string or array representing WHERE clause
    * @return array $items Items from database 
    */
    function QueryItems($column_name,$where)
    {
        $sql = $this->_get_select($where,$column_name);
        return CCDatabase::QueryItems($sql);
    }

    /**
    * Return the key for a record that matches the $where clause
    * 
    * <code>
    * $where['user_name'] = 'Fred';
    * $key = $table->QueryKey($where );
    * </code>
    * 
    * @param mixed $where string or array representing WHERE clause
    * @see Query
    * @return mixed $key Item from database, typically a primary key number
    */
    function QueryKey($where)
    {
        return( $this->QueryItem( $this->_key_field, $where ) );
    }
    
    /**
    * Return array of keys for a record that matches the $where clause
    * 
    * <code>
    * $where['user_name'] = 'Fred';
    * $keys = $table->QueryKeys($where );
    * foreach( $keys as $key )
    * //...
    * </code>
    * 
    * @param mixed $where string or array representing WHERE clause
    * @see Query
    * @return array $keys Array of keys matching the $where paramater
    */
    function QueryKeys($where='')
    {
        $sql = $this->_get_select($where,$this->_key_field);
        $qr = CCDatabase::Query($sql);
        $keys = array();
        while( $r = mysql_fetch_row($qr) )
            $keys[] = $r[0];
        return($keys);
    }

    /**
    * Return all rows matching specific keys 
    * 
    * <code>
    * $rows = $table->QueryKeyRows( array( 300, 9, 4465) );
    * foreach( $rows as $row )
    * //...
    * </code>
    * 
    * @param mixed $keys Array or comma sep. string of keys to match
    * @see Query
    * @return array $rows Table records that match keys
    */
    function QueryKeyRows($keys)
    {
        if( is_array($keys) )
            $keys = join(',',$keys);
        if( empty($keys) )
            return array();
        $where = "{$this->_key_field} IN ({$keys})";
        return $this->QueryRows($where);
    }

    /**
    * Return the row for the record where key is $key
    * 
    * The constructor of this class determines what the key column
    * is.
    * 
    * <code>
    * // return the row the record whose key value is '4501'
    * $row =& $table->QueryKeyRow(4501);
    * </code>
    * 
    * @param string $key Key value
    * @see CCTable
    * @return array $row Row from database
    */
    function QueryKeyRow($key)
    {
        $key = addslashes($key);
        return( $this->QueryRow( $this->_key_field . " = '$key'" ) );
    }

    /**
    * Return an item from the record where key is $key
    * 
    * <code>
    * $name = $table->QueryItemFromKey('user_name',1058);
    * </code>
    * 
    * @param string $column_name Name of table's column
    * @param string $key Key value
    * @return mixed $item Item from database 
    */
    function QueryItemFromKey($column_name,$key)
    {
        $key = addslashes($key);
        return( $this->QueryItem( $column_name, $this->_key_field . " = '$key'" ) );
    }

    /**
    * Returns a single row that matches a query
    * 
    * <code>
    * $where['user_name'] = 'Fred';
    * $row = $table->QueryRow($where );
    * </code>
    * 
    * @param mixed $where string or array representing WHERE clause
    * @return array $row Row from database
    * @see Query
    */
    function QueryRow($where, $columns='*')
    {
        return( CCDatabase::QueryRow( $this->_get_select($where,$columns) ) );
    }

    /**
    * Returns an array  of rows that matches a query
    * 
    * <code>
    * $where['user_name'] = 'Fred';
    * $rows =& $table->QueryRows($where );
    * foreach( $rows as $row )
    * {
    *   // ....
    * }
    * </code>
    * 
    * @param mixed $where string or array representing WHERE clause
    * @return array $row Array of rows from database
    * @see Query
    */
    function & QueryRows($where,$columns='*')
    {
        $r =& CCDatabase::QueryRows(  $this->_get_select($where,$columns) );
        return $r;
    }

    /**
    * Return the number of rows matching the $where clause
    *
    * @see Query
    * @param mixed $where string or array to filter results (see Query)
    * @return integer $num_rows 
    */
    function CountRows($where = '' )
    {
        if( $this->_group_on_key )
        {
            $sql = $this->_get_select($where,'COUNT(*)');
            $rows = CCDatabase::QueryRows($sql,false);
            return( count($rows) );
        }
        return( $this->QueryItem("COUNT(*)",$where) );
    }

    /**
    * Return a boolean if the key exists or not
    *
    * @param int $key 
    * @return boolean $key_exists
    */
    function KeyExists($key)
    {
        return( $this->CountRows( $this->_key_field . " = '$key'" ) == 1 );
    }

    /**
    * Returns max value for a column
    *
    * @see Query
    * @param string $column Name of column with max value
    * @param mixed $where string or array to filter results (see Query)
    * @return integer
    */
    function Max($column, $where = '' )
    {
        return( $this->QueryItem("MAX($column)",$where) );
    }

    /**
    * Inserts a new record into the database
    *
    * <code>
    * $args['column_name_1'] = 'value_1';
    * $args['column_name_2'] = 'value_2';
    * $args['column_name_3'] = 'value_3';
    * $table->Insert($args);
    * </code>
    *
    * For a tutorial see {@tutorial cchost.pkg#insert Insert a Record into a Table}
    *
    * @param array $fields Associative array of column names and values 
    */
    function Insert($fields)
    {
        if( !empty($this->_watch_next_id) && !empty($fields[$this->_key_field]) )
        {
            $this->Update($fields);
            $this->_watch_next_id = null;
        }
        else
        {
            $columns  = array_keys($fields);
            $data     = array_values($fields);
            $cols     = implode( ',', $columns );
            $count    = count($data);
            $values   = '';
            for( $i = 0; $i < $count; $i++ )
                $values .= " '" . addslashes($data[$i]) . "', ";
            $values   = substr($values,0,-2);
            $sql = "INSERT INTO {$this->_table_name} ($cols) VALUES ( $values )";
            CCDatabase::Query($sql);
        }
    }

    /**
    * Inserts a batch of new records into the database
    *
    * <code>
    * $columns = array( 'col_name_1', 'col_name_2', 'col_name_3' );
    * $new_records = array(
    *           array( 'value_1_1', 'value_1_2', 'value_1_3' ),
    *           array( 'value_2_1', 'value_2_2', 'value_3_3' ),
    *           array( 'value_3_1', 'value_3_2', 'value_3_3' )
    *         );
    * $table->InsertBatch($columns, $new_records);
    * </code>
    *
    * @param array $columns Names of columns
    * @param array $value Array of records to insert
    */
    function InsertBatch($columns,$values)
    {
        $valuestr = '';
        foreach( $values as $valuefields )
        {
            $valuestr .= '( ';
            foreach( $valuefields as $value )
            {
                $valuestr .= "'" . addslashes($value) . "', ";
            }
            $valuestr = substr($valuestr,0,-2) . '), ';
        }
        $cols = implode(',',$columns);
        $sql = "INSERT INTO {$this->_table_name} ($cols) VALUES " . substr($valuestr,0,-2);
        CCDatabase::Query($sql);
   }

    /** 
    * Update a record in the table
    *
    * One of the $fields parameter <b>must</b> be the key field for this table
    * 
    * <code>
    * $args['col_key']  = $row_id;
    * $args['column_1'] = 'value_1';
    * $args['column_2'] = 'value_2';
    * $table->Update($args);
    * </code>
    *
    * For a tutorial see {@tutorial cchost.pkg#update Update a Database Record}
    *
    *
    * @see UpdateWhere
    * @param array $fields Associative array of columns to update
    * @param boolean $autoquote true: wrap values in single quotes
    */
    function Update($fields,$autoquote=true)
    {
        $this->UpdateWhere($fields, $this->_key_field . "= '{$fields[$this->_key_field]}'",$autoquote);
    }

    /**
    * Update records that match a specific where filter
    *
    * The $fields array parameter should <b>not</b> include the key field of the table.
    * See the Query() method for acceptable $where filter syntax.
    *
    * @see Update
    * @see Query
    * @param array $fields Associative array of columns to update
    * @param boolean $autoquote true: wrap values in single quotes
    * @param mixed $where string or array to filter results (see Query)
    */
    function UpdateWhere($fields,$where,$autoquote=true)
    {
        $sets = '';        
        foreach( $fields as $k => $v )
        {
            $v = addslashes($v);
            if( $autoquote )
                $v = "'$v'";
            $sets .= " $k = $v, ";
        }
        if( !empty($where) )
            $where = ' WHERE ' . $this->_where_to_string($where);
        else
            $where = '';
        $sql = "UPDATE $this->_table_name SET " . substr($sets,0,-2) . $where;
        CCDatabase::Query($sql);
        $this->_last_sql = $sql;
    }

    /**
    * Increment a count field
    *
    * @param string  $field Name of the field to inc
    * @param integer $key   Record key/id
    */
    function Inc($field,$key)
    {
        $sql = "UPDATE {$this->_table_name} SET {$field} = {$field} + 1 WHERE {$this->_key_field} = {$key}";
        CCDatabase::Query($sql);
    }

    /**
    * Decrement a count field
    *
    * @param string  $field Name of the field to inc
    * @param integer $key   Record key/id
    */
    function Dec($field,$key)
    {
        $sql = "UPDATE {$this->_table_name} SET {$field} = {$field} - 1 WHERE {$this->_key_field} = {$key}";
        CCDatabase::Query($sql);
    }

    /**
    * Delete a record given a row key
    *
    * @see DeleteWhere
    * @param integer $key 
    */
    function DeleteKey($key)
    {
        $key = addslashes($key);
        $this->DeleteWhere($this->_key_field . "= '$key'");
    }

    /**
    * Delete records based on a $where filter
    *
    * To delete all records:
    * <code>
    * $table->DeleteWhere('1');
    * </code>
    *
    * @see DeleteKey
    * @param mixed $where string or array to filter results (see Query)
    */
    function DeleteWhere($where)
    {
        $where = $this->_where_to_string($where);
        $sql = "DELETE FROM $this->_table_name WHERE $where";
        CCDatabase::Query($sql);
    }

    /**
    * Return an ID that is guaranteed unique and unused in the table
    *
    * Use this method when creating new recrods and you need to ID
    * of the newly row:
    * <code>
    * function new_record($table,$value1,$value2)
    * {
    *     $args['id'] = $table->NextID();
    *     $args['column_1'] = $value1;
    *     $args['column_2'] = $value2;
    *     $table->Insert($args);
    *     return $args['id'];
    * }
    * </code>
    *
    * @return integer $next_id
    */
    function NextID()
    {
        if( 0 )
        {
            // sigh, this doesn't work for reviews...
            // will investigate later...
            
            $this->_watch_next_id = true;
            $sql = "INSERT INTO {$this->_table_name} () VALUES ()";
            CCDatabase::Query($sql);
            return CCDatabase::LastInsertID();
        }
        else
        {
            /*
                This is open to race conditions
            */
            $sql = "SHOW TABLE STATUS LIKE '$this->_table_name'" ;
            $row = CCDatabase::QueryRow($sql);
            return( $row['Auto_increment'] );
        }
    }

    function Lock($type='WRITE')
    {
        CCDatabase::Query('LOCK TABLES ' . $this->_table_name . ' WRITE');
    }

    function Unlock()
    {
        CCDatabase::Query('UNLOCK TABLES');
    }

    /**
    * Returns strings usable for searching serialized PHP strings
    * 
    * Returns an array with two strings in it:
    *   first string is usable as a field in SELECT and WHERE statements
    *   second string is usable in WHERE statements for regex matching
    * 
    * <code>
    * $arr = serialize( 
    *          array( 'foo' => 'bar', 
    *                 'birthday' => '2004-01-29' ) );
    * $where['s_field'] = $arr;
    * $table->Insert($where);
    * 
    * // then...
    * // query for anything but baz
    * array( $s_field ) = 
    *    $table->WhereForSerializedField('s_field','foo');
    * $qwhere = "$s_field <> 'baz'";
    * $rows = $table->QueryRows($qwhere);
    * 
    * // or...
    * // query for today's birthdays
    * array( , $s_regex_where ) = 
    *    $table->WhereForSerializedField('s_field',
    *                         'foo','[0-9]+-01-29');
    * $rows = $table->QueryRows($s_regex_where);
    * </code>        
    * 
    * The selects drawn from this is potentially a very slow operation so use sparingly.
    * 
    * NB: This has only been tested with 
    * @param string $dbcol Name of serialized data column
    * @param string $fname Name of field in serialized 
    * @param string $matches Regex expression to match
    * @return array as described above
    */
    function WhereForSerializedField($dbcol, $fname, $matches = '[^"]+')
    {
        $locator = 's:' . strlen($fname) . ':"' . $fname . '";';
        $loc_len = strlen($locator);
        $locate  = "LOCATE('$locator',$dbcol)";
        $len_pos = $loc_len + 2;
        $len_len = "LOCATE(':', SUBSTRING( $dbcol, $locate + $len_pos, 5  )) - 1";
        $val_len = "SUBSTRING( $dbcol, $locate + $len_pos, $len_len)";
        $val_pos = "($len_pos + $len_len + 2)";
        $where   = '(' . $dbcol . ' REGEXP \'' . $locator . '.:[1-9]+:"' . $matches . '"\')';
        $field   = "SUBSTRING( $dbcol, $locate + $val_pos, $val_len )";

        return ( array( $field, $where )  );
    }
}
 
?>
