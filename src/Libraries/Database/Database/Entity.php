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
	 * General data for this entity, usually this is simply all database columns for a row
	 * @var array $data
	 */
	protected array $data = [];

	/**
	 * New data to be inserted/updated once entity is saved
	 *
	 * @var array
	 */
	protected array $new = [];

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
	 * @param null|array|object $data Can be either an array of existing data or an entity ID to load.
	 * @param null|array $allowedFields Fields allowed to be set as data.
	 * @return void
	 */
	public function __construct(null|array|object $data = null, ?array $allowedFields = null) {
		$this->new = $this->process($data, $allowedFields);
	}

	/**
	 * Print the Entity object only for debugging purposes
	 *
	 * @return string
	 */
	public function __toString(): string {
		$result = static::class . "(" . $this->id() . "):\n";

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
		$this->new[$name] = $value;
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
		return $this->get($name);
	}

	/**
	 * Saves the entity to a long term storage.
	 * Empty strings are converted to null values
	 *
	 * @throws \BadMethodCallException If attempting to do an insert and data array is empty.
	 * @return int|string|static if a new entity was just inserted, returns the primary key for that entity, otherwise the current data is returned
	 */
	public function save(): int|string|static {
		// Check if the entity already exists (i.e., it is an update operation)
		if ($this->exists() === true) {
			Connection::getInstance()->upsert($this->getTableName(), $this->new, $this->getKeyFilter());
		} else {
			$primaryKey = static::getPrimaryKey();
			$insertedId = Connection::getInstance()->insert($this->getTableName(), $this->new);

			// If the primary key is not provided, insert and get the auto-incremented ID
			if (!isset($this->new[$primaryKey])) {
				$this->new[$primaryKey] = $insertedId; // Will be merged to data array
			}
		}

		$this->data = array_merge($this->data, $this->new);
		$this->new = [];

		$entityType = static::class;
		self::$instanceCache[$entityType][$this->id()] = $this;

		return $this;
	}

	/**
	 * Queries database for a given entity by the value of its primary key.
	 * Loaded antities are cached statically for the remainder of the request.
	 * When saved, the cache will be refreshed with the updated instance.
	 * 
	 * @param null|int|string $identifier The value of the entity's primary key. 
	 * @return static The loaded entity, empty entity if not exists
	 */
	public static function from(null|int|string $identifier): static {
		$entityType = static::class;

		if($identifier === null) {
			return new static();
		}

		if (!isset(self::$instanceCache[$entityType][$identifier])) {
			$keyField = static::getPrimaryKey();
			$data = Connection::getInstance()->fetchRow(static::getTableName(), [$keyField => $identifier]);

			$instance = static::with($data);

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
		return static::with($row);
	}

	/**
	 * Created a new instance of entity type with existing data
	 * @param iterable|\stdClass $row Row from database
	 * @return Collection<Entity>|Entity Collection of entities if passed an array, otherwise the provided object as an entity
	 */
	public static function with(iterable|\stdClass $row): Collection|Entity {
		if (is_iterable($row)) {
			$entities = [];
			foreach ($row as $data) $entities[] = static::with($data);
			return new Collection($entities);
		} else {
			$entity = new static();
			$entity->data = (array)$row;

			return $entity;
		}
	}

	/**
	 * Performs a search of the given criteria
	 *
	 * @param array $searches Sets of expressions to match. e.g. 'filepath LIKE :filepath'
	 * @param null|array $criteria Criteria variables for the search sets
	 * @param string $clause The clause to put between each criteria, default AND
	 * @return Collection|static
	 * @since 3.3.0
	 */
	public static function search(array $searches = [], null|array $criteria = null, string $clause = "AND"): Collection|static {
		$rows = Connection::getInstance()->search(static::getTableName(), $searches, $criteria, $clause);
		return self::with($rows);
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
		$result = Connection::getInstance()->delete($this->getTableName(), $this->getKeyFilter());
		$this->data = [];
		return $result;
	}

	/**
	 * Creates a new instance of any given entity
	 * @return Entity
	 */
	public static function new(): Entity {
		return new static;
	}

	/**
	 * Sets the data for the current object.
	 * If $data is an object, it is converted to an array.
	 * Empty strings in the dataset are converted to null, 
	 * and only the allowed fields are retained, if specified.
	 * 
	 * @param null|array|object $data          The data to be set, either an array, object, or null.
	 * @param null|array        $allowedFields The fields that are allowed to be set (optional).
	 * 
	 * @return array
	 */
	protected function process(null|array|object $data, null|array $allowedFields = null): array {
		// Return empty array if $data is null
		if ($data === null) {
			return $this->new;
		}

		// Get primary key field as string
		$primaryKey = static::getPrimaryKey();

		// Convert object to array
		$data = (array) $data;

		// Ensure the primary key is preserved during update operations.
		// If it's missing from both $data and $this->new, pull it from existing $this->data.
		// This prevents accidentally treating an update as a new insert.
		$data[$primaryKey] ??= $this->new[$primaryKey] ?? $this->data[$primaryKey] ?? null;

		//if(!isset($this->new[$primaryKey]) && !isset($data[$primaryKey])) {
		//	$data[$primaryKey] = $this->data[$primaryKey] ?? null;
		//}

		// Find empty strings in dataset and convert to null instead
		foreach ($data as $key => $value) {
			if ($value === '') {
				$data[$key] = null;
			}
		}

		// Filter out fields that are not allowed
		if ($allowedFields !== null) {
			$data = array_intersect_key($data, array_flip($allowedFields));
		}

		// Merge with existing data
		return array_merge($this->new, $data);
	}

	/**
	 * Sets ones or more properties to a given value.
	 *
	 * @param null|array|object $data key => value pairs of values to set
	 * @param null|array $allowedFields keys of fields allowed to be altered
	 * @return static The current entity instance
	 */
	public function set(null|array|object $data = null, null|array $allowedFields = null): static {
		if ($data !== null) {
			$data = $this->process($data, $allowedFields);
		}

		$this->new = $data ?? [];

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
		return $this->new[$key] ?? $this->data[$key] ?? null;
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
	public function safe(string $key): null|string {
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
