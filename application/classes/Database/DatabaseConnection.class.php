<?php
namespace Database {
	use PDO;
	use Exception;
	
	/**
	* Represents a connection between the server and the database.
	* Easily perform SQL queries without writing (more than neccesary) SQL.
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	* @version 1.0
	*/
	class DatabaseConnection {
		private $_connection;
		private static $singletonInstance;
		public $rowCount;

		/**
		* Initiate a new database connection through PDO.
		* @param (string) $hostname Hostname to connect to
		* @param (string) $username Username to use for authentication
		* @param (string) $password Password to use for authentication
		* @param (string) $database Name of the database to use
		* @return (void)
		* @throws DatabaseException
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		public function __construct($hostname, $username, $password, $database) {
			if (extension_loaded('pdo') === false) {
				throw new DatabaseException("Oh god! PDO does not appear to be enabled for this server.");
			}

			try {
				$this->_connection = new PDO("mysql:host=".$hostname.";dbname=".$database, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
				$this->_connection->query("SET NAMES utf8");
			} catch (PDODatabaseException $DatabaseException) {
				throw new DatabaseException($DatabaseException->getMessage(), $DatabaseException->getCode());
			}

			self::$singletonInstance = $this;
		}

		/**
		* Retrieve the latest initiated DatabaseConnection instance.
		* @since 1.0
		* @return (object)
		* @author Allan Thue Rehhoff
		*/
		public static function getInstance() {
			return self::$singletonInstance;
		}

		/**
		* Retrieve the connection instance used by the current DatabaseConnection instance.
		* You should rarely have a use for this though
		*/
		public function getConnection() {
			return $this->_connection;
		}

		/**
		* Execute a parameterized SQL query.
		* @param (string) $sql The SQL string to qeury against the database
		* @param (array) $args Arguments to pass along with the query
		* @param (int) $fetchMode Default fetch mode for result sets, set to null for query types that cannot be fetched such as UPDATE
		* @return (mixed)
		* @throws DatabaseException
		* @author Allan Thue Rehhoff
		* @since 1.0
		* @todo Find and alternative to casting errorcodes to integers for handling error codes.
		*/
		public function query($sql, $filters = null, $fetchMode = PDO::FETCH_OBJ) {
			$queryType = strtok($sql, " ");

			try {
				$stmt = $this->_connection->prepare($sql);
				$stmt->execute($filters);
				$this->rowCount = $stmt->rowCount();
				if(strtoupper(strtok($sql, " ")) == "SELECT") {
					$stmt->setFetchMode($fetchMode);
					return $stmt->fetchAll();
				} else {
					// There's properly a lot of obscure SQL keywords that does not support getting the last insert id, such as DELETE, ALTER, EXPLAIN, SHOW
					// But as long as this does not throw a hissy fit, the next developer can live happily ignoring that fact.
					return $this->lastInsertId();
				}
			} catch(PDODatabaseException $DatabaseException) {
				throw new DatabaseException($DatabaseException->getCode().": ".$DatabaseException->getMessage(), (int) $DatabaseException->getCode());
			}
		}

		/**
		* Select rows based on the given criteria
		* @param (string) $table Name of the table to query
		* @param (array) $criteria column => value pairs to filter the query results
		* @return (array)
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		public function select($table, $criteria = null) {
			$sql = "SELECT * FROM ".$table." WHERE ".$this->keysToSql($criteria, "AND");
			return $this->query($sql, $criteria);
		}

		/**
		* Fetch a single row from the given criteria.
		* Rows are not ordered, make sure your criteria matches the desired row.
		* @param (string) $table Name of the table containing the row to be fetched
		* @param (array) $criteria Criteria used to filter the rows.
		* @return (object)
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		public function fetchRow($table, $criteria = null) {
			$sql = "SELECT * FROM `".$table."` WHERE ".$this->keysToSql($criteria, "AND")." LIMIT 1";
			return $this->query($sql, $criteria)[0];
		}

		/**
		* Fetch a cells value from the given criteria.
		* @param (string) $table Name of the table containing the row to be fetched
		* @param (string) $column Column name in $table where cell value will be returned
		* @return (mixed)
		* @param (array) $criteria Criteria used to filter the rows.
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		public function fetchCell($table, $column, $criteria = null) {
			$sql = "SELECT `".$column."` FROM ".$table." WHERE ".$this->keysToSql($criteria, "AND")." LIMIT 1";
			return $this->query($sql, $criteria)[0]->$column;
		}

		/**
		* Inserts a row in the given table.
		* @param (string) $table Name of the table to insert the row in
		* @param (array) $variables Column => Value pairs to be inserted
		* @return (string) The last inserted ID
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		public function insert($table, $variables = null) {
			$variables = ($variables != null) ? $variables : [];
			return $this->createRow("INSERT", $table, $variables);
		}

		/**
		* Inserts a new row in the given table.
		* Already existing rows with matching PRIMARY key or UNIQUE index are deleted prior to inserting.
		* @param (string) $table Name of the table to replace into
		* @param (array) $variables Column => Value pairs to be inserted
		* @return (string) The last inserted ID
		*/
		public function replace($table, $variables) {
			return $this->createRow("REPLACE", $table, $variables);
		}

