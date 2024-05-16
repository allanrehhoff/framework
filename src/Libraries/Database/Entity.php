<?php

/**
 * The base class for any CRUD'able entity.
 */

namespace Database;

/**
 * Represents a CRUD'able entity.
 * @phpstan-consistent-constructor
 */
abstract class Entity {
	/**
	 * All loaded entities will br stored for the remainder of the request
	 * @var array $instanceCache
	 * @since v5.0.0
	 */
	private static array $instanceCache = [];

	/**
	 * @var mixed $key Value of the primary key field
	 */
	private mixed $key = null;

	/**
	 * @var array $data General data for this entity, usually this is simply all database columns for a row
	 */
	protected array $data = [];

	/**
	 * Subclasses must define getPrimaryKey
	 * 'late static binding' will be used to load
	 * entities, and identify primary key + table.
	 * @return string
	 */
	abstract public static function getPrimaryKey(): string;

	/**
	 * Subclasses must define getTableName
	 * 'late static binding' will be used to load
	 * entities, and identify primary key + table.
	 * @return string
	 */
	abstract public static function getTableName(): string;

	/**
	 * Loads a given entity and populates it with the given data.
	 * Use static::from(); to load a new entity by its primary key.
	 *
	 * @param mixed $data Can be either an array of existing data or an entity ID to load.
	 * @param null|array $allowedFields Fields allowed to be set as data.
	 * @return void
	 */
	public function __construct(mixed $data = null, ?array $allowedFields = null) {
		$this->set($data, $allowedFields);
	}

