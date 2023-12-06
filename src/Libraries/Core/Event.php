<?php
namespace Core {
	final class Event {
		/**
		 * @var array An associative array of event listeners.
		 */
		private static array $listeners = [];

		/**
		 * Clear all event listeners
		 * @param string $event the event to remove listeners from, if empty all listeners across all evenets will be removed, default empty string.
		 */
		public static function clear(string $event = '') {
			if($event == '') {
				self::$listeners = [];
			} elseif(isset(self::$listeners[$event])) {
				self::$listeners[$event] = [];
			}
		}
	
		/**
		 * Register an event listener for a specific event.
		 *
		 * @param string $event The event name.
		 * @param callable|array $listener
		 * 	The listener, which can be a callable, string, or class and method array.
		 * 	- If given a callable or closure the event listener will be called as-is and passed event arguments
		 * 	- If given an array the listener will be called in static class context and passed event arguments
		 * 	- If given a string fx. MockEventListener:class the listener will be called in object context and must then implement a 'handle' method.
		 *
		 * @throws \InvalidArgumentException When unsupported invalid listener type is provided.
		 */
		public static function addListener(string $event, callable|string|array $listener) {
			if(is_callable($listener)) {
				self::$listeners[$event][] = $listener;
			} else {
				[$class, $method] = is_array($listener) ? $listener : [$listener, "handle"];

				if(!class_exists($class)) {
					throw new \InvalidArgumentException("Event listener class ".$class." does not exist.");
				}
		
				if(!method_exists($class, $method)) {
					throw new \InvalidArgumentException("Event listener class ".$class." does not implement method '".$method."'.");
				}
		
				self::$listeners[$event][] = $listener;
			}
		}

		/**
		 * Remove a specific event listener for an event.
		 *
		 * @param string $event The event name.
		 * @param callable|string|array $listener The event listener to be removed.
		 * @throws \InvalidArgumentException When the listener does not exist for the event.
		 */
		public static function removeListener(string $event, callable|string|array $listener) {
			if (isset(self::$listeners[$event])) {
				$index = array_search($listener, self::$listeners[$event], true);

				if ($index !== false) {
					unset(self::$listeners[$event][$index]);
				} else {
					throw new \InvalidArgumentException("Listener not found for event '".$event."'.");
				}
			}
		}

		/**
		 * Emit an event and invoke all registered listeners for the event.
		 *
		 * @param string $event The event name.
		 * @param mixed ...$args Arguments to pass to the event listeners.
		 */
		public static function trigger(string $event, ...$args) {
			if(isset(self::$listeners[$event])) {
				foreach(self::$listeners[$event] as $listener) {
					if(is_string($listener) && !is_callable($listener)) {
						$listener = [new $listener, "handle"];
					} elseif (is_array($listener)) {
						$listener = implode('::', $listener);
					}

					$listener(...$args);
				}
			}
		}
	}
}