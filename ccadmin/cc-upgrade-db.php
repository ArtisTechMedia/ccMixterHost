<?

upgrade_db();

function upgrade_db()
{
    $new_tables_text = file_get_contents( dirname(__FILE__) . '/cchost_tables.sql');

    preg_match_all( '/CREATE TABLE ([^\s]+) \((.*\))\s+\) ENGINE[^;]+;/msU', $new_tables_text, $m );
    $new_tables = array();
    $c = count($m[1]);
    for( $i = 0; $i < $c; $i++ )
    {
        preg_match_all( '/^(?:\s+)?([^_\s]+_[^\s]+) (.*[^$])(?:,|$)/mUs', $m[2][$i], $fields );
        preg_match_all( "/^\s+([^_\s]+ .*\))(?:,|$)/msU", $m[2][$i], $indecies );
        $new_tables[ $m[1][$i] ] = array( 'cols' => $fields[1], 
                                          'cdefs' => $fields[2], 
                                          'indx' => $indecies[1], 
                                          'create' => $m[0][$i] );
    }
    unset($m);
        
    $old_tables = CCDatabase::ShowTables();
    foreach( $new_tables as $new_table_name => $new_table_info  )
    {
        if( in_array( $new_table_name, $old_tables ) )
        {
            $old_cols_raw = CCDatabase::QueryRows('DESCRIBE ' . $new_table_name);
            $old_cols = array();
            foreach( $old_cols_raw as $old_col )
                $old_cols[] = $old_col['Field'];
            $c = count($new_table_info['cols']);
            for( $i = 0; $i < $c; $i++ )
            {
                $new_col_name = $new_table_info['cols'][$i];
                if( !in_array( $new_col_name, $old_cols ) )
                {
                    $sql = 'ALTER TABLE ' . $new_table_name . ' ADD ' . $new_col_name . ' ' .$new_table_info['cdefs'][$i];
                    print( "Creating column: $new_table_name . $new_col_name<br />\n");
                    CCDatabase::Query($sql);
                }
            }
            foreach( $new_table_info['indx'] as $index )
            {
                if( strstr($index,'PRIMARY') )
                    continue;
                if( strstr($index,'FULLTEXT') )
                {
                    $def = str_replace('FULLTEXT KEY', 'FULLTEXT', $index);
                }
                else
                {
                    $def = str_replace('KEY ','INDEX ',trim($index));
                }
                print( "Creating index: $new_table_name . $def<br />\n");
                @mysql_query("alter table $new_table_name add $def");
                $err = mysql_error();
                if( $err )
                {
                    if( (strstr($err,'Duplicate key') === false) )
                        print("MySQL says (probaby ignorable): <b>$err</b><br />\n");
                    else
                        print(" - MySQL reports a duplicate key index, probably the index already exists?\n<br />");
                }
            }
        }
        else
        {
            print("Creating table: $new_table_name<br />\n");
            // create table
            $sql = $new_table_info['create'];
            CCDatabase::Query($sql);
        }

    }
}


?>