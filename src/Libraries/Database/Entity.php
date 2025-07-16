<?php

/**
 * The base class for any CRUD'able entity.
 */

namespace Database;

/**
 * Represents a CRUD'able entity.
 * @phpstan-consistent-constructor
 */
abstract class Entity implements \JsonSerializable {
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
		$this->new = $this->merge($data, $allowedFields);
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
	protected function merge(null|array|object $data, null|array $allowedFields = null): array {
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

			// Entities can opt-in to use a randomizer for their primary key
			// Usually happens when using UUIDs and the entity is using a
			// trait. If a primary key trait is not set, we will fallback 
			// to the default auto-incrementing behavior.
			if ($this->generatesPrimaryKey() === true) {
				// If the primary key is not provided, generate a new one
				// Allows entities using traits to set their own primary key value
				// using the getPrimaryKeyValue() method if not set by the user
				/** @disregard P1013 Function defined in trait */
				$this->new[$primaryKey] ??= $this->generatePrimaryKey();
				Connection::getInstance()->insert($this->getTableName(), $this->new, $primaryKey);
			} else {
				// If the primary key is not provided, insert and get the auto-incremented ID
				// New primary key will be merged into the data array later
				$insertedId = Connection::getInstance()->insert($this->getTableName(), $this->new);
				$this->new[$primaryKey] ??= $insertedId;
			}
		}

		$this->data = array_merge($this->data, $this->new);
		$this->new = [];

		// Caches the current entity instance in
		// the static cache for quick retrieval.
		// Mitigates multiple database queries
		// for the same entity instance.
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

		if ($identifier === null) {
			return new static();
		}

		if (!isset(self::$instanceCache[$entityType][$identifier])) {
			$keyField = static::getPrimaryKey();
			$data = Connection::getInstance()->fetchRow(static::getTableName(), [$keyField => $identifier]);

			$instance = static::hydrate($data);

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
		return static::hydrate($row ?? new \stdClass);
	}

	/**
	 * Backwards compatibility for the new hydrate method.
	 * @param iterable|\stdClass $row Row from database
	 * @since 6.0.0
	 * @deprecated 6.0.0 use hydrate() instead
	 * @return Collection<Entity>|Entity Collection of entities if passed an array, otherwise the provided object as an entity
	 */
	#[\Deprecated("6.0.0", "Use hydrate() instead")]
	public static function with(iterable|\stdClass $row): Collection|Entity {
		return static::hydrate($row);
	}

	/**
	 * Created a new instance of entity type with existing data
	 * @param null|iterable|\stdClass $row Row from database
	 * @return Collection<Entity>|Entity Collection of entities if passed an array, otherwise the provided object as an entity
	 */
	public static function hydrate(null|iterable|\stdClass $row): Collection|Entity {
		if ($row === null) {
			return new static();
		}

		if (is_iterable($row)) {
			$entities = [];
			foreach ($row as $data) $entities[] = static::hydrate($data);
			return new Collection($entities);
		} else {
			return static::new()->setData($row);
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
		if (empty($searches)) {
			foreach ($criteria ?? [] as $field => $value) {
				$searches[] = $field . " = :" . $field;
			}
		}

		$rows = Connection::getInstance()->search(static::getTableName(), $searches, $criteria, $clause);
		return self::hydrate($rows);
	}

	/**
	 * Helper method to quickly register a new entity
	 * @param null|array|object $data Data to insert
	 * @param null|array $allowedFields Name of the columns that are allowed to be set/updated
	 * @return static
	 */
	public static function insert(null|array|object $data, null|array $allowedFields = null): static {
		return static::new()->set($data, $allowedFields)->save();
	}

	/**
	 * Permanently delete a given entity row
	 *
	 * @return int Number of rows affected
	 */
	public function delete(): int {
		if (!$this->exists()) return 0; // Prevent deletion of non-existing entity, undefined index error
		$result = Connection::getInstance()->delete($this->getTableName(), $this->getKeyFilter());
		$this->data = [];
		return $result;
	}

	/**
	 * Creates a new instance of any given entity
	 * @param mixed ...$arguments Arguments to pass to the constructor
	 * @since 6.0.0
	 * @return static
	 */
	public static function new(mixed ...$arguments): static {
		return new static(...$arguments);
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
			$data = $this->merge($data, $allowedFields);
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
	 * Sets the current entity data
	 * 
	 * ⚠️ DANGER ZONE ⚠️
	 * This will overwrite any existing data when saved
	 * and will not check for allowed fields.
	 * This method is not recommended for use outside of the library.
	 * and should only be used when you know what you're doing.
	 *
	 * @param array|object $data The data to set
	 * @return static
	 * @internal This method is not recommended for use outside of the library.
	 * @since 6.0.0
	 */
	public function setData(array|object $data): static {
		$this->data = (array)$data;
		return $this;
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

		if (array_key_exists($key, $this->data) === true) {
			unset($this->data[$key]);
		}

		return $data;
	}

	/**
	 * Escape data for output
	 * 
	 * @param string $data The data string to escape
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

	/**
	 * Determine if the entity has been modified
	 *
	 * @return bool
	 */
	public function isModified(): bool {
		return !empty($this->new);
	}

	/**
	 * Check if the entity generates its own primary key
	 * Happens when entity uses a trait that implements
	 * the generatePrimaryKey method.
	 * @return bool
	 */
	public function generatesPrimaryKey(): bool {
		return method_exists($this, 'generatePrimaryKey');
	}

	/**
	 * Check if the primary key is set in either the data or new array
	 * @return bool
	 * @since 6.0.0
	 */
	public function hasPrimaryKeyValue(): bool {
		$key = static::getPrimaryKey();
		return isset($this->data[$key]) || isset($this->new[$key]);
	}

	/**
	 * Support serializing this entity to json object
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->items;
	}
}
