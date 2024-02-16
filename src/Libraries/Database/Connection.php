<?php
/**
* Represents a connection between the server and the database.
* Easily perform SQL queries without writing (more than neccesary) SQL.
*
* @version 4.0.0
*/
namespace Database {

	/**
	 * Base connection class
	 */
	class Connection {
		/** @var boolean True if a transaction has started, false otherwise. */
		protected $transactionStarted = false;

		/** @var object  The singleton instance of the this class. */
		protected static $singletonInstance;

		/** @var null|\PDO PDO Database handle */
		protected ?\PDO $dbh;

		/** @var null|array Filters to prepare before querying */
		protected ?array $filters = [];

		/** @var ?Statement Holds the last prepared statement after execution. */
		public ?Statement $statement;

		/** @var int Number of affected rows from last query */
		public int $rowCount = 0;

		/** @var int Number of queries executed. */
		public int $queryCount = 0;

		/** @var ?string Last query attempted to be executed. */
		public ?string $lastQuery = null;

		/** @var int Number When an array is passed as criteria this will be incremented for each value across all arrays */
		protected int $arrayINCounter = 0;

		/** @var string $database The database currently in use */
		public string $database = '';

		/** @var array $tables Whitelist of tables in initially selected database */
		public array $tables = [];

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
				throw new \RuntimeException("PDO does not appear to be enabled for this server.");
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
		 * @param string $method Name of method being called.
		 * @param string array $params Parameters being passed to method, default empty array.
		 * @throws \BadMethodCallException If the method called does not exist on the client (PDO) object.
		 * @since 1.3
		 * @return mixed
		 */
		public function __call(string $method, array $params = []): mixed {
			if(method_exists($this, $method)) {
				return call_user_func_array([$this, $method], $params);
			} else {
				throw new \BadMethodCallException("PDO::".$method." no such method.");
			}
		} 

		/**
		 * Does the actual connection
		 *
		 * @param string $hostname Hostname to connect to.
		 * @param string $username Username to use for authentication.
		 * @param string $password Password to use for authentication.
		 * @param string $database Name of the database to use.
		 * @return Connection
		 * @since 3.0
		 */
		public function connect(string $hostname, string $username, string $password, string $database): Connection {
			$this->dbh = new \PDO("mysql:host=".$hostname.";charset=utf8mb4", $username, $password);
			$this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->dbh->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ["Database\Statement", [$this]]);
			$this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

			$this->use($database);

			return $this;
		}

		/**
		 * Closes the PDO connection and nullifies any statements
		 *
		 * @return void
		 * @since 3.0
		 */
		public function close(): void {
			$this->statement = null;
			$this->dbh = null;
		}

		/**
		 * Retrieve the latest initiated \Database\Connection instance.
		 *
		 * @return Connection
		 * @since 1.0
		 */
		public static function getInstance(): Connection {
			return self::$singletonInstance;
		}

		/**
		 * Retrieve the connection instance used by the current \Database\Connection instance.
		 * You should rarely have a use for this though.
		 *
		 * @since 1.0
		 * @return Connection
		 */
		public function getConnection(): Connection {
			return $this;
		}

