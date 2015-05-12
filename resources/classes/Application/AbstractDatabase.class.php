<?php

//2012-02-14

/*

AbstractDatabase, a database abstraction layer for sqlite, mysql & mssql


ChangeLog:

v1.5.0 - 2013-12-06/07
Removed debugSQL()
Renamed getQueries() to getQueryCount()
Added logQueries() to log info about all queries (cpu & ram intensive, use only for debugging)
Added getExecutedQueries() returns all executed queries along with relevant data
Added getQueryStatistics() returns some simple statistics based on the executed queries
Added logQueryStackTrace() to add the stacktrace to the query log

v1.4.0 - 2013-05-03
Made properties protected instead of private

v1.3.0 - 2013-04-15
Added method queryValues()

v1.2.0 R2 - 2012-11-19
Made all variables camelCase

v1.2.0 - 2012-10-23
Added SQL variables to the debug output when using debugSQL()

v1.1.0 - 2012-10-15
Added method debugSQL() that will print out all sql statements before executing them

v1.0.0 - 2012-02-21
Initial release

v1.0.0 RC2 - 2012-02-19

v1.0.0 RC1 - 2012-02-15

v1.0.0 Dev - 2012-02-14


TODO:
Use exceptions instead of error detection to get better error messages

*/

namespace Application;
use \PDO;

class AbstractDatabase {

	const SQLITE2 = 1;
	const SQLITE2_MEM = 2;
	const SQLITE3 = 3;
	const SQLITE3_MEM = 4;
	const MYSQL = 5;
	const MSSQL = 6;
	
	protected $type = 0;
	protected $host = '';
	protected $user = '';
	protected $password = '';
	protected $database = '';
	protected $instance = null;
	protected $error = false;
	protected $errorMessage = '';
	protected $errorHandler = null;
	protected $debug = false;
	protected $showErrors = true;
	protected $queryCount = 0;
	protected $inTransaction = false;
	protected $logQueries = false;
	protected $logQueryStackTrace = false;
	protected $executedQueries = [];
	
	protected $driverTypes = array(
		self::SQLITE2		=>'sqlite2',
		self::SQLITE2_MEM	=>'sqlite2',
		self::SQLITE3		=>'sqlite',
		self::SQLITE3_MEM	=>'sqlite',
		self::MYSQL			=>'mysql',
		self::MSSQL			=>'mssql'
	);
	
	/*
	* for SQLite 2 & 3 the prototype is as follows
	* 
	* GenericDatabase(Type, Database);
	*/
	public function __construct($type, $host, $user=null, $password=null, $database=null) {
		$this->type = $type;
		
		if($type == self::SQLITE2 || $type == self::SQLITE2_MEM || $type == self::SQLITE3 || $type == self::SQLITE3_MEM) {
			$this->database = $host;
		} else if($type == self::MYSQL || $type == self::MSSQL) {
			$this->host = $host;
			$this->user = $user;
			$this->password = $password;
			$this->database = $database;
		} else {
			$this->setError('Invalid database type ('.$type.')');
			return false;
		}
		
		$this->instance = $this->connect($this->database);
		
		$this->checkError();
	}
	