	/**
	 * Print the Entity object only for debugging purposes
	 *
	 * @return string
	 */
	public function __toString(): string {
		$result = get_class($this) . "(" . $this->key . "):\n";

		foreach ($this->data as $key => $value) {
			$result .= " [" . $key . "] " . $value . "\n";
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
	public function __set(string $name, mixed $value) {
		$this->data[$name] = $value;
	}

	/**
	 * Checks if entry exists by key
	 * This is required together with __get for
	 * to support 'array_column' in \Database\Collection::getColumn
	 *
	 * @param string $name Name of the data index to check.
	 * @since 3.3.0
	 * @return bool
	 */
	public function __isset(string $name): bool {
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
	 * @throws \BadMethodCallException If attempting to do an insert and data array is empty.
	 * @return int|string|static if a new entity was just inserted, returns the primary key for that entity, otherwise the current data is returned
	 */
	public function save(): int|string|static {
		if ($this->exists() === true) {
			Connection::getInstance()->upsert($this->getTableName(), $this->data, $this->getKeyFilter());
		} else {
			$this->data[static::getPrimaryKey()] = Connection::getInstance()->insert($this->getTableName(), $this->data);
		}

		$entityType = static::class;
		self::$instanceCache[$entityType][$this->id()] = $this;

		return $this;
	}

	/**
	 * Queries database for a given entity by the value of its primary key.
	 * Loaded antities are cached statically for the remainder of the request.
	 * When saved, the cache will be refreshed with the updated instance.
	 * 
	 * @param int|string $identifier The value of the entity's primary key. 
	 * @return static The loaded entity, empty entity if not exists
	 */
	public static function from(int|string $identifier): static {
		$entityType = static::class;

		if (!isset(self::$instanceCache[$entityType][$identifier])) {
			$keyField = static::getPrimaryKey();
			$data = Connection::getInstance()->fetchRow(static::getTableName(), [$keyField => $identifier]);

			$instance = new static($data);

			if ($instance->exists()) {
				self::$instanceCache[$entityType][$identifier] = $instance;
			}
		}

		return self::$instanceCache[$entityType][$identifier] ?? new static();
	}

	/**
	 * Attempts to find an entity from a given field and value
	 * 
	 * @param string $field The database column/field to match
	 * @param int|string $value The value that $field is to be matched against
	 * @return static
	 */
	public static function find(string $field, int|string $value): static {
		$row = Connection::getInstance()->fetchRow(static::getTableName(), [$field => $value]);
		return new static($row);
	}

	/**
	 * Loads up one or more entities with given data.
	 * It is assumed the data to populate with is already fetched elsewhere.
	 * e.g. by the use of static::search();
	 *
	 * @param mixed $rows an array of ID's or a single ID to load
	 * @param bool $indexByIDs If loading multiple ID's set this to true, to index the resulting array by entity IDs
	 * @return Collection|static The loaded entities or a single if no array was provided
	 */
	public static function load(mixed $rows, bool $indexByIDs = true): Collection|static {
		$class = static::class;

		if (is_iterable($rows)) {
			$objects = [];

			foreach ($rows as $i => $row) {
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
	 * @param null|array $criteria Criteria variables for the search sets
	 * @return Collection|static
	 * @since 3.3.0
	 */
	public static function search(array $searches = [], ?array $criteria = null): Collection|static {
		$rows = Connection::getInstance()->search(static::getTableName(), $searches, $criteria);
		return self::load($rows);
	}

	/**
	 * Helper method to quickly register a new entity
	 * @param null|array|object $data Data to insert
	 * @return static
	 */
	public static function insert(null|array|object $data, null|array $allowedFields = null): static {
		$iEntity = new static($data, $allowedFields);
		return $iEntity->save();
	}

	/**
	 * Permanently delete a given entity row
	 *
	 * @return int Number of rows affected
	 */
	public function delete(): int {
		return Connection::getInstance()->delete($this->getTableName(), $this->getKeyFilter());
	}

	/**
	 * Creates a new instance of any given entity
	 * @return Entity
	 */
	public static function new(): Entity {
		return new static;
	}

	/**
	 * Sets ones or more properties to a given value.
	 *
	 * @param null|array|object $data key => value pairs of values to set
	 * @param null|array $allowedFields keys of fields allowed to be altered
	 * @return static The current entity instance
	 */
	public function set(null|array|object $data = null, ?array $allowedFields = null): static {
		if ($data !== null) {
			// Convert object to array
			// So we can merge it later
			$data = (array) $data;

			// Find empty strings in dataset and convert to null instead.
			// fx. JSON fields doesn't allow empty strings to be stored.
			// This also helps against empty strings telling exists(); to return true
			foreach ($data as $key => $value) {
				if ($value === '') $data[$key] = null;
			}

			if ($allowedFields !== null) {
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
	public function getData(): array {
		return $this->data;
	}

	/**
	 * Get the value corrosponding to a given key
	 *
	 * @param string $key key name of the value to retrieve.
	 * @return mixed
	 */
	public function get(string $key): mixed {
		return $this->data[$key] ?? null;
	}

	/**
	 * Get and shift a value off the data array
	 * 
	 * @param string $key key name of the value to retrieve
	 * @return mixed
	 */
	public function shift(string $key): mixed {
		$data = $this->get($key);

		if (array_key_exists($key, $data) === true) {
			unset($this->data[$key]);
		}

		return $data;
	}

	/**
	 * Escape data for output
	 * 
	 * @param $data The data string to escape
	 * @return string The Escaped string
	 */
	public function escape(string $data): string {
		return htmlspecialchars($data, ENT_QUOTES, "UTF-8");
	}

	/**
	 * Make a given value safe for insertion, could prevent future XSS injections
	 *
	 * @param string $key Key of the data value to retrieve
	 * @return null|string A html friendly string
	 */
	public function safe(string $key): ?string {
		$data = $this->get($key);

		if ($data === null) return null;

		return $this->escape($data);
	}

	/**
	 * Get the current value of primary key index.
	 *
	 * @return int|string the key value
	 */
	public function id(): int|string {
		$identifier = $this->data[static::getPrimaryKey()];
		return is_numeric($identifier) ? (int)$identifier : $this->escape($identifier);
	}

	/**
	 * Gets an array suitable for WHERE clauses in SQL statements
	 *
	 * @return array A filter array
	 */
	public function getKeyFilter(): array {
		return [static::getPrimaryKey() => $this->data[static::getPrimaryKey()]];
	}

	/**
	 * Determine if the loaded entity exists in db
	 *
	 * @return bool
	 */
	public function exists(): bool {
		return $this->data[static::getPrimaryKey()] ?? null !== null;
	}

	/**
	 * Determine if the loaded entity is new
	 *
	 * @return bool
	 */
	public function isNew(): bool {
		return !$this->exists();
	}
}
