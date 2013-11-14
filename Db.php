<?
/**
 * Base class Object 
 * php ver 5
 *
 * sql for creating error log table
 
	CREATE TABLE `db_log_error` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`query` TEXT NOT NULL ,
		`error_num` INT NOT NULL ,
		`error_desc` VARCHAR( 255 ) NOT NULL ,
		`log_date` DATETIME NOT NULL ,
		`url` VARCHAR( 255 ) NOT NULL,
		`ip` VARCHAR( 15 ) NOT NULL
	);

 * sql for creating query log table

	CREATE TABLE `db_log_query` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`query` TEXT NOT NULL ,
		`log_date` DATETIME NOT NULL ,
		`url` VARCHAR( 255 ) NOT NULL,
		`ip` VARCHAR( 15 ) NOT NULL
	);
	
 */
class Db
{
	const DB_MAKE_SQL_WHERE_LIST	= 'list';
	const DB_MAKE_SQL_WHERE_SEARCH	= 'search';

	const DB_SORT_DIRECTION_ASC		= 'ASC';
	const DB_SORT_DIRECTION_DESC	= 'DESC';

	const DB_LOG_ERROR_TABLE = 'db_log_error';
	const DB_LOG_QUERY_TABLE = 'db_log_query';
	
	const DB_LOG_ERROR_FILE = LOGDB_ERROR_FILE;// . '/db_log_error.log';
	const DB_LOG_QUERY_FILE = LOGDB_QUERY_FILE;// . '/log/db_log_query.log';
	
	/**
	*@desc static instance for singleton db connection
	*/
	private static $instance = null;

	/**
	* Connection variables used in constructor
	*/
	private $host;
	public $database;
	private $user;
	private $password;
	
	private $connection_id;
	private $last_id;
	
	private $are_tables_locked;
	
	public $last_error;
	public $last_errno;
	
	protected $getError;
	
	public $log_query;
	public $log_error;
	
	public $log_to_db;
	public $log_to_file;

	/**
	 * Costruttore
	 * 
	 * @param host 
	 * @param user 
	 * @param password 
	 * @param database 
	 *
	 */
	
	function __construct( $host, $user, $password, $database )
	{
		
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		
		$this->connection_id = $this->connect(	$this->host,
												$this->user,
												$this->password,
												$this->database);

		$sql = "SET time_zone = 'Europe/Rome'";
		mysql_query($sql, $this->connection_id);

		$this->are_tables_locked = false;
		
		$this->last_id = 0;
		
		$this->log_query = true;
		$this->log_error = true;

		$this->log_to_db = false;
		$this->log_to_file = true;
		
	} 
	
	public function close() {
		if(!is_null($this->connection_id)){
			print("Chiudo mysql ".$this->connection_id) ;
			mysql_close( $this->connection_id );
			$this->connection = null ;
		}
	}

	/**
	 * Distruttore
	 */
	function __destruct() 
	{

		if ( $this->are_tables_locked ) 
		{
			$this->unlockTables();
		}
		
		
		//$this->close() ;
		
	}

	/**
	*@desc static function for creation of singleton db connection
	* @return Db
	*/
	public static function getInstance( $host = null, $user = null, $password = null, $database = null )
	{

	  if(self::$instance == null)
	  {
		 $classe = __CLASS__;
		 self::$instance = new $classe($host, $user, $password, $database);
	  } else {
	  	if($host == null && $user == null && $password == null && $database == null){
			return self::$instance;
	  	}
	  	
	  	if ($host != self::$instance->host || $user != self::$instance->user || $password != self::$instance->password || $database != self::$instance->database) {
			$classe = __CLASS__;
			self::$instance = new $classe($host, $user, $password, $database);
		}
	  }

	  return self::$instance;
	}
	
	public function reset() {
			$this->close() ;
			$classe = __CLASS__;
			return self::$instance = new $classe(self::$instance->host, self::$instance->user, self::$instance->password, self::$instance->database);
	}
	
