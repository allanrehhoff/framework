<?php

//2011-06-25

/*
* Class to simplify database usage
ChangeLog:

v2.4.0 - 2013-05-03
Added extented where-selectors with operator control
Added method selectValues()

v2.3.2 - 2013-03-28
Fixed sum() to return int(0) when no results were found instead of NULL

v2.3.1 - 2013-03-04
Fixed rare bug in sqlWhere() where the foreach iterating over $where would also iterate over newly added items within the loop causing mismatches parameters

v2.3.0 - 2013-01-24
Added methods min(), max(), sum(), avg()

v2.2.2 R2 - 2012-11-19
Made all variables camelCase

v2.2.2 - 2012-10-15
Fixed mismatching variable count when using update() where the WHERE and the SET parameter was the same field and the WHERE is set to NULL

v2.2.1 - 2012-09-13
Fixed count() not working because the pseudo-sql-field COUNT(*) was quoted and thus not recognized as a function

v2.2.0 - 2012-09-11
Added method truncateTable()
Added proper escaping of field and table names

v2.1.0 - 2012-08-23
Added possibility to use OR in the WHERE statement in sqlWhere()

v2.0.0 - 2012-02-21
Changed database abstraction layer from SQLDatabase to AbstractDatabase
Rewrote most methods

v1.12.0 - 2012-02-06
Changed NULL support from string 'NULL' to php null

v1.11.1 - 2012-02-02
Prevented infinite loop in cases where the errorhandler made a call to simpledb

v1.11.0 - 2012-02-01
Made the error handler trigger in debug mode
Added 'debug' parameter to the error handler callback

v1.10.0 - 2011-12-23
Fixed errors was printed as an array.
Added setErrorHandler() to define a custom error handler

v1.9.0 - 2011-12-15
Added NULL support to WHERE; if uppercase 'NULL' is given as parameter in WHERE, it will perform a "WHERE field IS NULL" and not a "WHERE field = 'NULL'"

v1.8.0 - 2011-12-05
Added method getTables() to get an array of tables in the current database

v1.7.0 - 2011-11-02
Added method selectJoinRow()

v1.6.0 - 2011-10-12
Updated select(), selectJoin(), and query() to return an empty array on empty result
Changed selectJoin() to use 'USING()' instead of 'ON X = Y'

v1.5.0 - 2011-09-05
Added debug() method to enable/disable debugging
Added method getQueries() to get the current number of queries
Made ALL executions go through $this->exec() instead of going directly to $this->db->exec()

v1.4.0 - 2011-08-31
Made second parameter on insert() optional (to insert a blank row)
Changed insert()'s return value to lastInsertID()

v1.3.1 - 2011-08-15
Fixed utf8 bug

v1.3.0 - 2011-08-08
Added method selectJoin() to perform a simple join

v1.2.0 - 2011-08-05
Changed all functions to return null when no results were returned from the database
Updated compatibility with the latest SQLDatabase()

v1.1.0 - 2011-06-30
Fixed strict notices in selectRow() and selectValue()
Added method close()
Added option to give single value as limit
Added method count()
Added variables to query() and exec()
Added parameter Assoc to query()
Added method queryRow()
Added method queryValue()

v1.0.0 - 2011-06-25
Initial release

*/
namespace Application;

class SimpleDB extends AbstractDatabase {

	private function sqlWhere($where, &$vars=[]) {
		$sql = '';
		$vars = [];
		
		if($where === null) return '';
		
		//valid operators and whether they support IN
		$validOperators = array(
			'=='=>true,
			'!='=>true,
			'>'=>false,
			'>='=>false,
			'<'=>false,
			'<='=>false,
		);
		
		$operatorTranslationMap = array(
			'='=>'==',
			'<>'=>'!='
		);
		
		foreach($where as $fieldName => $operatorObject) {
			$sql .= (($sql == '')?' WHERE':' AND');
			
			if(!is_array($operatorObject)) $operatorObject = [$operatorObject, '=='];
			
			$fieldValue = $operatorObject[0];
			$operator = (isset($operatorObject[1])?$operatorObject[1]:'==');
			$operator = (isset($operatorTranslationMap[$operator])?$operatorTranslationMap[$operator]:$operator);
			
			$vars[$fieldName] = $fieldValue;
			
			if(!isset($validOperators[$operator])) {
				$this->setError('Invalid operator \''.$operator.'\'');
				exit;
			} else if(is_array($fieldValue) && $validOperators[$operator] === false) {
				$this->setError('SQL IN() does not support the operator \''.$operator.'\'');
				exit;
			}
			
			if($fieldValue === null) {
				if(!in_array($operator, ['==','!='])) {
					$this->setError('NULL values does not support the operator \''.$operator.'\'');
					exit;
				}
				
				$sql .= ' '.$this->escape($fieldName).' IS'.($operator == '!='?' NOT':'').' NULL';
				unset($vars[$fieldName]);
			} else if(is_array($fieldValue)) {
				if(count($fieldValue) > 0) {
					$fieldValues = [];
					foreach($fieldValue as $key => $val) {
						$newField = $fieldName.'_'.$key;
						
						$vars[$newField] = $val;
						
						$fieldValues[] = ':'.$newField;
					}
					
					unset($vars[$fieldName]);
					
					$sql .= ' '.$this->escape($fieldName).($operator == '!='?' NOT':'').' IN('.implode(',', $fieldValues).')';
				} else	{
					$this->setError('Error: OR parameter \''.$fieldName.'\' must contain at least 1 value');
				}
			} else {
				$sql .= ' '.$this->escape($fieldName).' '.($operator == '=='?'=':$operator).' :'.$fieldName;
			}
		};
		
		return $sql;
	}
	
