<?php
namespace Database {	
	use PDO;
	use Exception;

	/**
	* Handles the database connection.
	* Wraps around PDO
	*/
	class DbConnection {
		private static $connection;

		private $_db;

		private $_debug;
		private $_pageStart;
		private $_queryInfoList = array();

		/**
		* The constructer makes the initial database connection
		* @param (string) $host Hostname to connect to
		* @param (string) $dbName Name of the database to use
		* @param (string) $user Username to use for authentication
		* @param (string) $pass Password to use for authentication
		* @param (boolean) $debug Enable/Disable query debugging
		* @return void
		* @throws DatabaseException
		*/
		public function __construct($host, $dbName, $user, $pass, $debug = false) {
			$this->_debug = $debug;
			
			try {
				$this->_db = new PDO("mysql:host=".$host.";dbname=".$dbName, $user, $pass);
			} catch (Exception $e) {
				throw new DatabaseException("Unable to connect to database.");
			}
			$this->_db->query("SET NAMES utf8");
			if ($this->_debug) {
				$this->_pageStart = microtime(true);
			}
			DbConnection::$connection = $this;
		}
		
		/**
		* Get the currently instantiated database instance.
		* @return object
		*/
		public static function getInstance() {
			return DbConnection::$connection;
		}

		/**
		* Execute a query
		* @param (string) $sql The SQL string to qeury against the database
		* @param (array) $args Arguments to pass along with the query
		* @param (int) $fetchMode Default fetch mode for result sets
		* @return (mixed)
		* @throws DatabaseException
		*/
		public function execute($sql, $args = null, $fetchMode = PDO::FETCH_OBJECT) {
			if ($this->_debug) {
				$queryStart = microtime(true);
			}
			$stmt = $this->_db->prepare($sql);
			$stmt->setFetchMode($fetchMode);
			if (gettype($stmt) != "object") {
				throw new DatabaseException('SQL Error: '.$sql);
			}
			$stmt->execute($args);
			if ($stmt->errorCode() != "00000") {
				throw new DatabaseException($stnt->errorCode().': '.$stmt->errorInfo());
			}
			if ($this->_debug) {
				$queryInfo = array(
					"time" => microtime(true) - $queryStart,
					"count" => 1,
					"sql" => $sql
				);
				$e = new DatabaseException();
				$queryInfo["stacktrace"] = $e->getTraceAsString();
				$this->_queryInfoList[] = $queryInfo;
			}

			return $stmt->fetchAll();
		}

		/**
		* Wrapper for DbConnection::execute()
		* @param (array) $args Arguments to pass along with the query
		* @param (int) $fetchMode Default fetch mode for result sets
		* @return (mixed)
		*/
		public function query($sql, $args = null) {
			return $this->execute($sql, $args);
		}

		/**
		* Query a single row
		* @param (string) $sql The SQL to query
		* @param (array) $args The arguments to pass along with the query
		* @param (boolean) $ignore_errors Log errors return by the execution
		* @return (mixed)
		*/
		public function queryRow($sql, $args = null, $ignore_errors = false) {
			$result = $this->execute($sql, $args);
			if (count($result) == 0) {
				if (!$ignore_errors) {
					error_log(print_r(debug_backtrace(), true));
				}
				return null;
			}
			return $result[0];
		}

		public function queryValue($sql, $args = null, $ignore_errors = false) {
			$result = $this->execute($sql, $args, PDO::FETCH_NUM);
			if (count($result) == 0 || count($result[0]) == 0) {
				if (!$ignore_errors) {
					error_log(print_r(debug_backtrace(), true));
				}
				return null;
			}
			return $result[0][0];
		}

		public function select($table, $filters = null) {
			$sql = "SELECT * FROM `$table` WHERE" . $this->keysToSql($filters, " AND");
			return $this->query($sql, $filters);
		}

		public function selectRow($table, $filters = null, $ignore_errors = false) {
			$sql = "SELECT * FROM `$table` WHERE" . $this->keysToSql($filters, " AND");
			return $this->queryRow($sql, $filters, $ignore_errors);
		}

		public function selectValue($var, $table, $filters = null) {
			$sql = "SELECT `$var` FROM `$table` WHERE" . $this->keysToSql($filters, " AND");
			return $this->queryValue($sql, $filters);
		}

		public function count($table, $filters = null) {
			$sql = "SELECT COUNT(*) FROM `$table` WHERE" . $this->keysToSql($filters, " AND");
			return $this->queryValue($sql, $filters);
		}

		private function keysToSql($array, $seperator, $var_prefix = "") {
			if ($array == null) {
				return " 1";
			}
			$list = array();
			foreach ($array as $key => $value) {
				$list[] = " `$key` = :" . $var_prefix . $key;
			}
			return implode($seperator, $list);
		}

		public function update($table, $vars, $filters = null) {
			$sql = "UPDATE `$table` SET" . $this->keysToSql($vars, ",", "new_") . " WHERE" . $this->keysToSql($filters, " AND", "old_");
			$args = array();
			foreach ($vars as $key => $value) {
				$args["new_".$key] = $value;
			}
			foreach ($filters as $key => $value) {
				$args["old_".$key] = $value;
			}		
			$this->execute($sql, $args);
		}

		public function delete($table, $filters = null) {
			$sql = "DELETE FROM `$table` WHERE" . $this->keysToSql($filters, " AND");
			$this->execute($sql, $filters);
		}

		public function insert($table, $vars = null) {
			if ($vars == null) {
				$vars = array();
			}
			$this->createRow("INSERT", $table, $vars);
			return $this->lastInsertId();
		}

		public function replace($table, $vars) {
			$this->createRow("REPLACE", $table, $vars);
		}

		private function createRow($type, $table, $vars) {
			$list = array();
			$colon_list = array();
			foreach ($vars as $key => $value) {
				$list[] = $key;
				$colon_list[] = ":$key";
			}
			$sql = "$type INTO `$table`(" . implode(", ", $list) . ") VALUES(" . implode(", ", $colon_list) . ")";
			$this->execute($sql, $vars);
		}

		/**
		* Get the last inserted ID
		* @return int
		*/
		public function lastInsertId() {
			return $this->_db->lastInsertId();
		}
		
		/**
		* Print out the debugging information from queries
		* @return void
		*/
		public function echoDebug() {
			if ($this->_debug) {
				$now = microtime(true);
				$result = array();
				$result["totalTime"] = number_format($now - $this->_pageStart, 3) . " secs";
				
				$stacktraceToQueryInfo = array();
				$totalQueryTime = 0;
				foreach ($this->_queryInfoList as $queryInfo) {
					$totalQueryTime += $queryInfo["time"];
					$stacktrace = $queryInfo["stacktrace"];
					if (isset($stacktraceToQueryInfo[$stacktrace])) {
						$stacktraceToQueryInfo[$stacktrace]["count"]++;
						$stacktraceToQueryInfo[$stacktrace]["time"] += $queryInfo["time"];
					} else {
						$stacktraceToQueryInfo[$stacktrace] = $queryInfo;
					}
				}
				$queryInfoList = array_values($stacktraceToQueryInfo);
				foreach ($queryInfoList as &$queryInfo) {
					$queryInfo["time"] = number_format($queryInfo["time"], 3) . "secs";
				}
				$result["totalQueryTime"] = number_format($totalQueryTime, 3) . "secs";
				$result["queries"] = $queryInfoList;
				print '<script>';
				print 'if (console) {console.log('.json_encode($result).');}';
				print '</script>';
			}
		}
	}
}