		/**
		 * Switch database to use
		 * @param string $database Name of the database to use
		 * @return void
		 */
		public function use(string $database): void {
			$this->query("USE ".$database);

			$this->database = $database;

			$this->tables = $this->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN, 0);
			$this->tables = array_flip($this->tables);
		}

		/**
		 * Turns off autocommit mode. Until changes made to the database are committed.
		 *
		 * @return boolean
		 * @since 2.4
		 */
		public function beginTransaction(): bool {
			return $this->transactionStarted = $this->dbh->beginTransaction();
		}

		/**
		 * Helping wrapper function for PDO::beginTranstion();
		 *
		 * @see \Database\Connection::beginTransaction() to start a transaction
		 * @return boolean
		 * @since 1.3
		 */
		public function transaction(): bool {
			return $this->beginTransaction();
		}

		/**
		 * Commits a transaction, returning the database connection to autocommit mode.
		 *
		 * @throws \RuntimeException On failure to commit the query.
		 * @return boolean
		 * @since 1.3
		 */
		public function commit(): bool {
			if($this->transactionStarted === true) {
				return $this->dbh->commit();
			} else {
				throw new \RuntimeException("Attempted to commit when not in transaction, or transaction failed to start.");
			}
		}

		/**
		 * Rolls back the current transaction
		 *
		 * @throws \RuntimeException When attempting a rollback while not in a transaction.
		 * @return boolean
		 * @since 1.3
		 */
		public function rollback(): bool {
			if($this->transactionStarted === true) {
				return $this->dbh->rollBack();
			} else {
				throw new \RuntimeException("Attempted rollback when not in transaction.");
			}
		}

		/**
		 * Overloads PDO::prepare(); to provide support for additional checks and functionality.
		 *
		 * @param string $sql The parameterized SQL string to query against the database
		 * @param array $driverOptions Arguments to pass along with the query
		 * @throws \PDOException On failure to prepare statement.
		 * @return Statement|false Returns a prepared SQL statement, instance of Database\Statement, false on failure
		 * @since 2.3
		 */
		public function prepare(string $sql, array $driverOptions = []): Statement|false {
			// if a filter value is an array we'll create an IN syntax
			foreach($this->filters ?? [] as $column => $filter) {
				if(gettype($filter) === "array") {
					$tmparr = [];

					foreach($filter as $item) {
						$key = "val" . $this->arrayINCounter++;
						$tmparr[$key]  = $item;
						$this->filters[$key] = $item;
					}

					// (:val0, :val1, :val2)
					$in = "(:".implode(", :", array_keys($tmparr)).')';

					// Catches and replace only the whole part of a named parameter,
					// determined by a whitespace or end of line.
					// This prevents parameters from being wrongfully replaced,
					// where one parameter overlaps with the string of another
					// e.g. ':pizza¨' would replace part of ':pizzaria'
					$sql = preg_replace("/:" . preg_quote($column, '/') . "(\s|$)/", $in . '$1', $sql);

					unset($this->filters[$column]);
				}
			}

			$statement = $this->dbh->prepare($sql, $driverOptions);

			// Simply passing $this->filters to PDO::execute();
			// will treat all parameter values as PDO::PARAM_STR
			// resulting in errors when passing int or NULL as values
			foreach($this->filters ?? [] as $param => $value) {
				$type = gettype($value);

				$paramType = match ($type) {
					"NULL" => \PDO::PARAM_NULL,
					"boolean" => \PDO::PARAM_BOOL,
					"integer" => \PDO::PARAM_INT,
					"double" => \PDO::PARAM_STR,
					"string" => \PDO::PARAM_STR,
					default => \PDO::PARAM_STR,
				};

				$statement->bindValue($param, $value, $paramType);
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
		 * @throws \PDOException On error if PDO::ERRMODE_EXCEPTION option is true.
		 * @return Statement
		 */
		public function query(string $sql, ?array $filters = null, int $fetchMode = \PDO::FETCH_OBJ): Statement {
			try {
				$this->filters 	 = $filters;

				$this->statement = null;
				$this->statement = $this->prepare($sql);
				$this->statement->execute();
				$this->statement->setFetchMode($fetchMode);
				$this->queryCount++;
			} finally {
				$this->filters = [];
				$this->lastQuery = $sql;
			}

			return $this->statement;
		}

		/**
		 * Count total number rows in a column
		 *
		 * @param string $table Name of the table containing the rows to be counted
		 * @param array $criteria Criteria used to filter the rows.
		 * @return int Number of row count
		 */
		public function count(string $table, ?array $criteria = null): int {
			$sql = "SELECT * FROM `".$this->safeTable($table)."`";

			if($criteria != null) {
				$sql .= ' WHERE ' . $this->keysToSql($criteria, 'AND');
			}

			return (int)$this->query($sql, $criteria)->rowCount();
		}

		/**
		 * Fetch a single row from the given criteria.
		 * Rows are not ordered, make sure your criteria matches the desired row.
		 *
		 * @param string $table Name of the table containing the row to be fetched
		 * @param array $criteria Criteria used to filter the rows.
		 * @return ?\stdClass Returns the first row in the result set, false upon failure.
		 * @since 1.0
		 */
		public function fetchRow(string $table, ?array $criteria = null): ?\stdClass {
			return $this->select($table, $criteria)->getFirst();
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
		public function fetchCell(string $table, string $column, ?array $criteria = null): mixed {
			return $this->select($table, $criteria)->getColumn($column)[0] ?? null;
		}

		/**
		* Alias of \Database\Connection::fetchCell implemented for the drupal developers sake.
		*
		* @deprecated Use fetchCell instead.  
		* @param mixed ...$args See fetchCell.
		* @return mixed Returns a single column from the next row of a result set or FALSE if there are no rows.
		* @since 1.0
		*/
		public function fetchField(mixed ...$args): mixed {
			return $this->fetchCell(...$args);
		}

		/**
		 * Select rows based on the given criteria
		 *
		 * @param string $table Name of the table to query
		 * @param array $criteria column => value pairs to filter the query results
		 * @return Collection
		 * @since 1.0
		 */
		public function select(string $table, ?array $criteria = null): Collection {
			$sql = "SELECT * FROM ".$this->safeTable($table)." WHERE ".$this->keysToSql($criteria, "AND");
			return new Collection($this->query($sql, $criteria)->fetchAll());
		}

		/**
		 * Performs a search of the given criteria
		 * 
		 * @param string $table Name of the table to search
		 * @param array $searches Sets of expressions to match. e.g. 'filepath LIKE :filepath'
		 * @param null|array $criteria Criteria variables for the search sets
		 * @return Collection
		 * @since 3.1.3
		 */
		public function search(string $table, array $searches = [], ?array $criteria = null): Collection {
			$sql = "SELECT * FROM ".$this->safeTable($table)." WHERE ".implode(" AND ", $searches);
			return new Collection($this->query($sql, $criteria)->fetchAll());
		}

		/**
		 * Inserts multiple rows in a single query
		 * 
		 * @param string $table Table to insert into
		 * @param null|array $variables Multidimensional with associative sub-arrays to insert
		 * @return Statement
		 */
		public function insertMultiple(string $table, ?array $variables = null): Statement {
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

			$sql = "INSERT INTO "."`".$this->safeTable($table)."` (`" . implode("`, `", array_keys($variables[0])) . "`) VALUES (" . implode("), (", $binds) . ")";

			return $this->query($sql, $values);
		}

		/**
		 * Inserts a row in the given table.
		 *
		 * @param string $table Name of the table to insert the row in
		 * @param null|array $variables Column => Value pairs to be inserted
		 * @return int The last inserted ID
		 * @since 1.0
		 */
		public function insert(string $table, ?array $variables = null): int {
			$sql = $this->createRowSql("INSERT", $table, $variables);
			$this->query($sql, $variables);
			return (int) $this->dbh->lastInsertId();
		}

		/**
		 * Replaces a new row into the given table.
		 * Already existing rows with matching PRIMARY key or UNIQUE index are deleted and then re-inserted.
		 *
		 * @param string $table Name of the table to replace into
		 * @param null|array $variables Column => Value pairs to be inserted
		 * @return int The last inserted ID
		 * @since 1.0
		 */
		public function replace(string $table, ?array $variables = null): int {
			$sql = $this->createRowSql("REPLACE", $table, $variables);
			$this->query($sql, $variables);
			return (int) $this->dbh->lastInsertId();
		}

		/**
		 * Update or insert row, uses ON DUPLICATE KEY syntax
		 * 
		 * @param string $table Table to update or insert again
		 * @param null|array $variables column => value pairs to insert/update
		 * @return Statement
		 * @since 3.2.0
		 */
		public function upsert(string $table, ?array $variables = null): Statement {
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
		 * @param null|array $criteria Array of criterie for updating a row
		 * @return int Number of affected rows
		 * @since 1.0
		 */
		public function update(string $table, ?array $variables, ?array $criteria = null): int {
			$args = [];

			foreach($variables as $key => $value) $args["new_".$key] = $value;
			foreach($criteria as $key => $value) $args["old_".$key] = $value;
			
			$sql = "UPDATE `" . $this->safeTable($table) . "` SET " . $this->keysToSql($variables, ",", "new_") . " WHERE " . $this->keysToSql($criteria, " AND ", "old_");
			return $this->query($sql, $args)->rowCount();
		}

		/**
		 * Delete rows from the given table by criteria.
		 *
		 * @param string $table Table to delete rows from
		 * @param null|array $criteria Criteria for deletion
		 * @return int Number of rows affected.
		 * @since 1.0
		 */
		public function delete(string $table, ?array $criteria = null): int {
			$sql = "DELETE FROM `".$this->safeTable($table)."` WHERE ".$this->keysToSql($criteria, " AND");
			return $this->query($sql, $criteria)->rowCount();
		}

		/**
		 * Internal function to insert or replace a row.
		 *
		 * @param string $type SQL operator to with the INTO can be either INSERT or REPLACE
		 * @param string $table Table to insert/replace the row in.
		 * @param null|array $variables Column => Value pairs to insert.
		 * @return string The last inserted ID
		 * @since 1.0
		 */
		private function createRowSql(string $type, string $table, ?array $variables = null): string {
			$binds = [];
			$variables = $variables ?? [];

			foreach ($variables as $key => $value) {
				$binds[] = ":".$key;
			}

			$sql = $type . " INTO "."`".$this->safeTable($table)."` (`" . implode("`, `", array_keys($variables)) . "`) VALUES (" . implode(", ", $binds) . ")";

			return $sql;
		}

		/**
		 * Internal function to convert column=>value pairs into SQL.
		 * If a parameter value is an array, it will be treated as such, using the IN operator.
		 *
		 * @param null|array $array Array of arguments to parse (You sure yet that it's an array?)
		 * @param string $seperator String seperator to seperate the pairs with
		 * @param string $variablePrefix string to use for prefixing values in the SQL
		 * @return string
		 * @since 1.0
		 */
		private function keysToSql(?array $array, string $seperator, string $variablePrefix = ""): string {
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
				
				$list[] = " `".$column."` ".$operator." :".$variablePrefix.$column;
			}

			return implode(' '.$seperator, $list);
		}

		/**
		 * Debugging prepared statements can be severely painful,
		 * use this in place of, or in conjunction with, \Database\Connection::query(); to output the resulting SQL.
		 * Replaces any parameter placeholders in a query with the corrosponding value that parameter.
		 * Assumes anonymous parameters from $params are are in the same order as specified in $query
		 * 
		 * IMPORTANT :
		 * 	This function must not be used in a production environment for constructing queries to be executed
		 * 	Doing so opens up your application to SQL injection vulnerabilities.
		 *
		 * @param string $query A parameterized SQL query
		 * @param array $filters Parameters for $query
		 * @return string The interpolated query.
		 * @since 2.4
		 */
		public function debugQuery(string $query, array $filters): string {
			$emulatePepares = $this->dbh->getAttribute(\PDO::ATTR_EMULATE_PREPARES);

			$this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

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

			$this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, $emulatePepares);

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
		public function interpolateQuery(string $query, array $filters): string {
			return $this->debugQuery($query, $filters);
		}

		/**
		 * Get the last inserted ID
		 *
		 * @param mixed $seqname (optional) Name of the sequence object from which the ID should be returned.
		 * @return string|false Returns the ID of the last inserted row or sequence value 
		 * @throws \PDOException On error if PDO::ERRMODE_EXCEPTION option is true.
		 * @since 1.0
		 */
		public function lastInsertId(mixed $seqname = null): string|false {
			return $this->dbh->lastInsertId($seqname);
		}

		/**
		 * Checks if table name is safe and returns it.
		 * @param string $table Table name to assert exists
		 * @return string $table The table name to check
		 * @throws \InvalidArgumentException If table name provided does not exists.
		 */
		public function safeTable(string $table) {
			if(($this->tables[$table] ?? null) === null) {
				throw new \InvalidArgumentException(sprintf("'%s' is not a valid table in %s",  $table, $this->database));
			}

			return $table;
		}
	}
}