	public function registerQuery(Array $array_data , $error = false){
		/*if($error){
			$str = "<p style='color:#ff0000;' title='$error'>".$str."</p>" ;
		}*/
		$str = $array_data[0] ;
		$data = $array_data[1] ;
		
		$array_query = SharedData::getTplVar("query_log") ;
		if(is_array($array_query)){
			array_push($array_query , Array(Array($str , $data) , $error)) ;
		} else {
			$array_query = Array(Array(Array($str , $data) , $error)) ;
		}
		SharedData::setTplVar("query_log" , $array_query) ;
	}

	/**
	 * <p>query</p>
	 *
	 * Execute sql query using current valid connection.
	 * Use this method when need to do SELECT or complicated queries.
	 * If need to do INSERT or UPDATE use exec().
	 * Return the id_resource identifier.
	 * 
	 * 
	 * @param sql 
	 * 
	 * @return resource id
	 * @public
	 */
	public function query($sql)
	{
		$rs = 0;
		if ($sql) 
		{
			$rs = mysql_query($sql, $this->connection_id);
		}
		
		$error = false ;
		if ( mysql_errno() > 0 && $this->log_error ) 
		{	
			$this->last_errno = mysql_errno();
			$this->last_error = mysql_error();
			
			
			$error = Array("Error" , "({$this->last_errno}) ".$this->last_error );
			
			$this->logSqlError ($sql, mysql_error(), mysql_errno());
			$rs = 0;
			$this->last_id = 0; 
		}
		elseif($rs)
		{
			if(mysql_num_rows($rs) == 0){
				$error = "Warning: 0 Rows" ;
				$error = Array("Warning" , "0 Rows" );
			}
			
			$this->last_id = mysql_insert_id( $this->connection_id );
		}
				
		if ( $this->log_query == true )
		{
			$this->logSqlQuery( $sql );
		}
		
		return $rs;
	} 
    
    public function simpleQuery($sql)
    {
 
        if ($sql) 
        {
            mysql_query($sql, $this->connection_id);
        }
        
        if ( $this->log_query == true )
        {
            $this->logSqlQuery( $sql );
        }
    }
   
	
	/**
	 * <p>execute</p>
	 *
	 * Execute sql query using current valid connection.
	 * Use this method when need to do INSERT or UPDATE or DELETE.
	 * If need to do SELECT use query().
	 * This method differs from query() because the use of mysql_unbuffered_query() insted of mysql_query() (faster and better memory)
	 * Return the id_resource identifier.
	 * 
	 * @param sql 
	 * 
	 * @return resource id
	 * @public
	 */
	public function execute($sql)
	{		
		$rs = 0;

		if ($sql) 
		{
			$rs = mysql_unbuffered_query($sql, $this->connection_id);
		}
		$error = false ;
		if ( mysql_errno() > 0 && $this->log_error )
		{
			$this->last_errno = mysql_errno();
			$this->last_error = mysql_error();

			$error = "Error: ({$this->last_errno}) ".$this->last_error ;
			$error = Array("Error" , "({$this->last_errno}) ".$this->last_error );
			
			$this->logSqlError ($sql, mysql_error(), mysql_errno());
			$rs = 0;
			$this->last_id = 0;
		}
		else
		{
			/*if(mysql_affected_rows($this->connection_id) == 0){
				$error = "Warning: 0 Rows" ;
				$error = Array("Warning" , "0 Rows");
			}*/
			
			$this->last_id = mysql_insert_id( $this->connection_id );
           
		}
		
                  
		if ( $this->log_query == true )
		{
			$this->logSqlQuery( $sql );
		}
		
		return $rs;
	} 

	/**
	 * <p>getLastId</p>
	 *
	 * Get the id of last record inserted in table for the current connection
	 *  
	 * @return int
	 * @public
	 */
	public function getLastId()
	{

		if ($this->connection_id) 
		{
			//return mysql_insert_id($this->connection_id);
			return intval($this->last_id);
		}
		
		return -1;

	} 

