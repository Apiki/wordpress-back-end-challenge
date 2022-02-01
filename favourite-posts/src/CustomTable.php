<?php
namespace App;

/**
 * Allow to users register interactions with posts
 */
class CustomTable
{
    /**
     *  Run when instantiating object 
     *  Sets $wpdb global object and trigger custom table creation
     * @param array $tableSettings custom table definitions
     *  @type string $name The name of custom table
     *  @type array $columns Table columns definition (see this::createColumnStatement)
     *  @$type bool|string $ID If true or a string adds a auto increasing primary key field called `ID` or named as the provided string
     * @todo make this a extension of WPDB class itself
     */
    public function __construct( array $tableSettings )
    {
        global $wpdb; // just declare global once here and use internal property so on
        $this->WPDB = $wpdb;
        $this->name = $this->WPDB->prefix . $tableSettings['name'];
        $this->columns = $tableSettings['columns'];
        $this->autoID = isset( $tableSettings['ID'] ) ? ( $tableSettings['ID'] === true ? 'ID' : $tableSettings['ID'] ) : 'ID';
        $this->charset = isset( $tableSettings['charset'] ) ? $tableSettings['charset'] : $this->WPDB->charset;
        $this->collate = isset( $tableSettings['collate'] ) ? $tableSettings['collate'] : $this->WPDB->charset;
        $this->checkDbTable();
    }

    /**
     * Check DB for table existence
     * Query DB for table name and creates table if it not exisis
     */
    public function checkDbTable() : void
    {
        if( $this->WPDB->get_var("SHOW TABLES LIKE '{$this->name}'") != $this->name ) {
            $this->createTable();
        }
    }

    /**
     * Check if custom table is empty
     * Returns true if table count(*) is 0
     */
    public function is_empty() : bool
    {
        return $this->count() == 0;
    }

    /**
     * Count a set of records
     * Returns number of records matching array $args (as COLUMN => VALUE pairs)
     * @param array $args Pairs of COLUMN => VALUE to build a SQL WHERE clause. 
     *  Defaults to empty array, meaning returning all records (no WHERE clause)
     */
    public function count( array $args = array() ) : int
    {
        $where = $this->whereClause( $args );
        return $this->WPDB->get_var( "SELECT COUNT(*) FROM {$this->name}{$where}" );
    }