	private function connect($database) {
		$instance = null;
		
		if(!in_array($this->driverTypes[$this->type], PDO::getAvailableDrivers())) {
			$this->setError('PDO Driver not found ('.$this->driverTypes[$this->type].')');
			return;
		}
		
		try {
			if($this->type == self::SQLITE2) {
				$instance = new PDO('sqlite2:'.$database);
			} else if($this->type == self::SQLITE2_MEM) {
				$instance = new PDO('sqlite2::memory:');
			} else if($this->type == self::SQLITE3) {
				$instance = new PDO('sqlite:'.$database);
			} else if($this->type == self::SQLITE3_MEM) {
				$instance = new PDO('sqlite::memory:');
				
				//$instance = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_PERSISTENT=>true));
			} else if($this->type == self::MYSQL) {
				if(strpos($this->host, ':') !== false) {
					$hostPort = explode(':', $this->host);
					$varDSN = 'host='.$hostPort[0].';port='.$hostPort[1].';';
				} else {
					$varDSN = 'host='.$this->host.';';
				}
				
				$instance = new PDO('mysql:'.$varDSN.'dbname='.$database, $this->user, $this->password);
				
				$instance->exec('SET NAMES utf8;');
			} else if($this->type == self::MSSQL) {
				if(strpos($this->host, ':') !== false) {
					$hostPort = explode(':', $this->host);
					$varDSN = 'Server='.$hostPort[0].','.$hostPort[1].';';
				} else {
					$varDSN = 'Server='.$this->host.';';
				}
				
				$instance = new PDO('sqlsrv:'.$varDSN.'Database='.$database, $this->user, $this->password);
			}
		} catch(PDOException $ex) {
			$this->setError($ex->getMessage());
		}
		
		$instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		$instance->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);
		$instance->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
		$instance->setAttribute(PDO::ATTR_TIMEOUT, 10);
		$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		
		return $instance;
	}
	
	public function logQueries($do=true) {
		$this->logQueries = $do;
	}
	
	public function logQueryStackTrace($do=true) {
		$this->logQueryStackTrace = $do;
	}
	
	/*
	* Check if an error has occurred, and reference the error message
	*/
	public function error(&$err=null) {
		$err = $this->errorMessage;
		return $this->error;
	}
	
	private function errorSource($trace) {
		$len = count($trace);
		//find the last entry where 'class' is set to this class or the child class, this should be the first call made to the database thus the start of the error
		for($i=($len-1);$i>=0;$i--) {
			if(isset($trace[$i]['class']) && ($trace[$i]['class'] == __CLASS__ || $trace[$i]['class'] == get_class($this))) {
				return $trace[$i];
			}
		}
		
		if($this->debug) {
			print __CLASS__.': No valid trace found!';
			exit;
		} else {
			return false;
		}
	}
	
	/*
	* Sets an error message, and calls the error display mechanism
	*/
	protected function setError($message, $prefix=null) {
		$this->errorMessage = $message;
		$this->error = true;
		
		if($prefix === null) {
			$prefix = get_class($this).': ';
		}
		
		if($this->errorHandler !== null) {
			$callback = $this->errorHandler;
			$traceOptions = ((defined('DEBUG_BACKTRACE_IGNORE_ARGS')) ? DEBUG_BACKTRACE_IGNORE_ARGS : 0);
			
			$fullTrace = debug_backtrace($traceOptions);
			$errorSource = $this->errorSource($fullTrace);
			
			$callback($this->errorMessage, $errorSource, $this->showErrors, $fullTrace);
		} else if($this->showErrors) {
			print $prefix.$this->errorMessage;
			exit;
		}
	}
	
	private function checkError($obj=null) {
		if($obj === null) {
			$errInfo = $this->instance->errorInfo();
		} else {
			$errInfo = $obj->errorInfo();
		}
		
		if($errInfo[0] !== '00000' && $errInfo[0] !== '') {
			$this->setError('Error: '.$errInfo[2].' ('.$errInfo[0].')');
			return true;
		} else {
			return false;
		}
	}
	
	/*
	* Sets a custom error handler (overrides the showErrors() setting)
	*/
	public function setErrorHandler($callback) {
		if(is_callable($callback)) {
			$this->errorHandler = $callback;
			return true;
		} else {
			return false;
		}
	}
	
	/*
	* Only use for debugging, not development
	*/
	public function debug($enable=null) {
		if($enable === null) {
			return $this->debug;
		} else {
			$this->debug = (bool)$enable;
			
			if($this->debug) {
				$this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			} else {
				$this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);	//TODO: use exceptions instead (PDO::ERRMODE_EXCEPTION)
			}
		}
	}
	
	/*
	* NOTE: this setting has no direct effect when a custom error handler is used
	* however, the value is passed to the custom error handler so it might act based on this value
	*/
	public function showErrors($show=null) {
		if($show === null) {
			return $this->showErrors;
		} else {
			$this->showErrors = (bool)$show;
		}
	}
	
	/*
	* Returns the number of currently completed queries to the database
	*/
	public function getQueryCount() {
		return $this->queryCount;
	}
	
	public function getExecutedQueries() {
		return $this->executedQueries;
	}
	
	public function getQueryStatistics() {
		$queries = $this->getExecutedQueries();
		
		$data = [];
		if(count($queries) > 0) {
			$data['totalQueryTime'] = 0;
			foreach($queries as $query) {
				$data['totalQueryTime'] += $query->time;
			}
			
			$slowestSeen = 0;
			$data['slowestSingleQuery'] = '';
			foreach($queries as $query) {
				if($query->time > $slowestSeen) {
					$slowestSeen = $query->time;
					$data['slowestSingleQuery'] = $query;
				}
			}
			
			$queryTable = [];
			foreach($queries as $query) {
				$key = sha1($query->sql);
				
				if(!isset($queryTable[$key])) {
					$queryTable[$key] = (object) array(
						'sql'=>$query->sql,
						'totalTime'=>$query->time,
						'timesExecuted'=>1
					);
				} else {
					$queryTable[$key]->totalTime += $query->time;
					$queryTable[$key]->timesExecuted++;
				}
			}
			
			uasort($queryTable, function($a, $b) {
				if($a->timesExecuted < $b->timesExecuted) return 1;
				if($a->timesExecuted == $b->timesExecuted) return 0;
				if($a->timesExecuted > $b->timesExecuted) return -1;
			});
			
			$data['threeMostCommonQueries'] = array_slice(array_values($queryTable), 0, min(3, count($queryTable)));
			
			$compiledQueryTable = [];
			foreach($queries as $query) {
				$key = sha1($query->sql.json_encode($query->vars));
				
				if(!isset($compiledQueryTable[$key])) {
					$compiledQueryTable[$key] = (object)array (
						'sql'=>$query->sql,
						'totalTime'=>$query->time,
						'timesExecuted'=>1,
						'vars'=>$query->vars
					);
				} else {
					$compiledQueryTable[$key]->totalTime += $query->time;
					$compiledQueryTable[$key]->timesExecuted++;
				}
			}
			
			uasort($compiledQueryTable, function($a, $b) {
				if($a->timesExecuted < $b->timesExecuted) return 1;
				if($a->timesExecuted == $b->timesExecuted) return 0;
				if($a->timesExecuted > $b->timesExecuted) return -1;
			});
			
			$data['threeMostCommonCompiledQueries'] = array_slice(array_values($compiledQueryTable), 0, min(3, count($compiledQueryTable)));
			
			uasort($queryTable, function($a, $b) {
				if($a->totalTime < $b->totalTime) return 1;
				if($a->totalTime == $b->totalTime) return 0;
				if($a->totalTime > $b->totalTime) return -1;
			});
			
			$data['threeSlowestQueries'] = array_slice(array_values($queryTable), 0, min(3, count($queryTable)));
			
			uasort($compiledQueryTable, function($a, $b) {
				if($a->totalTime < $b->totalTime) return 1;
				if($a->totalTime == $b->totalTime) return 0;
				if($a->totalTime > $b->totalTime) return -1;
			});
			
			$data['threeSlowestCompiledQueries'] = array_slice(array_values($compiledQueryTable), 0, min(3, count($compiledQueryTable)));
		}
		
		return $data;
	}
	
	private function createStatement($sql, $vars) {
		foreach($vars as $name => $value) {
		
			//if the variable isn't used; remove it from the list
			if(strpos($sql, ':'.$name) === false) {
				unset($vars[$name]);
			} else {
				//to enable variable names with invalid characters
				if(preg_match('/^[a-z0-9_]+$/i', $name) != 1) {
					//if variable has invalid characters
					preg_match_all('/[a-z0-9_]+/i', $name, $matches);
					
					$newName = implode('_', $matches[0]);
					$sql = str_replace(':'.$name, ':'.$newName, $sql);
					unset($vars[$name]);
					$vars[$newName] = $value;
				}
			}
		}
		
		$statement = $this->instance->prepare($sql);
		
		$this->checkError();
		
		foreach($vars as $var => $value) {
			@$statement->bindValue(':'.$var, $value);
		}
		
		$this->checkError($statement);
		
		if($this->logQueries) $start = microtime(true);
		
		@$statement->execute(); //Sorry...
		
		if($this->logQueries) {
			$executedQuery = (object) array (
				'sql'=>$sql,
				'time'=>(microtime(true)-$start),
				'vars'=>$vars
			);
			
			if($this->logQueryStackTrace) $executedQuery->stackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			
			$this->executedQueries[] = $executedQuery;
		}
		
		$this->checkError($statement);
		
		$this->queryCount++;
		
		return $statement;
	}
	
	public function beginTransaction() {
		return ($this->inTransaction = $this->instance->beginTransaction());
	}
	
	public function commit() {
		if($this->inTransaction) {
			return !($this->inTransaction = !$this->instance->commit());
		} else {
			return false;
		}
	}
	
	public function rollback() {
		if($this->inTransaction) {
			return !($this->inTransaction = !$this->instance->rollback());
		} else {
			return false;
		}
	}
	
	/*
	* Executes the given SQL, and returns the number of affected rows
	*/
	public function exec($sql, $vars=[]) {
		$stmt = $this->createStatement($sql, $vars);
		
		return $stmt->rowCount();
	}
	
	/*
	* Queries the database with the given sql and returns a dataset with results
	*/
	public function query($sql, $vars=[]) {
		$stmt = $this->createStatement($sql, $vars);
		
		return $stmt->fetchAll();
	}
	
	/*
	* Queries the database with the given sql and returns a single result row
	*/
	public function queryRow($sql, $vars=[]) {
		$stmt = $this->createStatement($sql, $vars);
		
		$row = $stmt->fetch();
		
		return (($row === false)?null:$row);
	}
	
	/*
	* Queries the database with the given sql and returns the value of the first column in the result
	*/
	public function queryValue($sql, $vars=[]) {
		$stmt = $this->createStatement($sql, $vars);
		
		$value = $stmt->fetchColumn();
		
		return (($value === false) ? null : $value);
	}
	
	public function queryValues($sql, $vars=[]) {
		$values = [];
		foreach($this->query($sql, $vars) as $row) $values[] = current($row);
		
		return $values;
	}
	
	public function lastInsertID() {
		return $this->instance->lastInsertId();
	}
	
	/*
	* Returns an array with the names of all tables in the database
	*/
	public function getTables($database=null) {
		$database = (($database === null)?$this->database:$database);
		
		$tables = [];
		
		if($this->type == self::SQLITE2 || $this->type == self::SQLITE2_MEM || $this->type == self::SQLITE3 || $this->type == self::SQLITE3_MEM) {
			$rows = $this->query('SELECT * FROM sqlite_master');
			
			foreach($rows as $table) {
				$tables[] = $table['name'];
			}
		} else if($this->type == self::MYSQL) {
			$db = new self($this->type, $this->host, $this->user, $this->password, 'information_schema');
			$rows = $db->query('SELECT * FROM TABLES WHERE TABLE_SCHEMA = :database', array('database'=>$database));
			
			foreach($rows as $table) {
				$tables[] = $table['TABLE_NAME'];
			}
		} else if($this->type == self::MSSQL) {
			$this->setError('getTables(): Not implemented for this database type ('.$this->driverTypes[$this->type].')');
		}
		
		return $tables;
	}
}

?>