	/**
	 * <p>getNumRows</p>
	 * 
	 * Return the number of rows affected by a SELECT query. If id_resource is not valid return -1.
	 * 
	 * 
	 * @param id_resource
	 *
	 * @return int
	 * @public
	 */
	public function getNumRows($id_resource) 
	{
	
		if ($id_resource) 
		{
			return mysql_num_rows($id_resource);
		}
		
		return -1;
	} 

	/**
	 * <p>getAffectedRows</p>
	 *
	 * Return the number of rows affected by a INSERT, UPDATE or DELETE query. If connection_id is not valid return -1.
	 *
	 * @return int
	 * @public
	 */
	public function getAffectedRows() 
	{
	
		if ($this->connection_id) 
		{
			return mysql_affected_rows($this->connection_id);
		}
		
		return -1;
	} 

	/**
	 * <p>nextRecord</p>
	 *
	 * Return the current record and move to next.
	 * Use this method in code loop.
	 * example:
	 * $rs = $db->query($sql);
	 * while ($row = $db->nextRecord($rs)){
	 *  print_r($row);
	 * }
	 * 
	 * 
	 * @param id_resource
	 * @param stripslashes
	 * 
	 * @return Array
	 * @public
	 */
	public function nextRecord($id_resource, $stripslashes=false) 
	{		
		$row = array();
        if($id_resource)
		{
			$row = mysql_fetch_assoc($id_resource);

			if (mysql_errno() >0) {
				$this->last_errno = mysql_errno();
				$this->last_error = mysql_error();
	
				$this->logSqlError ($sql, mysql_error(), mysql_errno());
			}
			if ($stripslashes) {
				$row = $this->stripslashesArray( $row );
			}
		}
		else
		{
			echo $this->getError;
			//echo "query error";
			return false;
		}
		
		return $row;		
	} 
	
	/**
	 * <p>Return single record from a SELECT query.
	 * if field_name is specified return only the value of field_name
	 * </p>
	 * 
	 * @param sql 
	 * @param field_name 
	 * 
	 * @return mixed  
	 * @public
	 */
	public function getRecord($sql, $field_name = '', $stripslashes=false)
	{
		$retval = '';
		
		if ($sql) 
		{
			$rs = $this->query($sql);

			if ($this->getNumRows($rs)) 
			{
				$row = $this->nextRecord($rs, $stripslashes);
				
				if (trim($field_name) != '') 
				{
					$retval = $row[$field_name];
				} else {
					$retval = $row;
				}
				
			}
			
		}
		
		return $retval;
	}

	/**
	 * <p>Return all records by query and try tp parse strings replacing match with ini file</p>
	 * 
	 * @param sql 
	 *
	 * @return Array
	 * @public
	 */
	public function getRecordParsed($sql, $field_name = '', $stripslashes=false)
	{
		$record = $this->getRecord($sql, $field_name, $stripslashes);
		
		Common::parseArrayWithInifile($record);
		
		return $record;
	} 
	
	/**
	 * <p>Return all records by query</p>
	 * 
	 * @param sql 
	 *
	 * @return Array
	 * @public
	 */
	public function getRecordSet($sql, $stripslashes=false)
	{
	
		$recordset = Array();
		if ($sql) 
		{
			$rs = $this->query($sql);
			while ($row = $this->nextRecord($rs, $stripslashes))
			{
				$recordset[] = $row;
			}
		}

		$this->free($rs);

		return $recordset;
	}

    public function getRecordNameValue($sql, $name, $value)
	{

		$recordset = Array();
		if ($sql)
		{
			$rs = $this->query($sql);
			while ($row = $this->nextRecord($rs, $stripslashes))
			{
				if($name=='') {
					$recordset[] = $row[$value];
				}
				else {
					$recordset[$row[$name]] = $row[$value];
				}
			}
		}

		$this->free($rs);

		return $recordset;
	}

