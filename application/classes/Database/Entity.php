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
		protected $data = [];

		abstract protected function getKeyField() : string;
		abstract protected function getTableName() : string;

		/**
		* Loads a given entity, instantiates a new if none given.
		* @param mixed $data Can be either an array of existing data or an entity ID to load.
		* @return void
		* @author Allan Thue Rehhoff
		*/
		public function __construct($data = null, ?array $allowedFields = null) {
			$this->set($data, $allowedFields);
		}

		/**
		* Print the Entity object only for debugging purposes
		* @author Allan Thue Rehhoff
		* @return string
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
		* @param string $name Name of the property to set to $value
		* @param mixed $value A value to set
		* @return void
		*/
		public function __set(string $name, $value) {
			$this->data[$name] = $value;
		}

		/**
		* Gets the value for a given property name
		* @param string $name name of the property from whom to retrieve a value
		* @return mixed A property value
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
		* @return mixed if a new entity was just inserted, returns the primary key for that entity, otherwise the current data is returned
		*/
		public function save() {
			try {
				if ($this->exists() === true) {
					Connection::getInstance()->update($this->getTableName(), $this->data, $this->getKeyFilter());
					return $this->data;
				} else {
					$this->key = Connection::getInstance()->insert($this->getTableName(), $this->data);
					return $this->key;
				}
			} catch(Exception $e) {
				throw $e;
			}
		}

		/**
		 * Update or insert row
		 */
		public function upsert() {
			return Connection::getInstance()->upsert($this->getTableName(), $this->data, $this->getKeyFilter());
		}
		
		/**
		* Permanently delete a given entity row
		* @author Allan Thue Rehhoff
		* @return int Number of rows affected
		*/
		public function delete() : int {
			return Connection::getInstance()->delete($this->getTableName(), $this->getKeyFilter());		
		}

		/**
		* Make a given value safe for insertion, could prevent future XSS injections
		* @author Allan Thue Rehhoff
		* @param string Key of the data value to retrieve
		* @return string a html friendly string
		*/
		public function safe(string $key) : string {
			return htmlspecialchars($this->data[$key], ENT_QUOTES, "UTF-8");
		}

		/**
		* Load one or more ID's into entities
		* @todo Take this out of a static context
		* @param mixed $ids an array of ID's or an integer to load
		* @return mixed The loaded entities
		* @throws Exception
		* @author Allan Thue Rehhoff
		*/
		public static function load($ids) {
			$class = get_called_class();

			if(is_array($ids)) {
				$objects = [];
				foreach($ids as $id) $objects[] = new $class($id);
				return $objects;
			} else if(is_numeric($ids)) {
				return new $class((int) $ids);
			}

			throw new Exception($class."::load(); expects either an array or integer. '".gettype($ids)."' was provided.");
		}

		/**
		* Sets ones or more properties to a given value.
		* @param array $values key => value pairs of values to set
		* @param array $allowedFields keys of fields allowed to be altered
		* @return object The current entity instance
		* @author Allan Thue Rehhoff
		*/
		public function set($data = null, ?array $allowedFields = null) : Entity {
			if(is_object($data) === true) {
				$data = (array) $data;
			}

			if ($allowedFields != null) {
				$data = array_intersect_key($data, array_flip($allowedFields));
			}
			
			$key = $this->getKeyField();
			
			if ($data !== null && gettype($data) !== "array") {
				$data = [$key => $data];
			}

			if(isset($data[$key])) {
				$exists = Connection::getInstance()->fetchRow($this->getTableName(), [$key => $data[$key]]);

				if(!empty($exists)) {
					$this->key = $exists->$key;
					$this->data = (array)$exists;
					unset($data[$key]);
				}
			}

			if($data === null) {
				$data = [];
			}

			$this->data = array_merge($this->data, $data);
			return $this;
		}

		/**
		* Gets the current entity data
		* @return array
		* @author Allan Thue Rehhoff
		*/
		public function getData() : array {
			return $this->data;
		}

		/**
		* Get the value corrosponding to a given key
		* @param string $key key name of the value to retrieve.
		* @return mixed
		* @author Allan Thue Rehhoff
		*/
		public function get(string $key) {
			return isset($this->data[$key]) ? $this->data[$key] : false;
		}

		/**
		* Get the current value of key index
		* @return mixed the key value
		* @author Allan Thue Rehhoff
		*/
		public function getKey() {
			return $this->key;
		}

		/**
		* Gets an array suitable for WHERE clauses in SQL statements
		* @return array A filter array
		* @author Allan Thue Rehhoff
		*/
		public function getKeyFilter() : array {
			return [$this->getKeyField() => $this->key];
		}

		/**
		* Wrapper method for getKey();
		* @return mixed A key value
		* @author Allan Thue Rehhoff
		*/
		public function id() {
			return $this->key;
		}

		/**
		* Determine if the loaded entity exists in db
		* @return bool
		* @author Allan Thue Rehhoff
		*/
		public function exists() : bool {
			return $this->key !== null;
		}

		/**
		* Determine if the loaded entity is new
		* @return bool
		* @author Allan Thue Rehhoff
		*/
		public function isNew() : bool {
			return $this->getKey() === null;
		}
	}
}