<?php

namespace Database;

/**
 * Handles caching of entity instances
 * @since 9.0.0
 */
class EntityCache {
	/**
	 * Storage for cached entities
	 * @var array<string, array<string|int, Entity>>
	 */
	private static array $cache = [];

	/**
	 * Check if an entity exists in the cache
	 * 
	 * @param string $class The entity class name
	 * @param string|int $id The entity ID
	 * @return bool True if the entity exists in cache
	 */
	public static function contains(string $entityType, string|int $id): bool {
		return isset(self::$cache[$entityType]) && isset(self::$cache[$entityType][$id]);
	}

	/**
	 * Retrieve an entity from the cache
	 * 
	 * @param string $class The entity class name
	 * @param string|int $id The entity ID
	 * @return Entity|null The cached entity or null if not found
	 */
	public static function retrieve(string $entityType, string|int $id): null|Entity {
		return self::$cache[$entityType][$id] ?? null;
	}

	/**
	 * Remove an entity from the cache
	 * 
	 * @param string $class The entity class name
	 * @param string|int $id The entity ID
	 * @return void
	 */
	public static function remove(string $entityType, string|int $id): void {
		unset(self::$cache[$entityType][$id]);
	}

	/**
	 * Store an entity in the cache
	 * 
	 * @param Entity $entity The entity to store
	 * @param string|int $id The entity ID
	 * @return void
	 */
	public static function store(Entity $iEntity): void {
		$entityType = $iEntity::class;
		$identifier = $iEntity->id();

		self::$cache[$entityType] ??= [];
		self::$cache[$entityType][$identifier] = $iEntity;
	}

	/**
	 * Clear all cached entities
	 * 
	 * @return void
	 */
	public static function clear(): void {
		self::$cache = [];
	}
}