	/**
	 * <p>Return all records by query and try tp parse strings replacing match with ini file</p>
	 * 
	 * @param sql 
	 *
	 * @return Array
	 * @public
	 */
	public function getRecordSetParsed($sql, $stripslashes=false)
	{
		$recordset = $this->getRecordSet($sql, $stripslashes);
		
		Common::parseArrayWithInifile($recordset);
		
		return $recordset;
	} 
	
	/**
	 * <p>Return full table</p>
	 * 
	 * @param table_name 
	 * 
	 * @return Array
	 * @public
	 */
	public function getTable($table_name, $stripslashes=false) 
	{	
		$sql = "SELECT * FROM $table_name";
		return $this->getRecordset($sql, $stripslashes);
	} 

	/**
	 * <p>Do fast insert in table</p>
	 * 
	 * @param table_name 
	 * @param data 
	 * 
	 * @return last insert id
	 * @public
	 */
	public function insert($table_name, $data)
	{
		$sql_values = "" ;
		if (!$table_name)
		{
			return -1;			
		}
		
		if (!$data)
		{
			return -1;			
		}
		
		$sql_fields = join(",",array_keys($data));
		
		$tot_item = count($data);
		$i = 0;
		foreach($data as $item) 
		{
			//Magic Quote settato a On sul php.ini
			//Returns 0 if magic quotes gpc are off, 1 otherwise.
			if (get_magic_quotes_gpc())
			{ $sql_values .= " '".$item."' "; }
			else
			{ $sql_values .= " '".addslashes($item)."' "; }

			if ($i < $tot_item - 1) 
			{
				$sql_values .= ',';
			}
			$i++;
		}

		$sql = "INSERT INTO $table_name ($sql_fields) VALUES ($sql_values)";

		$this->execute($sql);
		
		return $this->getLastId();
		
	} 

	/**
	 * <p>update</p>
	 * 
	 * Do fast UPDATE on table, require unique field "id" in table
	 *
	 * @param table
	 * @param data 
	 * @param id
	 *
	 * @return affected rows
	 * @public
	 */
	public function update($table_name, $data, $id, $id_field = 'id')
	{
		if (!$table_name) 
		{
			return -1;
		}

		if (!$id) 
		{
			return -1;			
		}
		
		if (!$data) 
		{
			return -1;			
		}
		$i = 0;
		$tot_item = count($data);
		$sql_update = "" ;
		foreach($data as $key => $item) 
		{
			//$sql_update .= $key."='".addslashes(stripslashes($item))."'";
			
			if (get_magic_quotes_gpc())  //Magic Quote settato a On sul php.ini
			{ $sql_update  .= " ".$key." = '".$item."' "; }
			else
			{ $sql_update  .= " ".$key." = '".addslashes($item)."' "; }			
			
			if ($i < $tot_item - 1) 
			{
				$sql_update .= ',';
			}
			$i++;
		}

		$sql = "UPDATE $table_name SET $sql_update WHERE $id_field = '$id'";
		
		/*echo $sql;
		die();*/
		$this->execute($sql);
		
		return $this->getAffectedRows();
	} 

	/**
	 * <p>Do fast SELECT on table, require unique field "id" in table</p>
	 * 
	 * @param table_name 
	 * @param id 
	 * 
	 * @return Array
	 * @public
	 */
	public function select($table_name, $id, $stripslashes=false) 
	{
	
		$row = Array();
		if ($id) 
		{
			$sql = "SELECT * FROM $table_name WHERE id = '$id'";
			$row = $this->getRecord($sql, $stripslashes);
		}
		
		return $row;
	} 
 
