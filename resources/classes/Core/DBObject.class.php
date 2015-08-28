<?php
namespace Core;

	abstract class DBObject {
		private $key;
		protected $data;
		abstract function getKeyField();
		abstract function getTableName();

		function __construct($data = null) {
			if ($data !== null && gettype($data) != 'array') {
				$data = DbConnection::getConnection()->selectRow($this->getTableName(), array($this->getKeyField() => $data));
			}
			if ($data !== null) {
				$this->key = $data[$this->getKeyField()];
				unset($data[$this->getKeyField()]);
			} else {
				$data = array();
			}
			$this->data = $data;
		}
		
		function getKeyFilter() {
			return array($this->getKeyField() => $this->key);
		}
			
		function save() {
			if ($this->key == null) {
				$this->key = DbConnection::getConnection()->insert($this->getTableName(), $this->data);
				//$description = 'insert' . "\n\n" . json_encode($this->data, JSON_PRETTY_PRINT);
				//SystemLog::add($this, $description);
			} else {
				//$description = 'update' . "\n\nBefore:\n" . json_encode(Res::db()->selectRow($this->getTableName(), $this->getKeyFilter()), JSON_PRETTY_PRINT) . "\n\nAfter:\n" . json_encode($this->data, JSON_PRETTY_PRINT);
				//SystemLog::add($this, $description);
				DbConnection::getConnection()->update($this->getTableName(), $this->data, $this->getKeyFilter());		
			}
		}
		
		function delete() {
			//$description = 'delete' . "\n\n" . json_encode(Res::db()->selectRow($this->getTableName(), $this->getKeyFilter()), JSON_PRETTY_PRINT);
			//SystemLog::add($this, 'delete');
			DbConnection::getConnection()->delete($this->getTableName(), $this->getKeyFilter());		
			//DbConnection::getConnection()->update($this->getTableName(), array('deleted' => date("Y-m-d H:i:s", time())), $this->getKeyFilter());		
		}
		
		function set($values, $allowed_fields = null) {
			if ($allowed_fields != null) {
				$values = array_intersect_key($values, array_flip($allowed_fields));
			}
			$this->data = array_merge($this->data, $values);
		}

		function getData() {
			return $this->data;
		}
		
		function get($key) {
			return $this->data[$key];
		}
		function safe($key) {
			return htmlspecialchars($this->data[$key], ENT_QUOTES, 'UTF-8');
		}

		function getKey() {
			return $this->key;
		}
		function id() {
			return $this->key;
		}

		function __toString() {
			$result = get_class($this)."(".$this->key."):\n";
			foreach ($this->data as $key => $value) {
				$result .= " [".$key."] ".$value."\n";
			}
			return $result;
	    }
	    
	    static function fromIds($ids) {
			$class = get_called_class();
	    	$obj = new $class();
	    	$key = $obj->getKeyField();
		    $objects = array();
			foreach ($ids as $id) {
				$objects[] = new $class($id[$key]);
			}
			return $objects;
	    }

		public function __set($name, $value) {
			$this->data[$name] = $value;
		}
		
		public function __get($name) {
			return $this->data[$name];
		}

	}