	private function sqlOrderBy($orderBy) {
		$sql = '';
		
		if($orderBy === null) return '';
		
		foreach($orderBy as $field => $orderDir) {
			$sql .= (($sql == '')?' ORDER BY ':', ');
			$orderDirFxd = ((strtoupper($orderDir) == 'DESC') ? 'DESC' : 'ASC'); //to prevent typing errors
			$sql .= $this->escape($field).(($orderDir == '')?'':' '.$orderDirFxd);
		}
		
		return $sql;
	}
	
	private function sqlLimit($limit) {
		$sql = '';
		
		if($limit === null) {
			return '';
		} else if(count($limit) == 1) {
			return ' LIMIT '.(int)$limit[0];
		} else if(count($limit) == 2) {
			return ' LIMIT '.(int)$limit[0].','.(int)$limit[1];
		} else {
			return '';
		}
	}
	
	private function escape($field) {
		return '`'.str_replace('`', '``', $field).'`';
	}
	
	public function count($table, $where=null) {
		return (int)$this->selectValue('COUNT(*)', $table, $where);
	}
	
	public function insert($table, $data=null) {
		$fields = [];
		$values = [];
		
		foreach((array)$data as $field => $value) {
			$fields[] = $this->escape($field);
			$values[] = ':'.$field;
		}
		
		$sql = 'INSERT INTO '.$this->escape($table).'('.implode(',', $fields).') VALUES('.implode(',', $values).')';
		
		$vars = (($data === null)?[]:$data);
		$this->exec($sql, $vars);
		return $this->lastInsertID();
	}
	
	public function update($table, $data, $where=null) {
		$sql = 'UPDATE '.$this->escape($table).' SET ';
		
		if(count($data) == 0) return 0;
		
		$values = [];
		$sets = [];
		foreach($data as $field => $value)
		{
			$values[$field.'_value'] = $value;
			$sets[] = $this->escape($field).' = :'.$field.'_value';
		}
		
		$sql .= implode(', ', $sets).$this->sqlWhere($where, $vars);
		
		$vars = array_merge($values, $vars);
		return $this->exec($sql, $vars);
	}
	
	public function delete($table, $where=null) {
		$sql = 'DELETE FROM '.$this->escape($table).$this->sqlWhere($where, $vars);
		
		return $this->exec($sql, $vars);
	}
	
	public function select($table, $where=null, $orderBy=null, $limit=null) {
		$sql = 'SELECT * FROM '.$this->escape($table).$this->sqlWhere($where, $vars).$this->sqlOrderBy($orderBy).$this->sqlLimit($limit);
		
		return $this->query($sql, $vars);
	}
	
	public function selectValues($column, $table, $where=null, $orderBy=null, $limit=null) {
		$sql = 'SELECT '.$this->escape($column).' FROM '.$this->escape($table).$this->sqlWhere($where, $vars).$this->sqlOrderBy($orderBy).$this->sqlLimit($limit);
		
		return $this->queryValues($sql, $vars);
	}
	
	public function selectRow($table, $where=null, $orderBy=null) {
		$sql = 'SELECT * FROM '.$this->escape($table).$this->sqlWhere($where, $vars).$this->sqlOrderBy($orderBy);
		
		return $this->queryRow($sql, $vars);
	}
	
	public function selectValue($column, $table, $where=null, $orderBy=null) {
		$column = ((strtoupper($column) == 'COUNT(*)')?$column:$this->escape($column));
		$sql = 'SELECT '.$column.' FROM '.$this->escape($table).$this->sqlWhere($where, $vars).$this->sqlOrderBy($orderBy);
		
		return $this->queryValue($sql, $vars);
	}
	
	public function selectJoin($table1, $table2, $using, $where=null, $orderBy=null, $limit=null) {
		$sql = 'SELECT * FROM '.$this->escape($table1).' JOIN '.$this->escape($table2).' USING('.$this->escape($using).')'.$this->sqlWhere($where, $vars).$this->sqlOrderBy($orderBy).$this->sqlLimit($limit);
		
		return $this->query($sql, $vars);
	}
	
	public function selectJoinRow($table1, $table2, $using, $where=null, $orderBy=null) {
		$sql = 'SELECT * FROM '.$this->escape($table1).' JOIN '.$this->escape($table2).' USING('.$this->escape($using).')'.$this->sqlWhere($where, $vars).$this->sqlOrderBy($orderBy);
		
		return $this->queryRow($sql, $vars);
	}
	
	public function truncateTable($table) {
		$sql = 'TRUNCATE TABLE '.$this->escape($table);
		
		return $this->exec($sql);
	}
	
	public function min($column, $table, $where=null) {
		$sql = 'SELECT MIN('.$this->escape($column).') FROM '.$this->escape($table).$this->sqlWhere($where, $vars);
		
		return $this->queryValue($sql, $vars);
	}
	
	public function max($column, $table, $where=null) {
		$sql = 'SELECT MAX('.$this->escape($column).') FROM '.$this->escape($table).$this->sqlWhere($where, $vars);
		
		return $this->queryValue($sql, $vars);
	}
	
	public function sum($column, $table, $where=null) {
		$sql = 'SELECT SUM('.$this->escape($column).') FROM '.$this->escape($table).$this->sqlWhere($where, $vars);
		
		$result = $this->queryValue($sql, $vars);
		
		return (($result === null)?0:$result);
	}
	
	public function avg($column, $table, $where=null){
		$sql = 'SELECT AVG('.$this->escape($column).') FROM '.$this->escape($table).$this->sqlWhere($where, $vars);
		
		return $this->queryValue($sql, $vars);
	}
}

?>