 	/**
	 * <p>Do fast DELETE on table, require unique field "id" in table</p>
	 * 
	 * @param table_name 
	 * @param id 
	 * @param id_field      (opzionale)
	 * 
	 * @return affectedrows
	 * @public
	 */
	public function delete($table_name, $id, $id_field = 'id')
	{
	
		$row = Array();
		if ($id) 
		{
			$sql = "DELETE FROM $table_name WHERE $id_field = '$id'";
			//echo $sql . "<BR>";
			$row = $this->execute($sql);
		}
		else {
			return -1 ;
		}
		
		return $this->getAffectedRows();
	} 
 
 
 	public function free( $resource_id ) 
	{
		if ($resource_id) {
			mysql_free_result( $resource_id );
		}
	}

	public function getEnumValue($table, $col) 
	{
	
		$sql = "SHOW COLUMNS FROM ".$table." LIKE '".$col."'";
		
		$row = $this->getRecord($sql);
		
		$enum = array();
		if ( $row ) {
			$enum = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/", "\\2", $row['Type']) );
		}

		return $enum;
	}
	
	/**
	 * $table = array('users' => 'READ', 'product' => 'WRITE')
	 */	
	public function lockTables( array $tables ) 
	{
		
		if ( !count($tables) ) 
		{
			return false;
		}
		
		$sql = "LOCK TABLES ";
		foreach ( $tables as $table => $mode) 
		{
			if ( $mode != 'WRITE' && $mode != 'READ') 
			{
				$mode = 'READ';
			}
			$sql .= "$table $mode,";
		}
		
		$sql = substr($sql, 0, strlen($sql)-1);
		
		$this->execute( $sql );
		
		$this->are_tables_locked = true;
		
	}
	
	public function unlockTables() 
	{
	
		if ( $this->are_tables_locked ) 
		{
			$this->execute( 'UNLOCK TABLES' );
			$this->are_tables_locked = false;
		}
	
	}
	
	public static function makeSqlWhereList( array $filter )
	{
		
		return self::makeSqlWhere( $filter, self::DB_MAKE_SQL_WHERE_LIST );
		
	}
	
	public static function makeSqlWhereSearch( array $filter ) 
	{
		
		return self::makeSqlWhere( $filter, self::DB_MAKE_SQL_WHERE_SEARCH );
		
	}
	
	public static function makeSqlOrder( array $order ) 
	{
	
		$order_by = '';
		foreach($order as $field => $sort_direction) 
		{
			$sort_direction = strtoupper( $sort_direction );
			if ($sort_direction != self::DB_SORT_DIRECTION_ASC && $sort_direction != self::DB_SORT_DIRECTION_DESC )
			{
				$sort_direction = self::DB_SORT_DIRECTION_ASC;
			}
			
			$order_by .= " $field $sort_direction,";
		}
		
		if ( $order_by != '' ) 
		{
			$order_by = 'ORDER BY ' . substr( $order_by, 0, strlen($order_by)-1);
		}
		
		return $order_by;
		
	}
	
	public function getMultiRecordSet($sql) {
		$ritorno = false ;
		
		$mysqli = mysqli_init();	
		$mysqli->real_connect($this->host, $this->user, $this->password, $this->database);
		
		if (mysqli_connect_errno()){
		    printf("Connect failed: %s\n", mysqli_connect_error());
		    exit();
		}
		if($mysqli->real_query ($sql)){
			if($objResult = $mysqli->store_result()){
				$ritorno = Array() ;
				while($row = $objResult->fetch_assoc()){
					$ritorno[] = $row ;
				}
				
				$objResult->free_result();
			}
			else{
				print "no results found";
			}
		}
		else{	
			print $mysqli->error;
		}
		$mysqli->close();
		
		return $ritorno ;
	}
	
