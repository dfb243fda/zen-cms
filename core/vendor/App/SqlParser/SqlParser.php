<?php

namespace App\SqlParser;

use App\Utility\GeneralUtility;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class SqlParser implements ServiceManagerAwareInterface
{
    protected $serviceManager;
        
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getUpdateSuggestions($queries, $execute = false, $execute_query_types = array('create', 'insert', 'update', 'foreign'))
    {        
        $lines = GeneralUtility::trimExplode(LF, $queries, 1);

        foreach ($lines as $k=>$value) {
            if (substr($value, 0, 1) == '#') {
                unset($lines[$k]);
            }
        }
        $queries = implode(LF, $lines);
        $queries = str_replace('#__', DB_PREF, $queries);

        $queries = GeneralUtility::trimExplode(';', $queries);
        $queries = array_filter( $queries );
            
        foreach ($queries as $k=>$v) {
            $queries[$k] = str_replace('`', '', $v);
        }
        
        $cqueries = array(); // Creation Queries
        $foreign_queries = array();
        $insert_queries = array(); // Insertion Queries
        $update_queries = array();
        $for_update = array();
        $current_val = array();

        // Create a tablename index for an array ($cqueries) of queries
        foreach($queries as $qry) {
            if (preg_match("|CREATE TABLE ([^ ]*)|", $qry, $matches)) {
                $cqueries[ trim( $matches[1], '`' ) ] = $qry;
                $for_update[$matches[1]] = 'Created table '.$matches[1];
            } elseif (preg_match("|CREATE DATABASE ([^ ]*)|", $qry, $matches)) {
                array_unshift($cqueries, $qry);
            } elseif (preg_match("|ALTER TABLE (.*)ADD CONSTRAINT .* FOREIGN KEY|s", $qry, $matches)) { 
                $foreign_queries[] = $qry;
            } elseif (preg_match("|INSERT INTO ([^ ]*)|", $qry, $matches)) {
                $insert_queries[] = $qry;
            } elseif (preg_match("|UPDATE ([^ ]*)|", $qry, $matches)) {
                $update_queries[] = $qry;
            } else {
                // Unrecognized query type
            }
        }
                
        $db = $this->serviceManager->get('db');

        foreach ( $cqueries as $table => $qry ) {

            // Fetch the table column structure from the database
    //        $tablefields = $wpdb->get_results("DESCRIBE {$table};");

            try {
                $tablefields = $db->query("DESCRIBE {$table};", array())->toArray();
                
                foreach ($tablefields as $k=>$v) {
                    $tablefields[$k] = (object)$v;
                }                
            } catch (\Exception $e) {
                $tablefields = false;
            }            
            
            if ( ! $tablefields )
                continue;

            // Clear the field and index arrays
            $cfields = $indices = array();
            // Get all of the field names in the query from between the parens
            preg_match("|\((.*)\)|ms", $qry, $match2);
            $qryline = trim($match2[1]);

            // Separate field lines into an array
            $flds = explode("\n", $qryline);

            //echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($cqueries, true)."</pre><hr/>";

            // For every field line specified in the query
            foreach ($flds as $fld) {
                // Extract the field name
                preg_match("|^([^ ]*)|", trim($fld), $fvals);
                $fieldname = trim( $fvals[1], '`' );

                // Verify the found field name
                $validfield = true;
                switch (strtolower($fieldname)) {
                case '':
                case 'primary':
                case 'index':
                case 'fulltext':
                case 'unique':
                case 'key':
                    $validfield = false;
                    $indices[] = trim(trim($fld), ", \n");
                    break;
                }
                $fld = trim($fld);

                // If it's a valid field, add it to the field array
                if ($validfield) {
                    $cfields[strtolower($fieldname)] = trim($fld, ", \n");
                }
            }

            // For every field in the table
            foreach ($tablefields as $tablefield) {
                // If the table field exists in the field array...
                if (array_key_exists(strtolower($tablefield->Field), $cfields)) {
                    // Get the field type from the query
                    preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", $cfields[strtolower($tablefield->Field)], $matches);
                    $fieldtype = $matches[1];

                    // Is actual field type different from the field type in query?
                    if ($tablefield->Type != $fieldtype) {
                        // Add a query to change the column type
                        $query = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " . $cfields[strtolower($tablefield->Field)];
                        $cqueries[] = $query;
                        $for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
                        $current_val[md5($query)] = $tablefield->Type;
                    }

                    // Get the default value from the array
                        //echo "{$cfields[strtolower($tablefield->Field)]}<br>";
                    if (preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
                        $default_value = $matches[1];
                        if ($tablefield->Default != $default_value) {
                            // Add a query to change the column's default value
                            $query = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} SET DEFAULT '{$default_value}'";
                            $cqueries[] = $query;
                            $for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
                            $current_val[md5($query)] = $tablefield->Default;
                        }
                    }

                    // Remove the field from the array (so it's not added)
                    unset($cfields[strtolower($tablefield->Field)]);
                } else {
                    // This field exists in the table, but not in the creation queries?
                }
            }

            // For every remaining field specified for the table
            foreach ($cfields as $fieldname => $fielddef) {
                // Push a query line into $cqueries that adds the field to that table
                $cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
                $for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
            }

            // Index stuff goes here
            // Fetch the table index structure from the database
    //        $tableindices = $wpdb->get_results("SHOW INDEX FROM {$table};");
            
            $tableindices = $db->query("SHOW INDEX FROM {$table};", array())->toArray();
            foreach ($tableindices as $k=>$v) {
                $tableindices[$k] = (object)$v;
            }
            
            if ($tableindices) {
                // Clear the index array
                unset($index_ary);

                // For every index in the table
                foreach ($tableindices as $tableindex) {
                    // Add the index to the index data array
                    $keyname = $tableindex->Key_name;
                    $index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
                    $index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
                }

                // For each actual index in the index array
                foreach ($index_ary as $index_name => $index_data) {
                    // Build a create string to compare to the query
                    $index_string = '';
                    if ($index_name == 'PRIMARY') {
                        $index_string .= 'PRIMARY ';
                    } else if($index_data['unique']) {
                        $index_string .= 'UNIQUE ';
                    }
                    $index_string .= 'KEY ';
                    if ($index_name != 'PRIMARY') {
                        $index_string .= $index_name;
                    }
                    $index_columns = '';
                    // For each column in the index
                    foreach ($index_data['columns'] as $column_data) {
                        if ($index_columns != '') $index_columns .= ',';
                        // Add the field to the column list string
                        $index_columns .= $column_data['fieldname'];
                        if ($column_data['subpart'] != '') {
                            $index_columns .= '('.$column_data['subpart'].')';
                        }
                    }
                    // Add the column list to the index create string
                    $index_string .= ' ('.$index_columns.')';
                    if (!(($aindex = array_search($index_string, $indices)) === false)) {
                        unset($indices[$aindex]);
                        //echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br />Found index:".$index_string."</pre>\n";
                    }
                    //else echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br /><b>Did not find index:</b>".$index_string."<br />".print_r($indices, true)."</pre>\n";
                }
            }

            // For every remaining index specified for the table
            foreach ( (array) $indices as $index ) {
                // Push a query line into $cqueries that adds the index to that table
                $cqueries[] = "ALTER TABLE {$table} ADD $index";
                $for_update[$table.'.'.$fieldname] = 'Added index '.$table.' '.$index;
            }

            // Remove the original table creation query from processing
            unset( $cqueries[ $table ], $for_update[ $table ] );
        }

        $tmp = $foreign_queries;
        $foreign_queries = array();
        foreach ($tmp as $k=>$v) {
            if (preg_match("|ALTER TABLE (.*)ADD CONSTRAINT .* FOREIGN KEY|sU", $v, $matches)) {
                $table = trim($matches[1], '`');
                $table = trim($matches[1]);
                
                $sqlRes = $db->query("
                    select CONSTRAINT_NAME from information_schema.KEY_COLUMN_USAGE where TABLE_SCHEMA = ? and
                    TABLE_NAME = ? and REFERENCED_TABLE_NAME is not null;
                ", array($db->getCurrentSchema(), $table))->toArray();
                
                $foreign_keys = array();
                foreach ($sqlRes as $row) {
                    $foreign_keys[$row['CONSTRAINT_NAME']] = $row;
                }
                
                if (preg_match('/ADD CONSTRAINT .*$/s', $v, $matches2)) {                    
                    $str = $matches2[0];
                    
                    $parts = GeneralUtility::trimExplode(',', $str);
                    foreach ($parts as $part) {
                        if (preg_match('/ADD CONSTRAINT ([^ ]*)/', $part, $matches3)) {                            
                            $foreign_key_id = trim($matches3[1]);
                            if (!isset($foreign_keys[$foreign_key_id])) {
                                $foreign_queries[] = 'ALTER TABLE ' . $table . ' ' . $part;
                            }
                        }                        
                    }
                }
            }
        }
        
        $allQueries = array(
            'create'  => $cqueries,            
            'insert'  => $insert_queries,
            'update'  => $update_queries,
            'foreign' => $foreign_queries,
        );

        $result = array();
        foreach ($allQueries as $k=>$v) {
            foreach ($v as $v2) {
                $md5_hash = md5($v2);
                if (isset($current_val[$md5_hash])) {
                    $result[$k][$md5_hash] = array(
                        'query' => str_replace(LF, ' ', $v2),
                        'currentValue' => $current_val[$md5_hash],
                    );
                } else {
                    $result[$k][$md5_hash] = str_replace(LF, ' ', $v2);
                }                
            }
        }
        
        if ($execute) {
            foreach ($result as $k=>$v) {
                if (in_array($k, $execute_query_types)) {
                    foreach ($v as $v2) {
                        if (is_array($v2)) {
                            $db->query($v2['query'], array());
                        } else {
                            $db->query($v2, array());
                        }
                    }
                }
            }
        }
        
        return $result;
    }
}