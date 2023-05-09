<?php
	/**
	* Represents a connection between the server and the database.
	* Easily perform SQL queries without writing (more than neccesary) SQL.
	* Credits to Mikkel Jensen & Victoria Hansen from whom I in cold blood have undeterred copied some of this code from.
	*
	* @author Allan Thue Rehhoff
	* @version 3.2.1
	*/
	namespace Database {
		class Connection {
			/** @var boolean True if a transaction has started, false otherwise. */
			private $transactionStarted = false;

			/** @var object  The singleton instance of the this class. */
			private static $singletonInstance;

			/** @var ?\PDO PDO Database handle */
			private ?\PDO $dbh;

			/** @var ?array Filters to prepare before querying */
			private ?array $filters = [];

			/** @var ?Statement Holds the last prepared statement after execution. */
			public ?Statement $statement;

			/** @var ?int Number of affected rows from last query */
			public int $rowCount = 0;

			/** @var int Number of queries executed. */
			public int $queryCount = 0;

			/** @var ?string Last query attempted to be executed. */
			public ?string $lastQuery = null;

			/** @var int Number When an array is passed as criteria this will be incremented for each value across all arrays */
			private int $arrayINCounter = 0;

			/**
			* Initiate a new database connection using PDO as a driver.
			*
			* @param string $hostname Hostname to connect to
			* @param string $username Username to use for authentication
			* @param string $password Password to use for authentication
			* @param string $database Name of the database to use
			* @return void
			* @since 1.0
			*/
			public function __construct(string $hostname, string $username, string $password, string $database) {
				if (extension_loaded("pdo") === false) {
					throw new \Exception("PDO does not appear to be enabled for this server.");
				}

				$this->connect($hostname, $username, $password, $database);

				self::$singletonInstance = $this;
			}

			/**
			* This should most likely close the connection when you're done using the \Database\Connection
			*
			* @return void
			* @since 1.3
			*/
			public function __destruct() {
				$this->close();
			}

			/**
			* Allow methods not implemented by this class to be called on the connection
			*
			* @todo Consider removing the \Database\Connection::getConnection(); method now that we have this.
			* @throws Exception
			* @since 1.3
			*/
			public function __call(string $method, array $params = []) {
				if(method_exists($this, $method)) {
					return call_user_func_array([$this, $method], $params);
				} else {
					throw new \Exception("PDO::".$method." no such method.");
				}
			} 

			/**
			* Does the actual connection
			*
			* @param string $hostname Hostname to connect to
			* @param string $username Username to use for authentication
			* @param string $password Password to use for authentication
			* @param string $database Name of the database to use
			* @return void
			* @since 3.0
			*/
			public function connect(string $hostname, string $username, string $password, string $database) : Connection {
				$this->dbh = new \PDO("mysql:host=".$hostname.";dbname=".$database, $username, $password);
				$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				$this->dbh->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ["Database\Statement", [$this]]);
				$this->dbh->query("SET NAMES utf8mb4");

				return $this;
			}

			/**
			* Closes the PDO connection and nullifies any statements
			*
			* @return void
			* @since 3.0
			*/
			public function close() {
				$this->statement = null;
				$this->dbh = null;
			}

			/**
			* Retrieve the latest initiated \Database\Connection instance.
			*
			* @return Connection
			* @since 1.0
			*/
			public static function getInstance() : Connection {
				return self::$singletonInstance;
			}

			/**
			* Retrieve the connection instance used by the current \Database\Connection instance.
			* You should rarely have a use for this though.
			*
			* @since 1.0
			* @return Connection
			*/
			public function getConnection() : Connection {
				return $this;
			}

			/**
			* Turns off autocommit mode. Until changes made to the database are committed.
			*
			* @return boolean
			* @since 2.4
			*/
			public function beginTransaction() : bool {
				return $this->transactionStarted = $this->dbh->beginTransaction();
			}

			/**
			* Helping wrapper function for PDO::beginTranstion();
			*
			* @see Database\Connection::beginTransaction();
			* @return boolean
			* @since 1.3
			*/
			public function transaction() : bool {
				return $this->beginTransaction();
			}

			/**
			* Commits a transaction, returning the database connection to autocommit mode.
			*
			* @throws \PDOException
			* @return boolean
			* @since 1.3
			*/
			public function commit() : bool {
				if($this->transactionStarted === true) {
					return $this->dbh->commit();
				} else {
					throw new \PDOException("Attempted to commit when not in transaction, or transaction failed to start.");
				}
			}

			/**
			* Rolls back the current transaction
			*
			* @throws \PDOException
			* @return boolean
			* @since 1.3
			*/
			public function rollback() : bool {
				if($this->transactionStarted === true) {
					return $this->dbh->rollBack();
				} else {
					throw new \PDOException("Attempted rollback when not in transaction.");
				}
			}

			/**
			* Overloads PDO::prepare(); to provide support for additional checks and functionality.
			*
			* @param string $sql The parameterized SQL string to query against the database
			* @param array $driverOptions Arguments to pass along with the query
			* @since 2.3
			* @return Statement Returns a prepared SQL statement, instance of Database\Statement
			*/
			public function prepare(string $sql, array $driverOptions = []) : Statement {
				// if a filter value is an array we'll create an IN syntax
				if(!empty($this->filters)) {
					foreach($this->filters as $column => $filter) {
						if(is_array($filter) === true) {
							$tmparr = [];

							foreach($filter as $item) {
								$key = "val".$this->arrayINCounter;
								$tmparr[$key]  = $item;
								$this->filters[$key] = $item;
								$this->arrayINCounter++;
							}

							// (:val0, :val1, :val2)
							$in = "(:".implode(", :", array_keys($tmparr)).')';
							$sql = str_replace(":".$column, $in, $sql);
							unset($this->filters[$column]);
						}
					}
				}

				$statement = $this->dbh->prepare($sql, $driverOptions);

				// Simply passing $this->filters to PDO::execute();
				// will treat all parameter values as PDO::PARAM_STR
				// resulting in errors when passing int or NULL as values
				if(!empty($this->filters)) {
					foreach($this->filters as $param => $value) {
						$type = gettype($value);

						if($type == "NULL") {
							$statement->bindValue($param, $value, \PDO::PARAM_NULL);
						} else if($type == "boolean") {
							$statement->bindValue($param, $value, \PDO::PARAM_STR);
						} else if($type == "integer") {
							$statement->bindValue($param, $value, \PDO::PARAM_INT);
						} else if($type == "double") {
							$statement->bindValue($param, $value, \PDO::PARAM_STR);
						} else if($type == "string") {
							$statement->bindValue($param, $value, \PDO::PARAM_STR);
						} else {
							$statement->bindValue($param, $value);
						}
					}
				}

				return $statement;
			}

			/**
			* Execute a parameterized SQL query.
			*
			* @param string $sql The parameterized SQL string to query against the database
			* @param array $filters Arguments to pass along with the query.
			* @param int $fetchMode Set fetch mode for the query performed. Must be one of PDO::FETCH_* default is PDO::FETCH_OBJECT
			* @since 1.0 
			* @throws \PDOException
			* @return Statement
			*/
			public function query(string $sql, ?array $filters = null, int $fetchMode = \PDO::FETCH_OBJ) : Statement {
				try {
					$this->filters 	 = $filters;

					$this->statement = null;
					$this->statement = $this->prepare($sql);
					$this->statement->execute();
					$this->statement->setFetchMode($fetchMode);
					$this->queryCount++;
				} catch(\PDOException $exception) {
					throw new \PDOException($exception->getCode().": ".$exception->getMessage(), (int) $exception->getCode());
				} finally {
					$this->lastQuery = $sql;
				}

				return $this->statement;
			}

			/**
			* Count total number rows in a column
			*
			* @todo Find out if this can be replaced by countQuery();
			* @return int
			*/
			public function count(string $table, string $column, ?array $criteria = null) : int {
				$sql = "SELECT COUNT(`".$column."`) AS total FROM `".$table."`";

				if($criteria != null) {
					$sql .= ' WHERE ' . $this->keysToSql($criteria, 'AND');
				}

				return (int)$this->query($sql, $criteria)->fetch()->total;
			}

			/**
			 * Count the results of a query
			 * 
			 * @param string $query the parameterized query
			 * @param array $criteria Key value pairs to use with the query
			 * @return int Row coutn
			 */
			public function countQuery(string $query, array $criteria = []) : int {
				$result = $this->query($query, $criteria);
				return (int)$result->rowCount();
			}

			/**
			* Fetch a single row from the given criteria.
			* Rows are not ordered, make sure your criteria matches the desired row.
			*
			* @param string $table Name of the table containing the row to be fetched
			* @param array $criteria Criteria used to filter the rows.
			* @return mixed Returns the first row in the result set, false upon failure.
			* @since 1.0
			*/
			public function fetchRow(string $table, ?array $criteria = null) : ?\stdClass {
				$sql = "SELECT * FROM `".$table."` WHERE ".$this->keysToSql($criteria, "AND")." LIMIT 1";
				$row = $this->query($sql, $criteria)->fetch();
				return  $row !== false ? $row : null;
			}

			/**
			* Fetch a cells value from the given criteria.
			*
			* @param string $table Name of the table containing the row to be fetched
			* @param string $column Column name in $table where cell value will be returned
			* @param array $criteria Criteria used to filter the rows.
			* @return mixed Returns a single column from the next row of a result set or FALSE if there are no rows.
			* @since 1.0
			*/
			public function fetchCell(string $table, string $column, ?array $criteria = null) {
				$sql = "SELECT `".$column."` FROM `".$table."` WHERE ".$this->keysToSql($criteria, "AND")." LIMIT 1";
				return $this->query($sql, $criteria)->fetchColumn(0);
			}

			/**
			* Alias of \Database\Connection::fetchCell implemented for the drupal developers sake.
			*
			* @see \Database\Connection::fetchCell();
			*/
			public function fetchField(string $table, string $column, ?array $criteria = null) {
				return $this->fetchCell(...func_get_args());
			}

			/**
			* Fetches a column of values from the given table.
			* This function will always return an array.
			*
			* @param string $table Name of the table containing the rows to be fetched
			* @param string $column Column name in $table where value should be returned from.
			* @param array $criteria Criteria used to filter the rows.
			* @return array Returns an array containing all the rows matching in the resultset
			* @since 2.4
			*/
			public function fetchCol(string $table, string $column, ?array $criteria = null) : array {
				$sql = "SELECT `".$column."` FROM `".$table."` WHERE ".$this->keysToSql($criteria, "AND");
				return $this->query($sql, $criteria)->fetchCol();
			}

			/**
			* Select rows based on the given criteria
			*
			* @param string $table Name of the table to query
			* @param array $criteria column => value pairs to filter the query results
			* @return array
			* @since 1.0
			*/
			public function select(string $table, ?array $criteria = null) : array {
				$sql = "SELECT * FROM ".$table." WHERE ".$this->keysToSql($criteria, "AND");
				return $this->query($sql, $criteria)->fetchAll();
			}

			/**
			 * Performs a search of the given criteria
			 * 
			 * @param string $table Name of the table to search
			 * @param array $searches Sets of expressions to match. e.g. 'filepath LIKE :filepath'
			 * @param ?array $criteria Criteria variables for the search sets
			 * @return array
			 * @since 3.1.3
			 */
			public function search(string $table, array $searches = [], ?array $criteria = null) {
				$sql = "SELECT * FROM ".$table." WHERE ".implode(" AND ", $searches);
				return $this->query($sql, $criteria)->fetchAll();
			}

			/**
			 * Inserts multiple rows in a single query
			 * 
			 * @param string $table Table to insert into
			 * @param ?array $variables Multidimensional with associative sub-arrays to insert
			 * @return Statement
			 */
			public function insertMultiple(string $table, ?array $variables = null) : Statement {
				$binds = [];
				$values = [];

				foreach($variables as $i => $row) {
					$keys = [];

					foreach($row as $cell => $value) {
						$index = $cell . '_' . $i;

						$keys[] = ':' . $index;
						$values[$index] = $value;
					}

					$binds[] = implode(', ', $keys);
				}

				$sql = "INSERT INTO "."`$table` (`" . implode("`, `", array_keys($variables[0])) . "`) VALUES (" . implode("), (", $binds) . ")";

				return $this->query($sql, $values);
			}

			/**
			* Inserts a row in the given table.
			*
			* @param string $table Name of the table to insert the row in
			* @param array $variables Column => Value pairs to be inserted
			* @return int The last inserted ID
			* @since 1.0
			*/
			public function insert(string $table, ?array $variables = null) : int {
				$sql = $this->createRowSql("INSERT", $table, $variables);
				$this->query($sql, $variables);
				return (int) $this->dbh->lastInsertId();
			}

			/**
			* Replaces a new row into the given table.
			* Already existing rows with matching PRIMARY key or UNIQUE index are deleted and then re-inserted.
			*
			* @param string $table Name of the table to replace into
			* @param array $variables Column => Value pairs to be inserted
			* @return int The last inserted ID
			* @since 1.0
			*/
			public function replace(string $table, ?array $variables = null) : int {
				$sql = $this->createRowSql("REPLACE", $table, $variables);
				$this->query($sql, $variables);
				return (int) $this->dbh->lastInsertId();
			}

			/**
			 * Update or insert row, uses ON DUPLICATE KEY syntax
			 * 
			 * @param $table Table to update or insert again
			 * @param ?array $variables column => value pairs to insert/update
			 * @param ?array $criteria Criteria columns, e.g. columns with unique indexes
			 * @return Statement
			 * @since 3.2.0
			 */
			public function upsert(string $table, ?array $variables = null) : Statement {
				$updates = [];
				foreach($variables as $column => $value) $updates[] = '`' . $column . '` = VALUES(' . $column . ')';
				$sql = $this->createRowSql("INSERT", $table, $variables) . " ON DUPLICATE KEY UPDATE " . implode(',', $updates);

				return $this->query($sql, $variables);
			}

			/**
			* Update rows in the given table depending on the criteria.
			*
			* @param string $table Name of the table to update rows in
			* @param array $variables Column => Value pairs containg the new values for the row
			* @param array $criteria Array of criterie for updating a row
			* @return int Number of affected rows
			* @since 1.0
			*/
			public function update(string $table, ?array $variables, ?array $criteria = null) : int {
				$args = [];

				foreach ($variables as $key => $value) $args["new_".$key] = $value;
				foreach ($criteria as $key => $value) $args["old_".$key] = $value;
				
				$sql = "UPDATE `".$table."` SET ".$this->keysToSql($variables, ",", "new_")." WHERE ".$this->keysToSql($criteria, " AND ", "old_");
				return $this->query($sql, $args)->rowCount();
			}

			/**
			* Delete rows from the given table by criteria.
			*
			* @param string $table Table to delete rows from
			* @param array $criteria Criteria for deletion
			* @return int Number of rows affected.
			* @since 1.0
			* @todo Return affected row count
			*/
			public function delete(string $table, ?array $criteria = null) : int {
				$sql = "DELETE FROM `".$table."` WHERE ".$this->keysToSql($criteria, " AND");
				return $this->query($sql, $criteria)->rowCount();
			}

			/**
			* Internal function to insert or replace a row.
			*
			* @param string $type SQL operator to with the INTO can be either INSERT or REPLACE
			* @param string $table Table to insert/replace the row in.
			* @param array $variables Column => Value pairs to insert.
			* @return int The last inserted ID
			* @since 1.0
			*/
			private function createRowSql(string $type, string $table, ?array $variables = null) : string {
				$binds = [];
				$variables = $variables ?? [];

				foreach ($variables as $key => $value) {
					$binds[] = ":".$key;
				}

				$sql = $type . " INTO "."`$table` (`" . implode("`, `", array_keys($variables)) . "`) VALUES (" . implode(", ", $binds) . ")";

				return $sql;
			}

			/**
			* Internal function to convert column=>value pairs into SQL.
			* If a parameter value is an array, it will be treated as such, using the IN operator.
			*
			* @param array $array Array of arguments to parse (You sure yet that it's an array?)
			* @param string $seperator String seperator to seperate the pairs with
			* @param string $variablePrefix string to use for prefixing values in the SQL
			* @return string
			* @since 1.0
			*/
			private function keysToSql(?array $array, string $seperator, string $variablePrefix = "") : string {
				if ($array == null) return "1";

				$list = [];

				foreach ($array as $column => $value) {
					if($value === null && $seperator != ',') {
						$operator = "<=>";
					} else if(is_array($value)) {
						$operator = "IN";
					} else {
						$operator = '=';
					}
					
					$list[] = " `$column` $operator :".$variablePrefix.$column;
				}

				return implode(' '.$seperator, $list);
			}

			/**
			* Debugging prepared statements can be severely painful, use this as you would with \Database\Connection::query(); to output the resulting SQL
			* Replaces any parameter placeholders in a query with the corrosponding value that parameter.
			* Assumes anonymous parameters from $params are are in the same order as specified in $query
			*
			* @param string $query A parameterized SQL query
			* @param array $filter Parameters for $query
			* @return string The interpolated query.
			* @since 2.4
			*/
			public function debugQuery(string $query, array $filters) : string {
				$this->filters 	 = $filters;
				$statement = $this->prepare($query);

				$tmpFilters = [];
				foreach($this->filters as $column => $value) {
					if(substr($column, 0, 1) !== ':') {
						$arg = ":".$column;
					} else {
						$arg = $column;
					}

					$type = gettype($value);

					if($type == "string") {
						$value = "'".$value."'";
					} else if($type == "boolean") {
						$value = $value ? 1 : 0;
					} else if($type == "array") {
						$value = "('" . implode("', '", $value) . "')";
					}

					$tmpFilters[$arg] = $value;
				}

				return strtr($statement->queryString, $tmpFilters);
			}

			/**
			* Wrapper function for debugging purposes
			*
			* @see Database\Connection::debugQuery()
			* @param string $query A parameterized SQL query
			* @param array $filters Parameters for $query
			* @return string
			* @since 1.1
			*/
			public function interpolateQuery(string $query, array $filters) : string {
				return $this->debugQuery($query, $filters);
			}

			/**
			* Get the last inserted ID
			*
			* @return int Returns the ID of the last inserted row or sequence value 
			* @since 1.0
			*/
			public function lastInsertId($seqname = null) : int {
				return $this->dbh->lastInsertId($seqname);
			}
		}
	}