	private static function makeSqlWhere( $filter, $where_type ) 
	{
	
		$where = "1";
		
		foreach($filter as $field => $values) 
		{
			if (is_array($values)) 
			{
				$tmp = "0";
				for($i=0; $i<count($values); $i++) 
				{
					if ( $where_type == self::DB_MAKE_SQL_WHERE_SEARCH ) 
					{
						$tmp .= " OR $field LIKE '%".$values[$i]."'%";
					} else {
						$tmp .= " OR $field='".$values[$i]."'";
					}
				}
				$where .= ' AND (' . $tmp . ')';
			} else {
				
				if ( $where_type == self::DB_MAKE_SQL_WHERE_SEARCH ) 
				{
					$where .= " AND $field LIKE %$values% ";
				} else {
					$where .= " AND $field=$values ";
				}
				
			}
		}
		
		return 'WHERE ' . $where;
		
	}
	
	
	public function getDbInUse()
	{
		return $this->database;
	}
	/**
	 *
	 ***********************
	 ** private functions **
	 ***********************
	 *
	 */


	/**  
	 * <p>DB connection</p>
	 *
	 * @param		string	$host		Host del DB (opzionale)  
	 * @param		string	$user		User (opzionale)  
	 * @param		string	$password	Password (opzionale)  
	 * @param		string	$database	Nome del DB (opzionale)  
	 *  
	 * @return		int		L'id di connessione 
	 * @private	 
	 */
	private function connect( $host , $user , $password , $database )
	{  
		$id = mysql_connect($host, $user, $password, true);

		if ( $id )
		{
			if (!@mysql_select_db($database, $id))
			{
				$this->last_error = mysql_error();
				$this->last_errno = mysql_errno();
				return false;				
			}
		}

		return $id;
		  
	} 

	
	private function logSqlError ($query, $sql_error_desc, $sql_error_num) 
	{
		
		if ( $this->log_to_db )
		{
			$sql_error_desc = htmlspecialchars ($sql_error_desc);
			$sql_error_desc = addslashes($sql_error_desc);
			
			$sql= "INSERT INTO ".self::DB_LOG_ERROR_TABLE."
						(query, error_num, error_desc, log_date, url, ip)
						VALUES
						('".addslashes($query)."', $sql_error_num, '$sql_error_desc', NOW(), '".$_SERVER['REQUEST_URI']."' , '".$_SERVER['REMOTE_ADDR']."')";;
			$rs = @mysql_unbuffered_query($sql, $this->connection_id);
			$this->last_id = -1;
		}
		
		if ( $this->log_to_file )
		{
			$fp = fopen( self::DB_LOG_ERROR_FILE, 'a' );
			$log_msg = date('Y-m-d H:i:s') . ";" . $sql_error_num . ';' . $sql_error_desc . ";" . $_SERVER['REQUEST_URI'] . ";" . $_SERVER['REMOTE_ADDR'] . "\n";
			$log_msg .= $query . "\n";
			$log_msg .= str_repeat('-',20) . "\n";
			fwrite($fp,$log_msg);
			fclose($fp);			
		}
	}

	private function logSqlQuery( $query ) 
	{

		if ( $this->log_to_db )
		{
			$sql = "INSERT INTO ".self::DB_LOG_QUERY_TABLE."
						(query, log_date, url, ip)
						VALUES
						('".addslashes($query)."', NOW(), '".$_SERVER['REQUEST_URI']."', '".$_SERVER['REMOTE_ADDR']."')";
			$rs = mysql_unbuffered_query($sql, $this->connection_id);
		}
		
		if ( $this->log_to_file )
		{
			$fp = fopen( self::DB_LOG_QUERY_FILE, 'a' );
			$log_msg = date('Y-m-d H:i:s') . ";" . $_SERVER['REQUEST_URI'] . ";" . $_SERVER['REMOTE_ADDR'] . "\n";
			$log_msg .= $query . "\n";
			$log_msg .= str_repeat('-',20) . "\n";
			fwrite($fp,$log_msg);
			fclose($fp);			
		}		
	}
	
	private function stripslashesArray( $array ) 
	{
	
		if ( is_array( $array ) ) 
		{
			foreach($array as $key => $value) 
			{
				$array[$key] = stripslashes($value);
			}
		}
		return $array;
		
	}
	
	
	
}
?>