		/**
		* Internal function to insert or replace a row.
		* @param (string) $type SQL operator to with the INTO can be either INSERT or REPLACE
		* @param (string) $table Table to insert/replace the row in.
		* @param (array) $variables Column => Value pairs to insert.
		* @return (string) The last inserted ID
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		private function createRow($type, $table, $variables) {
			$binds = [];
			foreach ($variables as $key => $value) $binds[] = ":$key";
			$sql = $type. " INTO "."`$table` (" . implode(", ", array_keys($variables)) . ") VALUES (" . implode(", ", $binds) . ")";
			return $this->query($sql, $variables);
		}

		/**
		* Update rows in the given table depending on the criteria.
		* @param (string) $table Name of the table to update rows in
		* @param (array) $variables Column => Value pairs containg the new values for the row
		* @param (array) $criteria Array of criterie for updating a row
		* @return (int) Number of affected rows
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		public function update($table, $variables, $criteria = null) {
			$args = [];
			foreach ($variables as $key => $value) $args["new_".$key] = $value;
			foreach ($criteria as $key => $value) $args["old_".$key] = $value;
			
			$sql = "UPDATE `".$table."` SET ".$this->keysToSql($variables, ",", "new_")." WHERE ".$this->keysToSql($criteria, " AND ", "old_");
			$this->query($sql, $args);
			return $this->rowCount();
		}

		/**
		* Delete rows from the given table by criteria.
		* @param (string) $table Table to delete rows from
		* @param (array) $criteria Criteria for deletion
		* @author Allan Thue Rehhoff
		* @return (int) Number of rows affected.
		* @since 1.0
		* @todo Return row count
		*/
		public function delete($table, $criteria = null) {
			$sql = "DELETE FROM `".$table."` WHERE ".$this->keysToSql($criteria, " AND");
			$this->query($sql, $criteria);
			return $this->rowCount();
		}

		/**
		* Internal function to convert column=>value pairs into SQL.
		* @param (array) $array Array of arguments to parse (You sure yet that it's an array?)
		* @param (string) $seperator String seperator to seperate the pairs with
		* @param (string) $variablePrefix string to use for prefixing values in the SQL
		* @return (string)
		* @author Mikkel Jensen
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		private function keysToSql($array, $seperator, $variablePrefix = "") {
			if ($array == null) return "1";

			$list = [];
			foreach ($array as $column => $value) {
				$list[] = " `$column` = :".$variablePrefix.$column;
			}

			return implode(' '.$seperator, $list);
		}

		/**
		* Debugging prepared statements can be severely painful, use this as you would with DatabaseConnection::query(); to output the resulting SQL
		* @param (string) $sql A parameterized SQL query
		* @param (array) $params Parameters for $statement
		* @return (string)
		* @author Allan Thue Rehhoff
		* @since 1.0
		* @todo Thoroughly test this, it appears to be semi-broken
		*/
		public function compileQuery($sql, $params = []) {
			$statement = preg_replace_callback("/(?:^|\W):(\w+)(?!\w)/", function ($k) use ($params) {
				return sprintf(" '%s'", $params[$k[1]]);
			}, $statement );

			return $statement;
		}

		/**
		* Get the last inserted ID
		* @return (int)
		* @author Allan Thue Rehhoff
		* @since 1.0
		*/
		public function lastInsertId() {
			return $this->_connection->lastInsertId();
		}

		/**
		* Retrieve the number of affected rows from last query
		* @return (int)
		* @author Allan Rehhoff
		* @since 1.0
		*/
		public function rowCount() {
			return $this->rowCount;
		}
	}
}