<?php
/**
* The base class for any CRUD'able entity.
*/
namespace Database {
	use Exception;

	/**
	* Represents a CRUD'able entity.
	*/
	abstract class Entity {
		private $key;
		protected $data = [];

		abstract protected static function getKeyField() : string;
		abstract protected static function getTableName() : string;

		/**
		* Loads a given entity, instantiates a new if none given.
		*
		* @param mixed $data Can be either an array of existing data or an entity ID to load.
		* @return void
		*/
		public function __construct($data = null, ?array $allowedFields = null) {
			$this->set($data, $allowedFields);
		}

		/**
		* Print the Entity object only for debugging purposes
		*
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
		*
		* @param string $name Name of the property to set to $value
		* @param mixed $value A value to set
		* @return void
		*/
		public function __set(string $name, $value) {
			$this->data[$name] = $value;
		}

		/**
		 * Checks if entry exists by key
		 * This is required together with __get for
		 * to support 'array_column' in \Database\Collection::getColumn
		 *
		 * @since 3.3.0
		 * @return bool
		 */
		public function __isset($name) : bool {
		    return isset($this->data[$name]);
		}

		/**
		* Gets the value for a given property name
		*
		* @param string $name name of the property from whom to retrieve a value
		* @return mixed A property value
		*/
		#[\ReturnTypeWillChange]
		public function __get(string $name) {
			return $this->data[$name];
		}

		/**
		* Saves the entity to a long term storage.
		* Empty strings are converted to null values
		*
		* @throws \Exception
		* @return mixed if a new entity was just inserted, returns the primary key for that entity, otherwise the current data is returned
		*/
		public function save() {
			if ($this->exists() === true) {
				Connection::getInstance()->update($this->getTableName(), $this->data, $this->getKeyFilter());
				return $this->data;
			} else {
				if(empty($this->data)) throw new \Exception("Data variable is empty");
				$this->key = Connection::getInstance()->insert($this->getTableName(), $this->data);
				return $this->key;
			}
		}

		/**
		 * Update or insert row
		 * 
		 * @return Database\Statement
		 */
		public function upsert() : Statement {
			return Connection::getInstance()->upsert($this->getTableName(), $this->data, $this->getKeyFilter());
		}

		/**
		* Permanently delete a given entity row
		*
		* @return int Number of rows affected
		*/
		public function delete() : int {
			return Connection::getInstance()->delete($this->getTableName(), $this->getKeyFilter());
		}

		/**
		* Make a given value safe for insertion, could prevent future XSS injections
		*
		* @param string Key of the data value to retrieve
		* @return string a html friendly string
		*/
		public function safe(string $key) : ?string {
			$data = $this->get($key);

			if($data === null) return null;

			return htmlspecialchars($data, ENT_QUOTES, "UTF-8");
		}

		/**
		* Load one or more ID's into entities
		*
		* @param mixed $ids an array of ID's or an integer to load
		* @param bool $indexByIDs If loading multiple ID's set this to true, to index the resulting array by entity IDs
		* @return mixed The loaded entities
		* @throws \TypeError
		*/
		public static function load($rows, bool $indexByIDs = true) {
			$class = get_called_class();

			if(is_iterable($rows)) {
				$objects = [];

				foreach($rows as $i => $row) {
					$instance = new $class($row);
					$index = $indexByIDs ? $instance->id() : $i;
					$objects[$index] = $instance;
				}

				return new Collection($objects);
			} else if(is_numeric($rows)) {
				return new $class((int) $rows);
			}

			throw new \TypeError($class."::load(); expects either an array or integer. '".gettype($rows)."' was provided.");
		}

		/**
		 * Performs a search of the given criteria
		 *
		 * @param array $searches Sets of expressions to match. e.g. 'filepath LIKE :filepath'
		 * @param ?array $criteria Criteria variables for the search sets
		 * @return array
		 * @since 3.3.0
		 */
		public static function search(array $searches = [], ?array $criteria = null) {
			$rows = Connection::getInstance()->search(static::getTableName(), $searches, $criteria);
			return self::load($rows);
		}

		/**
		* Sets ones or more properties to a given value.
		*
		* @param array $values key => value pairs of values to set
		* @param array $allowedFields keys of fields allowed to be altered
		* @return object The current entity instance
		*/
		public function set($data = null, ?array $allowedFields = null) : Entity {
			if(is_object($data) === true) {
				$data = (array) $data;
			}

			if(is_array($data) === true) {
				// Find empty strings in dataset and convert to null instead.
				// JSON fields doesn't allow empty strings to be stored.
				// This also helps against empty strings telling exists(); to return true
				foreach($data as $key => $value) {
					$data[$key] = is_string($value) && trim($value) === '' ? null : $value;
				}
			}

			// Again empty strings should be null
			if(is_string($data) && trim($data) === '') {
				$data = null;
			}

			if($allowedFields != null) {
				$data = array_intersect_key($data, array_flip($allowedFields));
			}
			
			$key = static::getKeyField();
			
			if($data !== null && gettype($data) !== "array") {
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
		*
		* @return array
		*/
		public function getData() : array {
			return $this->data;
		}

		/**
		* Get the value corrosponding to a given key
		*
		* @param string $key key name of the value to retrieve.
		* @return mixed
		*/
		public function get(string $key) {
			return $this->data[$key] ?? null;
		}

		/**
		 * Get and shift a value off the data array
		 * 
		 * @param string $key key name of the value to retrieve
		 * @return mixed
		 */
		public function shift(string $key) {
			$data = $this->get($key);

			if(array_key_exists($key, $data) === true) {
				unset($this->data[$key]);
			}

			return $data;
		}

		/**
		* Get the current value of key index
		*
		* @return mixed the key value
		*/
		public function getKey() {
			return is_numeric($this->key) ? (int)$this->key : $this->safe(static::getKeyField());
		}

		/**
		* Gets an array suitable for WHERE clauses in SQL statements
		*
		* @return array A filter array
		*/
		public function getKeyFilter() : array {
			return [static::getKeyField() => $this->key];
		}

		/**
		* Wrapper method for getKey();
		*
		* @return mixed A key value
		*/
		public function id() {
			return $this->getKey();
		}

		/**
		* Determine if the loaded entity exists in db
		*
		* @return bool
		*/
		public function exists() : bool {
			return $this->key !== null;
		}

		/**
		* Determine if the loaded entity is new
		*
		* @return bool
		*/
		public function isNew() : bool {
			return $this->getKey() === null;
		}
	}
}