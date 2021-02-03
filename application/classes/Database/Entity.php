<?php
/**
* The base class for a CRUD'able entity.
* @author Allan Rehhoff
*/
namespace Database {
	use Exception;

	/**
	* Represents a CRUD'able entity.
	*/
	abstract class Entity {
		private $key;
		protected $data;

		abstract protected function getKeyField() : string;
		abstract protected function getTableName() : string;

		/**
		* Loads a given entity, instantiates a new if none given.
		* @param (mixed) $data Can be either an array of existing data or an entity ID to load.
		* @return void
		* @author Allan Thue Rehhoff
		*/
		public function __construct($data = null) {
			if ($data !== null && gettype($data) != "array") {
				$data = (array) Connection::getInstance()->fetchRow($this->getTableName(), [$this->getKeyField() => $data]);
			}

			if ($data !== null) {
				$key = $this->getKeyField();

				if(isset($data[$key])) {
					$this->key = $data[$key];
					unset($data[$key]);
				} else {
					$this->key = null;
				}
			} else {
				$data = [];
			}

			$this->data = $data;
		}

		/**
		* Print the Entity object only for debugging purposes
		* @author Allan Thue Rehhoff
		* @return (string)
		*/
		function __toString() {
			$result = get_class($this)."(".$this->key."):\n";
			foreach ($this->data as $key => $value) {
				$result .= " [".$key."] ".$value."\n";
			}
			return $result;
		}

		/**
		* Sets a property to a given value
		* @param (string) $name Name of the property to set to $value
		* @param (mixed) $value A value to set
		* @return void
		*/
		public function __set(string $name, $value) {
			$this->data[$name] = $value;
		}

		/**
		* Gets the value for a given property name
		* @param (string) $name name of the property from whom to retrieve a value
		* @return (mixed) A property value
		* @throws Exception
		* @author Allan Thue Rehhoff
		*/
		public function __get(string $name) {
			if ($name == $this->getKeyField()) {
				throw new Exception("Cannot return key field from getter, try calling ".get_called_class()."::id(); in object context instead.");
			}

			return $this->data[$name];
		}

		/**
		* Saves the entity to a long term storage.
		* @author Allan Thue Rehhoff
		* @return (mixed) if a new entity was just inserted, returns the primary key for that entity, otherwise the current data is returned
		*/
		public function save() {
			try {
				if ($this->key == null) {
					$this->key = Connection::getInstance()->insert($this->getTableName(), $this->data);
					return $this->key;
				} else {
					Connection::getInstance()->update($this->getTableName(), $this->data, $this->getKeyFilter());
					return $this->data;
				}
			} catch(Exception $e) {
				throw $e;
			}
		}
		
		/**
		* Permanently delete a given entity row
		* @author Allan Thue Rehhoff
		* @return (int) Number of rows affected
		*/
		public function delete() : int {
			return Connection::getInstance()->delete($this->getTableName(), $this->getKeyFilter());		
		}

		/**
		* Make a given value safe for insertion, could prevent future XSS injections
		* @author Allan Thue Rehhoff
		* @param (string) Key of the data value to retrieve
		* @return (string) a html friendly string
		*/
		public function safe(string $key) : string {
			return htmlspecialchars($this->data[$key], ENT_QUOTES, "UTF-8");
		}

		/**
		* Load one or more ID's into entities
		* @todo Take this out of a static context
		* @param (mixed) $ids an array of ID's or an integer to load
		* @return (mixed) The loaded entities
		* @throws Exception
		* @author Allan Thue Rehhoff
		*/
		public static function load($ids) {
			$class = get_called_class();
			$obj = new $class();
			$key = $obj->getKeyField();

			if(is_array($ids)) {
				$objects = [];
				foreach($ids as $id) $objects[] = new $obj($id);
				return $objects;
			} else if(is_numeric($ids)) {
				return new $obj((int) $ids);
			}

			throw new Exception($obj."::load(); expects either an array or integer. '".gettype($ids)."' was provided.");
		}

		/**
		* Sets ones or more properties to a given value.
		* @param (array) $values key => value pairs of values to set
		* @param (array) $allowed_fields keys of fields allowed to be altered
		* @return (object) The current entity instance
		* @author Allan Thue Rehhoff
		*/
		public function set(array $values, array $allowed_fields = null) : Entity {
			if ($allowed_fields != null) {
				$values = array_intersect_key($values, array_flip($allowed_fields));
			}

			$this->data = array_merge($this->data, $values);
			return $this;
		}

		/**
		* Gets the current entity data
		* @return (array)
		* @author Allan Thue Rehhoff
		*/
		public function getData() : array {
			return $this->data;
		}

		/**
		* Get the value corrosponding to a given key
		* @param (string) $key key name of the value to retrieve.
		* @return (mixed)
		* @author Allan Thue Rehhoff
		*/
		public function get(string $key) {
			return isset($this->data[$key]) ? $this->data[$key] : false;
		}

		/**
		* Get the current value of key index
		* @return (mixed) the key value
		* @author Allan Thue Rehhoff
		*/
		public function getKey() {
			return $this->key;
		}

		/**
		* Gets an array suitable for WHERE clauses in SQL statements
		* @return (array) A filter array
		* @author Allan Thue Rehhoff
		*/
		public function getKeyFilter() : array {
			return [$this->getKeyField() => $this->key];
		}

		/**
		* Wrapper method for getKey();
		* @return (mixed) A key value
		* @author Allan Thue Rehhoff
		*/
		public function id() {
			return $this->key;
		}
	}
}