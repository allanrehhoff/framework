<?php
	namespace Database {
		class Collection implements \ArrayAccess, \Iterator, \Countable {
			/**
			 * @var array Collection of objects that's iterable
			 */
			private $items = [];

			/**
			 * Constructor
			 * 
			 * @param array $objects Array of objects to store as collection
			 */
			public function __construct(array $objects) {
				$this->items = $objects;
			}

			/**
			 * Get first element of collection
			 * Returns null if collection is empty
			 * 
			 * @return mixed
			 */
			#[\ReturnTypeWillChange]
			public function getFirst() {
				if($this->isEmpty() === true) return null;

				$key = array_keys($this->items)[0];
				return $this->items[$key];
			}

			/**
			 * Get first element of collection
			 * Returns null if collection is empty
			 * 
			 * @return mixed
			 */
			#[\ReturnTypeWillChange]
			public function getLast() {
				if($this->isEmpty() === true) return null;

				$keys = array_keys($this->items);
				$key = end($keys);
				return $this->items[$key];
			}

			/**
			 * Coutn the number of elements in this collection
			 * 
			 * @return int Number of elements
			 */
			public function count() : int {
				return count($this->items);
			}

			/**
			 * Get the values of a given key as a collection
			 * 
			 * @param mixed $key array/object key to fetch values from
			 * @return \Database\Collection
			 */
			public function getColumn($key) : Collection {
				return new self(array_column($this->items, $key));
			}

			/**
			 * Tell whether the collection is empty or not
			 * 
			 * @return bool
			 */
			public function isEmpty() : bool {
				return $this->count() === 0;
			}

			/**
			 * Rewind the collection array back to the start
			 * 
			 * @return void
			 */
			public function rewind() : void {
				reset($this->items);
			}

			/**
			 * Get object object at current position
			 * 
			 * @return mixed
			 */
			#[\ReturnTypeWillChange]
			public function current() {
				return current($this->items);
			}

			/**
			 * Get current position
			 * 
			 * @return mixed
			 */
			#[\ReturnTypeWillChange]
			public function key() {
				return key($this->items);
			}

			/**
			 * Advance the internal cursor of an array
			 * 
			 * @return mixed
			 */
			#[\ReturnTypeWillChange]
			public function next() : void {
				next($this->items);
			}

			/**
			 * Check whether the collection contains more entries
			 * 
			 * @return bool
			 */
			public function valid() : bool {
				return key($this->items) !== null;
			}

			/**
			 * Determine if an item exists at an offset.
			 *
			 * @param $key
			 * @return bool
			 */
			public function offsetExists($key) : bool {
				return isset($this->items[$key]);
			}

			/**
			 * Get an item at a given offset.
			 *
			 * @param $key
			 * @return mixed
			 */
			#[\ReturnTypeWillChange]
			public function offsetGet($key) : mixed {
				return $this->items[$key];
			}

			/**
			 * Set the item at a given offset.
			 *
			 * @param  mixed $key
			 * @param  $value
			 * @return void
			 */
			public function offsetSet($key, $value) : void {
				if (is_null($key)) {
					$this->items[] = $value;
				} else {
					$this->items[$key] = $value;
				}
			}

			/**
			 * Unset the item at a given offset.
			 *
			 * @param  $key
			 * @return void
			 */
			public function offsetUnset($key) : void {
				unset($this->items[$key]);
			}

			/**
			 * Get all of the items in the collection.
			 *
			 * @return array
			 */
			public function all() {
				return $this->items;
			}
		} 
	}