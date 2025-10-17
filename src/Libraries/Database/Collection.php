<?php

namespace Database;

/**
 * Class Collection
 *
 * @package Database
 */
class Collection implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable {

	/**
	 * @var array Collection of items that's iterable
	 */
	private array $items = [];

	/**
	 * Collection constructor.
	 *
	 * @param array $items Array of items to store as a collection.
	 */
	public function __construct(array $items = []) {
		$this->items = $items;
	}

	/**
	 * Get the first element of the collection.
	 * Returns null if the collection is empty.
	 *
	 * @return mixed|null
	 */
	public function getFirst(): mixed {
		if ($this->isEmpty() === true) return null;

		$key = array_keys($this->items)[0];
		return $this->items[$key];
	}

	/**
	 * Get the last element of the collection.
	 * Returns null if the collection is empty.
	 *
	 * @return mixed|null
	 */
	public function getLast(): mixed {
		if ($this->isEmpty() === true) return null;

		$keys = array_keys($this->items);
		$key = end($keys);
		return $this->items[$key];
	}

	/**
	 * Find and return the first element in the collection where the callback returns true
	 *
	 * @param callable(mixed, mixed): bool $callback Function that receives (mixed $value, mixed $key)
	 *                                              and returns true when the desired item is found
	 * @param mixed $default Value to return if no matching element is found
	 * @return mixed The first matching element or the default value
	 */
	public function getOne(callable $callback, mixed $default = null): mixed {
		foreach ($this->items as $key => $value) {
			if ($callback($value, $key) === true) {
				return $value;
			}
		}

		return $default;
	}

	/**
	 * Count the number of elements in this collection.
	 *
	 * @return int Number of elements
	 */
	public function count(): int {
		return count($this->items);
	}

	/**
	 * Get the values of a given key as a collection.
	 *
	 * @param mixed $key Array/object key to fetch values from.
	 * @return \Database\Collection
	 */
	public function getColumn(mixed $key): Collection {
		return new self(array_column($this->items, $key));
	}

	/**
	 * Check whether the collection is empty or not.
	 *
	 * @return bool
	 */
	public function isEmpty(): bool {
		return $this->count() === 0;
	}

	/**
	 * Rewind the collection array back to the start.
	 *
	 * @return void
	 */
	public function rewind(): void {
		reset($this->items);
	}

	/**
	 * Get the object at the current position.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function current(): mixed {
		return current($this->items);
	}

	/**
	 * Get the current position.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function key(): mixed {
		return key($this->items);
	}

	/**
	 * Advance the internal cursor of an array.
	 *
	 * @return void
	 */
	public function next(): void {
		next($this->items);
	}

	/**
	 * Check whether the collection contains more entries.
	 *
	 * @return bool
	 */
	public function valid(): bool {
		return key($this->items) !== null;
	}

	/**
	 * Determine if an item exists at an offset.
	 *
	 * @param mixed $key The offset to check at.
	 * @return bool
	 */
	public function offsetExists(mixed $key): bool {
		return isset($this->items[$key]);
	}

	/**
	 * Get an item at a given offset.
	 *
	 * @param mixed $key The key to get value from
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet(mixed $key): mixed {
		return $this->items[$key];
	}

	/**
	 * Set the item at a given offset.
	 *
	 * @param mixed $key The key to set value at
	 * @param mixed $value The value to set for key
	 * @return void
	 */
	public function offsetSet(mixed $key, mixed $value): void {
		if (is_null($key)) {
			$this->items[] = $value;
		} else {
			$this->items[$key] = $value;
		}
	}

	/**
	 * Unset the item at a given offset.
	 *
	 * @param mixed $key Remove value at this key.
	 * @return void
	 */
	public function offsetUnset(mixed $key): void {
		unset($this->items[$key]);
	}

	/**
	 * Get all of the items in the collection.
	 *
	 * @return array
	 */
	public function all(): array {
		return $this->items;
	}

	/**
	 * Support serializing this collection to json object
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return $this->items;
	}

	/**
	 * Filter the collection using the provided callback.
	 *
	 * Iterates over each element in the collection and retains elements for which
	 * the callback returns a truthy value.
	 *
	 * A new Collection instance containing the filtered items is returned; the
	 * original collection is not modified.
	 *
	 * @param callable(mixed, mixed): bool $callback Callable that receives (mixed $item, mixed $key)
	 *       						and returns true to include the item in the resulting collection.
	 * @return Collection The new Collection containing only the items that passed the callback.
	 */
	public function filter(callable $callback): Collection {
		return new self(array_filter($this->items, $callback));
	}

	/**
	 * Apply a callback to each item in the collection and return a new collection with the results
	 *
	 * @param callable(mixed): mixed $callback Function to apply to each item
	 * @return Collection New collection with mapped items
	 */
	public function map(callable $callback): Collection {
		return new self(array_map($callback, $this->items));
	}

	/**
	 * Reduce the collection to a single value using a callback
	 *
	 * @param callable(mixed, mixed): mixed $callback Function that receives (mixed $carry, mixed $item)
	 * @param mixed $initial Initial value for the reduction
	 * @return mixed The final reduced value
	 */
	public function reduce(callable $callback, mixed $initial = null): mixed {
		return array_reduce($this->items, $callback, $initial);
	}

	/**
	 * Extract a slice of the collection
	 *
	 * @param int $offset Starting position
	 * @param int|null $length Length of the slice, or null for all remaining elements
	 * @return Collection New collection containing the slice
	 */
	public function slice(int $offset, ?int $length = null): Collection {
		return new self(array_slice($this->items, $offset, $length, true));
	}

	/**
	 * Merge another collection into this one
	 *
	 * @param Collection $other Collection to merge with
	 * @return Collection New collection containing all items from both collections
	 */
	public function merge(Collection $other): Collection {
		return new self(array_merge($this->items, $other->all()));
	}

	/**
	 * Remove duplicate values from the collection
	 *
	 * @return Collection New collection with unique items
	 */
	public function unique(): Collection {
		return new self(array_unique($this->items, SORT_REGULAR));
	}

	/**
	 * Remove one or more items from the collection by key
	 *
	 * @param array $key Single key or array of keys to remove
	 * @return self
	 */
	public function forget(array $keys): self {
		foreach ($keys as $k) {
			unset($this->items[$k]);
		}

		return $this;
	}
}