    /**
     * Create a custom table
     * Create SQL CREATE TABLE statement from $tableSettings and run against $wpdb;
     * @param array $tableSettings Array in same format as $this::__construct
     */
    public function createTable() : void
    { 
        if( $this->autoID ) {
            $this->columns = [ $this->autoID => [
                'type' => 'bigint',
                'flags' => 'NOT NULL AUTO_INCREMENT PRIMARY KEY'
            ] ] + $this->columns; // prepend this field
        }
        $columns = $this->createColumnsStatement();
        $charset = $this->WPDB->get_charset_collate();
        $SQL = "CREATE TABLE {$this->name} ( ${columns} ) ${charset}";
        // require this file only when creating tables (activation hook)
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $SQL );
    }

    /**
     * Build CREATE TABLE columns statements
     * Output a list of SQL table definintions suitable use in CREATE TABLE command
     * @param [array] $columnsSettings Array with pairs of string $columName => array $columnSettings
     */
    private function createColumnsStatement() : string
    {
        $columns = array();
        $primaryKey = false;
        foreach( $this->columns as $name => $column ) {
            // column settings
            $name = isset( $column['name'] ) ? $column['name'] : $name;
            $type = strtoupper( isset( $column['type'] ) ? $column['type'] : 'text' );
            $length = isset( $column['length'] ) ? $column['length'] : false;
            $flags = isset( $column['flags'] ) ? $column['flags'] : false;
            // store per feild statement here
            $columnStatement = '';
            // parse type x length
            if( empty( $length ) ) {
                switch ( $type ) {
                    case 'CHAR':
                    case 'VARCHAR':
                    case 'tinytext':
                        $length = 255;
                        break;
                    case 'TEXT':
                        $length = 65535;
                        break;
                    case 'MEDIUMTEXT':
                        $length = 16777215;
                        break;
                    case 'LONGTEXT':
                        $length = 4294967295;
                        break;
                    case 'SMALLINT':
                        $length = 5;
                        break;
                    case 'MEDIUMINT':
                        $length = 8;
                        break;
                    case 'INT':
                        $length = 10;
                        break;
                    case 'BIGINT':
                        $length = 20;
                        break;
                    case 'DOUBLE':
                        $length = 5;
                        $fixed = 4;
                        break;
                    case 'DECIMAL':
                        $length = 8;
                        $fixed = 8;
                        break;
                    default:
                        $length = false;
                        break;
                }
            }
            // set primary key correctly
            if( preg_match( '/\bPRIMARY\s+KEY\b/i', $flags, $matches ) ) {
                $flags = str_replace( $matches[0], '', $flags );
                $primaryKey = $name;
            }
            $columnStatement .= "${name} ${type}";
            if( $length !== false ) {
                $columnStatement .= "(${length})";
            }
            if( ! empty( $flags ) ) {
                $columnStatement .= " ${flags}";
            }
            $columns[] = $columnStatement;
        }
        if( $primaryKey ) {
            $columns[] = "PRIMARY KEY  (${primaryKey})";
        }
        else {
            throw new \Exception( sprintf( "Missing a PRIMARY KEY definition in table '%s'", $this->name ) );
        }
        return "\n" . implode( ",\n", $columns ) . "\n";
    }

    /**
     * Insert a record to this custom table.
     * Proxy to WPDB::insert method using current table ->name property.
     * @param array $data Data to insert: colum => value pairs to add to DB table
     * @return int Added row ID field
     */
    public function insert( array $data ) : int
    {
        $this->checkDbTable();
        if( ! $this->WPDB->insert( $this->name, $data ) ) {
            die( sprintf( "failed inserting data into table '%s' (debug data: %s)", $this->name, json_encode( $data ) ) );
        }
        return $this->WPDB->insert_id;
    }

    /**
     * Insert a record to this custom table.
     * Proxy to WPDB::insert method using current table ->name property.
     * @param array $data Data to insert: colum => value pairs to add to DB table
     * @return int Added row ID field
     */
    public function delete( array $data ) : int
    {
        $this->checkDbTable();
        return $this->WPDB->delete( $this->name, $data );
    }

    /**
     * Update a record to this custom table.
     * Proxy to WPDB::update method using current table ->name property.
     * @param array $data Data to insert: colum => value pairs to add to DB table
     * @return int Added row ID field
     */
    public function update( array $data, array $where ) : int
    {
        $this->checkDbTable();
        return $this->WPDB->update( $this->name, $data, $where );
    }

    /**
     * Retrieve a record matching all $args fields x values pairs
     * Search for a table row matching to $args field values (combined as a AND search)
     * @param array Field names and corresponding values to search in custom table
     * @return [ array ] | null Array with row data if there is a match from database. Null otherwise.
     */
    public function get_row( array $where_args ) : ?array
    {
        $this->checkDbTable();
        return $this->WPDB->get_row( $this->where( $where_args ), ARRAY_A );
    }

    /**
     * Retrieve a set of records matching all $args fields x values pairs
     * Search for any table rows matching to $args field values (combined as a AND search)
     * @param array Field names and corresponding values to search in custom table
     * @return [ array ] | null Array with a set of row data if there is any match from database. Empty array otherwise.
     */
    public function get_results( array $where_args ) : ?array
    {
        $this->checkDbTable();
        return $this->WPDB->get_results( $this->where( $where_args ) );
    }

    /**
     * Build a safe SQL query using $wpdb->prepare
     * Convertes an array of fieldnames x values to search in a WHERE search
     * @param array Field names and corresponding values to search in custom table
     */
    public function where( array $where ) : ?string
    {
        $this->checkDbTable();
        $where_clause = $this->whereClause( $where );
        return $this->WPDB->prepare(
            "
            SELECT * FROM {$this->name}${where_clause};
            ",
            ARRAY_A
        );
    }

    /**
     * Retrieve a single value from table
     * Builds a query from array $args and proccess 

    /**
     * Builds a where clause from an array
     * Array must have pairs of vaild table names and desired match values. Returns WHERE clause as string.
     *  Returns a empty string if array is empty
     */
    public function whereClause( array $args ) : string
    {
        return array_reduce( 
            array_keys( array_filter( $args ) ),
            function( $clause, $field )use( $args ){
                $value = $args[ $field ];
                if( empty( $clause ) ) {
                    $clause .= ' WHERE ';
                }
                else {
                    $clause .= ' AND ';
                }
                if( is_string( $value ) ) {
                    $value = "'${value}'";
                }
                $clause .= "${field} = ${value}";
                return $clause;
            },
            ''
        );
    }
}