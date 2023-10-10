<?php
/**
* The base class for any CRUD'able entity.
*/
namespace Database {
	/**
	 * Represents a CRUD'able entity.
	 * @phpstan-consistent-constructor
	 */
	abstract class Entity {
		/**
		 * @var mixed $key Value of the primary key field
		 */
		private mixed $key = null;

		/**
		 * @var array $data General data for this entity, usually this is simply all  database columns for a row
		 */
		protected array $data = [];

		/**
		 * Subclasses must define getKeyField
		 * 'late static binding' will be used to load
		 * entities, and identify primary key + table.
		 * @return string
		 */
		abstract protected static function getKeyField() : string;

		/**
		 * Subclasses must define getTableName
		 * 'late static binding' will be used to load
		 * entities, and identify primary key + table.
		 * @return string
		 */
		abstract protected static function getTableName() : string;

		/**
		* Loads a given entity, usually by an ID. Instantiates a new if none given.
		*
		* @param mixed $data Can be either an array of existing data or an entity ID to load.
		* @param ?array $allowedFields Fields allowed to be set as data
		* @return void
		*/
		public function __construct(mixed $data = null, ?array $allowedFields = null) {
			if(gettype($data) === "string" || gettype($data) === "integer") {
				$keyField = static::getKeyField();
				$exists = Connection::getInstance()->fetchRow($this->getTableName(), [$keyField => $data]);
				$data = (array)$exists;
			}

			$this->set($data, $allowedFields);
		}

		/**
		* Print the Entity object only for debugging purposes
		*
		* @return string
		*/
		public function __toString() : string {
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
		* @throws \Throwable
		* @return mixed if a new entity was just inserted, returns the primary key for that entity, otherwise the current data is returned
		*/
		#[\ReturnTypeWillChange]
		public function save() : mixed {
			if($this->exists() === true) {
				Connection::getInstance()->update($this->getTableName(), $this->data, $this->getKeyFilter());
				return $this->data;
			} else {
				if(empty($this->data)) throw new \BadMethodCallException("Data variable is empty");
				$this->key = Connection::getInstance()->insert($this->getTableName(), $this->data);
				return $this->key;
			}
		}

		/**
		 * Update or insert row
		 * 
		 * @return Statement
		 */
		public function upsert() : Statement {
			return Connection::getInstance()->upsert($this->getTableName(), $this->data);
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
		* @param string $key Key of the data value to retrieve
		* @return ?string A html friendly string
		*/
		public function safe(string $key) : ?string {
			$data = $this->get($key);

			if($data === null) return null;

			return htmlspecialchars($data, ENT_QUOTES, "UTF-8");
		}

		/**
		* Load one or more ID's into entities
		*
		* @param mixed $rows an array of ID's or a single ID to load
		* @param bool $indexByIDs If loading multiple ID's set this to true, to index the resulting array by entity IDs
		* @return Collection|Entity The loaded entities or a single if no array was provided
		* @throws \TypeError
		*/
		public static function load(mixed $rows, bool $indexByIDs = true) : Collection|Entity {
			$class = static::class;

			if(is_iterable($rows)) {
				$objects = [];

				foreach($rows as $i => $row) {
					$instance = new $class($row);
					$index = $indexByIDs ? $instance->id() : $i;
					$objects[$index] = $instance;
				}

				return new Collection($objects);
			} else {
				return new $class($rows);
			}
		}

		/**
		 * Performs a search of the given criteria
		 *
		 * @param array $searches Sets of expressions to match. e.g. 'filepath LIKE :filepath'
		 * @param ?array $criteria Criteria variables for the search sets
		 * @return Collection|Entity
		 * @since 3.3.0
		 */
		public static function search(array $searches = [], ?array $criteria = null) : Collection|Entity {
			$rows = Connection::getInstance()->search(static::getTableName(), $searches, $criteria);
			return self::load($rows);
		}

		/**
		 * Loads an entity from a given field and value
		 * @param string $field The database column/field to match
		 * @param string $value The value that $field is to be matched against
		 * @return Entity
		 */
		public static function from(string $field, mixed $value) : Entity {
			$row = Connection::getInstance()->fetchRow(static::getTableName(), [$field => $value]);
			return new static($row);
		}

		/**
		 * Creates a new instance of any given entity
		 * @return Entity
		 */
		public static function new() : Entity {
			return new static;
		}

		/**
		* Sets ones or more properties to a given value.
		*
		* @param null|array|object $data key => value pairs of values to set
		* @param ?array $allowedFields keys of fields allowed to be altered
		* @return Entity The current entity instance
		*/
		public function set(null|array|object $data = null, ?array $allowedFields = null) : Entity {
			if($data !== null) {
				// Convert object to array
				// So we can merge it later
				$data = (array) $data;

				// Find empty strings in dataset and convert to null instead.
				// JSON fields doesn't allow empty strings to be stored.
				// This also helps against empty strings telling exists(); to return true
				foreach($data as $key => $value) {
					$data[$key] = $value === '' ? null : $value;
				}

				$keyField = static::getKeyField();

				if(isset($data[$keyField])) {
					$this->key = $data[$keyField];
					unset($data[$keyField]);
				}

				if($allowedFields != null) {
					$data = array_intersect_key($data, array_flip($allowedFields));
				}

				$data = array_merge($this->data, $data);
			}

			$this->data = $data ?? [];

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
			return is_numeric($this->key) ? (int)$this->key : htmlspecialchars($this->key, ENT_QUOTES, "UTF-8");;
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
		public function id() : mixed {
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